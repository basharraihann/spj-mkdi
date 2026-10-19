<?php

namespace Database\Seeders;

use App\Models\Tusi;
use Illuminate\Database\Seeder;

class TusiSeeder extends Seeder
{
    /** Berdasarkan fungsi Biro MKDI (Pasal 13). Aman dijalankan ulang. */
    public function run(): void
    {
        $data = [
            ['PERENCANAAN', 'Perencanaan Program dan Kegiatan'],
            ['ANGGARAN', 'Perencanaan Kebutuhan Anggaran'],
            ['KINERJA', 'Pemantauan, Evaluasi, dan Pelaporan Kinerja'],
            ['RB', 'Reformasi Birokrasi'],
            ['DATIN', 'Pengelolaan Data dan Sistem Informasi'],
            ['TURT', 'Tata Usaha dan Rumah Tangga Biro'],
            ['LAINNYA', 'Koordinasi Umum / Lainnya'],
        ];

        foreach ($data as $i => [$kode, $nama]) {
            Tusi::updateOrCreate(['kode' => $kode], ['nama' => $nama, 'urutan' => $i + 1]);
        }
    }
}
