<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Status anggaran per memo: outstanding -> sakti -> sp2d (+ batal).
 * Hanya status "outstanding" yang dihitung sebagai outstanding di MakSisaService.
 *
 * Simpan di database/migrations/
 */
return new class extends Migration {
    public function up(): void
    {
        // Memo mandiri (non-perdin)
        Schema::table('memo_entries', function (Blueprint $table) {
            $table->string('status_anggaran', 20)->default('outstanding')->index()->after('nominal');
            $table->date('tgl_sakti')->nullable()->after('status_anggaran');
            $table->date('tgl_sp2d')->nullable()->after('tgl_sakti');
        });

        // Perdin: satu agenda bisa punya 2 memo (PNS & Non PNS), masing-masing punya status sendiri.
        // nominal_memo_* = snapshot nominal; kalau null, dihitung dari rincian peserta (biaya_asn / biaya_non_asn).
        Schema::table('agendas', function (Blueprint $table) {
            $table->unsignedBigInteger('nominal_memo_pns')->nullable();
            $table->unsignedBigInteger('nominal_memo_non_pns')->nullable();

            $table->string('status_pns', 20)->default('outstanding')->index();
            $table->string('status_non_pns', 20)->default('outstanding')->index();

            $table->date('tgl_sakti_pns')->nullable();
            $table->date('tgl_sakti_non_pns')->nullable();
            $table->date('tgl_sp2d_pns')->nullable();
            $table->date('tgl_sp2d_non_pns')->nullable();
        });

        // Jejak perubahan status: siapa, kapan, dari apa ke apa, kenapa
        Schema::create('memo_status_logs', function (Blueprint $table) {
            $table->id();
            $table->string('memo_type', 10);               // entry | agenda
            $table->unsignedBigInteger('memo_id');
            $table->string('jenis', 10)->nullable();        // pns | non_pns (khusus agenda)
            $table->string('dari', 20);
            $table->string('ke', 20);
            $table->string('alasan')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index(['memo_type', 'memo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memo_status_logs');

        Schema::table('agendas', function (Blueprint $table) {
            $table->dropColumn([
                'nominal_memo_pns', 'nominal_memo_non_pns',
                'status_pns', 'status_non_pns',
                'tgl_sakti_pns', 'tgl_sakti_non_pns',
                'tgl_sp2d_pns', 'tgl_sp2d_non_pns',
            ]);
        });

        Schema::table('memo_entries', function (Blueprint $table) {
            $table->dropColumn(['status_anggaran', 'tgl_sakti', 'tgl_sp2d']);
        });
    }
};
