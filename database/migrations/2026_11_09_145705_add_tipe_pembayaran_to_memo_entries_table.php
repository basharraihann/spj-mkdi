<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('memo_entries', function (Blueprint $table) {
            $table->string('tipe_pembayaran', 20)->default('ls')->after('jenis_memo');
        });
    }

    public function down(): void
    {
        Schema::table('memo_entries', function (Blueprint $table) {
            $table->dropColumn('tipe_pembayaran');
        });
    }
};