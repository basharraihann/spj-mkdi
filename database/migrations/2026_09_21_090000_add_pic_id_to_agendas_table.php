<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            // PIC (Person In Charge) yang membuat/mengurus agenda ini — beda dari
            // Penanggung Jawab Kegiatan (penanggung_jawab_id) yang muncul di SPD/nominatif.
            // Dipilih manual dari daftar pegawai, sama seperti pola ppk_id/bendahara_id.
            $table->foreignId('pic_id')->nullable()->after('penanggung_jawab_id')
                ->constrained('pegawai')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pic_id');
        });
    }
};
