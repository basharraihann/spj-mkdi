<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Nyimpen tujuan mana yang dipilih di popup Peng. Riil pas mode "sbm",
     * biar popup-nya bisa nampilin pilihan terakhir kalau dibuka lagi, dan
     * biar ada jejak dasar perhitungannya (bukan cuma angka akhir di kolom
     * peng_riil). Nullable karena cuma relevan kalau peng_riil_mode = 'sbm'.
     */
    public function up(): void
    {
        Schema::table('agenda_pegawai', function (Blueprint $table) {
            $table->foreignId('peng_riil_rate_id')->nullable()
                ->after('peng_riil_mode')
                ->constrained('peng_riil_rates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agenda_pegawai', function (Blueprint $table) {
            $table->dropConstrainedForeignId('peng_riil_rate_id');
        });
    }
};
