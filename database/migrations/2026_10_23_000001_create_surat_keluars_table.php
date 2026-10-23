<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_keluars', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_dokumen'); // nota_dinas, undangan, telaah_staf, notula, surat_pernyataan
            $table->string('nomor_surat')->nullable();
            $table->string('sifat')->default('biasa'); // biasa, terbatas, rahasia
            $table->string('hal');
            $table->date('tanggal_surat');
            $table->string('tujuan')->nullable();
            $table->string('dari')->nullable();
            $table->json('isi_payload'); // Menyimpan atribut spesifik dokumen (agenda, tempat, analisis, kesimpulan, dll)
            $table->json('tembusan')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('status_persetujuan')->default('draft'); // draft, diperiksa_es3, disetujui_es2, disetujui_es1, terbit
            $table->string('file_scan_ttd')->nullable(); // Untuk mengunggah hasil TTD basah/scan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keluars');
    }
};