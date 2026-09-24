<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            // Komponen biaya yang berlaku utk seluruh peserta di agenda ini,
            // dipilih di step "Buat Agenda". Disimpan sbg array string, mis:
            // ["tiket","hotel","lumpsum","representatif"]
            $table->json('komponen_biaya')->nullable()->after('status');

            // Jenis uang harian (sub-tipe lumpsum) yg dipakai di agenda ini,
            // mis: ["uh_biasa","uh_fullboard"]. Hanya relevan kalau
            // komponen_biaya mengandung "lumpsum".
            $table->json('jenis_uang_harian')->nullable()->after('komponen_biaya');
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropColumn(['komponen_biaya', 'jenis_uang_harian']);
        });
    }
};
