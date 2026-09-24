<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Nomor memo yang dibuat mandiri lewat halaman "Buat Nomor Memo", tanpa
     * terikat ke agenda manapun. Dipakai bareng dengan nomor_memo_pns/non_pns
     * di tabel agendas untuk menentukan "nomor berikutnya" (lihat
     * App\Services\NomorMemoService) dan ditampilkan gabung di memos/index.
     */
    public function up(): void
    {
        Schema::create('memo_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('nomor_urut'); // bagian angka di depan, mis. 325
            $table->string('nomor_memo'); // nomor lengkap, mis. 325/LS.D1.PPK/KU.00/09/2026
            $table->date('tanggal_memo');
            $table->string('uraian_kegiatan')->nullable();
            $table->foreignId('pic_id')->nullable()->constrained('pegawai')->nullOnDelete();
            $table->string('mak')->nullable();
            $table->unsignedBigInteger('nominal')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memo_entries');
    }
};
