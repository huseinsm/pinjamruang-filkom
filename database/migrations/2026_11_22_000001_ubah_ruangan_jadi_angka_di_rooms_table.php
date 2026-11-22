<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Nomor ruangan sekarang hanya angka per lantai (contoh: 5),
// kode lengkap seperti F4.5 dibentuk dari gedung + lantai + ruangan (Room::kode).
return new class extends Migration
{
    public function up(): void
    {
        // "4.5" -> "5" (ambil angka setelah titik terakhir)
        foreach (DB::table('rooms')->get(['id', 'ruangan']) as $room) {
            $nomor = (int) last(explode('.', (string) $room->ruangan));
            DB::table('rooms')->where('id', $room->id)->update(['ruangan' => (string) max($nomor, 1)]);
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropUnique(['gedung', 'ruangan']);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedSmallInteger('ruangan')->change();
            $table->unique(['gedung', 'lantai', 'ruangan']);
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropUnique(['gedung', 'lantai', 'ruangan']);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->string('ruangan', 10)->change();
        });

        // 5 di lantai 4 -> "4.5"
        foreach (DB::table('rooms')->get(['id', 'lantai', 'ruangan']) as $room) {
            DB::table('rooms')->where('id', $room->id)->update(['ruangan' => $room->lantai.'.'.$room->ruangan]);
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->unique(['gedung', 'ruangan']);
        });
    }
};
