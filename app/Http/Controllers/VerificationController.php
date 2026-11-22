<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VerificationController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'pending'  => Booking::where('status', 'pending')->count(),
            'approved' => Booking::where('status', 'approved')->count(),
            'rejected' => Booking::where('status', 'rejected')->count(),
            'rooms'    => Room::count(),
        ];

        $recent = Booking::with('user', 'room')->latest('updated_at')->limit(8)->get();

        return view('admin.dashboard', compact('stats', 'recent'));
    }

    public function index(Request $request)
    {
        $tab = $request->query('status', 'menunggu');
        if (! in_array($tab, ['menunggu', 'disetujui', 'ditolak', 'semua'])) {
            $tab = 'menunggu';
        }

        $search = trim((string) $request->query('q', ''));

        $query = Booking::with('user', 'room');

        if ($tab !== 'semua') {
            $query->where('status', $this->mapStatus($tab));
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhere('nama_peminjam', 'like', "%{$search}%")
                  ->orWhereHas('room', function ($r) use ($search) {
                      $s = trim($search);
                      if (preg_match('/^(\D+?)\s*(\d+)\.(\d+)$/', $s, $m)) {
                          $r->where('gedung', $m[1])
                            ->where('lantai', (int) $m[2])
                            ->where('ruangan', (int) $m[3]);
                      } else {
                          $r->where('gedung', 'like', "%{$s}%");
                      }
                  });
            });
        }

        $bookings = $query->orderBy('tanggal', 'desc')->orderBy('sesi')->paginate(8)->withQueryString();

        $counts = [
            'menunggu'  => Booking::where('status', 'pending')->count(),
            'disetujui' => Booking::where('status', 'approved')->count(),
            'ditolak'   => Booking::where('status', 'rejected')->count(),
            'semua'     => Booking::count(),
        ];

        $rooms = Room::orderBy('gedung')->orderBy('lantai')->orderBy('ruangan')
            ->get(['id', 'gedung', 'lantai', 'ruangan', 'kapasitas']);

        return view('admin.verifikasi.index', compact('bookings', 'tab', 'search', 'counts', 'rooms'));
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate(['status' => ['required', 'in:approved,rejected']]);

        if ($data['status'] === 'approved') {
            return DB::transaction(function () use ($booking) {
                if ($this->slotTerpakai($booking->room_id, $booking->tanggal, $booking->sesi, $booking->id)) {
                    return back()->with('error', 'Slot ruangan & sesi ini sudah dipakai pengajuan lain yang disetujui, jadi pengajuan ini tidak bisa disetujui.');
                }

                $this->tolakPendingLain($booking->room_id, $booking->tanggal, $booking->sesi, $booking->id);
                $booking->update(['status' => 'approved']);

                return back()->with('success', "Pengajuan atas nama {$booking->nama_pemohon} berhasil disetujui.");
            });
        }

        $booking->update(['status' => 'rejected']);

        return back()->with('success', "Pengajuan atas nama {$booking->nama_pemohon} berhasil ditolak.");
    }

    public function storeManual(Request $request)
    {
        $data = $request->validate([
            'gedung'          => ['required', 'string'],
            'lantai'          => ['required', 'integer', 'min:1'],
            'ruangan'         => ['required', 'integer', 'min:1'],
            'tanggal'         => ['required', 'date', 'after_or_equal:today'],
            'sesi'            => ['required', 'integer', Rule::in(array_keys(config('sesi')))],
            'nama_peminjam'   => ['required', 'string', 'max:100'],
            'kontak_peminjam' => ['nullable', 'string', 'max:100'],
            'keperluan'       => ['required', 'string', 'max:255'],
        ]);

        $room = Room::where('gedung', $data['gedung'])
            ->where('lantai', (int) $data['lantai'])
            ->where('ruangan', (int) $data['ruangan'])
            ->first();

        if (! $room) {
            return back()->withErrors([
                'ruangan' => "Ruangan {$data['gedung']} {$data['lantai']}.{$data['ruangan']} tidak ditemukan.",
            ])->withInput();
        }

        return DB::transaction(function () use ($data, $room) {
            if ($this->slotTerpakai($room->id, $data['tanggal'], (int) $data['sesi'])) {
                return back()->withErrors(['sesi' => 'Slot ini sudah terpakai. Pilih sesi atau ruangan lain.'])->withInput();
            }

            $booking = Booking::create([
                'user_id'         => auth()->id(),
                'room_id'         => $room->id,
                'tanggal'         => $data['tanggal'],
                'sesi'            => (int) $data['sesi'],
                'keperluan'       => $data['keperluan'],
                'status'          => 'approved',
                'nama_peminjam'   => $data['nama_peminjam'],
                'kontak_peminjam' => $data['kontak_peminjam'] ?? null,
            ]);

            $this->tolakPendingLain($room->id, $data['tanggal'], (int) $data['sesi'], $booking->id);

            return redirect()->route('admin.verifications.index', ['status' => 'disetujui'])
                ->with('success', "Slot {$room->kode} Sesi {$booking->sesi} (" . $this->tanggalIndo($booking->tanggal) . ") ditandai terpakai untuk {$data['nama_peminjam']}.");
        });
    }

    public function usedSessions(Request $request)
    {
        $room = Room::where('gedung', $request->query('gedung'))
            ->where('lantai', (int) $request->query('lantai'))
            ->where('ruangan', (int) $request->query('ruangan'))
            ->first();

        if (! $room) {
            return response()->json(['ditemukan' => false, 'nama' => null, 'kapasitas' => null, 'terpakai' => []]);
        }

        $terpakai = [];
        $tanggal = $request->query('tanggal');
        if ($tanggal && strtotime($tanggal) !== false) {
            $terpakai = Booking::where('room_id', $room->id)
                ->whereDate('tanggal', $tanggal)
                ->where('status', 'approved')
                ->orderBy('sesi')
                ->pluck('sesi')
                ->map(fn ($s) => (int) $s)
                ->values()
                ->all();
        }

        return response()->json([
            'ditemukan' => true,
            'nama'      => $room->kode,
            'kapasitas' => (int) $room->kapasitas,
            'terpakai'  => $terpakai,
        ]);
    }

    private function slotTerpakai(int $roomId, $tanggal, int $sesi, ?int $exceptId = null): bool
    {
        return Booking::where('room_id', $roomId)
            ->whereDate('tanggal', $tanggal)
            ->where('sesi', $sesi)
            ->where('status', 'approved')
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists();
    }

    private function tolakPendingLain(int $roomId, $tanggal, int $sesi, int $exceptId): void
    {
        Booking::where('room_id', $roomId)
            ->whereDate('tanggal', $tanggal)
            ->where('sesi', $sesi)
            ->where('status', 'pending')
            ->where('id', '!=', $exceptId)
            ->update(['status' => 'rejected']);
    }

    private function mapStatus(string $tab): string
    {
        return ['menunggu' => 'pending', 'disetujui' => 'approved', 'ditolak' => 'rejected'][$tab];
    }

    private function tanggalIndo($tanggal): string
    {
        $hari  = ['Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab','Sun'=>'Min'];
        $bulan = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        return $hari[$tanggal->format('D')] . ', ' . $tanggal->format('j') . ' ' . $bulan[$tanggal->month] . ' ' . $tanggal->format('Y');
    }
}