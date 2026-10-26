<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('disposisi', 'petunjuk')) {
            return;
        }

        Schema::table('disposisi', function (Blueprint $table) {
            $table->json('petunjuk')->nullable()->after('instruksi');
        });
    }

    public function down(): void
    {
        Schema::table('disposisi', function (Blueprint $table) {
            $table->dropColumn('petunjuk');
        });
    }
};
