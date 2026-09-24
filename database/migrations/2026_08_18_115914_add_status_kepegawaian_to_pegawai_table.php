<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('pegawai', 'status_kepegawaian')) {
            Schema::table('pegawai', function (Blueprint $table) {
                $table->enum('status_kepegawaian', ['PNS', 'Non PNS'])->default('Non PNS');
            });
        }
    }

    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropColumn('status_kepegawaian');
        });
    }
};
