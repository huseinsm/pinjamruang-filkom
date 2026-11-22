<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Nomor ruangan hanya angka; kode tampil (F4.5) dibentuk dari gedung + lantai + nomor
class RuanganTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        $user = new User(['name' => 'Admin Sarpras', 'email' => $role.'@ub.ac.id', 'password' => 'rahasia123']);
        $user->role = $role;
        $user->save();

        return $user;
    }

    private function room(string $gedung, int $lantai, int $nomor, string $nama = 'Ruang Kelas'): Room
    {
        return Room::create(['nama' => $nama, 'gedung' => $gedung, 'lantai' => $lantai, 'ruangan' => $nomor, 'kapasitas' => 40]);
    }

    public function test_kode_ruangan_dibentuk_dari_gedung_lantai_dan_nomor(): void
    {
        $this->assertSame('F4.5', $this->room('F', 4, 5)->kode);
        $this->assertSame('Rinov 3.1', $this->room('Rinov', 3, 1)->kode);
    }

    public function test_admin_menyimpan_nomor_ruangan_sebagai_angka(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post(route('admin.ruangan.store'), ['nama' => 'Ruang Seminar', 'gedung' => 'F', 'lantai' => 4, 'ruangan' => 5, 'kapasitas' => 60])
            ->assertRedirect(route('admin.ruangan.index'));

        $this->assertDatabaseHas('rooms', ['gedung' => 'F', 'lantai' => 4, 'ruangan' => 5]);
    }

    public function test_nomor_ruangan_harus_angka(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post(route('admin.ruangan.store'), ['nama' => 'Ruang Seminar', 'gedung' => 'F', 'lantai' => 4, 'ruangan' => '4.5', 'kapasitas' => 60])
            ->assertSessionHasErrors('ruangan');
    }

    public function test_nomor_sama_boleh_di_lantai_lain_tapi_tidak_di_lantai_yang_sama(): void
    {
        $this->room('F', 3, 1);
        $admin = $this->makeUser('admin');
        $data = ['nama' => 'Ruang Kelas', 'gedung' => 'F', 'ruangan' => 1, 'kapasitas' => 40];

        $this->actingAs($admin)->post(route('admin.ruangan.store'), $data + ['lantai' => 4])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.ruangan.store'), $data + ['lantai' => 3])->assertSessionHasErrors('ruangan');
    }

    public function test_tabel_kelola_menampilkan_nomor_dan_bisa_dicari_dengan_kode(): void
    {
        $this->room('F', 4, 5, 'Ruang Seminar');
        $this->room('G', 1, 2, 'Lab Jaringan');
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get(route('admin.ruangan.index', ['q' => 'F4.5']))
            ->assertOk()->assertSee('<td>5</td>', false)->assertSee('Ruang Seminar')->assertDontSee('Lab Jaringan');

        $this->actingAs($admin)->get(route('admin.ruangan.index', ['q' => '2']))
            ->assertOk()->assertSee('Lab Jaringan')->assertDontSee('Ruang Seminar');
    }

    public function test_filter_ruang_di_jadwal_berupa_angka_dan_bertingkat(): void
    {
        $this->room('F', 3, 1);
        $this->room('F', 3, 2);
        $this->room('G', 2, 1);
        $user = $this->makeUser('user');

        // Belum pilih gedung & lantai: filter Ruang nonaktif dan diabaikan
        $this->actingAs($user)->get(route('schedule.index', ['ruang' => 1]))
            ->assertOk()->assertSee('Pilih gedung &amp; lantai dulu', false)
            ->assertSee('F3.1')->assertSee('F3.2')->assertSee('G2.1');

        // Gedung F lantai 3, ruang 1
        $this->actingAs($user)->get(route('schedule.index', ['gedung' => 'F', 'lantai' => 3, 'ruang' => 1]))
            ->assertOk()->assertSee('F3.1')->assertDontSee('F3.2')->assertDontSee('G2.1');
    }
}
