<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menyimpan jalur surat masuk (deputi / eselon2 / admin / ppk) agar alur Deputi dan Eselon 2 terpisah.
 * Sebelumnya jalur hanya dipakai saat input untuk menentukan penerima disposisi awal dan tidak tersimpan.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('surat_masuk', 'jalur_tujuan')) {
            Schema::table('surat_masuk', function (Blueprint $table) {
                $table->string('jalur_tujuan', 20)->default('eselon2')->after('status');
                $table->index('jalur_tujuan');
            });
        }

        // Isi data lama berdasarkan penerima disposisi awal (tanpa induk)
        $akar = DB::table('disposisi')
            ->join('users', 'users.id', '=', 'disposisi.penerima_id')
            ->whereNull('disposisi.parent_dispo_id')
            ->orderBy('disposisi.id')
            ->get(['disposisi.surat_masuk_id', 'users.role']);

        foreach ($akar->unique('surat_masuk_id') as $row) {
            $jalur = match (true) {
                in_array($row->role, ['eselon1', 'deputi', 'deputi-d2'], true) => 'deputi',
                in_array($row->role, ['admin', 'admin-d2'], true) => 'admin',
                default => 'eselon2',
            };

            DB::table('surat_masuk')->where('id', $row->surat_masuk_id)->update(['jalur_tujuan' => $jalur]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('surat_masuk', 'jalur_tujuan')) {
            Schema::table('surat_masuk', function (Blueprint $table) {
                $table->dropIndex(['jalur_tujuan']);
                $table->dropColumn('jalur_tujuan');
            });
        }
    }
};
