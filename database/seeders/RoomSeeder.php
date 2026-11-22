<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        // [nama, gedung, lantai, nomor ruangan, kapasitas] -> tampil sebagai kode, mis. F4.5
        $rooms = [
            ['Ruang Kelas', 'A', 1, 4, 40],
            ['Ruang Kelas', 'F', 2, 4, 48],
            ['Ruang Kelas', 'F', 3, 1, 40],
            ['Ruang Kelas', 'F', 3, 2, 40],
            ['Ruang Kelas', 'F', 3, 3, 40],
            ['Ruang Seminar', 'F', 4, 5, 60],
            ['Lab Jaringan', 'G', 1, 2, 30],
            ['Lab RPL', 'G', 2, 1, 36],
            ['Ruang Rapat', 'Rinov', 3, 1, 20],
            ['Ruang Seminar', 'Rinov', 4, 2, 80],
        ];

        foreach ($rooms as [$nama, $gedung, $lantai, $ruangan, $kapasitas]) {
            Room::updateOrCreate(
                ['gedung' => $gedung, 'lantai' => $lantai, 'ruangan' => $ruangan],
                compact('nama', 'kapasitas')
            );
        }
    }
}
