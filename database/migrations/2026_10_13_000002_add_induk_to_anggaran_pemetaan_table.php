<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggaran_pemetaan', function (Blueprint $table) {
            // Induk unit kerja, mis. "Asdep 1 D1" -> induk "D1". Kosong = unit itu sendiri.
            $table->string('induk', 50)->nullable()->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('anggaran_pemetaan', function (Blueprint $table) {
            $table->dropColumn('induk');
        });
    }
};
