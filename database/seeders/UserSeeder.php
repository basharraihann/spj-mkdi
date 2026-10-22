<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password'); // Password default testing

        $users = [
            [
                'name' => 'Administrator System',
                'username' => 'admin',
                'email' => 'admin@kemenkopangan.go.id',
                'role' => User::ROLE_ADMIN ?? 'admin',
                'password' => $password,
            ],
            [
                'name' => 'Staf Persuratan / TU',
                'username' => 'tu',
                'email' => 'tu@kemenkopangan.go.id',
                'role' => User::ROLE_TU ?? 'tu',
                'password' => $password,
            ],
            [
                'name' => 'Ibu Deputi (Eselon I)',
                'username' => 'deputi',
                'email' => 'deputi@kemenkopangan.go.id',
                'role' => User::ROLE_ESELON1 ?? 'eselon1',
                'password' => $password,
            ],
            [
                'name' => 'Bapak Asdep / Sesdep (Eselon II)',
                'username' => 'asdep',
                'email' => 'asdep@kemenkopangan.go.id',
                'role' => User::ROLE_ESELON2 ?? 'eselon2',
                'password' => $password,
            ],
            [
                'name' => 'Pegawai Pelaksana / Staf',
                'username' => 'pegawai',
                'email' => 'pegawai@kemenkopangan.go.id',
                'role' => User::ROLE_USER ?? 'user',
                'password' => $password,
            ],
        ];

        foreach ($users as $user) {
            // Pencarian berbasis username mencegah error bentrok constraint unique
            User::updateOrCreate(
                ['username' => $user['username']],
                $user
            );
        }
    }
}