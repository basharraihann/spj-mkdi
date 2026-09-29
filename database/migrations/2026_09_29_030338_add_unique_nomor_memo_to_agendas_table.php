<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->unique('nomor_memo_pns', 'agendas_nomor_memo_pns_unique');
            $table->unique('nomor_memo_non_pns', 'agendas_nomor_memo_non_pns_unique');
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropUnique('agendas_nomor_memo_pns_unique');
            $table->dropUnique('agendas_nomor_memo_non_pns_unique');
        });
    }
};
