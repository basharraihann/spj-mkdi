<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lembar kontrol Surat Tugas: konseptor -> pemeriksa -> persetujuan Eselon 2
 * -> kontrol & penomoran admin Sesdep -> persetujuan Sesdep.
 * Tabel terpisah dan opsional: tidak mengubah tabel surat_tugas maupun alur lama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_tugas_kontrol', function (Blueprint $t) {
            $t->id();
            $t->foreignId('surat_tugas_id')->constrained('surat_tugas')->cascadeOnDelete();
            $t->unsignedTinyInteger('urutan');
            $t->string('tahap', 40); // konseptor, pemeriksa, persetujuan_es2, kontrol_sesdep, persetujuan_sesdep
            $t->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('status', 20)->default('menunggu'); // menunggu, disetujui, ditolak
            $t->text('catatan')->nullable();
            $t->timestamp('acted_at')->nullable();
            $t->timestamps();

            $t->unique(['surat_tugas_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_tugas_kontrol');
    }
};
