<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Nomor ST khusus buat peserta yang jabatannya "Kepala Biro" (Karo) — beda
     * dari `nomor_st` yang dipakai bareng semua peserta lain di agenda yang sama.
     * Nullable karena cuma relevan kalau salah satu peserta yang dipilih emang
     * Karo (dideteksi dari jabatan-nya, bukan field terpisah "siapa Karo-nya").
     */
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->string('nomor_st_karo')->nullable()->after('nomor_st');
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropColumn('nomor_st_karo');
        });
    }
};
