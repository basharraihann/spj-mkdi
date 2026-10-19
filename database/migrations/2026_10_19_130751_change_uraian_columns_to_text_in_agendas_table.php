<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->text('uraian_kegiatan')->change();
            $table->text('tujuan')->nullable()->change();
            $table->text('uraian_memo_pns')->nullable()->change();
            $table->text('uraian_memo_non_pns')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->string('uraian_kegiatan')->change();
            $table->string('tujuan')->nullable()->change();
            $table->string('uraian_memo_pns')->nullable()->change();
            $table->string('uraian_memo_non_pns')->nullable()->change();
        });
    }
};