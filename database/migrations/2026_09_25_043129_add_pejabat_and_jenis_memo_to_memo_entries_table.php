<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('memo_entries', function (Blueprint $table) {
            $table->string('jenis_memo')->nullable()->after('nomor_memo'); // 'konsumsi' | 'honorarium'
            $table->foreignId('ppk_id')->nullable()->after('pic_id')->constrained('pegawai')->nullOnDelete();
            $table->foreignId('bendahara_id')->nullable()->after('ppk_id')->constrained('pegawai')->nullOnDelete();
            $table->foreignId('penanggung_jawab_id')->nullable()->after('bendahara_id')->constrained('pegawai')->nullOnDelete();
            $table->foreignId('petugas_verifikasi_id')->nullable()->after('penanggung_jawab_id')->constrained('pegawai')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('memo_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ppk_id');
            $table->dropConstrainedForeignId('bendahara_id');
            $table->dropConstrainedForeignId('penanggung_jawab_id');
            $table->dropConstrainedForeignId('petugas_verifikasi_id');
            $table->dropColumn('jenis_memo');
        });
    }
};