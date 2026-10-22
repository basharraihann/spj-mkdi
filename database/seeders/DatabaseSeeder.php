<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Master Data & Referensi Dasar
            UnitSeeder::class,
            TusiSeeder::class,
            PegawaiSeeder::class,
            WamenkoPegawaiSeeder::class,
            KabupatenKotaSeeder::class,

            // 2. Data Anggaran & SBM
            MakOptionSeeder::class,
            SbmRateSeeder::class,
            SbmFlatRateSeeder::class,
            PengRiilRateSeeder::class,
            AnggaranPemetaanSeeder::class,

            // 3. Pengguna 5 Role Baku (tu, eselon1, eselon2, user, admin)
            UserSeeder::class,

            // 4. Dummy Modul Surat Masuk, Disposisi, & Surat Tugas
            PersuratanDummySeeder::class,
            SuratTugasContohSeeder::class,
        ]);
    }
}