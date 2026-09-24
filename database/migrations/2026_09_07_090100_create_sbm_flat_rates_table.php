<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Master rate SBM per provinsi. Dipakai buat auto-patok:
     * - UH Biasa (uh_biasa) -> UH Biasa 60% dihitung 60% dari nilai ini, gak disimpan
     *   terpisah (lihat SbmRate::uhBiasa60()).
     * - Peng. Riil (peng_riil) -> dipakai kalau mode-nya "sbm" di agenda_pegawai,
     *   kalau "manual"/at cost, nominal diisi bebas per peserta.
     *
     * UH Fullday & UH FullBoard TIDAK ada di sini karena flat sama di semua
     * provinsi (lihat tabel sbm_flat_rates).
     */
    public function up(): void
    {
        Schema::create('sbm_rates', function (Blueprint $table) {
            $table->id();
            $table->string('provinsi')->unique();
            $table->decimal('uh_biasa', 12, 2);
            $table->decimal('peng_riil', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sbm_rates');
    }
};