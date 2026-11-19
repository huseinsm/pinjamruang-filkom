@extends('layouts.auth')
@section('title', 'Daftar — PinjamRuang FILKOM')
@section('brand_title', 'Buat akun sekali, pinjam ruang kapan saja.')
@section('brand_text', 'Akun baru otomatis terdaftar sebagai pengguna.')

@section('content')
{{-- Desktop / Register & Mobile / Register — Husein (HSM) --}}
<div class="auth-head">
    <h2 class="t-h1">Buat akun</h2>
    <p class="t-body"><span class="d-none d-lg-inline">Isi data diri dengan email kampus.</span><span class="d-lg-none">Gunakan email kampus.</span></p>
</div>

<form method="POST" action="{{ route('register') }}" class="d-flex flex-column" style="gap: inherit" novalidate>
    @csrf
    <div class="field">
        <label class="field-label" for="name">Nama lengkap</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}"
               class="form-control @error('name') is-invalid @enderror"
               placeholder="Nama sesuai KTM" autocomplete="name" required autofocus>
        @error('name')<div class="field-error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
        <label class="field-label" for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}"
               class="form-control @error('email') is-invalid @enderror"
               placeholder="nama@student.ub.ac.id" autocomplete="email" required>
        @error('email')<div class="field-error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
        <label class="field-label" for="password">Password</label>
        <input id="password" type="password" name="password"
               class="form-control @error('password') is-invalid @enderror"
               placeholder="Minimal 8 karakter" autocomplete="new-password" required>
        @error('password')<div class="field-error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
        <label class="field-label" for="password_confirmation">Konfirmasi password</label>
        <input id="password_confirmation" type="password" name="password_confirmation"
               class="form-control @error('password') is-invalid @enderror"
               placeholder="Ulangi password" autocomplete="new-password" required>
    </div>

    <div class="field-hint d-none d-lg-block">Minimal 8 karakter, kombinasi huruf dan angka.</div>

    <button type="submit" class="btn btn-primary w-100">Buat Akun</button>
</form>

<div class="auth-switch">
    <span>Sudah punya akun?</span>
    <a href="{{ route('login') }}">Masuk</a>
</div>
@endsection
