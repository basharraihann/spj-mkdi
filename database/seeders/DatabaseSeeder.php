<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        $this->call([
            PegawaiSeeder::class,
            KabupatenKotaSeeder::class,
            MakOptionSeeder::class,
            SbmRateSeeder::class,
            SbmFlatRateSeeder::class,
            PengRiilRateSeeder::class,
        ]);
    }
}