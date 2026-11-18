<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('memo_progres', function (Blueprint $t) {
            $t->id();
            $t->string('memo_key')->unique();
            $t->string('tahap')->default('draft');
            $t->timestamp('dikirim_at')->nullable();
            $t->foreignId('dikirim_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('memo_pengajuan_kembali', function (Blueprint $t) {
            $t->id();
            $t->string('memo_key')->index();
            $t->foreignId('diajukan_oleh')->constrained('users');
            $t->text('alasan');
            $t->string('status')->default('menunggu'); // menunggu, disetujui, ditolak
            $t->foreignId('diputus_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('diputus_at')->nullable();
            $t->text('catatan_putusan')->nullable();
            $t->timestamps();
        });

        Schema::create('memo_verifikasi_logs', function (Blueprint $t) {
            $t->id();
            $t->string('memo_key')->index();
            $t->string('gerbang');              // K2 atau K3
            $t->boolean('lengkap_sesuai');
            $t->text('alasan')->nullable();
            $t->foreignId('user_id')->constrained('users');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memo_verifikasi_logs');
        Schema::dropIfExists('memo_pengajuan_kembali');
        Schema::dropIfExists('memo_progres');
    }
};