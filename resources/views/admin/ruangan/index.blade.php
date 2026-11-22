@extends('layouts.admin')
@section('title', 'Kelola Ruangan')

@push('styles')
<style>
    .room-filters { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
    .room-search { position: relative; flex: 1 1 280px; max-width: 420px; }
    .room-search .bi { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--pr-faint); pointer-events: none; }
    .room-search .form-control { padding-left: 40px; }
    .room-filters .form-select { width: auto; min-width: 170px; }

    .room-table-wrap { overflow-x: auto; }
    .room-table { width: 100%; min-width: 660px; border-collapse: collapse; }
    .room-table th { padding: 14px 20px; text-align: left; white-space: nowrap; border-bottom: 1px solid var(--pr-border);
        font-size: 11px; line-height: 16px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--pr-faint); }
    .room-table td { padding: 16px 20px; vertical-align: middle; border-bottom: 1px solid var(--pr-border); font-size: 13px; line-height: 20px; }
    .room-table tbody tr:last-child td { border-bottom: 0; }
    .room-table .room-name { font-weight: 600; }
    .room-table .room-empty { padding: 48px 20px; text-align: center; color: var(--pr-muted); }
    .room-actions { display: flex; justify-content: flex-end; gap: 8px; }
    .room-actions .pr-icon-btn { width: 34px; height: 34px; }
    .room-actions .pr-icon-btn:hover { background: var(--pr-subtle); }
    .room-actions .pr-icon-btn.is-danger { color: var(--pr-rejected-fg); }
    .room-actions .pr-icon-btn.is-danger:hover { background: var(--pr-rejected-bg); }

    .room-foot { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 20px; font-size: 13px; line-height: 20px; color: var(--pr-muted); }
    .room-foot .btn.disabled { opacity: .5; pointer-events: none; }

    #modalRuangan .modal-content, #modalHapus .modal-content { border: 0; border-radius: 16px; box-shadow: 0 16px 48px rgba(10, 27, 61, .18); }
    .modal-header { align-items: flex-start; padding: 20px 24px; border-bottom: 1px solid var(--pr-border); }
    .modal-header p { margin: 2px 0 0; font-size: 13px; line-height: 20px; color: var(--pr-muted); }
    .modal-body { display: flex; flex-direction: column; gap: 16px; padding: 24px; }
    .modal-footer { padding: 16px 24px; gap: 8px; background: var(--pr-bg); border-top: 1px solid var(--pr-border); border-radius: 0 0 16px 16px; }
    .room-field-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    @media (max-width: 575.98px) { .room-field-row { grid-template-columns: 1fr; } }
    .chip-row { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .chip-pick { border: 0; cursor: pointer; background: var(--pr-subtle); color: var(--pr-muted); }
    .chip-pick:hover { background: var(--pr-border); }
    .chip-pick.is-on { background: var(--pr-primary-soft); color: var(--pr-primary); font-weight: 600; }
</style>
@endpush

@section('content')
@php
    $gedungTeks = $gedungList->count() > 1
        ? $gedungList->slice(0, -1)->implode(', ') . ' dan ' . $gedungList->last()
        : $gedungList->first();
    $punyaFilter = request()->filled('q') || request()->filled('gedung') || request()->filled('lantai');
    $pilihCepat  = collect(['F', 'G', 'A', 'Rinov'])->merge($gedungList)->unique()->values();
@endphp

<div class="page-head">
    <div>
        <h1 class="t-h1">Kelola Ruangan</h1>
        <p>{{ $total }} ruangan{{ $gedungTeks ? ' di Gedung ' . $gedungTeks : '' }}.</p>
    </div>
    <button type="button" class="btn btn-primary" id="btnTambah">Tambah Ruangan</button>
</div>

{{-- Cari & filter --}}
<form method="GET" action="{{ route('admin.ruangan.index') }}" class="room-filters" role="search">
    <div class="room-search">
        <i class="bi bi-search" aria-hidden="true"></i>
        <input type="search" name="q" value="{{ request('q') }}" class="form-control"
               placeholder="Cari nama atau nomor ruangan" aria-label="Cari nama atau nomor ruangan">
    </div>
    <select name="gedung" class="form-select" aria-label="Filter gedung" onchange="this.form.submit()">
        <option value="">Semua gedung</option>
        @foreach ($gedungList as $g)
            <option value="{{ $g }}" @selected((string) request('gedung') === (string) $g)>{{ $g }}</option>
        @endforeach
    </select>
    <select name="lantai" class="form-select" aria-label="Filter lantai" onchange="this.form.submit()">
        <option value="">Semua lantai</option>
        @foreach ($lantaiList as $l)
            <option value="{{ $l }}" @selected((string) request('lantai') === (string) $l)>Lantai {{ $l }}</option>
        @endforeach
    </select>
</form>

{{-- Tabel --}}
<div class="pr-card room-table-wrap">
    <table class="room-table">
        <thead>
            <tr>
                <th>Nama ruangan</th>
                <th>Gedung</th>
                <th>Lantai</th>
                <th>Ruangan</th>
                <th>Kapasitas</th>
                <th style="text-align:right">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rooms as $room)
            <tr>
                <td class="room-name">{{ $room->nama }}</td>
                <td>{{ $room->gedung }}</td>
                <td>{{ $room->lantai }}</td>
                <td>{{ $room->ruangan }}</td>
                <td>{{ $room->kapasitas }} orang</td>
                <td>
                    <div class="room-actions">
                        <button type="button" class="pr-icon-btn" data-edit
                                data-id="{{ $room->id }}" data-nama="{{ $room->nama }}" data-gedung="{{ $room->gedung }}"
                                data-lantai="{{ $room->lantai }}" data-ruangan="{{ $room->ruangan }}" data-kapasitas="{{ $room->kapasitas }}"
                                aria-label="Ubah {{ $room->kode }} {{ $room->nama }}" title="Ubah">
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="pr-icon-btn is-danger" data-delete
                                data-id="{{ $room->id }}" data-nama="{{ $room->kode }} · {{ $room->nama }}" data-bookings="{{ $room->bookings_count }}"
                                aria-label="Hapus {{ $room->kode }} {{ $room->nama }}" title="Hapus">
                            <i class="bi bi-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="room-empty">
                    @if ($punyaFilter)
                        Tidak ada ruangan yang cocok. <a href="{{ route('admin.ruangan.index') }}">Hapus pencarian dan filter</a>
                    @else
                        Belum ada ruangan. Klik Tambah Ruangan untuk memulai.
                    @endif
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="room-foot">
    <span>Menampilkan {{ $rooms->firstItem() ?? 0 }}–{{ $rooms->lastItem() ?? 0 }} dari {{ $rooms->total() }} ruangan</span>
    <div class="d-flex gap-2">
        @if ($rooms->previousPageUrl())
            <a href="{{ $rooms->previousPageUrl() }}" class="btn btn-secondary-pr btn-sm">Sebelumnya</a>
        @else
            <span class="btn btn-secondary-pr btn-sm disabled" aria-disabled="true">Sebelumnya</span>
        @endif
        @if ($rooms->nextPageUrl())
            <a href="{{ $rooms->nextPageUrl() }}" class="btn btn-secondary-pr btn-sm">Berikutnya</a>
        @else
            <span class="btn btn-secondary-pr btn-sm disabled" aria-disabled="true">Berikutnya</span>
        @endif
    </div>
</div>
{{-- Modal tambah / ubah --}}
<div class="modal fade" id="modalRuangan" tabindex="-1" aria-labelledby="judulRuangan" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="formRuangan" action="{{ route('admin.ruangan.store') }}" class="modal-content"
              data-store-url="{{ route('admin.ruangan.store') }}"
              data-update-url="{{ route('admin.ruangan.update', ['room' => '__ID__']) }}"
              data-has-errors="{{ $errors->any() ? '1' : '0' }}"
              data-old-id="{{ old('_room_id') }}">
            @csrf
            <input type="hidden" name="_method" id="fMethod" value="POST">
            <input type="hidden" name="_room_id" id="fId" value="{{ old('_room_id') }}">

            <div class="modal-header">
                <div>
                    <h2 class="t-h3" id="judulRuangan">Tambah Ruangan</h2>
                    <p id="subRuangan">Ruangan baru langsung tampil di jadwal.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <div class="field">
                    <label class="field-label" for="fNama">Nama ruangan</label>
                    <input type="text" id="fNama" name="nama" maxlength="100" required placeholder="Contoh: Ruang Kelas"
                           class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}">
                    @error('nama') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field-label" for="fGedung">Gedung</label>
                    <input type="text" id="fGedung" name="gedung" maxlength="30" required autocomplete="off" placeholder="Contoh: F"
                           class="form-control @error('gedung') is-invalid @enderror" value="{{ old('gedung') }}">
                    @error('gedung') <span class="field-error">{{ $message }}</span> @enderror
                    <div class="chip-row">
                        <span class="field-hint">Pilih cepat:</span>
                        @foreach ($pilihCepat as $g)
                            <button type="button" class="chip chip-pick" data-gedung="{{ $g }}">{{ $g }}</button>
                        @endforeach
                    </div>
                    <span class="field-hint">Ketik nama gedung baru jika belum ada di daftar.</span>
                </div>

                <div class="room-field-row">
                    <div class="field">
                        <label class="field-label" for="fLantai">Lantai</label>
                        <input type="number" id="fLantai" name="lantai" min="1" max="20" required
                               class="form-control @error('lantai') is-invalid @enderror" value="{{ old('lantai') }}">
                        @error('lantai') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label class="field-label" for="fRuangan">Nomor ruangan</label>
                        <input type="number" id="fRuangan" name="ruangan" min="1" max="999" required placeholder="4"
                               class="form-control @error('ruangan') is-invalid @enderror" value="{{ old('ruangan') }}">
                        @error('ruangan') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label class="field-label" for="fKapasitas">Kapasitas</label>
                        <input type="number" id="fKapasitas" name="kapasitas" min="1" max="1000" required
                               class="form-control @error('kapasitas') is-invalid @enderror" value="{{ old('kapasitas') }}">
                        @error('kapasitas') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-pr" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Ruangan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal konfirmasi hapus --}}
<div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="judulHapus" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="formHapus" action="#" class="modal-content"
              data-destroy-url="{{ route('admin.ruangan.destroy', ['room' => '__ID__']) }}">
            @csrf
            @method('DELETE')
            <div class="modal-body">
                <h2 class="t-h3" id="judulHapus">Hapus ruangan?</h2>
                <p class="mb-0 t-small">Ruangan <strong id="hapusNama"></strong> akan dihapus permanen.</p>
                <p class="mb-0 t-small" id="hapusPeringatan" hidden style="color: var(--pr-rejected-fg)"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-pr" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger-pr">Hapus Ruangan</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
(function () {
    const form       = document.getElementById('formRuangan');
    const formHapus  = document.getElementById('formHapus');
    const storeUrl   = form.dataset.storeUrl;
    const updateUrl  = form.dataset.updateUrl;
    const destroyUrl = formHapus.dataset.destroyUrl;

    const modalRuangan = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRuangan'));
    const modalHapus   = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalHapus'));
    const f = {
        nama: document.getElementById('fNama'), gedung: document.getElementById('fGedung'),
        lantai: document.getElementById('fLantai'), ruangan: document.getElementById('fRuangan'),
        kapasitas: document.getElementById('fKapasitas'),
    };
    const chips = document.querySelectorAll('.chip-pick');

    function syncChips() {
        const v = f.gedung.value.trim().toLowerCase();
        chips.forEach(c => c.classList.toggle('is-on', c.dataset.gedung.toLowerCase() === v));
    }
    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
        form.querySelectorAll('.field-error').forEach(e => e.remove());
    }
    function setMode(id) {
        const edit = !!id;
        document.getElementById('judulRuangan').textContent = edit ? 'Ubah Ruangan' : 'Tambah Ruangan';
        document.getElementById('subRuangan').textContent = edit ? 'Perubahan langsung tampil di jadwal.' : 'Ruangan baru langsung tampil di jadwal.';
        document.getElementById('fMethod').value = edit ? 'PUT' : 'POST';
        document.getElementById('fId').value = edit ? id : '';
        form.action = edit ? updateUrl.replace('__ID__', id) : storeUrl;
    }

    document.getElementById('btnTambah').addEventListener('click', () => {
        clearErrors();
        Object.values(f).forEach(i => i.value = '');
        setMode(null);
        syncChips();
        modalRuangan.show();
    });

    document.querySelectorAll('[data-edit]').forEach(btn => btn.addEventListener('click', () => {
        clearErrors();
        const d = btn.dataset;
        f.nama.value = d.nama; f.gedung.value = d.gedung; f.lantai.value = d.lantai;
        f.ruangan.value = d.ruangan; f.kapasitas.value = d.kapasitas;
        setMode(d.id);
        syncChips();
        modalRuangan.show();
    }));

    chips.forEach(c => c.addEventListener('click', () => { f.gedung.value = c.dataset.gedung; syncChips(); f.gedung.focus(); }));
    f.gedung.addEventListener('input', syncChips);
    document.getElementById('modalRuangan').addEventListener('shown.bs.modal', () => f.nama.focus());

    document.querySelectorAll('[data-delete]').forEach(btn => btn.addEventListener('click', () => {
        const d = btn.dataset;
        formHapus.action = destroyUrl.replace('__ID__', d.id);
        document.getElementById('hapusNama').textContent = d.nama;
        const warn = document.getElementById('hapusPeringatan');
        const n = parseInt(d.bookings, 10) || 0;
        warn.hidden = n === 0;
        warn.textContent = n ? 'Ruangan ini punya ' + n + ' pengajuan peminjaman. Semua pengajuan itu ikut terhapus.' : '';
        modalHapus.show();
    }));

    if (form.dataset.hasErrors === '1') {
        // Validasi gagal: buka lagi modal dengan isian & pesan error dari server
        setMode(form.dataset.oldId || null);
        syncChips();
        modalRuangan.show();
    }
})();
</script>
@endpush