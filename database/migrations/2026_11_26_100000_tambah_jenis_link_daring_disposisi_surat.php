<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahan untuk tab Jadwal Pegawai:
 * - jenis        : surat_masuk | undangan | nota_dinas | nota_pimpinan
 * - link_daring  : link Zoom / Meet (terpisah dari kolom tempat)
 *
 * Hanya MENAMBAH kolom. Data lama otomatis berjenis "surat_masuk".
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('disposisi_surat', function (Blueprint $t) {
            if (!Schema::hasColumn('disposisi_surat', 'jenis')) {
                $t->string('jenis', 20)->default('surat_masuk')->after('sifat');
            }
            if (!Schema::hasColumn('disposisi_surat', 'link_daring')) {
                $t->string('link_daring', 500)->nullable()->after('tempat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('disposisi_surat', function (Blueprint $t) {
            foreach (['jenis', 'link_daring'] as $c) {
                if (Schema::hasColumn('disposisi_surat', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
    }
};
