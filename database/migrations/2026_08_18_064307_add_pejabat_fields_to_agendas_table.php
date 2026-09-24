<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->foreignId('ppk_id')->nullable()->constrained('pegawai')->nullOnDelete();
            $table->foreignId('bendahara_id')->nullable()->constrained('pegawai')->nullOnDelete();
            $table->foreignId('penanggung_jawab_id')->nullable()->constrained('pegawai')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ppk_id');
            $table->dropConstrainedForeignId('bendahara_id');
            $table->dropConstrainedForeignId('penanggung_jawab_id');
        });
    }
};
