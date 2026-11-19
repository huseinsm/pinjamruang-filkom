@extends('layouts.guest')
@section('title', 'PinjamRuang FILKOM — Peminjaman Ruang FILKOM UB')

@section('content')
{{-- Desktop / Landing & Mobile / Landing — Husein (HSM) --}}
<section class="pr-container hero">
    <div class="hero-copy">
        <span class="hero-badge"><span><span class="d-none d-lg-inline">Fakultas Ilmu Komputer</span><span class="d-lg-none">FILKOM</span> · Universitas Brawijaya</span></span>
        <h1 class="t-display">Pinjam ruang kuliah tanpa bolak-balik ke loket.</h1>
        <p class="t-body-lg">
            <span class="d-none d-lg-inline">Cek ruangan yang masih kosong, ajukan peminjaman untuk kegiatanmu, lalu pantau persetujuan admin dari satu tempat.</span>
            <span class="d-lg-none">Cek ruangan yang masih kosong, ajukan peminjaman, lalu pantau persetujuan admin dari satu tempat.</span>
        </p>
        <div class="hero-actions">
            <a href="{{ url('/pengajuan/buat') }}" class="btn btn-primary">Ajukan Peminjaman</a>
            <a href="{{ url('/jadwal') }}" class="btn btn-secondary-pr">Lihat Jadwal Ruangan</a>
        </div>
        <div class="hero-stats">
            <div class="hero-stat">
                <span class="hero-stat-value">4 gedung</span>
                <span class="hero-stat-label"><span class="d-none d-lg-inline">F, G, A &amp; Rinov</span><span class="d-lg-none">F, G, A, Rinov</span></span>
            </div>
            <span class="hero-divider" aria-hidden="true"></span>
            <div class="hero-stat">
                <span class="hero-stat-value">4 sesi</span>
                <span class="hero-stat-label"><span class="d-none d-lg-inline">setiap hari</span><span class="d-lg-none">per hari</span></span>
            </div>
            <span class="hero-divider" aria-hidden="true"></span>
            <div class="hero-stat">
                <span class="hero-stat-value">≤ 1 hari</span>
                <span class="hero-stat-label"><span class="d-none d-lg-inline">waktu verifikasi</span><span class="d-lg-none">verifikasi</span></span>
            </div>
        </div>
    </div>
    <div class="hero-image">
        <img src="{{ asset('images/gedung-filkom.jpg') }}" alt="Gedung FILKOM Universitas Brawijaya" width="1232" height="961">
    </div>
</section>

<section class="pr-container steps-section" id="cara-kerja">
    <div class="steps-head">
        <span class="t-overline text-accent">Cara kerja</span>
        <h2 class="t-h1">Tiga langkah, tanpa surat bolak-balik.</h2>
    </div>
    <div class="steps">
        <div class="step">
            <span class="t-overline text-accent">01</span>
            <div class="step-body">
                <h3 class="t-h2">Pilih ruang &amp; sesi</h3>
                <p class="t-body">
                    <span class="d-none d-lg-inline">Pilih gedung, lantai, dan ruangan, lalu sesi yang masih kosong pada tanggal kegiatan.</span>
                    <span class="d-lg-none">Pilih gedung, lantai, ruangan, lalu sesi yang masih kosong.</span>
                </p>
            </div>
        </div>
        <div class="step">
            <span class="t-overline text-accent">02</span>
            <div class="step-body">
                <h3 class="t-h2">Ajukan kegiatan</h3>
                <p class="t-body">
                    <span class="d-none d-lg-inline">Isi keperluan singkat. Sesi yang sudah disetujui untuk orang lain tidak bisa dipilih.</span>
                    <span class="d-lg-none">Isi keperluan singkat lalu kirim pengajuan.</span>
                </p>
            </div>
        </div>
        <div class="step">
            <span class="t-overline text-accent">03</span>
            <div class="step-body">
                <h3 class="t-h2">Tunggu persetujuan</h3>
                <p class="t-body">
                    <span class="d-none d-lg-inline">Admin meninjau pengajuan. Statusnya berubah menjadi Disetujui atau Ditolak di akunmu.</span>
                    <span class="d-lg-none">Admin meninjau, status berubah di akunmu.</span>
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
