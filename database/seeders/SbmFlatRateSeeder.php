<?php

namespace Database\Seeders;

use App\Models\SbmFlatRate;
use Illuminate\Database\Seeder;

class SbmFlatRateSeeder extends Seeder
{
    public function run(): void
    {
        SbmFlatRate::updateOrCreate(
            ['id' => 1],
            [
                'uh_fullday' => 95000,
                'uh_fullboard' => 130000,
            ]
        );
    }
}