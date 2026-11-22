<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    // READ: daftar + cari (nama "F 4.5" / nomor / gedung) + filter gedung & lantai
    public function index(Request $request)
    {
        $rooms = Room::query()
            ->withCount('bookings')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = trim($request->input('q'));

                if (ctype_digit($q)) {
                    // angka saja -> nomor ruangan
                    $query->where('ruangan', (int) $q);
                } elseif (preg_match('/^(\D+?)\s*(\d+)\.(\d+)$/u', $q, $m)) {
                    // nama lengkap, mis. "F 4.5", "F4.5", "Rinov 3.1"
                    $query->where('gedung', trim($m[1]))->where('lantai', (int) $m[2])->where('ruangan', (int) $m[3]);
                } else {
                    $query->where('gedung', 'like', "%{$q}%");
                }
            })
            ->when($request->filled('gedung'), fn ($query) => $query->where('gedung', $request->input('gedung')))
            ->when($request->filled('lantai'), fn ($query) => $query->where('lantai', $request->input('lantai')))
            ->orderBy('gedung')->orderBy('lantai')->orderBy('ruangan')
            ->paginate(8)
            ->withQueryString();

        $gedungList = Room::select('gedung')->distinct()->orderBy('gedung')->pluck('gedung');
        $lantaiList = Room::select('lantai')->distinct()->orderBy('lantai')->pluck('lantai');
        $total      = Room::count();

        return view('admin.ruangan.index', compact('rooms', 'gedungList', 'lantaiList', 'total'));
    }

    // Form tambah/ubah memakai modal, jadi create & edit diarahkan ke daftar
    public function create(): RedirectResponse
    {
        return redirect()->route('admin.ruangan.index');
    }

    public function edit(Room $room): RedirectResponse
    {
        return redirect()->route('admin.ruangan.index');
    }

    // CREATE
    public function store(Request $request): RedirectResponse
    {
        $room = Room::create($this->validated($request));

        return redirect()->route('admin.ruangan.index')
            ->with('success', "Ruangan {$room->nama} berhasil ditambahkan.");
    }

    // UPDATE
    public function update(Request $request, Room $room): RedirectResponse
    {
        $room->update($this->validated($request, $room));

        return redirect()->route('admin.ruangan.index')
            ->with('success', "Ruangan {$room->nama} berhasil diperbarui.");
    }

    // DELETE (pengajuan milik ruangan ini ikut terhapus karena cascadeOnDelete)
    public function destroy(Room $room): RedirectResponse
    {
        $nama = $room->nama;
        $room->delete();

        return redirect()->route('admin.ruangan.index')
            ->with('success', "Ruangan {$nama} berhasil dihapus.");
    }

    private function validated(Request $request, ?Room $room = null): array
    {
        $request->merge(['gedung' => trim((string) $request->input('gedung'))]);

        return $request->validate([
            'gedung'    => ['required', 'string', 'max:30'],
            'lantai'    => ['required', 'integer', 'min:1', 'max:20'],
            'ruangan'   => [
                // tanpa batas khusus; 65535 hanya batas tipe kolom (unsignedSmallInteger)
                'required', 'integer', 'min:1', 'max:65535',
                // nomor ruangan unik per gedung + lantai (sesuai unique di migration)
                Rule::unique('rooms', 'ruangan')
                    ->where(fn ($q) => $q->where('gedung', $request->input('gedung'))
                        ->where('lantai', $request->input('lantai')))
                    ->ignore($room?->id),
            ],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:1000'],
        ], [
            'required'       => ':attribute wajib diisi.',
            'integer'        => ':attribute harus berupa angka bulat.',
            'max.string'     => ':attribute maksimal :max karakter.',
            'max.numeric'    => ':attribute maksimal :max.',
            'min.numeric'    => ':attribute minimal :min.',
            'ruangan.unique' => 'Nomor ruangan ini sudah dipakai di gedung dan lantai tersebut.',
        ], [
            'gedung'    => 'Gedung',
            'lantai'    => 'Lantai',
            'ruangan'   => 'Nomor ruangan',
            'kapasitas' => 'Kapasitas',
        ]);
    }
}