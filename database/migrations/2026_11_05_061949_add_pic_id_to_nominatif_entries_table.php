<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('nominatif_entries', function (Blueprint $table) {
            $table->foreignId('pic_id')->nullable()->after('penanggung_jawab_id')
                ->constrained('pegawai')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('nominatif_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pic_id');
        });
    }
};
