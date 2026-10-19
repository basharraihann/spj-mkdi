<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambah kolom pelaksanaan tugas ke surat_tugas.
 * Aman dijalankan walau migration pelaksanaan versi sebelumnya (kota_tujuan) sudah pernah jalan:
 * kolom yang sudah ada dilewati, kota_tujuan dibuang bila ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        $tambah = [
            'tanggal_mulai' => fn (Blueprint $t) => $t->date('tanggal_mulai')->nullable(),
            'tanggal_selesai' => fn (Blueprint $t) => $t->date('tanggal_selesai')->nullable(),
            'provinsi' => fn (Blueprint $t) => $t->string('provinsi', 100)->nullable(),
            'kota' => fn (Blueprint $t) => $t->string('kota', 100)->nullable(),
            'tujuan' => fn (Blueprint $t) => $t->string('tujuan')->nullable(),
        ];

        foreach ($tambah as $kolom => $buat) {
            if (! Schema::hasColumn('surat_tugas', $kolom)) {
                Schema::table('surat_tugas', fn (Blueprint $t) => $buat($t));
            }
        }

        if (Schema::hasColumn('surat_tugas', 'kota_tujuan')) {
            Schema::table('surat_tugas', fn (Blueprint $t) => $t->dropColumn('kota_tujuan'));
        }
    }

    public function down(): void
    {
        foreach (['tanggal_mulai', 'tanggal_selesai', 'provinsi', 'kota', 'tujuan'] as $kolom) {
            if (Schema::hasColumn('surat_tugas', $kolom)) {
                Schema::table('surat_tugas', fn (Blueprint $t) => $t->dropColumn($kolom));
            }
        }
    }
};
