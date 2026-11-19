{{-- Ikon garis dari Figma (warna mengikuti currentColor). Pakai: @include('partials.icon', ['name' => 'door', 'size' => 18]) --}}
@php($size = $size ?? ($name === 'menu' ? 20 : 18))
@switch($name)
    @case('door')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.75 3H12a1.5 1.5 0 0 1 1.5 1.5V15"/><path d="M1.5 15h2.25"/><path d="M9.75 15h6.75"/><path d="M7.5 9v.0075"/><path d="M9.75 3.42v12.12a.75.75 0 0 1-.93.73L3.75 15V4.17a1.5 1.5 0 0 1 1.14-1.45l3-.75a1.5 1.5 0 0 1 1.86 1.45Z"/></svg>
        @break
    @case('chevron-down')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 6.75 9 11.25l4.5-4.5"/></svg>
        @break
    @case('logout')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 15.75h-3a1.5 1.5 0 0 1-1.5-1.5V3.75a1.5 1.5 0 0 1 1.5-1.5h3"/><path d="M12 12.75 15.75 9 12 5.25"/><path d="M15.75 9h-9"/></svg>
        @break
    @case('menu')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.33 5h13.34"/><path d="M3.33 10h13.34"/><path d="M3.33 15h13.34"/></svg>
        @break
    @case('grid')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.25" y="2.25" width="5.25" height="5.25" rx=".75"/><rect x="10.5" y="2.25" width="5.25" height="5.25" rx=".75"/><rect x="10.5" y="10.5" width="5.25" height="5.25" rx=".75"/><rect x="2.25" y="10.5" width="5.25" height="5.25" rx=".75"/></svg>
        @break
    @case('inbox')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16.5 9H12l-1.5 2.25h-3L6 9H1.5"/><path d="M4.09 3.83 1.5 9v4.5A1.5 1.5 0 0 0 3 15h12a1.5 1.5 0 0 0 1.5-1.5V9l-2.59-5.17A1.5 1.5 0 0 0 12.57 3H5.43a1.5 1.5 0 0 0-1.34.83Z"/></svg>
        @break
    @case('building')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="1.5" width="12" height="15" rx="1.5"/><path d="M6.75 16.5v-3h4.5v3"/><path d="M6 4.5h.0075M12 4.5h.0075M9 4.5h.0075M9 7.5h.0075M9 10.5h.0075M12 7.5h.0075M12 10.5h.0075M6 7.5h.0075M6 10.5h.0075"/></svg>
        @break
    @case('user')
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.25 15.75v-1.5a3 3 0 0 0-3-3h-4.5a3 3 0 0 0-3 3v1.5"/><circle cx="9" cy="5.25" r="3"/></svg>
        @break
@endswitch
