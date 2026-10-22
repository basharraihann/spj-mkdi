<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Hanya MENAMBAH tabel baru. Tabel surat_masuk dan disposisi tidak diubah.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('surat_masuk_counters')) {
            Schema::create('surat_masuk_counters', function (Blueprint $t) {
                $t->id();
                $t->unsignedSmallInteger('tahun')->unique();
                $t->unsignedInteger('last_number')->default(0);
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_masuk_counters');
    }
};