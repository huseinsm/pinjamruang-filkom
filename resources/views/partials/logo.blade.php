{{-- Logo PinjamRuang. $tone: 'dark' (latar terang) | 'light' (latar gelap) --}}
@php($tone = $tone ?? 'dark')
<a href="{{ $href ?? url('/') }}" class="logo {{ $tone === 'light' ? 'logo-light' : '' }}" aria-label="PinjamRuang FILKOM UB">
    <span class="logo-mark">@include('partials.icon', ['name' => 'door'])</span>
    <span class="logo-text">
        <span class="logo-name">PinjamRuang</span>
        <span class="logo-sub">FILKOM UB</span>
    </span>
</a>
