<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Rate transportasi ke terminal/bandara/stasiun/pelabuhan, dipakai sebagai
     * dasar Peng. Riil mode "SBM". Dropdown-nya BEBAS dipilih (gak terikat ke
     * Tujuan agenda), makanya tabelnya berdiri sendiri dari sbm_rates:
     * - kategori 'provinsi'    -> rate per provinsi (34 baris dari SBM)
     * - kategori 'jabodetabek' -> rate DKI Jakarta ke kab/kota sekitar (9 baris)
     *
     * Nilai di kolom `rate_one_way` itu SATU ARAH. Nominal yang dipakai di
     * aplikasi = 2x nilai ini (PP / pergi-pulang) — lihat
     * PengRiilRate::getRatePpAttribute().
     */
    public function up(): void
    {
        Schema::create('peng_riil_rates', function (Blueprint $table) {
            $table->id();
            $table->enum('kategori', ['provinsi', 'jabodetabek']);
            $table->string('tujuan');
            $table->decimal('rate_one_way', 12, 2);
            $table->timestamps();

            $table->unique(['kategori', 'tujuan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peng_riil_rates');
    }
};
