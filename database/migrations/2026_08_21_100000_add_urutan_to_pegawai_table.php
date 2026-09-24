<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            // null = belum pernah diurutkan manual, otomatis jatuh ke urutan default (golongan/nama)
            $table->unsignedInteger('urutan')->nullable()->after('role_penandatangan');
        });
    }

    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropColumn('urutan');
        });
    }
};
