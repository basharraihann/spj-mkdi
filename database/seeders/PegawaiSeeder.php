<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $semua = array_merge($this->dataBiro(), $this->dataDeputi());

        foreach ($semua as $row) {
            // Kunci unik: NIP/NIK kalau ada, kalau tidak pakai nama + unit kerja
            $key = !empty($row['nip'])
                ? ['nip' => $row['nip']]
                : ['nama' => $row['nama'], 'unit_kerja' => $row['unit_kerja']];

            Pegawai::updateOrCreate($key, $row);
        }
    }

    /** Biro Manajemen Kinerja, Data dan Informasi (data lama, tidak diubah) */
    private function dataBiro(): array
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

        return array_map(fn($row) => array_merge($row, ['unit_kerja' => $unitKerja]), $data);
    }

    /**
     * Deputi Bidang Koordinasi Usaha Pangan dan Pertanian (81 orang, dari Excel v1).
     * Untuk Non PNS, kolom 'nip' berisi NIK.
     */
    private function dataDeputi(): array
    {
        return [
            ['nama' => 'Widiastuti', 'nip' => '197005121994032001', 'pangkat' => 'Pembina Utama Madya', 'golongan' => 'IV/d', 'jabatan' => 'Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'unit_kerja' => 'Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Yasser', 'nip' => '3173050812880001', 'jabatan' => 'Tenaga Pendukung di Bidang Tata Usaha Pimpinan', 'unit_kerja' => 'Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Karsan', 'nip' => '196803221991031003', 'pangkat' => 'Pembina Utama Muda', 'golongan' => 'IV/c', 'jabatan' => 'Sekretaris Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Kunto Nugroho', 'nip' => '198912142018011001', 'pangkat' => 'Penata Tk I', 'golongan' => 'III/d', 'jabatan' => 'Analis Kebijakan Ahli Muda', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Achmad Kimsan', 'nip' => '197904092005021007', 'pangkat' => 'Penata', 'golongan' => 'III/c', 'jabatan' => 'Penelaah Teknis Kebijakan', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Sendy Pratiwi', 'nip' => '199309032018012001', 'pangkat' => 'Penata', 'golongan' => 'III/c', 'jabatan' => 'Analis Kebijakan Ahli Pertama', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Wahid Setiadi', 'nip' => '198808112022031002', 'pangkat' => 'Penata Muda Tk I', 'golongan' => 'III/b', 'jabatan' => 'Analis Kebijakan Ahli Pertama', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Laily Fitriah', 'nip' => '198304122008042002', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Penelaah Teknis Kebijakan', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Chyntya Syafril', 'nip' => '3275084206950016', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Reza Rama Gunada', 'nip' => '1802092409980001', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Rizka Awaliah', 'nip' => '3173084404000004', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Renato Fortinov Parulian Tampubolon', 'nip' => '3273121411970010', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Fathimah Husna', 'nip' => '3302255610020001', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Ira Putri Rahayu', 'nip' => '3172045402020006', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Yolanda Rosa Debora Panjaitan', 'nip' => '3201014310030000', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Nurrachmawati', 'nip' => '3674054108020001', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Yemima Veronika', 'nip' => '1209195510970003', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Rr Rizka Nurul Cahyani Santoso', 'nip' => '3275055310020012', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'St. Wulan Aprianti', 'nip' => '3601216504020005', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Rizki Catur Parmanto', 'nip' => '3306121904040001', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Adi Suheri', 'jabatan' => 'Driver Deputi', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Bagus Dwi Yulianto', 'jabatan' => 'Driver Sesdep', 'unit_kerja' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Kus Prisetiahadi', 'nip' => '197009251995031001', 'pangkat' => 'Pembina Utama Madya', 'golongan' => 'IV/d', 'jabatan' => 'Asisten Deputi Peningkatan Daya Saing Produk Tanaman Pangan', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Tanaman Pangan', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Henry Antonius Manalu', 'nip' => '197306031999031002', 'pangkat' => 'Pembina Tk I', 'golongan' => 'IV/b', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Tanaman Pangan', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Yuyun Kurnia Lestari', 'nip' => '198805042015022001', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Tanaman Pangan', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Ronald Hasintongan', 'nip' => '198209162014031001', 'pangkat' => 'Penata Tk I', 'golongan' => 'III/d', 'jabatan' => 'Analis Kebijakan Ahli Muda', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Tanaman Pangan', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Indira Wido Primadipta', 'nip' => '199506042019012001', 'pangkat' => 'Penata Muda Tk I', 'golongan' => 'III/b', 'jabatan' => 'Analis Kebijakan Ahli Pertama', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Tanaman Pangan', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Kunthi Habri Saputri', 'nip' => '9104014510940002', 'jabatan' => 'Staf Asdep PDSP Tanaman Pangan', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Tanaman Pangan', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Eduardus Eka Ramanditya', 'nip' => '3374090111010002', 'jabatan' => 'Staf Asdep PDSP Tanaman Pangan', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Tanaman Pangan', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Fatimah Kusuma Dewi', 'nip' => '3175087112020001', 'jabatan' => 'Staf Asdep PDSP Tanaman Pangan', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Tanaman Pangan', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Sabrina Nurraihana', 'nip' => '3201305009040006', 'jabatan' => 'Staf Asdep PDSP Tanaman Pangan', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Tanaman Pangan', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Ibnu Fajar Bisri', 'jabatan' => 'Driver A1', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Tanaman Pangan', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Silvi Charlote Sumanti', 'nip' => '197504122005022001', 'pangkat' => 'Pembina Utama Muda', 'golongan' => 'IV/c', 'jabatan' => 'Asisten Deputi Peningkatan Daya Saing Produk Pertanian', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Ratih Sintya Suly', 'nip' => '197912122005022001', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Analis Perdagangan Ahli Muda', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Noviarina Purnami Putri', 'nip' => '198211042009012004', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'R. Stevanus Bayu Mangkurat', 'nip' => '198509272019011001', 'pangkat' => 'Penata', 'golongan' => 'III/c', 'jabatan' => 'Analis Perdagangan Ahli Muda', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Wasinton Tulus Oktavianus Sianturi', 'nip' => '198010112014021001', 'pangkat' => 'Penata Tk I', 'golongan' => 'III/d', 'jabatan' => 'Analis Perdagangan Ahli Muda', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Retno Sawitri', 'nip' => '199004302014022001', 'pangkat' => 'Penata', 'golongan' => 'III/c', 'jabatan' => 'Negosiator Perdagangan Ahli Muda', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Salma Jihan Nabiilah', 'nip' => '3520164201010002', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Rima Nur Latifah', 'nip' => '3402046303020001', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Dimas Naufaldy Ardian', 'nip' => '3275090608030013', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Muhammad Khladun', 'nip' => '1106031712040002', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Bambang Utomo', 'jabatan' => 'Driver A2', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Bona Kusuma', 'nip' => '197610242005021001', 'pangkat' => 'Pembina Utama Muda', 'golongan' => 'IV/c', 'jabatan' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'unit_kerja' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Muhammad Rizal Panrelly', 'nip' => '196911231994031010', 'pangkat' => 'Pembina Utama Muda', 'golongan' => 'IV/c', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Yonatan Kriswidiyanto Sitohang', 'nip' => '197608052005021001', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Analis Perdagangan Ahli Madya', 'unit_kerja' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Dian Dwi Laksani', 'nip' => '198407162010122002', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Lailun Maulidiyah', 'nip' => '198412052010122002', 'pangkat' => 'Penata Tk I', 'golongan' => 'III/d', 'jabatan' => 'Analis Sumber Daya Manusia Aparatur Ahli Muda', 'unit_kerja' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Randi Morin', 'nip' => '198508062015051001', 'pangkat' => 'Penata Tk I', 'golongan' => 'III/d', 'jabatan' => 'Analis Kebijakan Ahli Muda', 'unit_kerja' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Gabriel Malona', 'nip' => '198807202014021002', 'pangkat' => 'Penata', 'golongan' => 'III/c', 'jabatan' => 'Negosiator Perdagangan Ahli Muda', 'unit_kerja' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Suhantono', 'nip' => '3674050502610005', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Randi Ernawan', 'nip' => '3271042812890016', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Claudia Yuliarta Abigael', 'nip' => '3175065707020008', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Azzahra Asya D S', 'nip' => '3578084710020004', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Naesha Ardibi', 'nip' => '3172036801060000', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Shalma Azzahra', 'nip' => '3276015011050000', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Shintia Octaviani Simanjuntak', 'nip' => '1217094210020002', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Utari Praba Ningrum', 'nip' => '3404036107040001', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Anwar Sanusi', 'jabatan' => 'Driver A3', 'unit_kerja' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Radian Bagiyono', 'nip' => '197411082000031002', 'pangkat' => 'Pembina Utama Muda', 'golongan' => 'IV/c', 'jabatan' => 'Asisten Deputi Peningkatan Daya Saing Produk Perkebunan dan Hortikultura', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Perkebunan dan Hortikultura', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Darto', 'nip' => '196811101991021001', 'pangkat' => 'Pembina Tk I', 'golongan' => 'IV/b', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Perkebunan dan Hortikultura', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Jhon Hasudungan Pardomuan Tambun', 'nip' => '196901291996031001', 'pangkat' => 'Pembina Tk I', 'golongan' => 'IV/b', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Perkebunan dan Hortikultura', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Mielyon Erison Datty', 'nip' => '197505212000031001', 'pangkat' => 'Pembina Utama Muda', 'golongan' => 'IV/c', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Perkebunan dan Hortikultura', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Naufal Farras Syihab', 'nip' => '199711132022031004', 'pangkat' => 'Penata Muda', 'golongan' => 'III/a', 'jabatan' => 'Penelaah Teknis Kebijakan', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Perkebunan dan Hortikultura', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Muhammad Fathin Akbar', 'nip' => '3674062204030010', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Perkebunan dan Hortikultura', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Intan Dwi Handayani', 'nip' => '3520165412020000', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Perkebunan dan Hortikultura', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Stephanie Silvi Tamba', 'nip' => '3275094907030000', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Perkebunan dan Hortikultura', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Neyreza Arviana Putri', 'nip' => '3519036103030000', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Perkebunan dan Hortikultura', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Gabriela Oktaviani Ayudya Putri', 'nip' => '3404104210000002', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Perkebunan dan Hortikultura', 'status_kepegawaian' => 'Non PNS'],  // status di Excel kosong, diasumsikan Non PNS (cek)
            ['nama' => 'Hasan Prastowo', 'jabatan' => 'Driver A4', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Perkebunan dan Hortikultura', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Harimurti', 'nip' => '197312252000031001', 'pangkat' => 'Pembina Utama Muda', 'golongan' => 'IV/c', 'jabatan' => 'Asisten Deputi Peningkatan Daya Saing Produk Peternakan', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Peternakan', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Fatma Puspitasari', 'nip' => '197905042003122001', 'pangkat' => 'Pembina Tk I', 'golongan' => 'IV/b', 'jabatan' => 'Analis Kebijakan Ahli Madya', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Peternakan', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Yudhi Ratna Kurniawan', 'nip' => '198008062005021001', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Analis Kebijakan Ahli Muda', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Peternakan', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Riana Sri Hastuti', 'nip' => '199511272020122013', 'pangkat' => 'Penata Muda Tk I', 'golongan' => 'III/b', 'jabatan' => 'Analis Kebijakan Ahli Pertama', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Peternakan', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'Ikka Fitri Mei Kennedy', 'nip' => '198905102020121009', 'pangkat' => 'Penata Muda Tk I', 'golongan' => 'III/b', 'jabatan' => 'Penelaah Teknis Kebijakan', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Peternakan', 'status_kepegawaian' => 'PNS'],
            ['nama' => 'M Khubbab Fairus', 'nip' => '3216122007950004', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Peternakan', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Exceline Viska Desyanti', 'nip' => '3374065312990000', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Peternakan', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Alifia Herizha', 'nip' => '3273162505040010', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Peternakan', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Nadya Ramadhani Suherwin', 'nip' => '3175085511030000', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Peternakan', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Ibad Dwi Alfreda', 'nip' => '3325126807040001', 'jabatan' => 'Staf Pelaksana', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Peternakan', 'status_kepegawaian' => 'Non PNS'],
            ['nama' => 'Driver Pak Hari', 'jabatan' => 'Driver A5', 'unit_kerja' => 'Asisten Deputi Peningkatan Daya Saing Produk Peternakan', 'status_kepegawaian' => 'Non PNS'],
        ];
    }
}