{{-- Sidebar/Admin (dipakai di desktop & offcanvas mobile) --}}
@php
    $pendingCount = \App\Models\Booking::where('status', 'pending')->count();
    $adminNav = [
        ['url' => url('/admin'),            'label' => 'Dashboard',            'icon' => 'grid',     'active' => request()->is('admin')],
        ['url' => url('/admin/verifikasi'), 'label' => 'Verifikasi Pengajuan', 'icon' => 'inbox',    'active' => request()->is('admin/verifikasi*'), 'badge' => $pendingCount],
        ['url' => url('/admin/ruangan'),    'label' => 'Kelola Ruangan',       'icon' => 'building', 'active' => request()->is('admin/ruangan*')],
    ];
@endphp
<aside class="sidebar">
    <div class="sidebar-top">
        <div class="sidebar-brand">@include('partials.logo', ['href' => url('/admin')])</div>
        <nav class="sidebar-nav" aria-label="Navigasi admin">
            <span class="t-overline sidebar-heading">Panel Admin</span>
            @foreach ($adminNav as $item)
                <a class="nav-link {{ $item['active'] ? 'active' : '' }}" href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif>
                    @include('partials.icon', ['name' => $item['icon']])
                    <span class="nav-text">{{ $item['label'] }}</span>
                    @if (! empty($item['badge']))
                        <span class="sidebar-badge">{{ $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>
    <div class="sidebar-user">
        @include('partials.avatar', ['user' => auth()->user()])
        <a class="sidebar-user-link" href="{{ route('profile.edit') }}" title="Profil">
            <span class="pr-user-name">{{ auth()->user()->name }}</span>
            <span class="pr-user-role">Sarpras FILKOM</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-logout" title="Keluar" aria-label="Keluar">@include('partials.icon', ['name' => 'logout'])</button>
        </form>
    </div>
</aside>
