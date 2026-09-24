<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Master kab/kota, buat dropdown cascading di form Buat/Edit Agenda:
     * pilih Provinsi (dari sbm_rates.provinsi) dulu -> baru dropdown Kab/Kota
     * ini kefilter sesuai provinsi yang dipilih.
     *
     * `provinsi` sengaja disimpan sbg string (sama kayak sbm_rates.provinsi),
     * bukan foreign key numerik — biar konsisten dg pola yang udah dipakai di
     * seluruh fitur SBM ini (agendas.tujuan juga string, matching by name).
     */
    public function up(): void
    {
        Schema::create('kabupaten_kota', function (Blueprint $table) {
            $table->id();
            $table->string('provinsi');
            $table->string('nama');
            $table->timestamps();

            $table->unique(['provinsi', 'nama']);
            $table->index('provinsi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kabupaten_kota');
    }
};
