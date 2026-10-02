<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nominatif_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('nominatif_entry_id')->constrained('nominatif_entries')->cascadeOnDelete();
            $t->unsignedInteger('urutan')->default(0);
            $t->text('uraian');
            $t->unsignedBigInteger('harga')->default(0);
            $t->decimal('jumlah', 12, 2)->default(1);
            $t->string('satuan', 50)->nullable();
            $t->decimal('ppn_persen', 5, 2)->default(0);
            $t->decimal('pph22_persen', 5, 2)->default(0);
            $t->decimal('pph23_persen', 5, 2)->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nominatif_items');
    }
};
