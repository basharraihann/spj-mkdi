<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            // satu pegawai hanya boleh dipetakan ke satu akun (unique)
            $t->foreignId('pegawai_id')->nullable()->after('unit_id')->unique()
                ->constrained('pegawai')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('pegawai_id');
        });
    }
};
