<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Nama ruangan tidak lagi diketik admin: dibentuk otomatis dari gedung + lantai + nomor
// (contoh "G 1.2"), lihat Room::kode / Room::nama.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('nama');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('nama', 100)->default('')->after('id');
        });

        foreach (DB::table('rooms')->get(['id', 'gedung', 'lantai', 'ruangan']) as $room) {
            DB::table('rooms')->where('id', $room->id)
                ->update(['nama' => "{$room->gedung} {$room->lantai}.{$room->ruangan}"]);
        }
    }
};
