<?php

namespace Database\Seeders;

use App\Models\SbmFlatRate;
use Illuminate\Database\Seeder;

class SbmFlatRateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            'uh_fullday' => 95000,
            'uh_fullboard' => 130000,
        ];

        foreach ($rates as $kategori => $nominal) {
            SbmFlatRate::updateOrCreate(
                ['kategori' => $kategori],
                ['nominal' => $nominal]
            );
        }
    }
}