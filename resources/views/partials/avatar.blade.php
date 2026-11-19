{{-- Avatar inisial (maks. 2 huruf dari nama). $user wajib, $class opsional (mis. 'avatar-lg') --}}
@php
    $initials = collect(preg_split('/\s+/', trim($user->name ?? '')))
        ->filter()->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->join('');
@endphp
<span class="avatar {{ $class ?? '' }}" aria-hidden="true">{{ $initials ?: '?' }}</span>
