@extends('layouts.app')
@section('title', 'Jadwal Ruangan')

@push('styles')
<style>
    .sched-filter { padding: 20px; margin-bottom: 16px; }
    .sched-filter-row { display: grid; grid-template-columns: 1.3fr 1fr 1fr 1.4fr auto; gap: 16px; align-items: end; }
    @media (max-width: 991.98px) { .sched-filter-row { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 575.98px) { .sched-filter-row { grid-template-columns: 1fr; } }

    .sched-meta { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px 16px; margin-bottom: 12px; font-size: 13px; line-height: 20px; color: var(--pr-muted); }
    .sched-legend { display: flex; flex-wrap: wrap; gap: 8px 20px; }
    .sched-legend span { display: inline-flex; align-items: center; gap: 8px; }
    .sched-legend i { width: 14px; height: 14px; border-radius: 4px; display: inline-block; }
    .lg-open { background: #fff; border: 1.5px solid var(--pr-approved-fg); }
    .lg-used { background: var(--pr-border); }
    .lg-past { background: var(--pr-bg); border: 1px dashed var(--pr-border-strong); }

    .sched-card { padding: 8px 20px 12px; overflow-x: auto; }
    .sched-table { min-width: 780px; }
    .sched-row { display: grid; grid-template-columns: 210px repeat(var(--n, 4), minmax(120px, 1fr)); gap: 12px; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--pr-border); }
    .sched-row:last-child { border-bottom: 0; }
    .sched-head { padding: 12px 0; }
    .sched-head .sched-sesi { font-size: 13px; line-height: 18px; font-weight: 600; }
    .sched-head .sched-jam { font-size: 13px; line-height: 20px; color: var(--pr-muted); }
    .sched-room-code { font-size: 13px; line-height: 18px; font-weight: 600; }
    .sched-room-sub { font-size: 13px; line-height: 20px; color: var(--pr-muted); }

    .slot { display: flex; align-items: center; justify-content: center; gap: 6px; height: 40px; padding: 0 10px; border-radius: 8px;
        font-size: 13px; line-height: 20px; font-weight: 500; text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .slot-open { background: #fff; border: 1px solid var(--pr-border-strong); color: var(--pr-approved-fg); }
    .slot-open::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex: none; }
    .slot-open:hover { background: var(--pr-approved-bg); border-color: var(--pr-approved-fg); color: var(--pr-approved-fg); }
    .slot-used { background: var(--pr-subtle); border: 1px solid transparent; color: var(--pr-muted); }
    .slot-past { background: var(--pr-bg); border: 1px dashed var(--pr-border); color: var(--pr-faint); }
    .sched-empty { padding: 48px 12px; text-align: center; color: var(--pr-muted); }
    .sched-note { display: flex; align-items: center; gap: 8px; margin-top: 12px; font-size: 13px; line-height: 20px; color: var(--pr-faint); }
</style>
@endpush

@section('content')
<div class="page-head">
    <div>
        <h1 class="t-h1">Jadwal Ruangan</h1>
        <p>Pilih sel yang masih tersedia untuk langsung mengajukan peminjaman.</p>
    </div>
    <a href="{{ url('/pengajuan/buat') }}" class="btn btn-primary">Ajukan Peminjaman</a>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('schedule.index') }}" class="pr-card sched-filter">
    <div class="sched-filter-row">
        <div class="field">
            <label class="field-label" for="fTanggal">Tanggal</label>
            <input type="date" id="fTanggal" name="tanggal" class="form-control" value="{{ $tanggal->toDateString() }}" onchange="this.form.submit()">
        </div>
        <div class="field">
            <label class="field-label" for="fGedung">Gedung</label>
            <select id="fGedung" name="gedung" class="form-select" onchange="this.form.ruang.value = ''; this.form.submit()">
                <option value="">Semua gedung</option>
                @foreach ($gedungList as $g)
                    <option value="{{ $g }}" @selected((string) request('gedung') === (string) $g)>{{ $g }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label class="field-label" for="fLantai">Lantai</label>
            <select id="fLantai" name="lantai" class="form-select" onchange="this.form.ruang.value = ''; this.form.submit()">
                <option value="">Semua lantai</option>
                @foreach ($lantaiList as $l)
                    <option value="{{ $l }}" @selected((string) request('lantai') === (string) $l)>Lantai {{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label class="field-label" for="fRuang">Ruang</label>
            {{-- Nomor ruangan saja; aktif setelah gedung & lantai dipilih --}}
            <select id="fRuang" name="ruang" class="form-select" onchange="this.form.submit()" @disabled(! $bisaPilihRuang)>
                <option value="">{{ $bisaPilihRuang ? 'Semua ruang' : 'Pilih gedung & lantai dulu' }}</option>
                @foreach ($opsiRuang as $nomor)
                    <option value="{{ $nomor }}" @selected((string) request('ruang') === (string) $nomor)>{{ $nomor }}</option>
                @endforeach
            </select>
        </div>
        <a href="{{ route('schedule.index') }}" class="btn btn-secondary-pr">Reset</a>
    </div>
</form>

{{-- Legenda --}}
<div class="sched-meta">
    <div class="sched-legend">
        <span><i class="lg-open"></i>Tersedia</span>
        <span><i class="lg-used"></i>Terpakai (sudah disetujui)</span>
        <span><i class="lg-past"></i>Sudah lewat</span>
    </div>
    <div>{{ $tanggalLabel }} &middot; Menampilkan {{ $rooms->count() }} ruangan</div>
</div>

{{-- Grid jadwal --}}
<div class="pr-card sched-card">
    <div class="sched-table" style="--n: {{ count($sesi) }}">
        <div class="sched-row sched-head">
            <div class="t-overline text-faint">Ruangan</div>
            @foreach ($sesi as $no => $s)
                <div>
                    <div class="sched-sesi">Sesi {{ $no }}</div>
                    <div class="sched-jam">{{ $s['mulai'] }} – {{ $s['selesai'] }}</div>
                </div>
            @endforeach
        </div>

        @forelse ($rooms as $room)
            <div class="sched-row">
                <div>
                    <div class="sched-room-code">{{ $room->kode }}</div>
                    <div class="sched-room-sub">{{ $room->nama }} &middot; Gedung {{ $room->gedung }}, Lt {{ $room->lantai }} &middot; {{ $room->kapasitas }} org</div>
                </div>
                @foreach ($sesi as $no => $s)
                    @php($c = $slots[$room->id][$no])
                    @if ($c['status'] === 'tersedia')
                        <a class="slot slot-open"
                           href="{{ url('/pengajuan/buat') . '?' . http_build_query(['room_id' => $room->id, 'tanggal' => $tanggal->toDateString(), 'sesi' => $no]) }}"
                           aria-label="Ajukan {{ $room->kode }} sesi {{ $no }}">Tersedia</a>
                    @elseif ($c['status'] === 'terpakai')
                        <div class="slot slot-used" title="{{ $c['label'] }}">{{ \Illuminate\Support\Str::limit($c['label'], 22) }}</div>
                    @else
                        <div class="slot slot-past">Lewat</div>
                    @endif
                @endforeach
            </div>
        @empty
            <div class="sched-empty">Tidak ada ruangan untuk filter ini. Ubah gedung, lantai, atau ruang, atau klik Reset.</div>
        @endforelse
    </div>
</div>

<div class="sched-note">
    <i class="bi bi-info-circle" aria-hidden="true"></i>
    Sel Terpakai hanya untuk pengajuan yang sudah disetujui. Klik sel Tersedia untuk langsung mengajukan.
</div>
@endsection

