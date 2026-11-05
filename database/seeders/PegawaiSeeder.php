<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        // Nama unit kerja (sumber: pegawaiseeder.xlsx)
        $mkdi = 'Biro Manajemen Kinerja, Data dan Informasi';
        $keuangan = 'Biro Keuangan dan BMN';

        $data = [
            // --- Biro MKDI ---
            ['nama' => 'Achmad Murman', 'nama_gelar' => 'Achmad Murman, S.T., M.T., M.Sc.', 'nip' => '197706232005021002', 'pangkat' => 'Pembina Tk I', 'golongan' => 'IV/b', 'jabatan' => 'Kepala Biro Manajemen Kinerja, Data dan Informasi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'PNS', 'role_penandatangan' => 'Penanggung Jawab Kegiatan'],
            ['nama' => 'Susanto', 'nip' => '197311011994031001', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Statistisi Ahli Madya', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Didik Syaiful Bachri', 'nip' => '197708101998031003', 'pangkat' => 'Penata TK I', 'golongan' => 'IV/a', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Tria Hatmanto', 'nama_gelar' => 'Tria Hatmanto, S.Pt.', 'nip' => '198702122014021001', 'pangkat' => 'Penata TK I', 'golongan' => 'III/d', 'jabatan' => 'Analis Kebijakan Ahli Muda', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'PNS', 'role_penandatangan' => 'PPK'],
            ['nama' => 'Don Bapkas Nisnoni', 'nip' => '197609272003121002', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Perencana Ahli Muda', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Abby Maulana Putra', 'nip' => '199304062025211033', 'golongan' => 'III/a', 'jabatan' => 'Penata Layanan Operasional', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Albi Erlangga Aryatama', 'jabatan' => 'Tenaga Pendukung Administrasi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'Non PNS', 'role_penandatangan' => 'Petugas Verifikasi'],
            ['nama' => 'Deaz Setyo Nugroho', 'jabatan' => 'Tenaga Pendukung Administrasi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Sudarno', 'jabatan' => 'Tenaga Pendukung Lainnya/Pengemudi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Haura Sahla Yumna Hanifah', 'nama_gelar' => 'Haura Sahla Yumna Hanifah, S.Par', 'jabatan' => 'Tenaga Pendukung Administrasi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Risna Farlina', 'jabatan' => 'Tenaga Pendukung Administrasi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Bashar Raihan Alghifari', 'jabatan' => 'Tenaga Pendukung Administrasi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Hana Isnaini Hasniah', 'jabatan' => 'Tenaga Pendukung Administrasi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Najwa Kania Ash Shidiq', 'jabatan' => 'Tenaga Pendukung Administrasi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'Non PNS'],

            // --- Biro Keuangan dan BMN ---
            ['nama' => 'Raka Panji Wibowo', 'nip' => '199504082020121001', 'pangkat' => 'Penata Muda', 'golongan' => 'III/a', 'jabatan' => 'Pranata Komputer Pertama', 'unit_kerja' => $keuangan, 'status_kepegawaian' => 'PNS', 'role_penandatangan' => 'Bendahara'],
        ];

        foreach ($data as $row) {
            Pegawai::updateOrCreate(
                ['nama' => $row['nama']],
                $row
            );
        }
    }
}