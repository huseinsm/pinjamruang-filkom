@php
    $labels = ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];
@endphp
<span class="badge-status badge-{{ $status }}">{{ $labels[$status] ?? ucfirst($status) }}</span>
