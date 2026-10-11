<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_pegawai', function (Blueprint $table) {
            // Null = peserta ikut rentang tanggal agenda penuh.
            // Diisi = tanggal khusus peserta (mis. cuma 1 atau 2 hari dari ST 3 hari).
            $table->date('tanggal_mulai')->nullable()->after('lama_hari');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
        });
    }

    public function down(): void
    {
        Schema::table('agenda_pegawai', function (Blueprint $table) {
            $table->dropColumn(['tanggal_mulai', 'tanggal_selesai']);
        });
    }
};
