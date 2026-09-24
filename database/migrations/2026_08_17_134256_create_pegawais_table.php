<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nama_gelar')->nullable(); // nama lengkap + gelar, buat ttd formal
            $table->string('nip')->nullable(); // ada yg blm punya NIP (tenaga pendukung)
            $table->string('pangkat')->nullable(); // Pembina Tk I, Penata TK I, dll
            $table->string('golongan')->nullable(); // IV/b, III/a, dll
            $table->string('jabatan');
            $table->string('unit_kerja')->nullable();
            $table->string('status_kepegawaian')->nullable(); // 'PNS' atau 'Non PNS', dipakai Agenda utk split biaya ASN/Non-ASN
            $table->string('role_penandatangan')->nullable(); // PPK, Bendahara, Penanggung Jawab Kegiatan, Petugas Verifikasi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
