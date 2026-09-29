<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Satu dokumen "Daftar Honorarium Narasumber" (tanpa agenda)
        Schema::create('nominatif_entries', function (Blueprint $table) {
            $table->id();
            $table->text('uraian_kegiatan');
            $table->date('tanggal'); // tanggal dokumen, tampil di judul: "JAKARTA, 07 AGUSTUS 2026"
            $table->foreignId('ppk_id')->nullable()->constrained('pegawai')->nullOnDelete();
            $table->foreignId('bendahara_id')->nullable()->constrained('pegawai')->nullOnDelete();
            $table->timestamps();
        });

        // Satu baris = satu narasumber. Bruto, pajak, netto dihitung dari honor x OJ x persen pajak.
        Schema::create('nominatif_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nominatif_entry_id')->constrained('nominatif_entries')->cascadeOnDelete();
            $table->unsignedInteger('urutan')->default(0);
            $table->string('nama');
            $table->string('npwp')->nullable();
            $table->string('sebagai')->default('Narasumber');
            $table->string('instansi')->nullable();
            $table->string('golongan')->nullable();
            $table->string('jabatan')->nullable();
            $table->unsignedBigInteger('honor')->default(0); // tarif per OJ
            $table->unsignedInteger('oj')->default(1);       // jumlah OJ (orang jam)
            $table->decimal('pajak_persen', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nominatif_peserta');
        Schema::dropIfExists('nominatif_entries');
    }
};
