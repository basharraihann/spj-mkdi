<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agenda_pegawai', function (Blueprint $table) {
            $table->dropColumn(['lumpsum_jatim', 'lumpsum_jateng']);
            $table->decimal('lumpsum', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('agenda_pegawai', function (Blueprint $table) {
            $table->dropColumn('lumpsum');
            $table->decimal('lumpsum_jatim', 12, 2)->nullable();
            $table->decimal('lumpsum_jateng', 12, 2)->nullable();
        });
    }
};
