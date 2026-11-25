@extends('layouts.app')

@section('title', 'Pengajuan Saya — PinjamRuang FILKOM')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
        <div>
            <h1 class="h3 fw-bold mb-1">Pengajuan Saya</h1>
            <p class="text-muted-pr mb-0">Riwayat dan status pengajuan peminjaman ruanganmu.</p>
        </div>
        <a href="{{ route('bookings.create') }}" class="btn btn-primary">Ajukan Peminjaman</a>
    </div>

    {{-- Tab status --}}
    @php
        $tabs = [
            [null, 'Semua', $counts['all']],
            ['pending', 'Menunggu', $counts['pending']],
            ['approved', 'Disetujui', $counts['approved']],
            ['rejected', 'Ditolak', $counts['rejected']],
        ];
    @endphp
    <ul class="nav nav-pills gap-1 mb-3" style="--bs-nav-pills-link-active-bg: var(--pr-primary);">
        @foreach ($tabs as [$key, $label, $total])
            <li class="nav-item">
                <a class="nav-link py-1 px-3 text-nowrap {{ $status === $key ? 'active' : 'text-muted-pr' }}"
                   href="{{ $key ? route('bookings.index', ['status' => $key]) : route('bookings.index') }}">
                    {{ $label }} <span class="small opacity-75">{{ $total }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    @if ($bookings->isEmpty())
        <div class="card">
            <div class="card-body text-center text-muted-pr py-5">
                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                <div class="small">
                    {{ $status ? 'Tidak ada pengajuan dengan status ini.' : 'Kamu belum pernah mengajukan peminjaman.' }}
                </div>
            </div>
        </div>
    @else
        {{-- Tabel untuk layar lebar --}}
        <div class="card d-none d-md-block">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr class="small text-muted-pr">
                            <th class="fw-semibold ps-3">Ruangan</th>
                            <th class="fw-semibold">Tanggal</th>
                            <th class="fw-semibold">Sesi</th>
                            <th class="fw-semibold">Keperluan</th>
                            <th class="fw-semibold">Diajukan</th>
                            <th class="fw-semibold pe-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $b)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold">{{ $b->room->nama }}</div>
                                    <div class="small text-muted-pr">Gedung {{ $b->room->gedung }} · Lt {{ $b->room->lantai }}</div>
                                </td>
                                <td>{{ $b->tanggal->translatedFormat('D, j M Y') }}</td>
                                <td>
                                    <div>Sesi {{ $b->sesi }}</div>
                                    <div class="small text-muted-pr">{{ $b->jam_sesi }}</div>
                                </td>
                                <td class="small">{{ $b->keperluan }}</td>
                                <td class="small text-muted-pr">{{ $b->created_at->translatedFormat('j M Y') }}</td>
                                <td class="pe-3">@include('bookings._status', ['status' => $b->status])</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kartu untuk layar kecil --}}
        <div class="d-md-none d-grid gap-2">
            @foreach ($bookings as $b)
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="fw-bold">{{ $b->room->nama }}</div>
                            @include('bookings._status', ['status' => $b->status])
                        </div>
                        <div class="small text-muted-pr mb-2">{{ $b->tanggal->translatedFormat('D, j M Y') }} · Sesi {{ $b->sesi }}</div>
                        <div class="small">{{ $b->keperluan }}</div>
                        <hr class="my-2">
                        <div class="small text-muted-pr">Diajukan {{ $b->created_at->translatedFormat('j M Y') }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mt-3">
            <div class="small text-muted-pr">
                Menampilkan {{ $bookings->firstItem() }}–{{ $bookings->lastItem() }} dari {{ $bookings->total() }} pengajuan
            </div>
            @if ($bookings->hasPages())
                {{ $bookings->links('pagination::bootstrap-5') }}
            @endif
        </div>
    @endif
@endsection
