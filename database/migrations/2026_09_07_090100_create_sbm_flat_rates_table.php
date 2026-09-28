<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Rate flat nasional (sama di semua provinsi): UH Fullday & UH FullBoard.
     * Satu baris saja (id = 1), diisi lewat SbmFlatRateSeeder.
     */
    public function up(): void
    {
        Schema::create('sbm_flat_rates', function (Blueprint $table) {
            $table->id();
            $table->decimal('uh_fullday', 12, 2);
            $table->decimal('uh_fullboard', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sbm_flat_rates');
    }
};