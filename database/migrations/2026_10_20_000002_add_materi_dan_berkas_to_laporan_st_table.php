<?php

use App\Models\LaporanSt;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Hanya MENAMBAH 3 kolom nullable ke tabel laporan ST. Data laporan yang sudah ada tidak berubah.
// Nama tabel diambil dari model supaya tidak salah tebak.
return new class extends Migration
{
    private function tabel(): string
    {
        return (new LaporanSt())->getTable();
    }

    public function up(): void
    {
        Schema::table($this->tabel(), function (Blueprint $t) {
            $tabel = $this->tabel();

            if (! Schema::hasColumn($tabel, 'materi')) {
                $t->longText('materi')->nullable();
            }
            if (! Schema::hasColumn($tabel, 'file_laporan_path')) {
                $t->string('file_laporan_path')->nullable();
            }
            if (! Schema::hasColumn($tabel, 'file_laporan_nama')) {
                $t->string('file_laporan_nama')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table($this->tabel(), function (Blueprint $t) {
            $tabel = $this->tabel();

            foreach (['materi', 'file_laporan_path', 'file_laporan_nama'] as $kolom) {
                if (Schema::hasColumn($tabel, $kolom)) {
                    $t->dropColumn($kolom);
                }
            }
        });
    }
};