<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Hanya MENAMBAH tabel baru. Tabel SPJ/anggaran lama tidak disentuh.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('surat_tugas_counters')) {
            Schema::create('surat_tugas_counters', function (Blueprint $t) {
                $t->id();
                $t->unsignedSmallInteger('tahun')->unique();
                $t->unsignedInteger('last_number')->default(0);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('surat_tugas')) {
            Schema::create('surat_tugas', function (Blueprint $t) {
                $t->id();
                $t->string('nomor')->nullable()->unique();
                $t->unsignedInteger('nomor_urut')->nullable();
                $t->unsignedSmallInteger('tahun')->nullable();
                $t->string('kode_klasifikasi', 50);
                $t->string('status', 20)->default('draf'); // draf, terbit, selesai, batal
                $t->text('menimbang')->nullable();
                $t->json('dasar')->nullable();
                $t->json('untuk')->nullable();
                $t->string('kepada')->nullable();
                $t->string('tempat', 100)->default('Jakarta');
                $t->date('tanggal');
                $t->string('jabatan_penandatangan');
                $t->string('nama_penandatangan');
                $t->string('nip_penandatangan', 30)->nullable();
                $t->unsignedBigInteger('arsip_surat_id')->nullable(); // untuk modul Arsip (tahap 2)
                $t->unsignedBigInteger('created_by')->nullable();
                $t->timestamp('diterbitkan_at')->nullable();
                $t->timestamp('dibatalkan_at')->nullable();
                $t->string('alasan_batal')->nullable();
                $t->timestamps();

                $t->unique(['tahun', 'nomor_urut']);
                $t->index('status');
                $t->index('tanggal');
            });
        }

        if (! Schema::hasTable('surat_tugas_pegawai')) {
            Schema::create('surat_tugas_pegawai', function (Blueprint $t) {
                $t->id();
                $t->foreignId('surat_tugas_id')->constrained('surat_tugas')->cascadeOnDelete();
                $t->unsignedBigInteger('pegawai_id')->nullable(); // tanpa FK, data pegawai tidak terikat
                $t->unsignedSmallInteger('urutan')->default(1);
                $t->string('nama');
                $t->string('nip', 30)->nullable();
                $t->string('jabatan')->nullable();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_tugas_pegawai');
        Schema::dropIfExists('surat_tugas');
        Schema::dropIfExists('surat_tugas_counters');
    }
};
