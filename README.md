# PinjamRuang FILKOM

Aplikasi web untuk mengajukan peminjaman ruangan FILKOM UB secara online.
User melihat ruangan yang tersedia lalu mengajukan peminjaman, kemudian admin
menerima atau menolak pengajuan. Sistem mencegah satu ruangan dipakai dua
kegiatan pada sesi yang sama.

## Pembagian Tugas
| Anggota | Fitur | Branch |
|---|---|---|
| Anggota 1 | Auth & Role | feat/auth |
| Anggota 2 | Manajemen Ruangan | feat/ruangan |
| Anggota 3 | Pengajuan | feat/pengajuan |
| Anggota 4 | Verifikasi | feat/verifikasi |

## Branch
- `main`: versi final dan stabil
- `dev`: penggabungan semua fitur
- `feat/*`: satu branch per fitur, dibuat dari `dev`