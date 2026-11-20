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

        // Hanya pengajuan berstatus approved pada tanggal terpilih
        $rooms = Room::query()
            ->with(['bookings' => fn ($q) => $q
                ->where('status', 'approved')
                ->whereDate('tanggal', $tanggal->toDateString())])
            ->when($request->filled('gedung'), fn ($q) => $q->where('gedung', $request->query('gedung')))
            ->when($request->filled('lantai'), fn ($q) => $q->where('lantai', $request->query('lantai')))
            ->when($request->filled('ruang'), fn ($q) => $q->where('id', $request->query('ruang')))
            ->orderBy('gedung')->orderBy('lantai')->orderBy('ruangan')
            ->get();

        // $slots[room_id][no_sesi] = ['status' => terpakai|lewat|tersedia, 'label' => ...]
        $slots = [];
        foreach ($rooms as $room) {
            $room->setAttribute('kode', $this->kode($room));
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
        $opsiRuang = Room::orderBy('gedung')->orderBy('lantai')->orderBy('ruangan')->get()
            ->map(fn (Room $r) => ['id' => $r->id, 'label' => $this->kode($r).' · '.$r->nama]);

        $tanggalLabel = $tanggal->copy()->locale('id')->translatedFormat('l, j F Y');

        return view('schedule.index', compact(
            'rooms', 'slots', 'sesi', 'tanggal', 'tanggalLabel', 'gedungList', 'lantaiList', 'opsiRuang'
        ));
    }

    // "A1.4" untuk gedung satu huruf, "Rinov 3.1" untuk gedung bernama panjang
    private function kode(Room $room): string
    {
        return mb_strlen($room->gedung) > 1
            ? "{$room->gedung} {$room->ruangan}"
            : "{$room->gedung}{$room->ruangan}";
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
