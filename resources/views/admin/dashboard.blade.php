@section('title', 'Dashboard Admin — PinjamRuang FILKOM')
@extends('layouts.admin')

@push('styles')
<style>
    .pr-table thead th{background:var(--pr-subtle);color:var(--pr-muted);font-size:11px;line-height:16px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;padding:10px 16px;border:0;}
    .pr-table tbody td{padding:14px 16px;border-color:var(--pr-border);vertical-align:middle;font-size:13px;line-height:20px;}
    .pr-table tbody tr:last-child td{border-bottom:0;}
</style>
@endpush

@section('content')
@php
    $hariIndo  = ['Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab','Sun'=>'Min'];
    $bulanIndo = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $labelStatus = ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];
@endphp

<div class="page-head">
    <div>
        <h1 class="t-h1">Dashboard</h1>
        <p class="t-body">Ringkasan pengajuan ruangan dan aktivitas terbaru.</p>
    </div>
    <a href="{{ url('/admin/verifikasi') }}" class="btn btn-primary btn-sm">Verifikasi Pengajuan</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="pr-card p-4 h-100 d-flex flex-column gap-1">
        <span class="t-overline text-faint">Menunggu verifikasi</span>
        <span class="t-h1">{{ $stats['pending'] }}</span>
        <span class="t-small text-muted-pr">Pengajuan baru masuk</span>
    </div></div>
    <div class="col-6 col-xl-3"><div class="pr-card p-4 h-100 d-flex flex-column gap-1">
        <span class="t-overline text-faint">Disetujui</span>
        <span class="t-h1">{{ $stats['approved'] }}</span>
        <span class="t-small text-muted-pr">Ruangan terpakai</span>
    </div></div>
    <div class="col-6 col-xl-3"><div class="pr-card p-4 h-100 d-flex flex-column gap-1">
        <span class="t-overline text-faint">Ditolak</span>
        <span class="t-h1">{{ $stats['rejected'] }}</span>
        <span class="t-small text-muted-pr">Slot dibuka kembali</span>
    </div></div>
    <div class="col-6 col-xl-3"><div class="pr-card p-4 h-100 d-flex flex-column gap-1">
        <span class="t-overline text-faint">Total ruangan</span>
        <span class="t-h1">{{ $stats['rooms'] }}</span>
        <span class="t-small text-muted-pr">Terdaftar di sistem</span>
    </div></div>
</div>

<div class="pr-card">
    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
        <h2 class="t-h3">Pengajuan terbaru</h2>
        <a class="btn btn-ghost btn-sm" href="{{ url('/admin/verifikasi') }}">Lihat semua</a>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 pr-table">
            <thead><tr><th>Pemohon</th><th>Ruangan</th><th>Jadwal</th><th>Keperluan</th><th>Status</th><th>Diproses</th></tr></thead>
            <tbody>
                @forelse ($recent as $b)
                    <tr>
                        <td><div class="d-flex align-items-center gap-2">
                            @include('partials.avatar', ['user' => $b->user])
                            <div><div class="t-label">{{ $b->user->name }}</div><div class="t-small text-muted-pr">{{ $b->user->email }}</div></div>
                        </div></td>
                        <td><div class="t-label">{{ $b->room->gedung }}{{ $b->room->ruangan }}</div><div class="t-small text-muted-pr">Gedung {{ $b->room->gedung }} · Lt {{ $b->room->lantai }}</div></td>
                        <td><div class="t-label">{{ $hariIndo[$b->tanggal->format('D')] }}, {{ $b->tanggal->format('j') }} {{ $bulanIndo[$b->tanggal->month] }} {{ $b->tanggal->format('Y') }}</div><div class="t-small text-muted-pr">Sesi {{ $b->sesi }} · {{ $b->jam_sesi }}</div></td>
                        <td>{{ $b->keperluan }}</td>
                        <td><span class="badge-status badge-{{ $b->status }}">{{ $labelStatus[$b->status] }}</span></td>
                        <td class="t-small text-muted-pr">{{ $b->updated_at->format('j') }} {{ $bulanIndo[$b->updated_at->month] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted-pr">Belum ada pengajuan masuk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection