<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('laporan')) {
            return;
        }

        Schema::create('laporan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disposisi_id')->constrained('disposisi')->cascadeOnDelete();
            $table->foreignId('pelapor_id')->constrained('users')->cascadeOnDelete();
            $table->text('isi');
            $table->string('drive_link', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
