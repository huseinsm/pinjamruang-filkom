@extends('layouts.auth')
@section('title', 'Masuk — PinjamRuang FILKOM')
@section('brand_title', 'Satu tempat untuk semua peminjaman ruang di FILKOM.')
@section('brand_text', 'Masuk untuk mengajukan ruangan dan memantau status pengajuanmu.')

@section('content')
{{-- Desktop / Login & Mobile / Login — Husein (HSM) --}}
<div class="auth-head">
    <h2 class="t-h1">Masuk</h2>
    <p class="t-body">Gunakan email dan password yang sudah terdaftar.</p>
</div>

<form method="POST" action="{{ route('login') }}" class="d-flex flex-column" style="gap: inherit" novalidate>
    @csrf
    <div class="field">
        <label class="field-label" for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}"
               class="form-control @error('email') is-invalid @enderror"
               placeholder="nama@student.ub.ac.id" autocomplete="username" required autofocus>
        @error('email')<div class="field-error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
        <label class="field-label" for="password">Password</label>
        <input id="password" type="password" name="password"
               class="form-control @error('password') is-invalid @enderror"
               placeholder="Masukkan password" autocomplete="current-password" required>
        @error('password')<div class="field-error">{{ $message }}</div>@enderror
    </div>

    <label class="pr-check">
        <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
        <span><span class="d-none d-lg-inline">Ingat saya di perangkat ini</span><span class="d-lg-none">Ingat saya</span></span>
    </label>

    <button type="submit" class="btn btn-primary w-100">Masuk</button>
</form>

<div class="auth-switch">
    <span>Belum punya akun?</span>
    <a href="{{ route('register') }}">Daftar sekarang</a>
</div>
@endsection
