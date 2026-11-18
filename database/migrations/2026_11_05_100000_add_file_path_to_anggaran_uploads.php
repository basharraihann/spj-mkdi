<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('anggaran_uploads', function (Blueprint $t) {
            $t->string('file_path')->nullable()->after('nama_file');
        });
    }

    public function down(): void
    {
        Schema::table('anggaran_uploads', fn(Blueprint $t) => $t->dropColumn('file_path'));
    }
};
