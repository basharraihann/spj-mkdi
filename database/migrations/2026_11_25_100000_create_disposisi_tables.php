<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modul Disposisi (Persuratan).
 *
 * disposisi_surat : surat masuk yang dicatat TU (satu baris per surat)
 * disposisi       : alur disposisi berjenjang (pohon). parent_id = disposisi sebelumnya
 *                   yang diteruskan, null = disposisi pertama dari TU.
 *
 * Hanya MEMBUAT tabel baru, tidak mengubah tabel lain.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('disposisi_surat')) {
            Schema::create('disposisi_surat', function (Blueprint $t) {
                $t->id();
                $t->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
                $t->string('nomor_agenda')->nullable();
                $t->string('nomor_surat');
                $t->date('tanggal_surat')->nullable();
                $t->date('tanggal_terima');
                $t->string('asal_surat');
                $t->text('perihal');
                $t->string('sifat', 20)->default('biasa');      // biasa | segera | sangat_segera | rahasia
                $t->string('lampiran')->nullable();
                $t->string('file_path')->nullable();             // disk local (privat)
                $t->string('file_name')->nullable();
                $t->string('status', 20)->default('proses');     // proses | selesai
                $t->timestamp('selesai_at')->nullable();
                $t->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();

                $t->index(['unit_id', 'status']);
            });
        }

        if (!Schema::hasTable('disposisi')) {
            Schema::create('disposisi', function (Blueprint $t) {
                $t->id();
                $t->foreignId('surat_id')->constrained('disposisi_surat')->cascadeOnDelete();
                $t->foreignId('parent_id')->nullable()->constrained('disposisi')->nullOnDelete();
                $t->foreignId('dari_user_id')->nullable()->constrained('users')->nullOnDelete();
                $t->foreignId('kepada_user_id')->nullable()->constrained('users')->nullOnDelete();
                $t->json('instruksi')->nullable();
                $t->text('catatan')->nullable();
                $t->date('batas_waktu')->nullable();
                $t->string('status', 20)->default('baru');       // baru | dibaca | diteruskan | selesai
                $t->timestamp('dibaca_at')->nullable();
                $t->timestamp('diteruskan_at')->nullable();
                $t->timestamp('selesai_at')->nullable();
                $t->text('laporan')->nullable();                  // laporan tindak lanjut
                $t->string('laporan_file_path')->nullable();
                $t->string('laporan_file_name')->nullable();
                $t->timestamps();

                $t->index(['kepada_user_id', 'status']);
                $t->index(['dari_user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('disposisi');
        Schema::dropIfExists('disposisi_surat');
    }
};
