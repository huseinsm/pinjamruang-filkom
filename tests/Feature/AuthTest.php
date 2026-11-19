<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

// Fitur auth & profil — Husein (HSM)
class AuthTest extends TestCase
{
    use RefreshDatabase;

    // Tujuan redirect setelah login: route milik anggota lain jika sudah ada, kalau belum ke /profil.
    private function home(string $routeName): string
    {
        return Route::has($routeName) ? route($routeName) : route('profile.edit');
    }

    private function makeUser(string $role = 'user', array $attrs = []): User
    {
        $user = new User(array_merge([
            'name' => 'Alya Putri Rahmadani',
            'email' => 'alya@student.ub.ac.id',
            'password' => 'rahasia123',
        ], $attrs));
        $user->role = $role;
        $user->save();

        return $user;
    }

    public function test_halaman_publik_bisa_dibuka(): void
    {
        $this->get('/')->assertOk()->assertSee('Pinjam ruang kuliah tanpa bolak-balik ke loket.');
        $this->get('/login')->assertOk()->assertSee('Gunakan email dan password yang sudah terdaftar.');
        $this->get('/register')->assertOk()->assertSee('Buat akun');
    }

    public function test_register_membuat_akun_dengan_role_user(): void
    {
        $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@student.ub.ac.id',
            'password' => 'password1',
            'password_confirmation' => 'password1',
            'role' => 'admin', // harus diabaikan
        ])->assertRedirect($this->home('home'));

        $user = User::where('email', 'budi@student.ub.ac.id')->firstOrFail();
        $this->assertSame('user', $user->fresh()->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_register_menolak_password_tanpa_angka_dan_email_ganda(): void
    {
        $this->makeUser();

        $this->from('/register')->post('/register', [
            'name' => 'Alya',
            'email' => 'alya@student.ub.ac.id',
            'password' => 'hanyahuruf',
            'password_confirmation' => 'hanyahuruf',
        ])->assertRedirect('/register')->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_login_mengarahkan_sesuai_role(): void
    {
        $this->makeUser();
        $this->post('/login', ['email' => 'alya@student.ub.ac.id', 'password' => 'rahasia123'])
            ->assertRedirect($this->home('home'));

        $this->post('/logout');

        $this->makeUser('admin', ['email' => 'admin@filkom.ub.ac.id']);
        $this->post('/login', ['email' => 'admin@filkom.ub.ac.id', 'password' => 'rahasia123'])
            ->assertRedirect($this->home('admin.dashboard'));
    }

    public function test_login_gagal_dengan_password_salah(): void
    {
        $this->makeUser();

        $this->from('/login')->post('/login', ['email' => 'alya@student.ub.ac.id', 'password' => 'salah123'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Email atau password salah.']);
        $this->assertGuest();
    }

    public function test_user_yang_sudah_login_tidak_bisa_buka_halaman_login(): void
    {
        $this->actingAs($this->makeUser())->get('/login')->assertRedirect($this->home('home'));
        $this->actingAs($this->makeUser('admin', ['email' => 'a@filkom.ub.ac.id']))->get('/register')->assertRedirect($this->home('admin.dashboard'));
    }

    public function test_logout(): void
    {
        $this->actingAs($this->makeUser())->post('/logout')->assertRedirect(route('landing'));
        $this->assertGuest();
    }

    public function test_profil_butuh_login_dan_tampil_untuk_user_dan_admin(): void
    {
        $this->get('/profil')->assertRedirect(route('login'));

        $this->actingAs($this->makeUser())->get('/profil')
            ->assertOk()->assertSee('Nama dan email ditampilkan pada setiap pengajuan.')->assertSee('Pengguna');

        $this->actingAs($this->makeUser('admin', ['email' => 'admin@filkom.ub.ac.id', 'name' => 'Admin Sarpras']))->get('/profil')
            ->assertOk()->assertSee('Nama dan email ditampilkan di panel admin.')->assertSee('Panel Admin');
    }

    public function test_update_informasi_akun(): void
    {
        $user = $this->makeUser();
        $this->makeUser('user', ['email' => 'lain@student.ub.ac.id']);

        $this->actingAs($user)->from('/profil')->put('/profil', ['name' => 'X', 'email' => 'lain@student.ub.ac.id'])
            ->assertSessionHasErrorsIn('profile', ['email']);

        $this->actingAs($user)->put('/profil', ['name' => 'Alya P.', 'email' => 'alya.p@student.ub.ac.id'])
            ->assertRedirect(route('profile.edit'));
        $this->assertSame('alya.p@student.ub.ac.id', $user->fresh()->email);
    }

    public function test_ubah_password_butuh_password_lama_yang_benar(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->from('/profil')->put('/profil/password', [
            'current_password' => 'salah123',
            'password' => 'passwordBaru1',
            'password_confirmation' => 'passwordBaru1',
        ])->assertSessionHasErrorsIn('password', ['current_password']);

        $this->actingAs($user)->put('/profil/password', [
            'current_password' => 'rahasia123',
            'password' => 'passwordBaru1',
            'password_confirmation' => 'passwordBaru1',
        ])->assertRedirect(route('profile.edit'));

        $this->assertTrue(Hash::check('passwordBaru1', $user->fresh()->password));
    }
}
