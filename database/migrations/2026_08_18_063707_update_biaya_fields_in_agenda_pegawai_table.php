<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agenda_pegawai', function (Blueprint $table) {
            $table->dropColumn(['uang_harian', 'transport', 'hotel', 'biaya_lainnya']);
            $table->decimal('tiket', 12, 2)->nullable();
            $table->decimal('peng_riil', 12, 2)->nullable();
            $table->decimal('lumpsum_jatim', 12, 2)->nullable();
            $table->decimal('lumpsum_jateng', 12, 2)->nullable();
            $table->decimal('representatif', 12, 2)->nullable();
            $table->decimal('hotel', 12, 2)->nullable();
            $table->integer('lama_hari')->nullable();
            $table->string('keterangan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('agenda_pegawai', function (Blueprint $table) {
            $table->dropColumn(['tiket', 'peng_riil', 'lumpsum_jatim', 'lumpsum_jateng', 'representatif', 'hotel', 'lama_hari', 'keterangan']);
            $table->decimal('uang_harian', 12, 2)->nullable();
            $table->decimal('transport', 12, 2)->nullable();
            $table->decimal('hotel', 12, 2)->nullable();
            $table->decimal('biaya_lainnya', 12, 2)->nullable();
        });
    }
};
