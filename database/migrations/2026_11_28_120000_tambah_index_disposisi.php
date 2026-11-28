<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Index kepada_user_id+status dan surat_id sudah ada, jadi hanya dua ini yang ditambah.
return new class extends Migration {
    public function up(): void
    {
        Schema::table('disposisi', function (Blueprint $t) {
            $t->index('batas_waktu', 'disposisi_batas_waktu_index');
        });
        Schema::table('disposisi_surat', function (Blueprint $t) {
            $t->index('tanggal_kegiatan', 'disposisi_surat_tanggal_kegiatan_index');
        });
    }

    public function down(): void
    {
        Schema::table('disposisi', function (Blueprint $t) {
            $t->dropIndex('disposisi_batas_waktu_index');
        });
        Schema::table('disposisi_surat', function (Blueprint $t) {
            $t->dropIndex('disposisi_surat_tanggal_kegiatan_index');
        });
    }
};
