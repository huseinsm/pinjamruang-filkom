<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'room_id', 'tanggal', 'sesi', 'keperluan', 'status',
        'nama_peminjam', 'kontak_peminjam',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    // Jam sesi, contoh: "09.30 – 12.00"
    public function getJamSesiAttribute(): string
    {
        $s = config('sesi')[$this->sesi] ?? null;
        return $s ? "{$s['mulai']} – {$s['selesai']}" : '-';
    }

    public function getIsManualAttribute(): bool
    {
        return $this->nama_peminjam !== null;
    }

    public function getNamaPemohonAttribute(): string
    {
        return $this->nama_peminjam ?? $this->user->name;
    }
}