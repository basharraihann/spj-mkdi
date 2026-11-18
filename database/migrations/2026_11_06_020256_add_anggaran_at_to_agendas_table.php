<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $t) {
            $t->timestamp('anggaran_at')->nullable()->after('mak');
        });
    }
    public function down(): void
    {
        Schema::table('agendas', fn(Blueprint $t) => $t->dropColumn('anggaran_at'));
    }
};
