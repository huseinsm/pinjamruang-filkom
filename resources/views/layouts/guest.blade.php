<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body>
    {{-- Layout halaman publik (landing) — Navbar/Public & Topbar/Mobile (Public) --}}
    <header class="pr-navbar">
        <div class="pr-container pr-navbar-inner">
            <div class="pr-navbar-left">
                @include('partials.logo')
                <nav class="pr-nav d-none d-lg-flex" aria-label="Navigasi utama">
                    <a class="pr-nav-link" href="{{ url('/jadwal') }}">Jadwal Ruangan</a>
                    <a class="pr-nav-link" href="{{ url('/#cara-kerja') }}">Cara Kerja</a>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @auth
                    <a href="{{ \App\Http\Controllers\AuthController::homeFor(auth()->user()) }}" class="btn btn-primary btn-sm">
                        {{ auth()->user()->isAdmin() ? 'Panel Admin' : 'Beranda' }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm d-none d-lg-inline-flex">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm d-none d-lg-inline-flex">Daftar</a>
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm d-lg-none">Masuk</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        @if (session('success') || session('error'))
            <div class="pr-container pt-4">@include('partials.flash')</div>
        @endif
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="pr-container site-footer-inner">
            <span><span class="d-none d-lg-inline">© {{ date('Y') }} PinjamRuang FILKOM — Proyek Akhir Pengembangan Aplikasi Web</span><span class="d-lg-none">© {{ date('Y') }} PinjamRuang FILKOM</span></span>
            <span>Fakultas Ilmu Komputer · Universitas Brawijaya</span>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
