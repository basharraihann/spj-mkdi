<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $mkdi = 'Biro Manajemen Kinerja, Data dan Informasi';
        $keuangan = 'Biro Keuangan dan BMN';

        $data = [
            ['nama' => 'Achmad Murman', 'nip' => '197706232005021002', 'pangkat' => 'Pembina Tk I', 'golongan' => 'IV/b', 'jabatan' => 'Kepala Biro Manajemen Kinerja, Data dan Informasi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'PNS', 'role_penandatangan' => 'Penanggung Jawab Kegiatan', 'eselon' => 'II'],
            ['nama' => 'Susanto', 'nip' => '197311011994031001', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Statistisi Ahli Madya', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Didik Syaiful Bachri', 'nip' => '197708101998031003', 'pangkat' => 'Penata Tk I', 'golongan' => 'IV/a', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Tria Hatmanto', 'nip' => '198702122014021001', 'pangkat' => 'Penata Tk I', 'golongan' => 'III/d', 'jabatan' => 'Analis Kebijakan Ahli Muda', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'PNS', 'role_penandatangan' => 'PPK'],
            ['nama' => 'Don Bapkas Nisnoni', 'nip' => '197609272003121002', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Perencana Ahli Muda', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Abby Maulana Putra', 'nip' => '199304062025211033', 'golongan' => 'III/a', 'jabatan' => 'Penata Layanan Operasional', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Albi Erlangga Aryatama', 'jabatan' => 'Tenaga Pendukung Administrasi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'Non PNS', 'role_penandatangan' => 'Petugas Verifikasi'],
            ['nama' => 'Deaz Setyo Nugroho', 'jabatan' => 'Tenaga Pendukung Administrasi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Sudarno', 'jabatan' => 'Tenaga Pendukung Lainnya/Pengemudi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Haura Sahla Yumna Hanifah', 'jabatan' => 'Tenaga Pendukung Administrasi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Risna Farlina', 'jabatan' => 'Tenaga Pendukung Administrasi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Bashar Raihan Alghifari', 'jabatan' => 'Tenaga Pendukung Administrasi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Hana Isnaini Hasniah', 'jabatan' => 'Tenaga Pendukung Administrasi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Najwa Kania Ash Shidiq', 'jabatan' => 'Tenaga Pendukung Administrasi', 'unit_kerja' => $mkdi, 'status_kepegawaian' => 'Non PNS'],

            ['nama' => 'Raka Panji Wibowo', 'nip' => '199504082020121001', 'pangkat' => 'Penata Muda', 'golongan' => 'III/a', 'jabatan' => 'Pranata Komputer Pertama', 'unit_kerja' => $keuangan, 'status_kepegawaian' => 'PNS', 'role_penandatangan' => 'Bendahara'],
        ];

        // Nama lama "Bashar Raihan A" diganti jadi nama lengkap supaya
        // updateOrCreate di bawah tidak bikin baris ganda.
        Pegawai::where('nama', 'Bashar Raihan A')
            ->update(['nama' => 'Bashar Raihan Alghifari']);

        // Kosongkan nama_gelar untuk pegawai di seeder ini (data lama yang
        // sudah terisi tidak otomatis hilang oleh updateOrCreate).
        Pegawai::whereIn('nama', array_column($data, 'nama'))
            ->update(['nama_gelar' => null]);

        foreach ($data as $row) {
            Pegawai::updateOrCreate(
                ['nama' => $row['nama']],
                $row
            );
        }
    }
}