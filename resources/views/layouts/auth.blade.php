<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body>
    {{-- Layout Login & Register: panel brand gelap (600px) + form 400px rata tengah horizontal & vertikal --}}
    <div class="auth-shell">
        <aside class="auth-brand">
            @include('partials.logo', ['tone' => 'light'])
            <div class="auth-brand-copy">
                <h1 class="auth-brand-title">@yield('brand_title')</h1>
                <p class="auth-brand-text">@yield('brand_text')</p>
            </div>
            <div class="auth-brand-foot">Fakultas Ilmu Komputer · Universitas Brawijaya</div>
        </aside>
        <main class="auth-main">
            <div class="auth-form">
                @include('partials.flash')
                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
