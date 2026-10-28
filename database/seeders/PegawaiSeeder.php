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
        $d2 = 'Deputi Bidang Koordinasi Usaha Pangan dan Pertanian';
        $sesdep = 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian';
        $a1 = 'Asisten Deputi Peningkatan Daya Saing Produk Tanaman Pangan';
        $a2 = 'Asisten Deputi Peningkatan Daya Saing Produk Pertanian';
        $a3 = 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian';
        $a4 = 'Asisten Deputi Peningkatan Daya Saing Produk Perkebunan dan Hortikultura';
        $a5 = 'Asisten Deputi Peningkatan Daya Saing Produk Peternakan';
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

            // --- Deputi 2 ---
            ['nama' => 'Widiastuti', 'nip' => '197005121994032001', 'pangkat' => 'Pembina Utama Madya', 'golongan' => 'IV/d', 'jabatan' => $d2, 'unit_kerja' => $d2, 'status_kepegawaian' => 'PNS', 'role_penandatangan' => 'Penanggung Jawab Kegiatan'],
            ['nama' => 'Yasser', 'jabatan' => 'Tenaga Pendukung di Bidang Tata Usaha Pimpinan', 'unit_kerja' => $d2, 'status_kepegawaian' => 'Non PNS'],

            // --- Sesdep ---
            ['nama' => 'Karsan', 'nip' => '196803221991031003', 'pangkat' => 'Pembina Utama Muda', 'golongan' => 'IV/c', 'jabatan' => 'Sekretaris Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'PNS', 'role_penandatangan' => 'Penanggung Jawab Kegiatan'],
            ['nama' => 'Kunto Nugroho', 'nip' => '198912142018011001', 'pangkat' => 'Penata Tk I', 'golongan' => 'III/d', 'jabatan' => 'Analis Kebijakan Ahli Muda', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'PNS', 'role_penandatangan' => 'PPK'],
            ['nama' => 'Achmad Kimsan', 'nip' => '197904092005021007', 'pangkat' => 'Penata', 'golongan' => 'III/c', 'jabatan' => 'Penelaah Teknis Kebijakan', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Sendy Pratiwi', 'nip' => '199309032018012001', 'pangkat' => 'Penata', 'golongan' => 'III/c', 'jabatan' => 'Analis Kebijakan Ahli Pertama', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Wahid Setiadi', 'nip' => '198808112022031002', 'pangkat' => 'Penata Muda Tk I', 'golongan' => 'III/b', 'jabatan' => 'Analis Kebijakan Ahli Pertama', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Laily Fitriah', 'nip' => '198304122008042002', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Penelaah Teknis Kebijakan', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Chyntya Syafril', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Reza Rama Gunada', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Rizka Awaliah', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Renato Fortinov Parulian Tampubolon', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Fathimah Husna', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Ira Putri Rahayu', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Yolanda Rosa Debora Panjaitan', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Nurrachmawati', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Yemima Veronika', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Rr Rizka Nurul Cahyani Santoso', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'St. Wulan Aprianti', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Rizki Catur Parmanto', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Adi Suheri', 'jabatan' => 'Driver Deputi', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Bagus Dwi Yulianto', 'jabatan' => 'Driver Sesdep', 'unit_kerja' => $sesdep, 'status_kepegawaian' => 'Non PNS'],

            // --- Asdep A1 ---
            ['nama' => 'Kus Prisetiahadi', 'nip' => '197009251995031001', 'pangkat' => 'Pembina Utama Madya', 'golongan' => 'IV/d', 'jabatan' => $a1, 'unit_kerja' => $a1, 'status_kepegawaian' => 'PNS', 'role_penandatangan' => 'Penanggung Jawab Kegiatan'],
            ['nama' => 'Henry Antonius Manalu', 'nip' => '197306031999031002', 'pangkat' => 'Pembina Tk I', 'golongan' => 'IV/b', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => $a1, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Yuyun Kurnia Lestari', 'nip' => '198805042015022001', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => $a1, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Ronald Hasintongan', 'nip' => '198209162014031001', 'pangkat' => 'Penata Tk I', 'golongan' => 'III/d', 'jabatan' => 'Analis Kebijakan Ahli Muda', 'unit_kerja' => $a1, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Indira Wido Primadipta', 'nip' => '199506042019012001', 'pangkat' => 'Penata Muda Tk I', 'golongan' => 'III/b', 'jabatan' => 'Analis Kebijakan Ahli Pertama', 'unit_kerja' => $a1, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Kunthi Habri Saputri', 'jabatan' => 'Staf Asdep PDSP Tanaman Pangan', 'unit_kerja' => $a1, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Eduardus Eka Ramanditya', 'jabatan' => 'Staf Asdep PDSP Tanaman Pangan', 'unit_kerja' => $a1, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Fatimah Kusuma Dewi', 'jabatan' => 'Staf Asdep PDSP Tanaman Pangan', 'unit_kerja' => $a1, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Sabrina Nurraihana', 'jabatan' => 'Staf Asdep PDSP Tanaman Pangan', 'unit_kerja' => $a1, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Ibnu Fajar Bisri', 'jabatan' => 'Driver A1', 'unit_kerja' => $a1, 'status_kepegawaian' => 'Non PNS'],

            // --- Asdep A2 ---
            ['nama' => 'Silvi Charlote Sumanti', 'nip' => '197504122005022001', 'pangkat' => 'Pembina Utama Muda', 'golongan' => 'IV/c', 'jabatan' => $a2, 'unit_kerja' => $a2, 'status_kepegawaian' => 'PNS', 'role_penandatangan' => 'Penanggung Jawab Kegiatan'],
            ['nama' => 'Ratih Sintya Suly', 'nip' => '197912122005022001', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Analis Perdagangan Ahli Muda', 'unit_kerja' => $a2, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Noviarina Purnami Putri', 'nip' => '198211042009012004', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => $a2, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'R. Stevanus Bayu Mangkurat', 'nip' => '198509272019011001', 'pangkat' => 'Penata', 'golongan' => 'III/c', 'jabatan' => 'Analis Perdagangan Ahli Muda', 'unit_kerja' => $a2, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Wasinton Tulus Oktavianus Sianturi', 'nip' => '198010112014021001', 'pangkat' => 'Penata Tk I', 'golongan' => 'III/d', 'jabatan' => 'Analis Perdagangan Ahli Muda', 'unit_kerja' => $a2, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Retno Sawitri', 'nip' => '199004302014022001', 'pangkat' => 'Penata', 'golongan' => 'III/c', 'jabatan' => 'Negosiator Perdagangan Ahli Muda', 'unit_kerja' => $a2, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Salma Jihan Nabiilah', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a2, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Rima Nur Latifah', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a2, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Dimas Naufaldy Ardian', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a2, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Muhammad Khladun', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a2, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Bambang Utomo', 'jabatan' => 'Driver A2', 'unit_kerja' => $a2, 'status_kepegawaian' => 'Non PNS'],

            // --- Asdep A3 ---
            ['nama' => 'Bona Kusuma', 'nip' => '197610242005021001', 'pangkat' => 'Pembina Utama Muda', 'golongan' => 'IV/c', 'jabatan' => $a3, 'unit_kerja' => $a3, 'status_kepegawaian' => 'PNS', 'role_penandatangan' => 'Penanggung Jawab Kegiatan'],
            ['nama' => 'Muhammad Rizal Panrelly', 'nip' => '196911231994031010', 'pangkat' => 'Pembina Utama Muda', 'golongan' => 'IV/c', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => $a3, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Yonatan Kriswidiyanto Sitohang', 'nip' => '197608052005021001', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Analis Perdagangan Ahli Madya', 'unit_kerja' => $a3, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Dian Dwi Laksani', 'nip' => '198407162010122002', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => $a3, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Lailun Maulidiyah', 'nip' => '198412052010122002', 'pangkat' => 'Penata Tk I', 'golongan' => 'III/d', 'jabatan' => 'Analis Sumber Daya Manusia Aparatur Ahli Muda', 'unit_kerja' => $a3, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Randi Morin', 'nip' => '198508062015051001', 'pangkat' => 'Penata Tk I', 'golongan' => 'III/d', 'jabatan' => 'Analis Kebijakan Ahli Muda', 'unit_kerja' => $a3, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Gabriel Malona', 'nip' => '198807202014021002', 'pangkat' => 'Penata', 'golongan' => 'III/c', 'jabatan' => 'Negosiator Perdagangan Ahli Muda', 'unit_kerja' => $a3, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Suhantono', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a3, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Randi Ernawan', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a3, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Claudia Yuliarta Abigael', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a3, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Azzahra Asya D S', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a3, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Naesha Ardibi', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a3, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Shalma Azzahra', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a3, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Shintia Octaviani Simanjuntak', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a3, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Utari Praba Ningrum', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a3, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Anwar Sanusi', 'jabatan' => 'Driver A3', 'unit_kerja' => $a3, 'status_kepegawaian' => 'Non PNS'],

            // --- Asdep A4 ---
            ['nama' => 'Radian Bagiyono', 'nip' => '197411082000031002', 'pangkat' => 'Pembina Utama Muda', 'golongan' => 'IV/c', 'jabatan' => $a4, 'unit_kerja' => $a4, 'status_kepegawaian' => 'PNS', 'role_penandatangan' => 'Penanggung Jawab Kegiatan'],
            ['nama' => 'Darto', 'nip' => '196811101991021001', 'pangkat' => 'Pembina Tk I', 'golongan' => 'IV/b', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => $a4, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Jhon Hasudungan Pardomuan Tambun', 'nip' => '196901291996031001', 'pangkat' => 'Pembina Tk I', 'golongan' => 'IV/b', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => $a4, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Mielyon Erison Datty', 'nip' => '197505212000031001', 'pangkat' => 'Pembina Utama Muda', 'golongan' => 'IV/c', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => $a4, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Naufal Farras Syihab', 'nip' => '199711132022031004', 'pangkat' => 'Penata Muda', 'golongan' => 'III/a', 'jabatan' => 'Penelaah Teknis Kebijakan', 'unit_kerja' => $a4, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Muhammad Fathin Akbar', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a4, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Intan Dwi Handayani', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a4, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Stephanie Silvi Tamba', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a4, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Neyreza Arviana Putri', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a4, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Gabriela Oktaviani Ayudya Putri', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a4, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Hasan Prastowo', 'jabatan' => 'Driver A4', 'unit_kerja' => $a4, 'status_kepegawaian' => 'Non PNS'],

            // --- Asdep A5 ---
            ['nama' => 'Harimurti', 'nip' => '197312252000031001', 'pangkat' => 'Pembina Utama Muda', 'golongan' => 'IV/c', 'jabatan' => $a5, 'unit_kerja' => $a5, 'status_kepegawaian' => 'PNS', 'role_penandatangan' => 'Penanggung Jawab Kegiatan'],
            ['nama' => 'Fatma Puspitasari', 'nip' => '197905042003122001', 'pangkat' => 'Pembina Tk I', 'golongan' => 'IV/b', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => $a5, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Yudhi Ratna Kurniawan', 'nip' => '198008062005021001', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Analis Kebijakan Ahli Muda', 'unit_kerja' => $a5, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Riana Sri Hastuti', 'nip' => '199511272020122013', 'pangkat' => 'Penata Muda Tk I', 'golongan' => 'III/b', 'jabatan' => 'Analis Kebijakan Ahli Pertama', 'unit_kerja' => $a5, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Ikka Fitri Mei Kennedy', 'nip' => '198905102020121009', 'pangkat' => 'Penata Muda Tk I', 'golongan' => 'III/b', 'jabatan' => 'Penelaah Teknis Kebijakan', 'unit_kerja' => $a5, 'status_kepegawaian' => 'PNS'],
            ['nama' => 'M Khubbab Fairus', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a5, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Exceline Viska Desyanti', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a5, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Alifia Herizha', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a5, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Nadya Ramadhani Suherwin', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a5, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Ibad Dwi Alfreda', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => $a5, 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Driver Pak Hari', 'jabatan' => 'Driver A5', 'unit_kerja' => $a5, 'status_kepegawaian' => 'Non PNS'],

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