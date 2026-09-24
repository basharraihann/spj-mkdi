<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            // Lampiran Surat - kode giat/komponen/akun
            $table->string('kode_giat')->nullable();
            $table->string('uraian_giat')->nullable();
            $table->string('kode_komponen')->nullable();
            $table->string('uraian_komponen')->nullable();
            $table->string('kode_akun_ap')->nullable();      // baris "AP"
            $table->string('uraian_akun_ap')->nullable();
            $table->string('kode_belanja')->nullable();      // baris "524111"
            $table->string('uraian_belanja')->nullable();

            // Petugas verifikasi (pegawai)
            $table->foreignId('petugas_verifikasi_id')->nullable()
                ->constrained('pegawai')->nullOnDelete();

            // Lampiran Memo - klasifikasi & anggaran
            $table->string('klasifikasi')->default('PDN'); // TKDN / PDN / Impor
            $table->decimal('nilai_persen', 5, 2)->default(100);
            $table->decimal('pagu', 15, 2)->nullable();
            $table->decimal('pengajuan_nominal', 15, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('petugas_verifikasi_id');
            $table->dropColumn([
                'kode_giat', 'uraian_giat',
                'kode_komponen', 'uraian_komponen',
                'kode_akun_ap', 'uraian_akun_ap',
                'kode_belanja', 'uraian_belanja',
                'klasifikasi', 'nilai_persen',
                'pagu', 'pengajuan_nominal',
            ]);
        });
    }
};
