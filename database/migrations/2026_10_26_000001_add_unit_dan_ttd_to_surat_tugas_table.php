<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            // Unit pembuat ST (null = ST lama / dibuat admin tanpa unit)
            $table->foreignId('unit_id')->nullable()->after('created_by')
                ->constrained('units')->nullOnDelete();

            // Hasil penandatanganan (TTD/TTE dilakukan di luar aplikasi, file diunggah)
            $table->string('file_ttd_path')->nullable();
            $table->string('file_ttd_nama')->nullable();
            $table->foreignId('ditandatangani_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('ditandatangani_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
            $table->dropConstrainedForeignId('ditandatangani_by');
            $table->dropColumn(['file_ttd_path', 'file_ttd_nama', 'ditandatangani_at']);
        });
    }
};
