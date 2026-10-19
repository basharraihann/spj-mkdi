<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_tugas_pegawai', function (Blueprint $t) {
            $t->string('jenis', 10)->default('pns')->after('pegawai_id');
        });
    }

    public function down(): void
    {
        Schema::table('surat_tugas_pegawai', function (Blueprint $t) {
            $t->dropColumn('jenis');
        });
    }
};
