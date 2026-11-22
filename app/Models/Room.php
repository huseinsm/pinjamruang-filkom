<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = ['nama', 'gedung', 'lantai', 'ruangan', 'kapasitas'];

    protected function casts(): array
    {
        return ['lantai' => 'integer', 'ruangan' => 'integer', 'kapasitas' => 'integer'];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // Kode tampilan: "F4.5" untuk gedung satu huruf, "Rinov 3.1" untuk gedung bernama panjang.
    // Dibentuk dari gedung + lantai + nomor ruangan, jadi tidak disimpan di database.
    public function getKodeAttribute(): string
    {
        $nomor = "{$this->lantai}.{$this->ruangan}";

        return mb_strlen($this->gedung) > 1 ? "{$this->gedung} {$nomor}" : "{$this->gedung}{$nomor}";
    }
}
