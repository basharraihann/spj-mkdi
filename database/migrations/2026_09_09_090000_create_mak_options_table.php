<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Master MAK per unit — dipakai jadi dropdown di section "Anggaran &
     * Administrasi" (create/edit agenda), gantiin input teks manual.
     *
     * `mak` = kode lengkap 6 segmen ("7459.ABR.006.076.EE.524111"), harus sama
     * persis formatnya kayak yang divalidasi regex di
     * AgendaController::validateAdministrasiFields().
     *
     * Uraian giat/komponen/akun_ap/belanja disimpan di sini juga, biar milih
     * MAK di dropdown otomatis ngisi ke-4 uraian itu sekaligus (gak perlu
     * ngetik manual per field kayak sekarang).
     */
    public function up(): void
    {
        Schema::create('mak_options', function (Blueprint $table) {
            $table->id();
            $table->string('mak')->unique();
            $table->string('uraian_giat')->nullable();
            $table->string('uraian_komponen')->nullable();
            $table->string('uraian_akun_ap')->nullable();
            $table->string('uraian_belanja')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mak_options');
    }
};
