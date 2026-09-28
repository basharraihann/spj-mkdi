<?php

namespace Database\Seeders;

use App\Models\SbmFlatRate;
use Illuminate\Database\Seeder;

class SbmFlatRateSeeder extends Seeder
{
    /**
     * Tarif flat SBM selalu 1 baris (id = 1). Pakai firstOrCreate supaya
     * kalau seeder dijalankan ulang, nilai yang sudah diedit admin tidak
     * ketimpa. Ganti ke updateOrCreate kalau memang mau di-reset paksa.
     */
    public function run(): void
    {
        SbmFlatRate::unguarded(function () {
            SbmFlatRate::firstOrCreate(
                ['id' => 1],
                [
                    'uh_fullday' => 95000,
                    'uh_fullboard' => 130000,
                ]
            );
        });
    }
}