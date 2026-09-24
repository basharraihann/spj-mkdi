<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agenda_pegawai', function (Blueprint $table) {
            // Breakdown lumpsum: jumlah hari x tarif per hari, untuk 2 jenis uang harian.
            // Kolom 'lumpsum' (total) tetap dipertahankan & dihitung dari 4 kolom ini,
            // supaya Nominatif dan total di Rincian Biaya tidak perlu diubah.
            $table->unsignedTinyInteger('hari_dinas_biasa')->nullable()->after('lumpsum');
            $table->decimal('rate_dinas_biasa', 12, 2)->nullable()->after('hari_dinas_biasa');
            $table->unsignedTinyInteger('hari_fullboard')->nullable()->after('rate_dinas_biasa');
            $table->decimal('rate_fullboard', 12, 2)->nullable()->after('hari_fullboard');
        });
    }

    public function down(): void
    {
        Schema::table('agenda_pegawai', function (Blueprint $table) {
            $table->dropColumn(['hari_dinas_biasa', 'rate_dinas_biasa', 'hari_fullboard', 'rate_fullboard']);
        });
    }
};