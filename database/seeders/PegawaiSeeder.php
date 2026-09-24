<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $unitKerja = 'Biro Manajemen Kinerja, Data dan Informasi';

        $data = [
            ['nama' => 'Achmad Murman', 'nama_gelar' => 'Achmad Murman, S.T., M.T., M.Sc.', 'nip' => '197706232005021002', 'pangkat' => 'Pembina Tk I', 'golongan' => 'IV/b', 'jabatan' => 'Kepala Biro Manajemen Kinerja, Data dan Informasi', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Susanto', 'nip' => '197311011994031001', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Statistisi Ahli Madya', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Didik Syaiful Bachri', 'nip' => '197708101998031003', 'pangkat' => 'Penata TK I', 'golongan' => 'IV/a', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Tria Hatmanto', 'nama_gelar' => 'Tria Hatmanto, S.Pt.', 'nip' => '198702122014021001', 'pangkat' => 'Penata TK I', 'golongan' => 'III/d', 'jabatan' => 'Analis Kebijakan Ahli Muda', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Don Bapkas Nisnoni', 'nip' => '197609272003121002', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Perencana Ahli Muda', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Abby Maulana Putra', 'nip' => '199304062025211033', 'golongan' => 'III/a', 'jabatan' => 'Penata Layanan Operasional', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Albi Erlangga Aryatama', 'jabatan' => 'Tenaga Pendukung Administrasi', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Deaz Setyo Nugroho', 'jabatan' => 'Tenaga Pendukung Administrasi', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Sudarno', 'jabatan' => 'Tenaga Pendukung Lainnya/Pengemudi', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Haura Sahla Yumna Hanifah', 'nama_gelar' => 'Haura Sahla Yumna Hanifah, S.Par', 'jabatan' => 'Tenaga Pendukung Administrasi', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Risna Farlina', 'jabatan' => 'Tenaga Pendukung Administrasi', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Bashar Raihan A', 'jabatan' => 'Tenaga Pendukung Administrasi', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Hana Isnaini Hasniah', 'jabatan' => 'Tenaga Pendukung Administrasi', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Najwa Kania Ash Shidiq', 'jabatan' => 'Tenaga Pendukung Administrasi', 'status_kepegawaian' => 'Non PNS'],
        ];

        foreach ($data as $row) {
            // updateOrCreate biar seeder ini aman dijalanin ulang: kalau pegawai dengan
            // nama yang sama sudah ada (mis. dari seed sebelumnya yang belum punya
            // unit_kerja), datanya di-update, bukan bikin baris baru.
            Pegawai::updateOrCreate(
                ['nama' => $row['nama']],
                array_merge($row, ['unit_kerja' => $unitKerja])
            );
        }
    }
}