<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    private const TZ = 'Asia/Jakarta';

    public function index(Request $request)
    {
        $sekarang = Carbon::now(self::TZ);
        $tanggal = $this->parseTanggal($request->query('tanggal'), $sekarang);
        $sesi = config('sesi');

        // Filter Ruang = isian nomor (angka bebas), hanya berlaku jika gedung & lantai sudah dipilih
        $bisaPilihRuang = $request->filled('gedung') && $request->filled('lantai');
        $nilaiRuang = (string) $request->query('ruang');
        $ruang = $bisaPilihRuang && ctype_digit($nilaiRuang) && (int) $nilaiRuang > 0 ? (int) $nilaiRuang : null;

        // Hanya pengajuan berstatus approved pada tanggal terpilih
        $rooms = Room::query()
            ->with(['bookings' => fn ($q) => $q
                ->where('status', 'approved')
                ->whereDate('tanggal', $tanggal->toDateString())])
            ->when($request->filled('gedung'), fn ($q) => $q->where('gedung', $request->query('gedung')))
            ->when($request->filled('lantai'), fn ($q) => $q->where('lantai', $request->query('lantai')))
            ->when($ruang !== null, fn ($q) => $q->where('ruangan', $ruang))
            ->orderBy('gedung')->orderBy('lantai')->orderBy('ruangan')
            ->get();

        // $slots[room_id][no_sesi] = ['status' => terpakai|lewat|tersedia, 'label' => ...]
        $slots = [];
        foreach ($rooms as $room) {
            $booking = $room->bookings->keyBy('sesi');

            foreach ($sesi as $no => $s) {
                $akhir = $tanggal->copy()->setTimeFromTimeString(str_replace('.', ':', $s['selesai']));

                if ($b = $booking->get($no)) {
                    $slots[$room->id][$no] = ['status' => 'terpakai', 'label' => $b->keperluan];
                } elseif ($akhir->lt($sekarang)) {
                    $slots[$room->id][$no] = ['status' => 'lewat', 'label' => 'Lewat'];
                } else {
                    $slots[$room->id][$no] = ['status' => 'tersedia', 'label' => 'Tersedia'];
                }
            }
        }

        // Data untuk dropdown filter
        $gedungList = Room::select('gedung')->distinct()->orderBy('gedung')->pluck('gedung');
        $lantaiList = Room::select('lantai')->distinct()->orderBy('lantai')->pluck('lantai');

        $tanggalLabel = $tanggal->copy()->locale('id')->translatedFormat('l, j F Y');

        return view('schedule.index', compact(
            'rooms', 'slots', 'sesi', 'tanggal', 'tanggalLabel', 'gedungList', 'lantaiList', 'ruang', 'bisaPilihRuang'
        ));
    }

    private function parseTanggal(?string $nilai, Carbon $default): Carbon
    {
        if ($nilai) {
            try {
                return Carbon::createFromFormat('Y-m-d', $nilai, self::TZ)->startOfDay();
            } catch (\Throwable $e) {
                // tanggal tidak valid → pakai hari ini
            }
        }

        return $default->copy()->startOfDay();
    }
}
