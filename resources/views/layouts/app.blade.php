<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body>
    {{-- Layout user — Navbar/User (desktop) & Topbar/Mobile (User) --}}
    @php
        $navItems = [
            ['url' => url('/beranda'),        'label' => 'Beranda',           'active' => request()->is('beranda')],
            ['url' => url('/jadwal'),         'label' => 'Jadwal Ruangan',    'active' => request()->is('jadwal*')],
            ['url' => url('/pengajuan/buat'), 'label' => 'Ajukan Peminjaman', 'active' => request()->is('pengajuan/buat')],
            ['url' => url('/pengajuan'),      'label' => 'Pengajuan Saya',    'active' => request()->is('pengajuan')],
        ];
        $onProfile = request()->is('profil*');
    @endphp
    <header class="pr-navbar">
        <div class="pr-container pr-navbar-inner">
            <div class="pr-navbar-left">
                @include('partials.logo', ['href' => url('/beranda')])
                <nav class="pr-nav d-none d-lg-flex" aria-label="Navigasi utama">
                    @foreach ($navItems as $item)
                        <a class="pr-nav-link {{ $item['active'] ? 'active' : '' }}" href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
                    @endforeach
                </nav>
            </div>

            {{-- Desktop: chip pengguna + dropdown --}}
            <div class="dropdown d-none d-lg-block">
                <button class="pr-user-chip {{ $onProfile ? 'active' : '' }}" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    @include('partials.avatar', ['user' => auth()->user()])
                    <span class="pr-user-meta">
                        <span class="pr-user-name">{{ \Illuminate\Support\Str::words(auth()->user()->name, 2, '') }}</span>
                        <span class="pr-user-role">Pengguna</span>
                    </span>
                    <span class="text-muted-pr d-inline-flex">@include('partials.icon', ['name' => 'chevron-down', 'size' => 16])</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end pr-dropdown">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">@include('partials.icon', ['name' => 'user']) Profil</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger-pr">@include('partials.icon', ['name' => 'logout']) Keluar</button>
                        </form>
                    </li>
                </ul>
            </div>

            {{-- Mobile: avatar (ke profil) + tombol menu --}}
            <div class="d-flex d-lg-none align-items-center gap-2">
                <a href="{{ route('profile.edit') }}" aria-label="Profil">@include('partials.avatar', ['user' => auth()->user()])</a>
                <button class="pr-icon-btn" type="button" data-bs-toggle="collapse" data-bs-target="#menuUser" aria-controls="menuUser" aria-expanded="false" aria-label="Buka menu">
                    @include('partials.icon', ['name' => 'menu'])
                </button>
            </div>
        </div>
        <div class="collapse d-lg-none pr-mobile-menu" id="menuUser">
            <nav class="pr-nav" aria-label="Navigasi utama (mobile)">
                @foreach ($navItems as $item)
                    <a class="pr-nav-link {{ $item['active'] ? 'active' : '' }}" href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @endforeach
                <a class="pr-nav-link {{ $onProfile ? 'active' : '' }}" href="{{ route('profile.edit') }}">Profil</a>
            </nav>
        </div>
    </header>

    <main class="pr-container pr-main">
        @include('partials.flash', ['class' => 'mb-4'])
        @yield('content')
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
