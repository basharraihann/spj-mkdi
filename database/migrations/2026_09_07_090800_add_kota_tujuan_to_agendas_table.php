<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Level kedua dari Tujuan: provinsi tetap di kolom `tujuan` (dipakai buat
     * patok rate UH via sbm_rates), kab/kota spesifiknya di kolom baru ini —
     * dropdown-nya cascading, kefilter sesuai provinsi yang dipilih di
     * `tujuan`. Nullable karena data lama (sebelum fitur ini) belum ada nilainya.
     */
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->string('kota_tujuan')->nullable()->after('tujuan');
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropColumn('kota_tujuan');
        });
    }
};
