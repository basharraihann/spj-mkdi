<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // GANTI: waktu deploy fitur outstanding / waktu migrate dijalankan
        $batas = '2026-11-25 00:00:00';

        foreach (['agendas', 'memo_entries'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->boolean('is_legacy')->default(false);
            });

            // Hanya memo yang dibuat sebelum batas yang ditandai lama
            DB::table($tabel)
                ->where(function ($q) use ($batas) {
                    $q->whereNull('created_at')
                        ->orWhere('created_at', '<', $batas);
                })
                ->update(['is_legacy' => true]);
        }
    }

    public function down(): void
    {
        foreach (['agendas', 'memo_entries'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->dropColumn('is_legacy');
            });
        }
    }
};