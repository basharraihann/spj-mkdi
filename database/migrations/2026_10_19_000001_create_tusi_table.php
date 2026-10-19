<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tusi', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 20)->unique();
            $t->string('nama', 150);
            $t->text('deskripsi')->nullable();
            $t->unsignedSmallInteger('urutan')->default(0);
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tusi');
    }
};
