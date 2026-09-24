<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Alat angkut yang dipakai buat perjalanan dinas ini — muncul di SPD.
     */
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->enum('alat_angkut', ['darat', 'udara', 'laut', 'darat_udara'])
                ->nullable()
                ->after('kota_tujuan');
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropColumn('alat_angkut');
        });
    }
};
