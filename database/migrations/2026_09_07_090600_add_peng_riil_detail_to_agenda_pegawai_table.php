<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Peng. Riil sekarang mendukung banyak entry per peserta (misal beberapa leg
     * transportasi sekaligus, campuran manual & SBM, tiap entry punya keterangan
     * sendiri). Detail tiap entry disimpan di sini sebagai JSON array:
     * [{mode, value, rate_id, tujuan, keterangan}, ...].
     *
     * Kolom lama `peng_riil_mode` & `peng_riil_rate_id` TETAP DIPERTAHANKAN (tidak
     * dihapus) — cuma dipakai sebagai metadata ringkas dari entry pertama saja,
     * buat kompatibilitas kode lama yang mungkin masih membacanya. Sumber
     * kebenaran total biaya tetap kolom `peng_riil` (SUM semua entry di
     * `peng_riil_detail`, dihitung server-side di AgendaController@pesertaStore).
     */
    public function up(): void
    {
        Schema::table('agenda_pegawai', function (Blueprint $table) {
            $table->json('peng_riil_detail')->nullable()->after('peng_riil_rate_id');
        });
    }

    public function down(): void
    {
        Schema::table('agenda_pegawai', function (Blueprint $table) {
            $table->dropColumn('peng_riil_detail');
        });
    }
};
