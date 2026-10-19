<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Hanya MENAMBAH 2 kolom nullable. Data ST dan peserta yang sudah ada tidak berubah.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_tugas_pegawai', function (Blueprint $t) {
            if (! Schema::hasColumn('surat_tugas_pegawai', 'tanggal_mulai')) {
                $t->date('tanggal_mulai')->nullable()->after('jabatan');
            }
            if (! Schema::hasColumn('surat_tugas_pegawai', 'tanggal_selesai')) {
                $t->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surat_tugas_pegawai', function (Blueprint $t) {
            foreach (['tanggal_mulai', 'tanggal_selesai'] as $c) {
                if (Schema::hasColumn('surat_tugas_pegawai', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
    }
};