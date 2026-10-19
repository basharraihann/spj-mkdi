<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('surat_tugas', function (Blueprint $t) {
            $t->foreignId('tusi_id')->nullable()->after('status')
                ->constrained('tusi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $t) {
            $t->dropConstrainedForeignId('tusi_id');
        });
    }
};
