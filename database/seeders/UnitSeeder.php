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