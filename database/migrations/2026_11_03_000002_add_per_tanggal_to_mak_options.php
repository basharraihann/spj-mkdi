<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mak_options', function (Blueprint $table) {
            if (!Schema::hasColumn('mak_options', 'per_tanggal')) {
                // Tanggal laporan FA yang menjadi sumber angka pagu/realisasi/sisa
                $table->date('per_tanggal')->nullable()->after('periode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mak_options', function (Blueprint $table) {
            $table->dropColumn('per_tanggal');
        });
    }
};
