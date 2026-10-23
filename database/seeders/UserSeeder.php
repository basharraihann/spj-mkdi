<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password'); // Password default testing

        // 1. Buat Data Pegawai Dummy Terlebih Dahulu
        $pegawaiTu = Pegawai::updateOrCreate(
            ['nip' => '199001012015011001'],
            [
                'nama' => 'Staf Persuratan / TU',
                'jabatan' => 'Pengelola Persuratan',
                'unit_kerja' => 'Biro Manajemen Kinerja, Data, dan Informasi',
            ]
        );

        $pegawaiDeputi = Pegawai::updateOrCreate(
            ['nip' => '197502021998022001'],
            [
                'nama' => 'Ibu Deputi (Eselon I)',
                'jabatan' => 'Deputi 2 Bidang Koordinasi Usaha Pangan dan Pertanian',
                'unit_kerja' => 'Deputi 2 Bidang Koordinasi Usaha Pangan dan Pertanian',
            ]
        );

        $pegawaiAsdep = Pegawai::updateOrCreate(
            ['nip' => '198003032005031002'],
            [
                'nama' => 'Asdep 1 D2',
                'jabatan' => 'Asisten Deputi Peningkatan Daya Saing Produk Tanaman Pangan',
                'unit_kerja' => 'Deputi 2 Bidang Koordinasi Usaha Pangan dan Pertanian',
            ]
        );

        $pegawaiStaf = Pegawai::updateOrCreate(
            ['nip' => '199504042020041003'],
            [
                'nama' => 'Bashar Raihan',
                'jabatan' => 'Tenaga Pendukung Administrasi',
                'unit_kerja' => 'Deputi 2 Bidang Koordinasi Usaha Pangan dan Pertanian',
            ]
        );

        // 2. Mapping User dengan Pegawai ID yang Sesuai
        $users = [
            [
                'name' => 'Administrator System',
                'username' => 'admin',
                'email' => 'admin@kemenkopangan.go.id',
                'role' => User::ROLE_ADMIN ?? 'admin',
                'password' => $password,
                'pegawai_id' => null,
            ],
            [
                'name' => 'Staf Persuratan / TU',
                'username' => 'tu',
                'email' => 'tu@kemenkopangan.go.id',
                'role' => User::ROLE_TU ?? 'tu',
                'password' => $password,
                'pegawai_id' => $pegawaiTu->id,
            ],
            [
                'name' => 'Ibu Deputi (Eselon I)',
                'username' => 'deputi',
                'email' => 'deputi@kemenkopangan.go.id',
                'role' => User::ROLE_ESELON1 ?? 'eselon1',
                'password' => $password,
                'pegawai_id' => $pegawaiDeputi->id,
            ],
            [
                'name' => 'Bapak Asdep / Sesdep (Eselon II)',
                'username' => 'asdep',
                'email' => 'asdep@kemenkopangan.go.id',
                'role' => User::ROLE_ESELON2 ?? 'eselon2',
                'password' => $password,
                'pegawai_id' => $pegawaiAsdep->id,
            ],
            [
                'name' => 'Bashar Raihan (Staf)',
                'username' => 'pegawai',
                'email' => 'pegawai@kemenkopangan.go.id',
                'role' => User::ROLE_USER ?? 'user',
                'password' => $password,
                'pegawai_id' => $pegawaiStaf->id,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['username' => $user['username']],
                $user
            );
        }
    }
}