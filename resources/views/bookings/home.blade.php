@extends('layouts.app')

@section('title', 'Beranda — PinjamRuang FILKOM')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ $greeting }}, {{ \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->first() }}</h1>
            <div class="text-muted-pr small">{{ $today->translatedFormat('l, j F Y') }}</div>
        </div>
        <a href="{{ route('bookings.create') }}" class="btn btn-primary">Ajukan Peminjaman</a>
    </div>

    <div class="row g-3">
        {{-- Jadwal berikutnya (di mobile tampil lebih dulu) --}}
        <div class="col-lg-4 order-lg-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <small class="text-uppercase fw-semibold text-muted-pr">Jadwal berikutnya</small>
                        @if ($next)
                            @include('bookings._status', ['status' => 'approved'])
                        @endif
                    </div>

                    @if ($next)
                        <div class="h3 fw-bold mb-0">{{ $next->room->nama }}</div>
                        <div class="text-muted-pr small mb-3">Gedung {{ $next->room->gedung }} · Lantai {{ $next->room->lantai }}</div>
                        <ul class="list-unstyled small mb-0 d-grid gap-2">
                            <li><i class="bi bi-calendar-event me-2 text-muted-pr"></i>{{ $next->tanggal->translatedFormat('l, j F Y') }}</li>
                            <li><i class="bi bi-clock me-2 text-muted-pr"></i>Sesi {{ $next->sesi }} · {{ $next->jam_sesi }}</li>
                            <li><i class="bi bi-card-text me-2 text-muted-pr"></i>{{ $next->keperluan }}</li>
                        </ul>
                    @else
                        <div class="text-center text-muted-pr py-4">
                            <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                            <div class="small">Belum ada jadwal peminjaman yang disetujui.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Pengajuan terbaru --}}
        <div class="col-lg-8 order-lg-1">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h6 fw-bold mb-0">Pengajuan terbaru</h2>
                        <a href="{{ route('bookings.index') }}" class="small text-decoration-none" style="color: var(--pr-primary);">Lihat semua <i class="bi bi-arrow-right"></i></a>
                    </div>

                    @if ($latest->isEmpty())
                        <div class="text-center text-muted-pr py-4">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            <div class="small mb-3">Kamu belum pernah mengajukan peminjaman.</div>
                            <a href="{{ route('bookings.create') }}" class="btn btn-outline-primary btn-sm">Ajukan sekarang</a>
                        </div>
                    @else
                            {{-- Tabel untuk layar lebar --}}
                            <div class="table-responsive d-none d-md-block">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr class="small text-muted-pr">
                                            <th class="fw-semibold">Ruangan</th>
                                            <th class="fw-semibold">Jadwal</th>
                                            <th class="fw-semibold">Keperluan</th>
                                            <th class="fw-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($latest as $row)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $row->room->nama }}</div>
                                                    <div class="small text-muted-pr">Gedung {{ $row->room->gedung }} · Lt {{ $row->room->lantai }}</div>
                                                </td>
                                                <td>
                                                    <div class="text-nowrap">{{ $row->tanggal->translatedFormat('D, j M Y') }}</div>
                                                    <div class="small text-muted-pr">Sesi {{ $row->sesi }}</div>
                                                </td>
                                                <td class="small">{{ \Illuminate\Support\Str::limit($row->keperluan, 40) }}</td>
                                                <td>@include('bookings._status', ['status' => $row->status])</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Daftar untuk layar kecil --}}
                            <div class="d-md-none">
                                @foreach ($latest as $row)
                                    <div class="d-flex justify-content-between align-items-start gap-2 py-2 {{ $loop->last ? '' : 'border-bottom' }}">
                                        <div>
                                            <div class="fw-semibold small">{{ $row->room->nama }} · {{ $row->tanggal->translatedFormat('D, j M') }} · Sesi {{ $row->sesi }}</div>
                                            <div class="small text-muted-pr">{{ \Illuminate\Support\Str::limit($row->keperluan, 40) }}</div>
                                        </div>
                                        @include('bookings._status', ['status' => $row->status])
                                    </div>
                                @endforeach
                            </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
