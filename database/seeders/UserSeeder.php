<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Password default semua akun seed. GANTI sebelum dipakai di production.
        $password = Hash::make('password');

        $unit = Unit::where('kode', 'Biro MKDI')->first();

        if (!$unit) {
            $this->command?->warn("Unit 'Biro MKDI' tidak ditemukan. Jalankan UnitSeeder dulu.");
            return;
        }

        // Format: username => [nama, role]
        $users = [
            ['username' => 'admin', 'name' => 'Administrator', 'role' => 'admin'],

            ['username' => 'achmad', 'name' => 'Achmad Murman', 'role' => 'user'],
            ['username' => 'susanto', 'name' => 'Susanto', 'role' => 'user'],
            ['username' => 'didik', 'name' => 'Didik Syaiful Bachri', 'role' => 'user'],
            ['username' => 'tria', 'name' => 'Tria Hatmanto', 'role' => 'user'],
            ['username' => 'don', 'name' => 'Don Bapkas Nisnoni', 'role' => 'user'],
            ['username' => 'abby', 'name' => 'Abby Maulana Putra', 'role' => 'user'],
            ['username' => 'albi', 'name' => 'Albi Erlangga Aryatama', 'role' => 'user'],
            ['username' => 'deaz', 'name' => 'Deaz Setyo Nugroho', 'role' => 'user'],
            ['username' => 'sudarno', 'name' => 'Sudarno', 'role' => 'user'],
            ['username' => 'haura', 'name' => 'Haura Sahla Yumna Hanifah', 'role' => 'user'],
            ['username' => 'risna', 'name' => 'Risna Farlina', 'role' => 'user'],
            ['username' => 'bashar', 'name' => 'Bashar Raihan Alghifari', 'role' => 'user'],
            ['username' => 'hana', 'name' => 'Hana Isnaini Hasniah', 'role' => 'user'],
            ['username' => 'najwa', 'name' => 'Najwa Kania Ash Shidiq', 'role' => 'user'],
        ];

        DB::transaction(function () use ($users, $unit, $password) {
            foreach ($users as $u) {
                User::updateOrCreate(
                    ['username' => $u['username']],
                    [
                        'name' => $u['name'],
                        'email' => $u['username'] . '@example.com',
                        'role' => $u['role'],
                        'unit_id' => $unit->id,
                        'email_verified_at' => now(),
                        'password' => $password,
                    ]
                );
            }
        });
    }
}