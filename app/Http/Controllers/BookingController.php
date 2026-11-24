<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Beranda user, Ajukan, Pengajuan Saya — Reno (RS)
class BookingController extends Controller
{
    private const STATUSES = ['pending', 'approved', 'rejected'];

    // Beranda User: pengajuan terbaru + jadwal berikutnya yang sudah disetujui
    public function home(): View
    {
        $user = auth()->user();
        $now = now('Asia/Jakarta');

        $latest = Booking::with('room')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $next = Booking::with('room')
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('tanggal', '>=', $now->toDateString())
            ->orderBy('tanggal')
            ->orderBy('sesi')
            ->first();

        $hour = (int) $now->format('G');
        $greeting = match (true) {
            $hour < 11 => 'Selamat pagi',
            $hour < 15 => 'Selamat siang',
            $hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };

        return view('bookings.home', [
            'greeting' => $greeting,
            'today' => $now,
            'latest' => $latest,
            'next' => $next,
        ]);
    }

    // Formulir Ajukan Peminjaman
    public function create(): View
    {
        $today = now('Asia/Jakarta')->toDateString();

        $rooms = Room::orderBy('gedung')->orderBy('lantai')->orderBy('ruangan')
            ->get(['id', 'nama', 'gedung', 'lantai', 'ruangan', 'kapasitas']);

        // Slot yang sudah disetujui: [room_id => [tanggal => [sesi, ...]]]
        $taken = [];
        Booking::where('status', 'approved')
            ->whereDate('tanggal', '>=', $today)
            ->get(['room_id', 'tanggal', 'sesi'])
            ->each(function ($b) use (&$taken) {
                $taken[$b->room_id][$b->tanggal->toDateString()][] = (int) $b->sesi;
            });

        return view('bookings.create', [
            'rooms' => $rooms,
            'taken' => $taken,
            'sesi' => config('sesi'),
            'today' => $today,
        ]);
    }

    // Simpan pengajuan dengan status awal pending
    public function store(Request $request): RedirectResponse
    {
        $today = now('Asia/Jakarta')->toDateString();

        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'tanggal' => ['required', 'date', 'after_or_equal:'.$today],
            'sesi' => ['required', 'integer', 'in:'.implode(',', array_keys(config('sesi')))],
            'keperluan' => ['required', 'string', 'max:255'],
        ], [
            'room_id.required' => 'Pilih ruangan terlebih dahulu.',
            'room_id.exists' => 'Ruangan yang dipilih tidak ditemukan.',
            'tanggal.required' => 'Tanggal peminjaman wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tanggal.after_or_equal' => 'Tanggal peminjaman tidak boleh di masa lalu.',
            'sesi.required' => 'Pilih salah satu sesi.',
            'sesi.in' => 'Sesi yang dipilih tidak valid.',
            'keperluan.required' => 'Keperluan kegiatan wajib diisi.',
            'keperluan.max' => 'Keperluan maksimal 255 karakter.',
        ]);

        // Sesi yang sudah disetujui untuk kegiatan lain tidak boleh diajukan lagi
        $taken = Booking::where('room_id', $data['room_id'])
            ->whereDate('tanggal', $data['tanggal'])
            ->where('sesi', $data['sesi'])
            ->where('status', 'approved')
            ->exists();

        if ($taken) {
            return back()
                ->withInput()
                ->withErrors(['sesi' => 'Sesi ini sudah disetujui untuk kegiatan lain. Pilih sesi atau ruangan lain.']);
        }

        Booking::create([
            'user_id' => auth()->id(),
            'room_id' => $data['room_id'],
            'tanggal' => $data['tanggal'],
            'sesi' => $data['sesi'],
            'keperluan' => $data['keperluan'],
            'status' => 'pending',
        ]);

        return redirect()->route('bookings.index')
            ->with('success', 'Pengajuan berhasil dikirim dan menunggu persetujuan admin.');
    }

    // Pengajuan Saya: riwayat milik user yang sedang login
    public function index(Request $request): View
    {
        $status = $request->query('status');
        if (! in_array($status, self::STATUSES, true)) {
            $status = null;
        }

        $mine = Booking::where('user_id', auth()->id());

        $counts = (clone $mine)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $bookings = (clone $mine)
            ->with('room')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('bookings.index', [
            'bookings' => $bookings,
            'status' => $status,
            'counts' => [
                'all' => $counts->sum(),
                'pending' => (int) ($counts['pending'] ?? 0),
                'approved' => (int) ($counts['approved'] ?? 0),
                'rejected' => (int) ($counts['rejected'] ?? 0),
            ],
        ]);
    }
}
