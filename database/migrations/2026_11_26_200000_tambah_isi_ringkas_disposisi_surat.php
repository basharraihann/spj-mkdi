<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('disposisi_surat', function (Blueprint $t) {
            if (!Schema::hasColumn('disposisi_surat', 'isi_ringkas')) {
                $t->text('isi_ringkas')->nullable()->after('perihal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('disposisi_surat', function (Blueprint $t) {
            if (Schema::hasColumn('disposisi_surat', 'isi_ringkas')) {
                $t->dropColumn('isi_ringkas');
            }
        });
    }
};