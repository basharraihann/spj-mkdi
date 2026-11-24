<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['agendas', 'memo_entries'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->boolean('is_legacy')->default(false);
            });

            // Semua data yang ada saat migrate = dibuat sebelum sistem outstanding
            DB::table($tabel)->update(['is_legacy' => true]);
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