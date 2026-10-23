<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password'); // Password default testing

        // 1. Data Pegawai Akun Baku Standar
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

        $pegawaiAsdep1 = Pegawai::updateOrCreate(
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

        // 2. Data Pegawai Khusus Akun Deputi 2 (D2) -> NIP berbeda agar tidak bentrok
        $pegawaiTuD2 = Pegawai::updateOrCreate(
            ['nip' => '199001012015011002'],
            [
                'nama' => 'TU Deputi 2',
                'jabatan' => 'Pengelola Persuratan D2',
                'unit_kerja' => 'Deputi 2 Bidang Koordinasi Usaha Pangan dan Pertanian',
            ]
        );

        $pegawaiDeputiD2 = Pegawai::updateOrCreate(
            ['nip' => '197502021998022002'],
            [
                'nama' => 'Ibu Deputi 2',
                'jabatan' => 'Deputi 2 Bidang Koordinasi Usaha Pangan dan Pertanian',
                'unit_kerja' => 'Deputi 2 Bidang Koordinasi Usaha Pangan dan Pertanian',
            ]
        );

        $pegawaiAsdepD2 = Pegawai::updateOrCreate(
            ['nip' => '198003032005031003'],
            [
                'nama' => 'Bapak Asdep D2',
                'jabatan' => 'Asisten Deputi 1 D2',
                'unit_kerja' => 'Deputi 2 Bidang Koordinasi Usaha Pangan dan Pertanian',
            ]
        );

        $pegawaiStafD2 = Pegawai::updateOrCreate(
            ['nip' => '199504042020041004'],
            [
                'nama' => 'Bashar Raihan (D2)',
                'jabatan' => 'Tenaga Pendukung Administrasi D2',
                'unit_kerja' => 'Deputi 2 Bidang Koordinasi Usaha Pangan dan Pertanian',
            ]
        );

        // 3. Mapping User (Satu pegawai_id hanya untuk satu User)
        $users = [
            // --- AKUN STANDAR ---
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
                'pegawai_id' => $pegawaiAsdep1->id,
            ],
            [
                'name' => 'Bashar Raihan (Staf)',
                'username' => 'pegawai',
                'email' => 'pegawai@kemenkopangan.go.id',
                'role' => User::ROLE_USER ?? 'user',
                'password' => $password,
                'pegawai_id' => $pegawaiStaf->id,
            ],

            // --- AKUN KHUSUS DEPUTI 2 (D2) ---
            [
                'name' => 'Admin Deputi 2',
                'username' => 'admin-d2',
                'email' => 'admin.d2@kemenkopangan.go.id',
                'role' => 'admin-d2',
                'password' => $password,
                'pegawai_id' => null,
            ],
            [
                'name' => 'TU Deputi 2',
                'username' => 'tu-d2',
                'email' => 'tu.d2@kemenkopangan.go.id',
                'role' => 'tu-d2',
                'password' => $password,
                'pegawai_id' => $pegawaiTuD2->id,
            ],
            [
                'name' => 'Ibu Deputi 2',
                'username' => 'deputi-d2',
                'email' => 'deputi.d2@kemenkopangan.go.id',
                'role' => 'deputi-d2',
                'password' => $password,
                'pegawai_id' => $pegawaiDeputiD2->id,
            ],
            [
                'name' => 'Bapak Asdep D2',
                'username' => 'asdep-d2',
                'email' => 'asdep.d2@kemenkopangan.go.id',
                'role' => 'asdep-d2',
                'password' => $password,
                'pegawai_id' => $pegawaiAsdepD2->id,
            ],
            [
                'name' => 'Bashar Raihan (Pegawai D2)',
                'username' => 'pegawai-d2',
                'email' => 'pegawai.d2@kemenkopangan.go.id',
                'role' => 'pegawai-d2',
                'password' => $password,
                'pegawai_id' => $pegawaiStafD2->id,
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