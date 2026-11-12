<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('anggaran_uploads', function (Blueprint $t) {
            $t->date('per_tanggal')->nullable()->after('periode');
        });

        Schema::table('mak_options', function (Blueprint $t) {
            $t->boolean('is_active')->default(true)->after('per_tanggal');
            // 'fa' = berasal dari FA Detail (boleh dinonaktifkan sync), 'manual' = tidak disentuh sync
            $t->string('sumber', 10)->default('fa')->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('mak_options', fn(Blueprint $t) => $t->dropColumn(['is_active', 'sumber']));
        Schema::table('anggaran_uploads', fn(Blueprint $t) => $t->dropColumn('per_tanggal'));
    }
};
