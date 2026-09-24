<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            // Shared di kedua varian memo (PNS & Non-PNS)
            $table->string('tujuan_memo')->nullable();
            $table->string('perihal_memo')->nullable();

            // Dipisah per status kepegawaian
            $table->string('nomor_memo_pns')->nullable();
            $table->string('nomor_memo_non_pns')->nullable();
            $table->text('uraian_memo_pns')->nullable();
            $table->text('uraian_memo_non_pns')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropColumn([
                'tujuan_memo',
                'perihal_memo',
                'nomor_memo_pns',
                'nomor_memo_non_pns',
                'uraian_memo_pns',
                'uraian_memo_non_pns',
            ]);
        });
    }
};