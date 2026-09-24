<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Peng. Riil per provinsi belum semuanya ada datanya (data "Uang Representasi"
     * belum lengkap), jadi kolomnya perlu boleh null — beda dari uh_biasa yang
     * udah lengkap dari awal. Ini alter terpisah karena migration
     * create_sbm_rates_table sebelumnya udah kejalanin duluan (NOT NULL).
     */
    public function up(): void
    {
        Schema::table('sbm_rates', function (Blueprint $table) {
            $table->decimal('peng_riil', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sbm_rates', function (Blueprint $table) {
            $table->decimal('peng_riil', 12, 2)->nullable(false)->change();
        });
    }
};
