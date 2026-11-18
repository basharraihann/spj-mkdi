<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jenis memo yang diketik manual disimpan sebagai kategori "lainnya"
     * (supaya filter tidak membengkak), sedangkan teks aslinya disimpan di jenis_manual
     * dan tetap tampil di memo / PDF.
     */
    public function up(): void
    {
        Schema::table('memo_entries', function (Blueprint $table) {
            $table->string('jenis_manual', 100)->nullable()->after('jenis_memo');
        });

        // Data yang sudah terlanjur diketik manual (jenis_memo bukan salah satu kategori baku):
        // pindahkan teksnya ke jenis_manual, kategorinya jadi "lainnya".
        $baku = [
            'konsumsi', 'honorarium', 'atk', 'seminar_kit', 'sewa_ruangan',
            'fullboard_meeting', 'fullday_meeting', 'perdin', 'lainnya',
        ];

        DB::table('memo_entries')
            ->whereNotNull('jenis_memo')
            ->where('jenis_memo', '!=', '')
            ->whereNotIn('jenis_memo', $baku)
            ->update([
                'jenis_manual' => DB::raw('jenis_memo'),
                'jenis_memo' => 'lainnya',
            ]);
    }

    public function down(): void
    {
        // Kembalikan teks manual ke jenis_memo sebelum kolomnya dihapus.
        DB::table('memo_entries')
            ->whereNotNull('jenis_manual')
            ->update(['jenis_memo' => DB::raw('jenis_manual')]);

        Schema::table('memo_entries', function (Blueprint $table) {
            $table->dropColumn('jenis_manual');
        });
    }
};
