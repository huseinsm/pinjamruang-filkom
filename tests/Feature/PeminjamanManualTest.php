<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanManualTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $u = User::factory()->create();
        $u->forceFill(['role' => 'admin'])->save();
        return $u;
    }

    private function room(): Room
    {
        return Room::create(['gedung' => 'G', 'lantai' => 1, 'ruangan' => 2, 'kapasitas' => 40]);
    }

    private function payload(Room $room, array $ubah = []): array
    {
        return array_merge([
            'form'            => 'manual',
            'gedung'          => $room->gedung,
            'lantai'          => $room->lantai,
            'ruangan'         => $room->ruangan,
            'tanggal'         => now()->addDay()->format('Y-m-d'),
            'sesi'            => 2,
            'nama_peminjam'   => 'Himpunan Mahasiswa TI',
            'kontak_peminjam' => '081234567890',
            'keperluan'       => 'Rapat kerja himpunan',
        ], $ubah);
    }

    public function test_admin_bisa_mencatat_slot_kosong(): void
    {
        $admin = $this->admin();
        $room  = $this->room();

        $this->actingAs($admin)
            ->post(route('admin.verifications.manual'), $this->payload($room))
            ->assertRedirect(route('admin.verifications.index', ['status' => 'disetujui']));

        $this->assertDatabaseHas('bookings', [
            'room_id'       => $room->id,
            'user_id'       => $admin->id,
            'nama_peminjam' => 'Himpunan Mahasiswa TI',
            'status'        => 'approved',
        ]);
    }

    public function test_pending_lain_di_slot_sama_otomatis_ditolak(): void
    {
        $admin = $this->admin();
        $room  = $this->room();
        $tgl   = now()->addDay()->format('Y-m-d');

        $b1 = Booking::create(['user_id' => User::factory()->create()->id, 'room_id' => $room->id, 'tanggal' => $tgl, 'sesi' => 2, 'keperluan' => 'A', 'status' => 'pending']);
        $b2 = Booking::create(['user_id' => User::factory()->create()->id, 'room_id' => $room->id, 'tanggal' => $tgl, 'sesi' => 2, 'keperluan' => 'B', 'status' => 'pending']);

        $this->actingAs($admin)->post(route('admin.verifications.manual'), $this->payload($room));

        $this->assertDatabaseHas('bookings', ['id' => $b1->id, 'status' => 'rejected']);
        $this->assertDatabaseHas('bookings', ['id' => $b2->id, 'status' => 'rejected']);
        $this->assertEquals(1, Booking::where('status', 'approved')->count());
    }

    public function test_slot_sudah_approved_menghasilkan_error_sesi(): void
    {
        $admin = $this->admin();
        $room  = $this->room();
        $tgl   = now()->addDay()->format('Y-m-d');

        Booking::create(['user_id' => User::factory()->create()->id, 'room_id' => $room->id, 'tanggal' => $tgl, 'sesi' => 2, 'keperluan' => 'Sudah ada', 'status' => 'approved']);

        $this->actingAs($admin)
            ->post(route('admin.verifications.manual'), $this->payload($room))
            ->assertSessionHasErrors('sesi');

        $this->assertEquals(1, Booking::count());
    }

    public function test_tanggal_kemarin_ditolak_validasi(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.verifications.manual'), $this->payload($this->room(), ['tanggal' => now()->subDay()->format('Y-m-d')]))
            ->assertSessionHasErrors('tanggal');
    }

    public function test_nomor_ruangan_tidak_ada_ditolak(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.verifications.manual'), $this->payload($this->room(), ['ruangan' => 99]))
            ->assertSessionHasErrors('ruangan');
    }

    public function test_pencarian_kode_ruangan_menemukan_booking(): void
    {
        $room = $this->room();
        $user = User::factory()->create(['name' => 'Alya Putri']);
        Booking::create(['user_id' => $user->id, 'room_id' => $room->id, 'tanggal' => now()->addDay()->format('Y-m-d'), 'sesi' => 1, 'keperluan' => 'Latihan', 'status' => 'pending']);

        $this->actingAs($this->admin())
            ->get(route('admin.verifications.index', ['status' => 'semua', 'q' => 'G 1.2']))
            ->assertOk()
            ->assertSee('Alya Putri');
    }

    public function test_non_admin_tidak_bisa_mencatat_manual(): void
    {
        $user = User::factory()->create();
        $res  = $this->actingAs($user)->post(route('admin.verifications.manual'), $this->payload($this->room()));

        $this->assertTrue(in_array($res->status(), [302, 403]));
        $this->assertDatabaseMissing('bookings', ['nama_peminjam' => 'Himpunan Mahasiswa TI']);
    }

    public function test_slot_terpakai_mengembalikan_sesi_yang_benar(): void
    {
        $room = $this->room();
        $tgl  = now()->addDay()->format('Y-m-d');
        foreach ([2, 3] as $s) {
            Booking::create(['user_id' => User::factory()->create()->id, 'room_id' => $room->id, 'tanggal' => $tgl, 'sesi' => $s, 'keperluan' => 'X', 'status' => 'approved']);
        }

        $this->actingAs($this->admin())
            ->getJson(route('admin.verifications.used', ['gedung' => 'G', 'lantai' => 1, 'ruangan' => 2, 'tanggal' => $tgl]))
            ->assertOk()
            ->assertJson(['ditemukan' => true, 'nama' => 'G 1.2', 'kapasitas' => 40, 'terpakai' => [2, 3]]);
    }
}