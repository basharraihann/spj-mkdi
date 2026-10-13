<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('anggaran_items', function (Blueprint $t) {
            $t->bigInteger('realisasi_lalu')->default(0)->after('pagu');
            $t->bigInteger('realisasi_ini')->default(0)->after('realisasi_lalu');
        });
    }

    public function down(): void
    {
        Schema::table('anggaran_items', function (Blueprint $t) {
            $t->dropColumn(['realisasi_lalu', 'realisasi_ini']);
        });
    }
};
