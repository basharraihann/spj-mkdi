<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Izin "minus": memo boleh disimpan walau nominalnya melebihi sisa efektif MAK,
 * TAPI wajib alasan + dasar, dan tidak bisa dikirim ke Keuangan sebelum izinnya
 * disetujui PPK / Keuangan (Gate setujui-minus).
 *
 * Satu memo bisa punya beberapa baris (riwayat). Yang berlaku = baris terakhir (id terbesar).
 * memo_key memakai format yang sama dengan daftar memo: entry-12, agenda-3-pns, agenda-3-non_pns.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('memo_izin_minus', function (Blueprint $t) {
            $t->id();
            $t->string('memo_key')->index();
            $t->string('mak')->nullable();
            $t->bigInteger('nominal')->default(0);
            $t->bigInteger('sisa_efektif')->default(0);   // boleh negatif
            $t->bigInteger('kekurangan')->default(0);     // nominal - sisa_efektif
            $t->text('alasan');
            $t->string('dasar')->nullable();              // mis. nomor nota dinas / revisi POK yang sedang diproses
            $t->foreignId('diajukan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->string('status', 20)->default('menunggu'); // menunggu | disetujui | ditolak
            $t->foreignId('diputus_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('diputus_at')->nullable();
            $t->text('catatan_putusan')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memo_izin_minus');
    }
};
