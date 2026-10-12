<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('anggaran_uploads', function (Blueprint $t) {
            $t->id();
            $t->string('periode')->nullable();
            $t->string('satker_kode', 20)->nullable();
            $t->string('satker_nama')->nullable();
            $t->string('nama_file');
            $t->unsignedInteger('jumlah_item')->default(0);
            $t->bigInteger('total_pagu')->default(0);
            $t->bigInteger('total_realisasi')->default(0);
            $t->boolean('is_active')->default(false)->index();
            $t->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('anggaran_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('anggaran_upload_id')->constrained('anggaran_uploads')->cascadeOnDelete();

            $t->string('keg_kode', 30);   $t->string('keg_uraian', 500)->nullable();
            $t->string('ro_kode', 30);    $t->string('ro_uraian', 500)->nullable();
            $t->string('komp_kode', 30);  $t->string('komp_uraian', 500)->nullable();
            $t->string('sub_kode', 30);   $t->string('sub_uraian', 500)->nullable();
            $t->string('akun_kode', 30);  $t->string('akun_uraian', 500)->nullable();
            $t->string('item_kode', 30);  $t->text('item_uraian')->nullable();

            $t->bigInteger('pagu')->default(0);
            $t->bigInteger('realisasi')->default(0);

            $t->index(['anggaran_upload_id', 'keg_kode', 'ro_kode', 'komp_kode', 'sub_kode', 'akun_kode'], 'anggaran_items_path_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggaran_items');
        Schema::dropIfExists('anggaran_uploads');
    }
};
