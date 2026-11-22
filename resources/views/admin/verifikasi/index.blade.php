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
    .pill-manual{display:inline-flex;align-items:center;font-size:11px;line-height:16px;font-weight:600;background:#EFEEEA;color:var(--pr-muted);border-radius:999px;padding:1px 7px;}
    .sesi-option.terpakai{opacity:.55;background:var(--pr-subtle);cursor:not-allowed;}
    .sesi-terpakai{display:none;color:var(--pr-rejected-fg);font-weight:600;}
    .sesi-option.terpakai .sesi-terpakai{display:inline;}
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
        <p class="t-body">Terima atau tolak pengajuan, atau catat peminjaman yang disetujui di luar website.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalManual">+ Catat Peminjaman Manual</button>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
    <nav class="pr-tabs" aria-label="Tab status">
        <a href="{{ $urlTab('menunggu') }}"  class="{{ $tab === 'menunggu'  ? 'active' : '' }}">Menunggu <span class="count">{{ $counts['menunggu'] }}</span></a>
        <a href="{{ $urlTab('disetujui') }}" class="{{ $tab === 'disetujui' ? 'active' : '' }}">Disetujui <span class="count">{{ $counts['disetujui'] }}</span></a>
        <a href="{{ $urlTab('ditolak') }}"   class="{{ $tab === 'ditolak'   ? 'active' : '' }}">Ditolak <span class="count">{{ $counts['ditolak'] }}</span></a>
        <a href="{{ $urlTab('semua') }}"     class="{{ $tab === 'semua'     ? 'active' : '' }}">Semua <span class="count">{{ $counts['semua'] }}</span></a>
    </nav>
    <form method="GET" style="width:100%;max-width:280px;">
        <input type="hidden" name="status" value="{{ $tab }}">
        <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Cari pemohon atau ruangan">
    </form>
</div>

<div class="pr-card">
    <div class="table-responsive">
        <table class="table mb-0 pr-table">
            <thead>
                <tr><th>Pemohon</th><th>Ruangan</th><th>Jadwal</th><th>Keperluan</th><th>Diproses</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($bookings as $b)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar">{{ strtoupper(substr($b->nama_pemohon, 0, 1)) }}</div>
                                <div>
                                    <div class="t-label">{{ $b->nama_pemohon }}</div>
                                    @if ($b->is_manual)
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="pill-manual">Dicatat admin</span>
                                            <span class="t-small text-muted-pr">{{ $b->kontak_peminjam ?: '—' }}</span>
                                        </div>
                                    @else
                                        <div class="t-small text-muted-pr">{{ $b->user->email }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="t-label">{{ $b->room->kode }}</div>
                            <div class="t-small text-muted-pr">Gedung {{ $b->room->gedung }} · Lt {{ $b->room->lantai }}</div>
                        </td>
                        <td>
                            <div class="t-label">{{ $hariIndo[$b->tanggal->format('D')] }}, {{ $b->tanggal->format('j') }} {{ $bulanIndo[$b->tanggal->month] }} {{ $b->tanggal->format('Y') }}</div>
                            <div class="t-small text-muted-pr">Sesi {{ $b->sesi }} · {{ $b->jam_sesi }}</div>
                        </td>
                        <td style="min-width:180px;">{{ $b->keperluan }}</td>
                        <td class="t-small text-muted-pr">{{ $b->updated_at->format('j') }} {{ $bulanIndo[$b->updated_at->month] }}</td>
                        <td><span class="badge-status badge-{{ $b->status }}">{{ $labelStatus[$b->status] }}</span></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-secondary-pr btn-sm" data-bs-toggle="modal" data-bs-target="#modalStatus"
                                data-url="{{ route('admin.verifications.update', $b) }}"
                                data-status="{{ $b->status }}"
                                data-ringkasan="{{ $b->nama_pemohon }} · {{ $b->room->kode }} · {{ $hariIndo[$b->tanggal->format('D')] }}, {{ $b->tanggal->format('j') }} {{ $bulanIndo[$b->tanggal->month] }} {{ $b->tanggal->format('Y') }} · Sesi {{ $b->sesi }}">
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

<div class="mt-3 d-flex flex-column gap-1">
    <p class="t-small text-faint d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-info-circle"></i> Status pengajuan yang sudah diverifikasi masih bisa diubah lewat tombol Ubah.
    </p>
    <p class="t-small text-faint d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-info-circle"></i> Baris berlabel "Dicatat admin" adalah peminjaman di luar website yang dicatat manual oleh admin.
    </p>
    {{ $bookings->links('pagination::bootstrap-5') }}
</div>

{{-- Modal Ubah Status --}}
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

{{-- Modal Catat Peminjaman Manual --}}
<div class="modal fade" id="modalManual" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ route('admin.verifications.manual') }}" class="modal-content" style="border:0;border-radius:16px;">
            @csrf
            <input type="hidden" name="form" value="manual">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="t-h3">Catat Peminjaman Manual</h5>
                    <p class="t-small text-muted-pr mb-0">Tandai slot terpakai untuk peminjaman yang disetujui di luar website.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="row g-3">
                    <div class="col-md-4 field">
                        <label class="field-label" for="mGedung">Gedung</label>
                        <select name="gedung" id="mGedung" class="form-select @error('gedung') is-invalid @enderror"></select>
                        @error('gedung')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 field">
                        <label class="field-label" for="mLantai">Lantai</label>
                        <select name="lantai" id="mLantai" class="form-select @error('lantai') is-invalid @enderror"></select>
                        @error('lantai')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 field">
                        <label class="field-label" for="mRuangan">Ruangan</label>
                        <input type="number" min="1" step="1" name="ruangan" id="mRuangan" value="{{ old('ruangan') }}" class="form-control @error('ruangan') is-invalid @enderror">
                        @error('ruangan')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="field-hint mt-1 mb-3" id="mPreview">Ruangan tidak ditemukan</div>

                <div class="field mb-3">
                    <label class="field-label" for="mTanggal">Tanggal</label>
                    <input type="date" name="tanggal" id="mTanggal" min="{{ now()->format('Y-m-d') }}" value="{{ old('tanggal') }}" class="form-control @error('tanggal') is-invalid @enderror">
                    @error('tanggal')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <p class="t-label mb-2">Sesi</p>
                <div class="row g-2 mb-1">
                    @foreach (config('sesi') as $nomor => $jam)
                        <div class="col-6 col-md-3">
                            <label class="status-option sesi-option">
                                <input class="form-check-input" type="radio" name="sesi" value="{{ $nomor }}" {{ (string) old('sesi') === (string) $nomor ? 'checked' : '' }} required>
                                <span class="t-label">Sesi {{ $nomor }}</span>
                                <span class="t-small text-muted-pr">{{ $jam['mulai'] }}–{{ $jam['selesai'] }}</span>
                                <span class="sesi-terpakai t-small">Terpakai</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('sesi')<div class="field-error mb-2">{{ $message }}</div>@enderror

                <div class="row g-3 mt-1">
                    <div class="col-md-6 field">
                        <label class="field-label" for="mNama">Nama peminjam</label>
                        <input type="text" name="nama_peminjam" id="mNama" value="{{ old('nama_peminjam') }}" class="form-control @error('nama_peminjam') is-invalid @enderror" maxlength="100">
                        @error('nama_peminjam')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 field">
                        <label class="field-label" for="mKontak">Kontak (opsional)</label>
                        <input type="text" name="kontak_peminjam" id="mKontak" value="{{ old('kontak_peminjam') }}" class="form-control @error('kontak_peminjam') is-invalid @enderror" maxlength="100">
                        @error('kontak_peminjam')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="field mt-3">
                    <label class="field-label" for="mKeperluan">Keperluan</label>
                    <textarea name="keperluan" id="mKeperluan" rows="3" maxlength="255" class="form-control @error('keperluan') is-invalid @enderror">{{ old('keperluan') }}</textarea>
                    @error('keperluan')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="pr-alert mt-3" style="background:var(--pr-primary-soft);color:var(--pr-primary);">
                    <i class="bi bi-info-circle"></i>
                    <span>Peminjaman ini langsung berstatus Disetujui dan slot tampil Terpakai di Jadwal Ruangan. Pengajuan lain yang masih menunggu di slot yang sama otomatis ditolak.</span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary-pr btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan &amp; Tandai Terpakai</button>
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

    (function () {
        const ROOMS = @json($rooms);
        const USED_URL = @json(route('admin.verifications.used'));
        const el = (id) => document.getElementById(id);
        const gedung = el('mGedung'), lantai = el('mLantai'), ruangan = el('mRuangan'), tanggal = el('mTanggal'), preview = el('mPreview');
        const uniq = (a) => [...new Set(a)];

        function isiGedung() {
            gedung.innerHTML = '<option value="">Pilih gedung</option>' +
                uniq(ROOMS.map(r => r.gedung)).map(g => `<option value="${g}">${g}</option>`).join('');
        }
        function isiLantai() {
            const daftar = uniq(ROOMS.filter(r => r.gedung === gedung.value).map(r => Number(r.lantai))).sort((a, b) => a - b);
            lantai.innerHTML = '<option value="">Pilih lantai</option>' + daftar.map(l => `<option value="${l}">${l}</option>`).join('');
        }
        function pratinjau() {
            const r = ROOMS.find(r => r.gedung === gedung.value && Number(r.lantai) === Number(lantai.value) && Number(r.ruangan) === Number(ruangan.value));
            preview.textContent = r ? `${r.gedung} ${r.lantai}.${r.ruangan} · ${r.kapasitas} orang` : 'Ruangan tidak ditemukan';
            preview.style.color = r ? '' : 'var(--pr-rejected-fg)';
        }
        async function cekTerpakai() {
            document.querySelectorAll('#modalManual input[name="sesi"]').forEach(r => {
                r.disabled = false;
                r.closest('.status-option').classList.remove('terpakai');
            });
            if (!gedung.value || !lantai.value || !ruangan.value || !tanggal.value) return;
            const params = new URLSearchParams({ gedung: gedung.value, lantai: lantai.value, ruangan: ruangan.value, tanggal: tanggal.value });
            try {
                const res = await fetch(`${USED_URL}?${params}`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                (data.terpakai || []).forEach(n => {
                    const r = document.querySelector(`#modalManual input[name="sesi"][value="${n}"]`);
                    if (r) { r.disabled = true; r.checked = false; r.closest('.status-option').classList.add('terpakai'); }
                });
            } catch (e) { /* jaringan gagal: biarkan semua sesi aktif */ }
        }
        const refresh = () => { pratinjau(); cekTerpakai(); };

        isiGedung();
        gedung.addEventListener('change', () => { isiLantai(); refresh(); });
        lantai.addEventListener('change', refresh);
        ruangan.addEventListener('input', refresh);
        tanggal.addEventListener('change', refresh);

        const oldGedung = @json(old('gedung'));
        const oldLantai = @json(old('lantai'));
        if (oldGedung) { gedung.value = oldGedung; isiLantai(); }
        if (oldLantai) { lantai.value = String(oldLantai); }
        refresh();

        @if ($errors->any() && old('form') === 'manual')
            new bootstrap.Modal(el('modalManual')).show();
        @endif
    })();
</script>
@endpush