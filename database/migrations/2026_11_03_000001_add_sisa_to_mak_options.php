<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mak_options', function (Blueprint $table) {
            if (!Schema::hasColumn('mak_options', 'pagu')) {
                $table->bigInteger('pagu')->default(0)->after('uraian_belanja');
            }
            if (!Schema::hasColumn('mak_options', 'realisasi')) {
                $table->bigInteger('realisasi')->default(0)->after('pagu');
            }
            if (!Schema::hasColumn('mak_options', 'sisa')) {
                $table->bigInteger('sisa')->default(0)->after('realisasi'); // boleh negatif (realisasi melebihi pagu)
            }
            if (!Schema::hasColumn('mak_options', 'periode')) {
                $table->string('periode', 30)->nullable()->after('sisa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mak_options', function (Blueprint $table) {
            $table->dropColumn(['pagu', 'realisasi', 'sisa', 'periode']);
        });
    }
};
