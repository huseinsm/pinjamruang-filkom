<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;

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

        $recent = Booking::with('user', 'room')
            ->latest('updated_at')
            ->limit(8)
            ->get();

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
                  ->orWhereHas('room', fn ($r) => $r->where('nama', 'like', "%{$search}%")
                      ->orWhere('gedung', 'like', "%{$search}%"));
            });
        }

        $bookings = $query->orderBy('tanggal', 'desc')->orderBy('sesi')->paginate(8)->withQueryString();

        $counts = [
            'menunggu'  => Booking::where('status', 'pending')->count(),
            'disetujui' => Booking::where('status', 'approved')->count(),
            'ditolak'   => Booking::where('status', 'rejected')->count(),
            'semua'     => Booking::count(),
        ];

        return view('admin.verifikasi.index', compact('bookings', 'tab', 'search', 'counts'));
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        if ($data['status'] === 'approved') {
            $bentrok = Booking::where('id', '!=', $booking->id)
                ->where('room_id', $booking->room_id)
                ->where('tanggal', $booking->tanggal)
                ->where('sesi', $booking->sesi)
                ->where('status', 'approved')
                ->exists();

            if ($bentrok) {
                return back()->with('error', 'Slot ruangan & sesi ini sudah dipakai pengajuan lain yang disetujui, jadi pengajuan ini tidak bisa disetujui.');
            }

            Booking::where('id', '!=', $booking->id)
                ->where('room_id', $booking->room_id)
                ->where('tanggal', $booking->tanggal)
                ->where('sesi', $booking->sesi)
                ->where('status', 'pending')
                ->each(fn (Booking $lain) => $lain->update(['status' => 'rejected']));
        }

        $booking->update(['status' => $data['status']]); // updated_at = waktu diproses

        $label = $data['status'] === 'approved' ? 'disetujui' : 'ditolak';

        return back()->with('success', "Pengajuan atas nama {$booking->user->name} berhasil {$label}.");
    }

    private function mapStatus(string $tab): string
    {
        return ['menunggu' => 'pending', 'disetujui' => 'approved', 'ditolak' => 'rejected'][$tab];
    }
}