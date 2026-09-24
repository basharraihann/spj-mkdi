<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agenda_pegawai', function (Blueprint $table) {
            // Komponen biaya tambahan (di luar tiket/peng_riil/lumpsum/representatif/hotel yg sudah ada)
            $table->decimal('dukungan_transportasi', 12, 2)->nullable()->after('hotel');
            $table->decimal('transportasi_darat', 12, 2)->nullable()->after('dukungan_transportasi');
            $table->decimal('transportasi_lokal', 12, 2)->nullable()->after('transportasi_darat');
            $table->decimal('penginapan_30', 12, 2)->nullable()->after('transportasi_lokal');
            $table->decimal('belanja_bahan', 12, 2)->nullable()->after('penginapan_30');
            $table->decimal('honor_narsum', 12, 2)->nullable()->after('belanja_bahan');

            // Breakdown lumpsum tambahan utk 2 jenis UH baru: Biasa 60% & Fullday
            // (hari_dinas_biasa/rate_dinas_biasa = UH Biasa, hari_fullboard/rate_fullboard = UH FullBoard, sudah ada)
            $table->unsignedTinyInteger('hari_biasa_60')->nullable()->after('rate_fullboard');
            $table->decimal('rate_biasa_60', 12, 2)->nullable()->after('hari_biasa_60');
            $table->unsignedTinyInteger('hari_fullday')->nullable()->after('rate_biasa_60');
            $table->decimal('rate_fullday', 12, 2)->nullable()->after('hari_fullday');
        });
    }

    public function down(): void
    {
        Schema::table('agenda_pegawai', function (Blueprint $table) {
            $table->dropColumn([
                'dukungan_transportasi', 'transportasi_darat', 'transportasi_lokal',
                'penginapan_30', 'belanja_bahan', 'honor_narsum',
                'hari_biasa_60', 'rate_biasa_60', 'hari_fullday', 'rate_fullday',
            ]);
        });
    }
};
