<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('nominatif_entries', function (Blueprint $t) {
            $t->string('jenis_detail', 20)->default('honorarium')->after('id');
            $t->foreignId('penanggung_jawab_id')->nullable()->constrained('pegawai')->nullOnDelete();
            // nominatif custom tidak butuh lokasi
            $t->string('provinsi', 100)->nullable()->change();
            $t->string('kota', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('nominatif_entries', function (Blueprint $t) {
            $t->dropConstrainedForeignId('penanggung_jawab_id');
            $t->dropColumn('jenis_detail');
        });
    }
};
