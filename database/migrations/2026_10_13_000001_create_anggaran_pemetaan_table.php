<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('anggaran_pemetaan', function (Blueprint $t) {
            $t->id();
            // Kolom kosong disimpan sebagai '' (bukan NULL) supaya unique index bekerja di MySQL.
            $t->string('keg_kode', 30)->default('');
            $t->string('ro_kode', 30);
            $t->string('komp_kode', 30)->default('');
            $t->string('sub_kode', 30)->default('');
            $t->string('unit', 50);
            $t->string('sub_unit', 50)->nullable();
            $t->timestamps();

            $t->unique(['keg_kode', 'ro_kode', 'komp_kode', 'sub_kode'], 'pemetaan_unik');
            $t->index('unit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggaran_pemetaan');
    }
};
