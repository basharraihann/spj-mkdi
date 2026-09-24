<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Di migration aslinya (add_lampiran_fields_to_agendas_table), `klasifikasi`
     * & `nilai_persen` dibikin NOT NULL dengan default (klasifikasi='PDN',
     * nilai_persen=100) — bukan nullable. Field admin/anggaran di form
     * Buat/Edit Agenda sekarang didesain SEMUANYA opsional, jadi kalau user
     * ngosongin salah satu ini, Laravel ngirim NULL eksplisit ke insert/update
     * (bukan "kolom di-skip" — jadi default-nya gak kepakai, MySQL nolak krn
     * NOT NULL). Migration ini nyamain 2 kolom ini jadi nullable.
     *
     * Kolom lain (kode_giat, uraian_giat, kode_komponen, uraian_komponen,
     * kode_akun_ap, uraian_akun_ap, kode_belanja, uraian_belanja,
     * petugas_verifikasi_id, pagu, pengajuan_nominal, nomor_memo_pns,
     * nomor_memo_non_pns, uraian_memo_pns, uraian_memo_non_pns) SUDAH nullable
     * dari migration aslinya masing-masing — sengaja gak disentuh di sini.
     */
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->string('klasifikasi')->nullable()->default('PDN')->change();
            $table->decimal('nilai_persen', 5, 2)->nullable()->default(100)->change();
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->string('klasifikasi')->default('PDN')->change();
            $table->decimal('nilai_persen', 5, 2)->default(100)->change();
        });
    }
};
