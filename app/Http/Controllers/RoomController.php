<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    // READ: daftar + cari (nama / nomor) + filter gedung & lantai
    public function index(Request $request)
    {
        $rooms = Room::query()
            ->withCount('bookings')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = trim($request->input('q'));
                $query->where(function ($w) use ($q) {
                    $w->where('nama', 'like', "%{$q}%")
                      ->orWhere('ruangan', 'like', "%{$q}%");
                });
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
        $request->merge([
            'nama'    => trim((string) $request->input('nama')),
            'gedung'  => trim((string) $request->input('gedung')),
            'ruangan' => trim((string) $request->input('ruangan')),
        ]);

        return $request->validate([
            'nama'      => ['required', 'string', 'max:100'],
            'gedung'    => ['required', 'string', 'max:30'],
            'lantai'    => ['required', 'integer', 'min:1', 'max:20'],
            'ruangan'   => [
                'required', 'string', 'max:10',
                // nomor ruangan unik per gedung (sesuai unique di migration)
                Rule::unique('rooms', 'ruangan')
                    ->where(fn ($q) => $q->where('gedung', $request->input('gedung')))
                    ->ignore($room?->id),
            ],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:1000'],
        ], [
            'required'       => ':attribute wajib diisi.',
            'integer'        => ':attribute harus berupa angka bulat.',
            'max.string'     => ':attribute maksimal :max karakter.',
            'max.numeric'    => ':attribute maksimal :max.',
            'min.numeric'    => ':attribute minimal :min.',
            'ruangan.unique' => 'Nomor ruangan ini sudah dipakai di gedung tersebut.',
        ], [
            'nama'      => 'Nama ruangan',
            'gedung'    => 'Gedung',
            'lantai'    => 'Lantai',
            'ruangan'   => 'Nomor ruangan',
            'kapasitas' => 'Kapasitas',
        ]);
    }
}