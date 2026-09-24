<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Nyimpen mode Peng. Riil per peserta:
     * - 'sbm'    -> nominal dipatok otomatis dari sbm_rates.peng_riil sesuai
     *               agendas.tujuan, input di UI dikunci (readonly).
     * - 'manual' -> at cost, nominal diisi bebas oleh user (default kalau
     *               belum pernah diisi, biar behavior lama gak berubah).
     */
    public function up(): void
    {
        Schema::table('agenda_pegawai', function (Blueprint $table) {
            $table->enum('peng_riil_mode', ['sbm', 'manual'])
                ->default('manual')
                ->after('peng_riil');
        });
    }

    public function down(): void
    {
        Schema::table('agenda_pegawai', function (Blueprint $table) {
            $table->dropColumn('peng_riil_mode');
        });
    }
};
