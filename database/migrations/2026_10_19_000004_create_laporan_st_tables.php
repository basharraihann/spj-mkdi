<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Satu laporan per ST. "Belum diisi" = belum ada baris di sini.
        Schema::create('laporan_st', function (Blueprint $t) {
            $t->id();
            $t->foreignId('surat_tugas_id')->unique()->constrained('surat_tugas')->cascadeOnDelete();
            $t->string('status', 20)->default('draf'); // draf | dikirim
            $t->longText('notulensi')->nullable();     // wajib saat dikirim (dicek di controller)
            $t->text('keputusan')->nullable();
            $t->foreignId('diperbarui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('dikirim_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('dikirim_at')->nullable();
            $t->timestamps();
        });

        Schema::create('laporan_st_tindak_lanjut', function (Blueprint $t) {
            $t->id();
            $t->foreignId('laporan_st_id')->constrained('laporan_st')->cascadeOnDelete();
            $t->unsignedSmallInteger('urutan')->default(0);
            $t->text('uraian');
            $t->string('pic', 150)->nullable();
            $t->date('tenggat')->nullable();
            $t->timestamps();
        });

        // Satu baris per akun peserta yang harus lapor. Dasar halaman "Tugas Saya".
        Schema::create('laporan_st_tugas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('surat_tugas_id')->constrained('surat_tugas')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('status', 20)->default('menunggu'); // menunggu | selesai | ditutup
            $t->timestamps();

            $t->unique(['surat_tugas_id', 'user_id']);
            $t->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_st_tugas');
        Schema::dropIfExists('laporan_st_tindak_lanjut');
        Schema::dropIfExists('laporan_st');
    }
};
