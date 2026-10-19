<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_st_files', function (Blueprint $t) {
            $t->id();
            $t->foreignId('surat_tugas_id')->constrained('surat_tugas')->cascadeOnDelete();
            $t->foreignId('uploaded_by')->constrained('users');
            $t->string('kategori');                 // notulensi_nd | materi
            $t->string('nama_asli');
            $t->string('path');
            $t->string('mime')->nullable();
            $t->unsignedBigInteger('ukuran')->default(0);
            $t->timestamps();

            $t->index(['surat_tugas_id', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_st_files');
    }
};
