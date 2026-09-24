<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Desain awal Peng. Riil dikunci ke provinsi Tujuan agenda (kolom
     * sbm_rates.peng_riil). Ternyata behavior yg bener: dropdown bebas pilih
     * tujuan + rate transportasi (lihat peng_riil_rates), jadi kolom ini gak
     * kepake lagi. Aman didrop — isinya masih null semua (belum pernah diisi).
     */
    public function up(): void
    {
        Schema::table('sbm_rates', function (Blueprint $table) {
            $table->dropColumn('peng_riil');
        });
    }

    public function down(): void
    {
        Schema::table('sbm_rates', function (Blueprint $table) {
            $table->decimal('peng_riil', 12, 2)->nullable();
        });
    }
};
