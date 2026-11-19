<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body>
    {{-- Layout admin — Sidebar/Admin (desktop) & Topbar/Mobile (Admin) + offcanvas --}}
    <header class="pr-navbar admin-topbar">
        <div class="pr-container pr-navbar-inner">
            @include('partials.logo', ['href' => url('/admin')])
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('profile.edit') }}" aria-label="Profil">@include('partials.avatar', ['user' => auth()->user()])</a>
                <button class="pr-icon-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarAdmin" aria-controls="sidebarAdmin" aria-label="Buka menu">
                    @include('partials.icon', ['name' => 'menu'])
                </button>
            </div>
        </div>
    </header>
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarAdmin" style="width: 280px" aria-label="Menu admin">
        @include('partials.admin-sidebar')
    </div>

    <div class="admin-shell">
        @include('partials.admin-sidebar')
        <main class="admin-content">
            @include('partials.flash', ['class' => 'mb-4'])
            @yield('content')
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
