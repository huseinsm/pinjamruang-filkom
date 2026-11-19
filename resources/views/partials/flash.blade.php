{{-- Pesan flash sukses / gagal --}}
@if (session('success'))
    <div class="pr-alert pr-alert-success {{ $class ?? '' }}" role="status">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="pr-alert pr-alert-error {{ $class ?? '' }}" role="alert">{{ session('error') }}</div>
@endif
