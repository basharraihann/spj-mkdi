<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            // MAK utuh, misal: 7458.ABR.006.075.EE.524119
            // kode_giat/kode_komponen/kode_akun_ap/kode_belanja diturunkan otomatis dari sini.
            $table->string('mak')->nullable();

            // Field "Dari" di memorandum (dipakai bersama PNS & Non-PNS)
            $table->string('dari_memo')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropColumn(['mak', 'dari_memo']);
        });
    }
};
