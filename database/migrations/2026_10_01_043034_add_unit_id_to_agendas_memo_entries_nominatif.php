<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Tambah unit_id kalau belum ada (nullable dulu)
        foreach (['agendas', 'memo_entries', 'nominatif_entries'] as $name) {
            if (!Schema::hasColumn($name, 'unit_id')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->foreignId('unit_id')->nullable()->after('id')
                        ->constrained('units')->restrictOnDelete();
                });
            }
        }

        // 2. Backfill yang masih NULL ke unit dengan id terkecil
        $defaultUnitId = DB::table('units')->orderBy('id')->value('id');

        if ($defaultUnitId) {
            foreach (['agendas', 'memo_entries', 'nominatif_entries'] as $name) {
                DB::table($name)->whereNull('unit_id')->update(['unit_id' => $defaultUnitId]);
            }
        }

        // 3. Unique nomor memo jadi per-unit
        Schema::table('agendas', function (Blueprint $table) {
            if (Schema::hasIndex('agendas', 'agendas_nomor_memo_pns_unique')) {
                $table->dropUnique('agendas_nomor_memo_pns_unique');
            }
            if (Schema::hasIndex('agendas', 'agendas_nomor_memo_non_pns_unique')) {
                $table->dropUnique('agendas_nomor_memo_non_pns_unique');
            }
        });

        Schema::table('agendas', function (Blueprint $table) {
            if (!Schema::hasIndex('agendas', 'agendas_unit_nomor_memo_pns_unique')) {
                $table->unique(['unit_id', 'nomor_memo_pns'], 'agendas_unit_nomor_memo_pns_unique');
            }
            if (!Schema::hasIndex('agendas', 'agendas_unit_nomor_memo_non_pns_unique')) {
                $table->unique(['unit_id', 'nomor_memo_non_pns'], 'agendas_unit_nomor_memo_non_pns_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropUnique('agendas_unit_nomor_memo_pns_unique');
            $table->dropUnique('agendas_unit_nomor_memo_non_pns_unique');
        });

        Schema::table('agendas', function (Blueprint $table) {
            $table->unique('nomor_memo_pns', 'agendas_nomor_memo_pns_unique');
            $table->unique('nomor_memo_non_pns', 'agendas_nomor_memo_non_pns_unique');
        });
    }
};