<?php

namespace Database\Seeders;

use App\Models\MakOption;
use Illuminate\Database\Seeder;

class MakOptionSeeder extends Seeder
{
    /**
     * Data dari RINCIAN_KERTAS_KERJA_SATKER (RKAKL multiyear), sheet
     * RKK_MULTIYEAR_SATKER — di-parse otomatis dari struktur pohon Kode Giat >
     * Kode Komponen > Kode Sub Komponen > Kode Belanja di kolom A & D.
     *
     * Kegiatan: 7459.ABR.006 (Rekomendasi Kebijakan Bidang Usaha Pangan dan
     * Pertanian). Komponen yg ke-cover: 075, 076, 077, 078.
     *
     * TODO: kalau satker kamu punya kegiatan/komponen lain di luar RKK ini,
     * tambahin lagi entry-nya di sini pakai format yang sama.
     */
    private const DATA = [
        '7459.ABR.006.075.AA.521111' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Sistem Informasi Manajemen Internal Terintegrasi',
            'uraian_akun_ap' => 'TERLAKSANANYA SISTEM INFORMASI MANAJEMEN INTERNAL TERINTEGRASI 0000 - Pusat',
            'uraian_belanja' => 'Belanja Keperluan Perkantoran',
        ],
        '7459.ABR.006.075.AA.521211' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Sistem Informasi Manajemen Internal Terintegrasi',
            'uraian_akun_ap' => 'TERLAKSANANYA SISTEM INFORMASI MANAJEMEN INTERNAL TERINTEGRASI 0000 - Pusat',
            'uraian_belanja' => 'Belanja Bahan',
        ],
        '7459.ABR.006.075.AA.522141' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Sistem Informasi Manajemen Internal Terintegrasi',
            'uraian_akun_ap' => 'TERLAKSANANYA SISTEM INFORMASI MANAJEMEN INTERNAL TERINTEGRASI 0000 - Pusat',
            'uraian_belanja' => 'Belanja Sewa',
        ],
        '7459.ABR.006.075.AA.522151' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Sistem Informasi Manajemen Internal Terintegrasi',
            'uraian_akun_ap' => 'TERLAKSANANYA SISTEM INFORMASI MANAJEMEN INTERNAL TERINTEGRASI 0000 - Pusat',
            'uraian_belanja' => 'Belanja Jasa Profesi',
        ],
        '7459.ABR.006.075.AA.522191' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Sistem Informasi Manajemen Internal Terintegrasi',
            'uraian_akun_ap' => 'TERLAKSANANYA SISTEM INFORMASI MANAJEMEN INTERNAL TERINTEGRASI 0000 - Pusat',
            'uraian_belanja' => 'Belanja Jasa Lainnya',
        ],
        '7459.ABR.006.075.AA.524111' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Sistem Informasi Manajemen Internal Terintegrasi',
            'uraian_akun_ap' => 'TERLAKSANANYA SISTEM INFORMASI MANAJEMEN INTERNAL TERINTEGRASI 0000 - Pusat',
            'uraian_belanja' => 'Belanja Perjalanan Dinas Biasa',
        ],
        '7459.ABR.006.075.AA.524113' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Sistem Informasi Manajemen Internal Terintegrasi',
            'uraian_akun_ap' => 'TERLAKSANANYA SISTEM INFORMASI MANAJEMEN INTERNAL TERINTEGRASI 0000 - Pusat',
            'uraian_belanja' => 'Belanja Perjalanan Dinas Dalam Kota',
        ],
        '7459.ABR.006.075.AA.524114' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Sistem Informasi Manajemen Internal Terintegrasi',
            'uraian_akun_ap' => 'TERLAKSANANYA SISTEM INFORMASI MANAJEMEN INTERNAL TERINTEGRASI 0000 - Pusat',
            'uraian_belanja' => 'Belanja Perjalanan Dinas Paket Meeting Dalam Kota',
        ],
        '7459.ABR.006.075.AA.524119' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Sistem Informasi Manajemen Internal Terintegrasi',
            'uraian_akun_ap' => 'TERLAKSANANYA SISTEM INFORMASI MANAJEMEN INTERNAL TERINTEGRASI 0000 - Pusat',
            'uraian_belanja' => 'Belanja Perjalanan Dinas Paket Meeting Luar Kota',
        ],
        '7459.ABR.006.075.AA.532111' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Sistem Informasi Manajemen Internal Terintegrasi',
            'uraian_akun_ap' => 'TERLAKSANANYA SISTEM INFORMASI MANAJEMEN INTERNAL TERINTEGRASI 0000 - Pusat',
            'uraian_belanja' => 'Belanja Modal Peralatan dan Mesin',
        ],
        '7459.ABR.006.075.AA.536111' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Sistem Informasi Manajemen Internal Terintegrasi',
            'uraian_akun_ap' => 'TERLAKSANANYA SISTEM INFORMASI MANAJEMEN INTERNAL TERINTEGRASI 0000 - Pusat',
            'uraian_belanja' => 'Belanja Modal Lainnya',
        ],
        '7459.ABR.006.076.BB.521213' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'TERLAKSANANYA PENGELOLAAN INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Honor Output Kegiatan',
        ],
        '7459.ABR.006.076.BB.522191' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'TERLAKSANANYA PENGELOLAAN INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Jasa Lainnya',
        ],
        '7459.ABR.006.076.BB.524119' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'TERLAKSANANYA PENGELOLAAN INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Perjalanan Dinas Paket Meeting Luar Kota',
        ],
        '7459.ABR.006.076.CC.521211' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'KOORDINASI TATA KELOLA INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Bahan',
        ],
        '7459.ABR.006.076.CC.521213' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'KOORDINASI TATA KELOLA INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Honor Output Kegiatan',
        ],
        '7459.ABR.006.076.CC.522141' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'KOORDINASI TATA KELOLA INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Sewa',
        ],
        '7459.ABR.006.076.CC.522151' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'KOORDINASI TATA KELOLA INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Jasa Profesi',
        ],
        '7459.ABR.006.076.CC.524111' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'KOORDINASI TATA KELOLA INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Perjalanan Dinas Biasa',
        ],
        '7459.ABR.006.076.CC.524113' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'KOORDINASI TATA KELOLA INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Perjalanan Dinas Dalam Kota',
        ],
        '7459.ABR.006.076.DD.521211' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'PENYUSUNAN DATA INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Bahan',
        ],
        '7459.ABR.006.076.DD.522151' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'PENYUSUNAN DATA INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Jasa Profesi',
        ],
        '7459.ABR.006.076.DD.524111' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'PENYUSUNAN DATA INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Perjalanan Dinas Biasa',
        ],
        '7459.ABR.006.076.EE.521111' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'PEMBANGUNAN SISTEM INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Keperluan Perkantoran',
        ],
        '7459.ABR.006.076.EE.522151' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'PEMBANGUNAN SISTEM INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Jasa Profesi',
        ],
        '7459.ABR.006.076.EE.524111' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'PEMBANGUNAN SISTEM INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Perjalanan Dinas Biasa',
        ],
        '7459.ABR.006.076.EE.524119' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'PEMBANGUNAN SISTEM INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Perjalanan Dinas Paket Meeting Luar Kota',
        ],
        '7459.ABR.006.076.EE.536111' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Pengelolaan Informasi Kinerja Pangan Nasional',
            'uraian_akun_ap' => 'PEMBANGUNAN SISTEM INFORMASI KINERJA PANGAN NASIONAL',
            'uraian_belanja' => 'Belanja Modal Lainnya',
        ],
        '7459.ABR.006.077.CC.522191' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Tata Kelola Media Placement Menko Pangan',
            'uraian_akun_ap' => 'TERLAKSANANYA TATA KELOLA MEDIA PLACEMENT MENKO PANGAN',
            'uraian_belanja' => 'Belanja Jasa Lainnya',
        ],
        '7459.ABR.006.077.CC.532111' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Tata Kelola Media Placement Menko Pangan',
            'uraian_akun_ap' => 'TERLAKSANANYA TATA KELOLA MEDIA PLACEMENT MENKO PANGAN',
            'uraian_belanja' => 'Belanja Modal Peralatan dan Mesin',
        ],
        '7459.ABR.006.078.DD.521119' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Monev Koordinasi Kebijakan Sistem Informasi Pangan Terintegrasi Nasional',
            'uraian_akun_ap' => 'TERLAKSANANYA MONEV KOORDINASI KEBIJAKAN SISTEM INFORMASI PANGAN TERINTEGRASI NASIONAL',
            'uraian_belanja' => 'Belanja Barang Operasional Lainnya',
        ],
        '7459.ABR.006.078.DD.522141' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Monev Koordinasi Kebijakan Sistem Informasi Pangan Terintegrasi Nasional',
            'uraian_akun_ap' => 'TERLAKSANANYA MONEV KOORDINASI KEBIJAKAN SISTEM INFORMASI PANGAN TERINTEGRASI NASIONAL',
            'uraian_belanja' => 'Belanja Sewa',
        ],
        '7459.ABR.006.078.DD.522191' => [
            'uraian_giat' => 'Rekomendasi Kebijakan Program Prioritas Nasional Bidang Usaha Pangan dan Pertanian',
            'uraian_komponen' => 'Monev Koordinasi Kebijakan Sistem Informasi Pangan Terintegrasi Nasional',
            'uraian_akun_ap' => 'TERLAKSANANYA MONEV KOORDINASI KEBIJAKAN SISTEM INFORMASI PANGAN TERINTEGRASI NASIONAL',
            'uraian_belanja' => 'Belanja Jasa Lainnya',
        ],
    ];

    public function run(): void
    {
        foreach (self::DATA as $mak => $uraian) {
            MakOption::updateOrCreate(['mak' => $mak], $uraian);
        }
    }
}