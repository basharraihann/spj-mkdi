<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('nominatif_entries', function (Blueprint $table) {
            $table->string('provinsi', 100)->nullable()->after('tanggal');
            $table->string('kota', 100)->nullable()->after('provinsi');
        });
    }

    public function down(): void
    {
        Schema::table('nominatif_entries', function (Blueprint $table) {
            $table->dropColumn(['provinsi', 'kota']);
        });
    }
};