<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class WamenkoPegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $unitKerja = 'Wamenko';

        $data = [
            ['nama' => 'Hanif Faisol Nurofiq', 'nama_gelar' => 'Hanif Faisol Nurofiq, S.Hut., M.P.', 'jabatan' => 'Wakil Menteri Koordinator Bidang Pangan', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Hariyanto', 'jabatan' => 'Tenaga Ahli Wamenko', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Imam Santoso', 'jabatan' => 'Pengawal Pribadi', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Zulfika', 'jabatan' => 'Pengawal Pribadi', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Astrid Siahaya', 'nip' => '198308192008042001', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Pengelola Layanan Operasional', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Dinda Nur Haliza', 'jabatan' => 'Staf Wamenko', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Anisyah', 'jabatan' => 'Staf Wamenko', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'M Bagus Heditiya Pratama', 'jabatan' => 'Staf Wamenko', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Rezky Okma', 'jabatan' => 'Staf Wamenko', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Muhammad Rifki Bahtiar', 'jabatan' => 'Tim Media Wamenko', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Aditia Maulana Putra', 'jabatan' => 'Tim Media Wamenko', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Aditya Rizki Maulana', 'jabatan' => 'Tim Media Wamenko', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Yudha Darma Wijaya', 'jabatan' => 'Driver', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Ahmad Khoeruddin', 'jabatan' => 'Driver', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Rangga', 'jabatan' => 'Patwal', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Agi Fatra', 'jabatan' => 'Patwal', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Ajeng Amalia Kartika Dwi', 'jabatan' => 'Tim Media Wamenko', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Wanda Putra', 'nip' => '199404302017081002', 'pangkat' => 'Penata', 'golongan' => 'III/c', 'jabatan' => 'Analis Kebijakan Ahli Pertama', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Muhammad Akasah Sofyan', 'jabatan' => 'Tim Media Wamenko', 'status_kepegawaian' => 'PNS'],
        ];

        foreach ($data as $row) {
            Pegawai::updateOrCreate(
                ['nama' => $row['nama']],
                array_merge($row, ['unit_kerja' => $unitKerja])
            );
        }
    }
}
