<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Profil user & admin — Husein (HSM)
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        // User: total pengajuan miliknya. Admin: pengajuan yang sudah diputuskan (disetujui/ditolak).
        $bookingCount = $user->isAdmin()
            ? Booking::whereIn('status', ['approved', 'rejected'])->count()
            : $user->bookings()->count();

        return view('profile.edit', compact('user', 'bookingCount'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validateWithBag('profile', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
        ]);

        $user->update($data);

        return redirect()->route('profile.edit')->with('success', 'Informasi akun berhasil disimpan.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'regex:/[A-Za-z]/', 'regex:/[0-9]/', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini salah.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.regex' => 'Password baru harus kombinasi huruf dan angka.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.different' => 'Password baru harus berbeda dari password saat ini.',
        ]);

        // Di-hash otomatis oleh cast 'hashed' pada model User.
        $request->user()->update(['password' => $data['password']]);

        return redirect()->route('profile.edit')->with('success', 'Password berhasil diperbarui.');
    }
}
