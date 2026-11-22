<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = ['gedung', 'lantai', 'ruangan', 'kapasitas'];

    protected function casts(): array
    {
        return ['lantai' => 'integer', 'ruangan' => 'integer', 'kapasitas' => 'integer'];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // Nama/kode ruangan: gedung + lantai + nomor, contoh "F 2.4" = gedung F, lantai 2, ruangan nomor 4.
    // Tidak disimpan di database.
    public function getKodeAttribute(): string
    {
        return "{$this->gedung} {$this->lantai}.{$this->ruangan}";
    }

    // Alias agar $room->nama tetap bisa dipakai di tampilan
    public function getNamaAttribute(): string
    {
        return $this->kode;
    }
}
