<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('surat_masuk', 'unit_id')) {
            Schema::table('surat_masuk', function (Blueprint $table) {
                $table->foreignId('unit_id')->nullable()->after('created_by_id')
                    ->constrained('units')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('surat_masuk', 'jalur_tujuan')) {
            Schema::table('surat_masuk', function (Blueprint $table) {
                $table->string('jalur_tujuan', 20)->default('eselon2')->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('surat_masuk', 'unit_id')) {
            Schema::table('surat_masuk', function (Blueprint $table) {
                $table->dropConstrainedForeignId('unit_id');
            });
        }
    }
};
