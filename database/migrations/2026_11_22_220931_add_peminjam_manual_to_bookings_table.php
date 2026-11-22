<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('nama_peminjam', 100)->nullable()->after('user_id');
            $table->string('kontak_peminjam', 100)->nullable()->after('nama_peminjam');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['nama_peminjam', 'kontak_peminjam']);
        });
    }
};