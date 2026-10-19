<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->date('tanggal_mulai')->nullable()->after('tanggal');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            $table->string('kota_tujuan', 100)->nullable()->after('tanggal_selesai');
            $table->string('tujuan')->nullable()->after('kota_tujuan');
        });
    }

    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->dropColumn(['tanggal_mulai', 'tanggal_selesai', 'kota_tujuan', 'tujuan']);
        });
    }
};