@extends('layouts.app')

@section('title', 'Ajukan Peminjaman — PinjamRuang FILKOM')

@section('content')
    <style>
        .sesi-chip { display: block; height: 100%; padding: 10px 12px; border: 1px solid var(--pr-border); border-radius: 12px; background: #fff; cursor: pointer; }
        .sesi-chip .jam { font-size: 12px; color: var(--pr-muted); }
        .sesi-chip .ket { font-size: 12px; font-weight: 600; color: #17703F; }
        .btn-check:checked + .sesi-chip { border-color: var(--pr-primary); background: var(--pr-primary); color: #fff; }
        .btn-check:checked + .sesi-chip .jam,
        .btn-check:checked + .sesi-chip .ket { color: #fff; }
        .btn-check:disabled + .sesi-chip { background: #EFEDE7; cursor: not-allowed; color: var(--pr-muted); }
        .btn-check:disabled + .sesi-chip .ket { color: var(--pr-muted); }
    </style>

    <nav class="small mb-2">
        <a href="{{ url('/jadwal') }}" class="text-decoration-none text-muted-pr"><i class="bi bi-chevron-left"></i> Jadwal Ruangan</a>
    </nav>
    <h1 class="h3 fw-bold mb-1">Ajukan Peminjaman</h1>
    <p class="text-muted-pr mb-4">Pengajuan akan diteruskan ke admin untuk diperiksa.</p>

    <form method="POST" action="{{ route('bookings.store') }}" id="formAjukan" novalidate>
        @csrf
        <div class="card">
            <div class="card-body p-4">

                {{-- Ruangan --}}
                <h2 class="h6 fw-bold mb-1">Ruangan</h2>
                <p class="small text-muted-pr">Pilih gedung, lantai, lalu ruangan yang ingin dipinjam.</p>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label for="gedung" class="form-label small fw-semibold">Gedung</label>
                        <select id="gedung" class="form-select"></select>
                    </div>
                    <div class="col-sm-6">
                        <label for="lantai" class="form-label small fw-semibold">Lantai</label>
                        <select id="lantai" class="form-select"></select>
                    </div>
                    <div class="col-12">
                        <label for="room_id" class="form-label small fw-semibold">Ruangan</label>
                        <select id="room_id" name="room_id" class="form-select @error('room_id') is-invalid @enderror"></select>
                        @error('room_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Waktu --}}
                <h2 class="h6 fw-bold mb-1">Waktu</h2>
                <p class="small text-muted-pr">Sesi yang sudah disetujui untuk kegiatan lain tidak dapat dipilih.</p>
                <div class="mb-3">
                    <label for="tanggal" class="form-label small fw-semibold">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" min="{{ $today }}"
                           value="{{ old('tanggal', $today) }}"
                           class="form-control @error('tanggal') is-invalid @enderror">
                    @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <label class="form-label small fw-semibold">Sesi</label>
                <div class="row g-2 mb-1" id="sesiList">
                    @foreach ($sesi as $no => $s)
                        <div class="col-6 col-md-3">
                            <input type="radio" class="btn-check" name="sesi" id="sesi{{ $no }}" value="{{ $no }}"
                                   data-sesi="{{ $no }}" {{ (string) old('sesi') === (string) $no ? 'checked' : '' }}>
                            <label class="sesi-chip" for="sesi{{ $no }}">
                                <div class="fw-semibold small">Sesi {{ $no }}</div>
                                <div class="jam">{{ $s['mulai'] }} – {{ $s['selesai'] }}</div>
                                <div class="ket">Tersedia</div>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('sesi') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                {{-- Kegiatan --}}
                <h2 class="h6 fw-bold mt-4 mb-1">Kegiatan</h2>
                <p class="small text-muted-pr">Jelaskan singkat kegiatan yang akan dilaksanakan.</p>
                <div class="mb-3">
                    <label for="keperluan" class="form-label small fw-semibold">Keperluan</label>
                    <textarea id="keperluan" name="keperluan" rows="3" maxlength="255"
                              class="form-control @error('keperluan') is-invalid @enderror"
                              placeholder="Contoh: Rapat koordinasi panitia Dies Natalis HMIF">{{ old('keperluan') }}</textarea>
                    @error('keperluan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="alert alert-primary small d-flex gap-2 mb-4" role="alert" style="background: var(--pr-primary-soft); border-color: transparent; color: var(--pr-primary);">
                    <i class="bi bi-info-circle"></i>
                    <div>Status menjadi Menunggu sampai admin menyetujui atau menolak pengajuan.</div>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2">
                    <button type="submit" class="btn btn-primary px-4">Kirim Pengajuan</button>
                    <a href="{{ route('home') }}" class="btn btn-link text-decoration-none text-muted-pr">Batal</a>
                </div>
            </div>
        </div>
    </form>

    <script>
        (function () {
            const rooms = @json($rooms);
            const taken = @json($taken); // { room_id: { 'YYYY-MM-DD': [sesi, ...] } }
            const oldRoom = @json(old('room_id'));

            const gedung = document.getElementById('gedung');
            const lantai = document.getElementById('lantai');
            const room = document.getElementById('room_id');
            const tanggal = document.getElementById('tanggal');
            const chips = document.querySelectorAll('#sesiList input[name="sesi"]');

            const uniq = (arr) => [...new Set(arr)];
            const fill = (el, items, placeholder) => {
                el.innerHTML = '';
                const first = new Option(placeholder, '');
                el.add(first);
                items.forEach(([value, label]) => el.add(new Option(label, value)));
            };

            function loadGedung(selected) {
                fill(gedung, uniq(rooms.map(r => r.gedung)).map(g => [g, 'Gedung ' + g]), 'Pilih gedung');
                if (selected) gedung.value = selected;
            }
            function loadLantai(selected) {
                const list = uniq(rooms.filter(r => r.gedung === gedung.value).map(r => r.lantai)).sort((a, b) => a - b);
                fill(lantai, list.map(l => [l, 'Lantai ' + l]), 'Pilih lantai');
                lantai.disabled = !gedung.value;
                if (selected) lantai.value = selected;
            }
            function loadRoom(selected) {
                const list = rooms.filter(r => r.gedung === gedung.value && String(r.lantai) === String(lantai.value));
                fill(room, list.map(r => [r.id, r.nama + ' (' + r.kapasitas + ' org)']), 'Pilih ruangan');
                room.disabled = !lantai.value;
                if (selected) room.value = selected;
            }

            // Tandai sesi yang sudah terisi untuk ruangan + tanggal yang dipilih
            function refreshSesi() {
                const busy = ((taken[room.value] || {})[tanggal.value]) || [];
                chips.forEach(chip => {
                    const isBusy = busy.includes(Number(chip.dataset.sesi));
                    chip.disabled = isBusy;
                    if (isBusy && chip.checked) chip.checked = false;
                    chip.nextElementSibling.querySelector('.ket').textContent = isBusy ? 'Terisi' : (chip.checked ? 'Dipilih' : 'Tersedia');
                });
            }

            gedung.addEventListener('change', () => { loadLantai(); loadRoom(); refreshSesi(); });
            lantai.addEventListener('change', () => { loadRoom(); refreshSesi(); });
            room.addEventListener('change', refreshSesi);
            tanggal.addEventListener('change', refreshSesi);
            chips.forEach(c => c.addEventListener('change', refreshSesi));

            // Isi awal (juga memulihkan pilihan jika validasi gagal)
            const current = oldRoom ? rooms.find(r => String(r.id) === String(oldRoom)) : null;
            loadGedung(current ? current.gedung : null);
            loadLantai(current ? current.lantai : null);
            loadRoom(current ? current.id : null);
            refreshSesi();
        })();
    </script>
@endsection
