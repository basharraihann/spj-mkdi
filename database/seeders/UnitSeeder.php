<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            // Biro, Deputi, Wamenko
            ['kode' => 'Biro MKDI', 'nama' => 'Biro Manajemen Kinerja Data dan Informasi'],
            ['kode' => 'Biro HKS', 'nama' => 'Biro Hukum dan Kerjasama'],
            ['kode' => 'Biro SDMO', 'nama' => 'Biro Sumber Daya Manusia dan Organisasi'],
            ['kode' => 'Biro UHM', 'nama' => 'Biro Umum dan Hubungan Masyarakat'],
            ['kode' => 'Biro KBMN', 'nama' => 'Biro Keuangan dan BMN'],
            ['kode' => 'Deputi 1', 'nama' => 'Deputi Bidang Koordinasi Tata Niaga dan Distribusi Pangan'],
            ['kode' => 'Deputi 2', 'nama' => 'Deputi Bidang Koordinasi Usaha Pangan dan Pertanian'],
            ['kode' => 'Deputi 3', 'nama' => 'Deputi Bidang Koordinasi Keterjangkauan dan Keamanan Pangan'],
            ['kode' => 'Deputi 4', 'nama' => 'Deputi Bidang Koordinasi Sumber Daya Maritim'],
            ['kode' => 'Wamenko', 'nama' => 'Wakil Menteri Koordinator Bidang Pangan'],

            // Deputi 1 - Tata Niaga dan Distribusi Pangan
            ['kode' => 'Sekretariat D1', 'nama' => 'Sekretariat Deputi Bidang Koordinasi Tata Niaga dan Distribusi Pangan'],
            ['kode' => 'Asdep 1 D1', 'nama' => 'Asisten Deputi Tata Niaga Perdagangan Pangan Luar Negeri'],
            ['kode' => 'Asdep 2 D1', 'nama' => 'Asisten Deputi Tata Niaga Perdagangan Pangan Dalam Negeri'],
            ['kode' => 'Asdep 3 D1', 'nama' => 'Asisten Deputi Stabilisasi Harga Pangan'],
            ['kode' => 'Asdep 4 D1', 'nama' => 'Asisten Deputi Logistik Pangan Dalam Negeri'],
            ['kode' => 'Asdep 5 D1', 'nama' => 'Asisten Deputi Sistem Distribusi Pangan'],

            // Deputi 2 - Usaha Pangan dan Pertanian
            ['kode' => 'Sekretariat D2', 'nama' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian'],
            ['kode' => 'Asdep 1 D2', 'nama' => 'Asisten Deputi Peningkatan Daya Saing Produk Tanaman Pangan'],
            ['kode' => 'Asdep 2 D2', 'nama' => 'Asisten Deputi Peningkatan Daya Saing Produk Pertanian'],
            ['kode' => 'Asdep 3 D2', 'nama' => 'Asisten Deputi Peningkatan Daya Saing Produk Perkebunan dan Hortikultura'],
            ['kode' => 'Asdep 4 D2', 'nama' => 'Asisten Deputi Peningkatan Daya Saing Produk Peternakan'],
            ['kode' => 'Asdep 5 D2', 'nama' => 'Asisten Deputi Penguatan Sarana dan Prasarana Produksi Pertanian'],

            // Deputi 3 - Keterjangkauan dan Keamanan Pangan
            ['kode' => 'Sekretariat D3', 'nama' => 'Sekretariat Deputi Bidang Koordinasi Keterjangkauan dan Keamanan Pangan'],
            ['kode' => 'Asdep 1 D3', 'nama' => 'Asisten Deputi Cadangan Pangan dan Bantuan Pangan'],
            ['kode' => 'Asdep 2 D3', 'nama' => 'Asisten Deputi Pengelolaan Hutan Berkelanjutan'],
            ['kode' => 'Asdep 3 D3', 'nama' => 'Asisten Deputi Keamanan dan Mutu Pangan dan Gizi'],
            ['kode' => 'Asdep 4 D3', 'nama' => 'Asisten Deputi Ekonomi Sirkular dan Dampak Lingkungan'],
            ['kode' => 'Asdep 5 D3', 'nama' => 'Asisten Deputi Produksi Pangan dan Perubahan Iklim'],

            // Deputi 4 - Sumber Daya Maritim
            ['kode' => 'Sekretariat D4', 'nama' => 'Sekretariat Deputi Bidang Koordinasi Sumber Daya Maritim'],
            ['kode' => 'Asdep 1 D4', 'nama' => 'Asisten Deputi Pengelolaan Kelautan dan Ruang Laut'],
            ['kode' => 'Asdep 2 D4', 'nama' => 'Asisten Deputi Pengelolaan Perikanan Tangkap'],
            ['kode' => 'Asdep 3 D4', 'nama' => 'Asisten Deputi Pengembangan Perikanan Budidaya'],
            ['kode' => 'Asdep 4 D4', 'nama' => 'Asisten Deputi Penguatan Daya Saing Sumber Daya Maritim'],
            ['kode' => 'Asdep 5 D4', 'nama' => 'Asisten Deputi Sarana dan Prasarana Kelautan dan Perikanan'],
        ];

        DB::transaction(function () use ($units) {
            // 1. Gabungkan duplikat MKDI: pertahankan unit lama (id 1),
            //    pindahkan semua referensi dari duplikat, lalu hapus duplikatnya.
            $lama = Unit::where('kode', 'MKDI')->first();
            $dupe = Unit::where('kode', 'Biro MKDI')->first();

            if ($lama && $dupe && $lama->id !== $dupe->id) {
                foreach (['users', 'agendas', 'memo_entries', 'nominatif_entries'] as $table) {
                    DB::table($table)
                        ->where('unit_id', $dupe->id)
                        ->update(['unit_id' => $lama->id]);
                }
                $dupe->delete();
            }

            // 2. Samakan kode lama ke format baru (id tetap)
            Unit::where('kode', 'MKDI')->update(['kode' => 'Biro MKDI']);
            Unit::where('kode', 'KEU')->update(['kode' => 'Biro KBMN']);

            // 3. Upsert sisanya berdasarkan kode
            foreach ($units as $unit) {
                Unit::updateOrCreate(
                    ['kode' => $unit['kode']],
                    ['nama' => $unit['nama']]
                );
            }
        });
    }
}