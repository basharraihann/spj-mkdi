<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Id event Google Calendar "unit" per surat
 * (surat masuk berjadwal -> kalender seluruh staf di unit pencatat).
 *
 * Hanya MENAMBAH kolom.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('disposisi_surat', function (Blueprint $t) {
            if (!Schema::hasColumn('disposisi_surat', 'google_event_id_unit')) {
                $t->string('google_event_id_unit')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('disposisi_surat', function (Blueprint $t) {
            if (Schema::hasColumn('disposisi_surat', 'google_event_id_unit')) {
                $t->dropColumn('google_event_id_unit');
            }
        });
    }
};