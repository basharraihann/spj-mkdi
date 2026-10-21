<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Tabel Surat Masuk
        if (!Schema::hasTable('surat_masuk')) {
            Schema::create('surat_masuk', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_agenda')->unique();
                $table->string('nomor_surat');
                $table->string('asal_surat');
                $table->text('perihal');
                $table->date('tanggal_surat');
                $table->date('tanggal_diterima');
                $table->text('drive_link')->nullable();
                $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('status', ['baru', 'didisposisi', 'selesai'])->default('baru');
                $table->timestamps();
            });
        }

        // 2. Tabel Disposisi
        if (!Schema::hasTable('disposisi')) {
            Schema::create('disposisi', function (Blueprint $table) {
                $table->id();
                $table->foreignId('surat_masuk_id')->constrained('surat_masuk')->cascadeOnDelete();
                $table->foreignId('parent_dispo_id')->nullable()->constrained('disposisi')->nullOnDelete();
                $table->foreignId('pengirim_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('penerima_id')->constrained('users')->cascadeOnDelete();
                $table->text('instruksi');
                $table->date('tenggat_waktu')->nullable();
                $table->enum('sifat', ['biasa', 'segera', 'rahasia'])->default('biasa');
                $table->boolean('perlu_st')->default(false);
                $table->boolean('is_tembusan')->default(false);
                $table->enum('status', ['baru', 'dibaca', 'diproses', 'selesai'])->default('baru');
                $table->timestamp('dibaca_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. Tabel Nota Dinas
        if (!Schema::hasTable('nota_dinas')) {
            Schema::create('nota_dinas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('disposisi_id')->constrained('disposisi')->cascadeOnDelete();
                $table->string('nomor_nd')->nullable()->unique();
                $table->string('perihal');
                $table->text('ringkasan_hasil');
                $table->text('drive_link_hasil')->nullable();
                $table->foreignId('pembuat_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // 4. Tambah relasi ke tabel surat_tugas existing
        if (Schema::hasTable('surat_tugas') && !Schema::hasColumn('surat_tugas', 'disposisi_id')) {
            Schema::table('surat_tugas', function (Blueprint $table) {
                $table->foreignId('disposisi_id')->nullable()->after('id')->constrained('disposisi')->nullOnDelete();
            });
        }

        // 5. Tambah kolom role di tabel users jika belum ada
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('pegawai')->after('email');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('surat_tugas') && Schema::hasColumn('surat_tugas', 'disposisi_id')) {
            Schema::table('surat_tugas', function (Blueprint $table) {
                $table->dropForeign(['disposisi_id']);
                $table->dropColumn('disposisi_id');
            });
        }
        Schema::dropIfExists('nota_dinas');
        Schema::dropIfExists('disposisi');
        Schema::dropIfExists('surat_masuk');
    }
};