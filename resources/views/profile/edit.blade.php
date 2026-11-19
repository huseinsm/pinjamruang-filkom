@extends($user->isAdmin() ? 'layouts.admin' : 'layouts.app')
@section('title', 'Profil — PinjamRuang FILKOM')

@section('content')
{{-- Desktop & Mobile / User — Profil dan Admin — Profil — Husein (HSM) --}}
<div class="page-head">
    <div>
        <h1 class="t-h1">Profil</h1>
        <p class="t-body">Kelola data akun dan password kamu.</p>
    </div>
</div>

<div class="profile-grid">
    {{-- Kartu ringkasan akun --}}
    <section class="pr-card profile-side" aria-label="Ringkasan akun">
        @include('partials.avatar', ['user' => $user, 'class' => 'avatar-lg'])
        <div class="profile-id">
            <span class="t-h3">{{ $user->name }}</span>
            <span class="t-small">{{ $user->email }}</span>
        </div>
        <span class="chip">{{ $user->isAdmin() ? 'Admin' : 'Pengguna' }}</span>
        <div class="profile-sep"></div>
        <dl class="profile-row mb-0">
            <dt>{{ $user->isAdmin() ? 'Akun dibuat' : 'Bergabung' }}</dt>
            <dd>{{ $user->created_at?->locale('id')->translatedFormat('j F Y') ?? '-' }}</dd>
        </dl>
        <dl class="profile-row mb-0">
            <dt>{{ $user->isAdmin() ? 'Pengajuan diproses' : 'Total pengajuan' }}</dt>
            <dd>{{ $bookingCount }} pengajuan</dd>
        </dl>
    </section>

    <div class="profile-forms">
        {{-- Informasi akun --}}
        <form method="POST" action="{{ route('profile.update') }}" class="pr-card profile-card" novalidate>
            @csrf
            @method('PUT')
            <div class="profile-card-head">
                <h2 class="t-h3">Informasi akun</h2>
                <p>{{ $user->isAdmin() ? 'Nama dan email ditampilkan di panel admin.' : 'Nama dan email ditampilkan pada setiap pengajuan.' }}</p>
            </div>
            <div class="field-row">
                <div class="field">
                    <label class="field-label" for="name">Nama lengkap</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="form-control {{ $errors->profile->has('name') ? 'is-invalid' : '' }}" autocomplete="name" required>
                    @if ($errors->profile->has('name'))<div class="field-error">{{ $errors->profile->first('name') }}</div>@endif
                </div>
                <div class="field">
                    <label class="field-label" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="form-control {{ $errors->profile->has('email') ? 'is-invalid' : '' }}" autocomplete="email" required>
                    @if ($errors->profile->has('email'))<div class="field-error">{{ $errors->profile->first('email') }}</div>@endif
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>

        {{-- Ubah password --}}
        <form method="POST" action="{{ route('profile.password') }}" class="pr-card profile-card" novalidate>
            @csrf
            @method('PUT')
            <div class="profile-card-head">
                <h2 class="t-h3">Ubah password</h2>
                <p>Gunakan minimal 8 karakter, kombinasi huruf dan angka.</p>
            </div>
            <div class="field">
                <label class="field-label" for="current_password">Password saat ini</label>
                <input id="current_password" type="password" name="current_password"
                       class="form-control {{ $errors->password->has('current_password') ? 'is-invalid' : '' }}"
                       placeholder="Masukkan password saat ini" autocomplete="current-password" required>
                @if ($errors->password->has('current_password'))<div class="field-error">{{ $errors->password->first('current_password') }}</div>@endif
            </div>
            <div class="field-row">
                <div class="field">
                    <label class="field-label" for="password">Password baru</label>
                    <input id="password" type="password" name="password"
                           class="form-control {{ $errors->password->has('password') ? 'is-invalid' : '' }}"
                           placeholder="Minimal 8 karakter" autocomplete="new-password" required>
                    @if ($errors->password->has('password'))<div class="field-error">{{ $errors->password->first('password') }}</div>@endif
                </div>
                <div class="field">
                    <label class="field-label" for="password_confirmation">Konfirmasi password baru</label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           class="form-control {{ $errors->password->has('password') ? 'is-invalid' : '' }}"
                           placeholder="Ulangi password baru" autocomplete="new-password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-secondary-pr">Perbarui Password</button>
        </form>

        {{-- Mobile: tombol keluar di bawah halaman profil --}}
        <form method="POST" action="{{ route('logout') }}" class="d-lg-none">
            @csrf
            <button type="submit" class="btn btn-danger-pr w-100">Keluar</button>
        </form>
    </div>
</div>
@endsection
