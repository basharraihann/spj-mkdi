<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahan modul Disposisi:
 * - jadwal kegiatan di surat (untuk kalender & Google Calendar)
 * - id event Google Calendar per disposisi
 * - tabel feedback disposisi
 * - tabel akun Google Calendar per user
 *
 * Hanya MENAMBAH kolom/tabel. Aman dijalankan walau migrasi disposisi sebelumnya sudah jalan.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('disposisi_surat', function (Blueprint $t) {
            if (!Schema::hasColumn('disposisi_surat', 'tanggal_kegiatan')) {
                $t->date('tanggal_kegiatan')->nullable()->after('lampiran');
            }
            if (!Schema::hasColumn('disposisi_surat', 'waktu_mulai')) {
                $t->time('waktu_mulai')->nullable()->after('tanggal_kegiatan');
            }
            if (!Schema::hasColumn('disposisi_surat', 'waktu_selesai')) {
                $t->time('waktu_selesai')->nullable()->after('waktu_mulai');
            }
            if (!Schema::hasColumn('disposisi_surat', 'tempat')) {
                $t->string('tempat')->nullable()->after('waktu_selesai');
            }
        });

        Schema::table('disposisi', function (Blueprint $t) {
            if (!Schema::hasColumn('disposisi', 'google_event_id')) {
                $t->string('google_event_id')->nullable()->after('laporan_file_name');
            }
        });

        if (!Schema::hasTable('disposisi_feedback')) {
            Schema::create('disposisi_feedback', function (Blueprint $t) {
                $t->id();
                $t->foreignId('surat_id')->constrained('disposisi_surat')->cascadeOnDelete();
                $t->foreignId('disposisi_id')->nullable()->constrained('disposisi')->nullOnDelete();
                $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $t->text('isi');
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('google_kalender_akun')) {
            Schema::create('google_kalender_akun', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $t->string('google_email')->nullable();
                $t->text('access_token')->nullable();    // terenkripsi (cast encrypted)
                $t->text('refresh_token')->nullable();   // terenkripsi
                $t->timestamp('expires_at')->nullable();
                $t->string('calendar_id')->default('primary');
                $t->timestamp('terakhir_sinkron')->nullable();
                $t->text('error_terakhir')->nullable();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('google_kalender_akun');
        Schema::dropIfExists('disposisi_feedback');

        Schema::table('disposisi', function (Blueprint $t) {
            if (Schema::hasColumn('disposisi', 'google_event_id')) {
                $t->dropColumn('google_event_id');
            }
        });
        Schema::table('disposisi_surat', function (Blueprint $t) {
            foreach (['tanggal_kegiatan', 'waktu_mulai', 'waktu_selesai', 'tempat'] as $c) {
                if (Schema::hasColumn('disposisi_surat', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
    }
};
