<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $roomIds = Room::pluck('id');

        if ($roomIds->isEmpty()) {
            $this->command->warn('Ruangan kosong. Jalankan: php artisan db:seed --class=RoomSeeder');
            return;
        }

        $orang = [
            ['name' => 'Alya Putri',   'email' => 'alya.putri@student.ub.ac.id'],
            ['name' => 'Nadia Kusuma', 'email' => 'nadia.k@student.ub.ac.id'],
            ['name' => 'Bima Saputra', 'email' => 'bima.saputra@student.ub.ac.id'],
            ['name' => 'Raka Pratama', 'email' => 'raka.p@student.ub.ac.id'],
        ];

        foreach ($orang as $i => $o) {
            $users[$i] = User::firstOrCreate(['email' => $o['email']], [
                'name' => $o['name'],
                'password' => 'password123',
            ]);
        }

        $data = [
            [0, 0, '2026-11-18', 3, 'Latihan tim Gemastik', 'approved'],
            [1, 1, '2026-11-19', 3, 'Rapat pleno BEM FILKOM', 'approved'],
            [2, 2, '2026-11-19', 1, 'Kelas pengganti Basis Data', 'approved'],
            [3, 3, '2026-11-20', 2, 'Asistensi praktikum PBO', 'approved'],
            [2, 3, '2026-11-20', 2, 'Try out persiapan CP', 'pending'],      // bentrok dgn approved → uji pesan error
            [0, 1, '2026-11-21', 1, 'Briefing panitia hackathon', 'pending'],
            [1, 2, '2026-11-21', 2, 'Study club UI/UX', 'pending'],
            [2, 0, '2026-11-22', 4, 'Simulasi kompetisi CP', 'pending'],
            [0, 2, '2026-11-23', 3, 'Rapat divisi media', 'pending'],        // satu slot dua pending → uji auto-tolak
            [1, 2, '2026-11-23', 3, 'Kelas inspirasi himpunan', 'pending'],
            [3, 0, '2026-11-17', 1, 'Latihan debat mahasiswa', 'rejected'],
        ];

        foreach ($data as [$u, $r, $tanggal, $sesi, $keperluan, $status]) {
            Booking::create([
                'user_id'   => $users[$u]->id,
                'room_id'   => $roomIds[$r] ?? $roomIds[0],
                'tanggal'   => $tanggal,
                'sesi'      => $sesi,
                'keperluan' => $keperluan,
                'status'    => $status,
            ]);
        }
    }
}