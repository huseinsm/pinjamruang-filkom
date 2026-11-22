@section('title', 'Verifikasi Pengajuan — PinjamRuang FILKOM')
@extends('layouts.admin')

@push('styles')
<style>
    .pr-tabs{display:flex;gap:4px;border-bottom:1px solid var(--pr-border);flex-wrap:wrap;}
    .pr-tabs a{padding:10px 14px;font-size:13px;line-height:20px;font-weight:500;color:var(--pr-muted);text-decoration:none;border-bottom:2px solid transparent;display:inline-flex;align-items:center;gap:8px;}
    .pr-tabs a.active{color:var(--pr-primary);border-bottom-color:var(--pr-primary);font-weight:600;}
    .pr-tabs .count{background:var(--pr-subtle);border-radius:999px;padding:1px 8px;font-size:11px;line-height:16px;font-weight:600;}
    .pr-tabs a.active .count{background:var(--pr-primary-soft);color:var(--pr-primary);}
    .pr-table thead th{background:var(--pr-subtle);color:var(--pr-muted);font-size:11px;line-height:16px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;padding:10px 16px;border:0;}
    .pr-table tbody td{padding:14px 16px;border-color:var(--pr-border);vertical-align:middle;font-size:13px;line-height:20px;}
    .pr-table tbody tr:last-child td{border-bottom:0;}
    .status-option{position:relative;display:flex;flex-direction:column;gap:2px;border:1px solid var(--pr-border-strong);border-radius:12px;padding:14px 40px 14px 16px;cursor:pointer;background:#fff;height:100%;}
    .status-option input{position:absolute;top:14px;right:14px;margin:0;}
    .status-option:has(input:checked){border-color:var(--pr-primary);box-shadow:0 0 0 1px var(--pr-primary);background:var(--pr-primary-soft);}
</style>
@endpush

@section('content')
@php
    $hariIndo  = ['Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab','Sun'=>'Min'];
    $bulanIndo = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $labelStatus = ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];
    $urlTab = fn (string $t) => url('/admin/verifikasi?status=' . $t . ($search !== '' ? '&q=' . urlencode($search) : ''));
@endphp

<div class="page-head">
    <div>
        <h1 class="t-h1">Verifikasi Pengajuan</h1>
        <p class="t-body">Terima atau tolak pengajuan yang masuk.</p>
    </div>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
    <nav class="pr-tabs" aria-label="Tab status">
        <a href="{{ $urlTab('menunggu') }}"  class="{{ $tab === 'menunggu'  ? 'active' : '' }}">Menunggu <span class="count">{{ $counts['menunggu'] }}</span></a>
        <a href="{{ $urlTab('disetujui') }}" class="{{ $tab === 'disetujui' ? 'active' : '' }}">Disetujui <span class="count">{{ $counts['disetujui'] }}</span></a>
        <a href="{{ $urlTab('ditolak') }}"   class="{{ $tab === 'ditolak'   ? 'active' : '' }}">Ditolak <span class="count">{{ $counts['ditolak'] }}</span></a>
        <a href="{{ $urlTab('semua') }}"     class="{{ $tab === 'semua'     ? 'active' : '' }}">Semua <span class="count">{{ $counts['semua'] }}</span></a>
    </nav>
    <form method="GET" style="width:280px;">
        <input type="hidden" name="status" value="{{ $tab }}">
        <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Cari pemohon atau ruangan">
    </form>
</div>

<div class="pr-card">
    <div class="table-responsive">
        <table class="table mb-0 pr-table">
            <thead>
                <tr>
                    <th>Pemohon</th><th>Ruangan</th><th>Jadwal</th><th>Keperluan</th><th>Diproses</th><th>Status</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $b)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @include('partials.avatar', ['user' => $b->user])
                                <div>
                                    <div class="t-label">{{ $b->user->name }}</div>
                                    <div class="t-small text-muted-pr">{{ $b->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="t-label">{{ $b->room->gedung }}{{ $b->room->ruangan }}</div>
                            <div class="t-small text-muted-pr">Gedung {{ $b->room->gedung }} · Lt {{ $b->room->lantai }}</div>
                        </td>
                        <td>
                            <div class="t-label">{{ $hariIndo[$b->tanggal->format('D')] }}, {{ $b->tanggal->format('j') }} {{ $bulanIndo[$b->tanggal->month] }} {{ $b->tanggal->format('Y') }}</div>
                            <div class="t-small text-muted-pr">Sesi {{ $b->sesi }} · {{ $b->jam_sesi }}</div>
                        </td>
                        <td style="min-width:180px;">{{ $b->keperluan }}</td>
                        <td class="t-small text-muted-pr">{{ $b->updated_at->format('j') }} {{ $bulanIndo[$b->updated_at->month] }}</td>
                        <td>
                            <span class="badge-status badge-{{ $b->status }}">{{ $labelStatus[$b->status] }}</span>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-secondary-pr btn-sm" data-bs-toggle="modal" data-bs-target="#modalStatus"
                                data-url="{{ route('admin.verifications.update', $b) }}"
                                data-status="{{ $b->status }}"
                                data-ringkasan="{{ $b->user->name }} · {{ $b->room->gedung }}{{ $b->room->ruangan }} · {{ $hariIndo[$b->tanggal->format('D')] }}, {{ $b->tanggal->format('j') }} {{ $bulanIndo[$b->tanggal->month] }} {{ $b->tanggal->format('Y') }} · Sesi {{ $b->sesi }}">
                                Ubah
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted-pr">Tidak ada pengajuan pada tab ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3 d-flex justify-content-between align-items-center gap-3 flex-wrap">
    <p class="t-small text-faint d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-info-circle"></i> Status pengajuan yang sudah diverifikasi masih bisa diubah lewat tombol Ubah.
    </p>
    {{ $bookings->links('pagination::bootstrap-5') }}
</div>

{{-- ===== Modal "Ubah Status Pengajuan" (sesuai prototipe Figma) ===== --}}
<div class="modal fade" id="modalStatus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="formStatus" class="modal-content" style="border:0;border-radius:16px;">
            @csrf
            @method('PATCH')
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="t-h3">Ubah Status Pengajuan</h5>
                    <p class="t-small text-muted-pr mb-0" id="modalRingkasan"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="t-small mb-2">Status saat ini: <span class="badge-status" id="modalStatusSaatIni"></span></p>
                <p class="t-label mb-2">Status baru</p>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="status-option">
                            <input class="form-check-input" type="radio" name="status" value="approved" required>
                            <span class="t-label">Disetujui</span>
                            <span class="t-small text-muted-pr">Ruangan dipakai untuk kegiatan ini.</span>
                        </label>
                    </div>
                    <div class="col-6">
                        <label class="status-option">
                            <input class="form-check-input" type="radio" name="status" value="rejected" required>
                            <span class="t-label">Ditolak</span>
                            <span class="t-small text-muted-pr">Slot dibuka kembali untuk pengajuan lain.</span>
                        </label>
                    </div>
                </div>
                <div class="pr-alert" style="background:var(--pr-primary-soft);color:var(--pr-primary);">
                    <i class="bi bi-info-circle"></i>
                    <span>Mengubah ke Disetujui hanya bisa jika slot belum dipakai pengajuan lain yang disetujui. Pengajuan lain yang masih menunggu di slot itu otomatis ditolak.</span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary-pr btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('modalStatus').addEventListener('show.bs.modal', (event) => {
        const btn   = event.relatedTarget;
        const map   = { pending: ['Menunggu', 'badge-pending'], approved: ['Disetujui', 'badge-approved'], rejected: ['Ditolak', 'badge-rejected'] };
        const badge = document.getElementById('modalStatusSaatIni');

        document.getElementById('formStatus').action = btn.dataset.url;
        document.getElementById('modalRingkasan').textContent = btn.dataset.ringkasan;
        badge.textContent = map[btn.dataset.status][0];
        badge.className   = 'badge-status ' + map[btn.dataset.status][1];

        const radio = document.querySelector(`#modalStatus input[name="status"][value="${btn.dataset.status}"]`);
        if (radio) radio.checked = true;
    });
</script>
@endpush