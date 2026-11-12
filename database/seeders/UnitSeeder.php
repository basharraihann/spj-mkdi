<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            // Unit baru: Deputi 2
            ['kode' => 'Sesdep D2', 'nama' => 'Sekretaris Deputi Bidang Koordinasi Usaha Pangan dan Pertanian'],
            ['kode' => 'Asdep 1 D2', 'nama' => 'Asisten Deputi Peningkatan Daya Saing Produk Tanaman Pangan'],
            ['kode' => 'Asdep 2 D2', 'nama' => 'Asisten Deputi Peningkatan Daya Saing Produk Pertanian'],
            ['kode' => 'Asdep 3 D2', 'nama' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian'],
            ['kode' => 'Asdep 4 D2', 'nama' => 'Asisten Deputi Peningkatan Daya Saing Produk Perkebunan & Hortikultura'],
            ['kode' => 'Asdep 5 D2', 'nama' => 'Asisten Deputi Peningkatan Daya Saing Produk Peternakan'],
        ];

        foreach ($units as $unit) {
            $exists = DB::table('units')->where('kode', $unit['kode'])->exists();

            if ($exists) {
                DB::table('units')
                    ->where('kode', $unit['kode'])
                    ->update(['nama' => $unit['nama'], 'updated_at' => now()]);
            } else {
                DB::table('units')->insert($unit + [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}