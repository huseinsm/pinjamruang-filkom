<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Nama ruangan = gedung + lantai + nomor, mis. "F 2.4" = gedung F, lantai 2, ruangan nomor 4
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

    private function room(string $gedung, int $lantai, int $nomor): Room
    {
        return Room::create(['gedung' => $gedung, 'lantai' => $lantai, 'ruangan' => $nomor, 'kapasitas' => 40]);
    }

    public function test_nama_ruangan_dibentuk_dari_gedung_lantai_dan_nomor(): void
    {
        $this->assertSame('F 2.4', $this->room('F', 2, 4)->kode);
        $this->assertSame('Rinov 3.1', $this->room('Rinov', 3, 1)->nama);
    }

    public function test_admin_menambah_ruangan_tanpa_mengetik_nama(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post(route('admin.ruangan.store'), ['gedung' => 'F', 'lantai' => 4, 'ruangan' => 5, 'kapasitas' => 60])
            ->assertRedirect(route('admin.ruangan.index'))
            ->assertSessionHas('success', 'Ruangan F 4.5 berhasil ditambahkan.');

        $this->assertDatabaseHas('rooms', ['gedung' => 'F', 'lantai' => 4, 'ruangan' => 5]);
    }

    public function test_nomor_ruangan_harus_angka_tanpa_batas_15(): void
    {
        $admin = $this->makeUser('admin');
        $data = ['gedung' => 'F', 'lantai' => 4, 'kapasitas' => 60];

        $this->actingAs($admin)->post(route('admin.ruangan.store'), $data + ['ruangan' => '4.5'])->assertSessionHasErrors('ruangan');
        $this->actingAs($admin)->post(route('admin.ruangan.store'), $data + ['ruangan' => 0])->assertSessionHasErrors('ruangan');
        $this->actingAs($admin)->post(route('admin.ruangan.store'), $data + ['ruangan' => 21])->assertSessionHasNoErrors();
    }

    public function test_nomor_sama_boleh_di_lantai_lain_tapi_tidak_di_lantai_yang_sama(): void
    {
        $this->room('F', 3, 1);
        $admin = $this->makeUser('admin');
        $data = ['gedung' => 'F', 'ruangan' => 1, 'kapasitas' => 40];

        $this->actingAs($admin)->post(route('admin.ruangan.store'), $data + ['lantai' => 4])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.ruangan.store'), $data + ['lantai' => 3])->assertSessionHasErrors('ruangan');
    }

    public function test_tabel_kelola_menampilkan_nama_lengkap_dan_nomor_serta_bisa_dicari(): void
    {
        $this->room('F', 4, 5);
        $this->room('G', 1, 2);
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get(route('admin.ruangan.index', ['q' => 'F 4.5']))
            ->assertOk()->assertSee('F 4.5')->assertSee('<td>5</td>', false)->assertDontSee('G 1.2');

        $this->actingAs($admin)->get(route('admin.ruangan.index', ['q' => 'F4.5']))
            ->assertOk()->assertSee('F 4.5')->assertDontSee('G 1.2');

        $this->actingAs($admin)->get(route('admin.ruangan.index', ['q' => '2']))
            ->assertOk()->assertSee('G 1.2')->assertDontSee('F 4.5');
    }

    public function test_filter_ruang_di_jadwal_berupa_isian_angka_dan_bertingkat(): void
    {
        $this->room('F', 3, 1);
        $this->room('F', 3, 2);
        $this->room('G', 2, 1);
        $user = $this->makeUser('user');

        // Belum pilih gedung & lantai: isian Ruang nonaktif dan diabaikan
        $this->actingAs($user)->get(route('schedule.index', ['ruang' => 1]))
            ->assertOk()->assertSee('type="number" id="fRuang"', false)->assertSee('Pilih gedung &amp; lantai dulu', false)
            ->assertSee('F 3.1')->assertSee('F 3.2')->assertSee('G 2.1');

        // Gedung F lantai 3, ruang 1
        $this->actingAs($user)->get(route('schedule.index', ['gedung' => 'F', 'lantai' => 3, 'ruang' => 1]))
            ->assertOk()->assertSee('F 3.1')->assertDontSee('F 3.2')->assertDontSee('G 2.1');
    }
}
