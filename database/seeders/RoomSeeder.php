<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        // [gedung, lantai, nomor ruangan, kapasitas] -> tampil sebagai "F 4.5"
        $rooms = [
            ['A', 1, 4, 40],
            ['F', 2, 4, 48],
            ['F', 3, 1, 40],
            ['F', 3, 2, 40],
            ['F', 3, 3, 40],
            ['F', 4, 5, 60],
            ['G', 1, 2, 30],
            ['G', 2, 1, 36],
            ['Rinov', 3, 1, 20],
            ['Rinov', 4, 2, 80],
        ];

        foreach ($rooms as [$gedung, $lantai, $ruangan, $kapasitas]) {
            Room::updateOrCreate(
                ['gedung' => $gedung, 'lantai' => $lantai, 'ruangan' => $ruangan],
                compact('kapasitas')
            );
        }
    }
}
