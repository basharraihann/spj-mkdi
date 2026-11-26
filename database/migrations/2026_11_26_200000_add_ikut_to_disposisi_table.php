<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disposisi', function (Blueprint $table) {
            // Penanda Eselon II ikut hadir / menangani bersama staf yang ia tunjuk.
            $table->boolean('ikut')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('disposisi', function (Blueprint $table) {
            $table->dropColumn('ikut');
        });
    }
};
