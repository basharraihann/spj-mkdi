<?php

namespace Database\Seeders;

use App\Models\Disposisi;
use App\Models\NotaDinas;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Database\Seeder;

class PersuratanDummySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil User Berdasarkan Role Standar ATAU Role Deputi 2
        $tu = User::whereIn('role', ['tu', 'tu-d2'])->first() 
            ?? User::where('username', 'tu-d2')->first() 
            ?? User::where('username', 'tu')->first();

        $deputi = User::whereIn('role', ['eselon1', 'deputi', 'deputi-d2'])->first() 
            ?? User::where('username', 'deputi-d2')->first() 
            ?? User::where('username', 'deputi')->first();

        $asdep = User::whereIn('role', ['eselon2', 'asdep', 'asdep-d2'])->first() 
            ?? User::where('username', 'asdep-d2')->first() 
            ?? User::where('username', 'asdep')->first();

        $pegawai = User::whereIn('role', ['user', 'pegawai', 'pegawai-d2'])->first() 
            ?? User::where('username', 'pegawai-d2')->first() 
            ?? User::where('username', 'pegawai')->first();

        if (!$tu || !$deputi || !$asdep || !$pegawai) {
            return; // Pastikan UserSeeder sudah dijalankan lebih dahulu
        }

        $dummyDrive = 'https://drive.google.com/file/d/1bYeUBY3rGUKg67z5UV509WwJ0s__kosH/view?usp=drive_link';

        // 2. Daftar 10 Data Surat Masuk
        $daftarSurat = [
            [
                'nomor_agenda'    => 'AG-001/MKDI/X/2026',
                'nomor_surat'     => 'B-890/KEMENTAN/10/2026',
                'asal_surat'      => 'Kementerian Pertanian',
                'perihal'         => 'Undangan Rapat Koordinasi Ketersediaan Cadangan Pangan Nasional',
                'tanggal_surat'   => '2026-10-10',
                'tanggal_diterima'=> '2026-10-11',
                'status'          => 'selesai',
            ],
            [
                'nomor_agenda'    => 'AG-002/MKDI/X/2026',
                'nomor_surat'     => '005/BAPANAS/10/2026',
                'asal_surat'      => 'Badan Pangan Nasional',
                'perihal'         => 'Permohonan Data Laporan Evaluasi Distribusi Pangan Wilayah Barat',
                'tanggal_surat'   => '2026-10-12',
                'tanggal_diterima'=> '2026-10-13',
                'status'          => 'didisposisi',
            ],
            [
                'nomor_agenda'    => 'AG-003/MKDI/X/2026',
                'nomor_surat'     => 'ND-102/KEMENDAG/10/2026',
                'asal_surat'      => 'Kementerian Perdagangan',
                'perihal'         => 'Usulan Kebijakan Stabilisasi Harga Beras dan Minyak Goreng',
                'tanggal_surat'   => '2026-10-14',
                'tanggal_diterima'=> '2026-10-15',
                'status'          => 'didisposisi',
            ],
            [
                'nomor_agenda'    => 'AG-004/MKDI/X/2026',
                'nomor_surat'     => 'B-450/BULOG/10/2026',
                'asal_surat'      => 'Perum BULOG',
                'perihal'         => 'Laporan Penyerapan Gabah Petani Semester II Tahun 2026',
                'tanggal_surat'   => '2026-10-15',
                'tanggal_diterima'=> '2026-10-16',
                'status'          => 'selesai',
            ],
            [
                'nomor_agenda'    => 'AG-005/MKDI/X/2026',
                'nomor_surat'     => '120/KEMENKEU/DJP/2026',
                'asal_surat'      => 'Kementerian Keuangan',
                'perihal'         => 'Penyampaian Pagu Indikatif Anggaran Ketahanan Pangan TA 2027',
                'tanggal_surat'   => '2026-10-16',
                'tanggal_diterima'=> '2026-10-17',
                'status'          => 'didisposisi',
            ],
            [
                'nomor_agenda'    => 'AG-006/MKDI/X/2026',
                'nomor_surat'     => 'B-301/SETKAB/10/2026',
                'asal_surat'      => 'Sekretariat Kabinet',
                'perihal'         => 'Undangan Sidang Kabinet Terbatas Sektor Pertanian & Maritim',
                'tanggal_surat'   => '2026-10-17',
                'tanggal_diterima'=> '2026-10-18',
                'status'          => 'didisposisi',
            ],
            [
                'nomor_agenda'    => 'AG-007/MKDI/X/2026',
                'nomor_surat'     => '088/BPS/PR/10/2026',
                'asal_surat'      => 'Badan Pusat Statistik',
                'perihal'         => 'Rilis Proyeksi Produksi Padi dan Luas Panen Nasional 2026',
                'tanggal_surat'   => '2026-10-18',
                'tanggal_diterima'=> '2026-10-19',
                'status'          => 'baru',
            ],
            [
                'nomor_agenda'    => 'AG-008/MKDI/X/2026',
                'nomor_surat'     => 'B-991/KEMENDAGRI/10/2026',
                'asal_surat'      => 'Kementerian Dalam Negeri',
                'perihal'         => 'Laporan Pengawasan Tim Pengendalian Inflasi Daerah (TPID)',
                'tanggal_surat'   => '2026-10-19',
                'tanggal_diterima'=> '2026-10-20',
                'status'          => 'didisposisi',
            ],
            [
                'nomor_agenda'    => 'AG-009/MKDI/X/2026',
                'nomor_surat'     => '012/IDFOOD/10/2026',
                'asal_surat'      => 'PT Rajawali Nusantara Indonesia (ID FOOD)',
                'perihal'         => 'Permohonan Dukungan Distribusi Gula dan Minyak Goreng Curah',
                'tanggal_surat'   => '2026-10-20',
                'tanggal_diterima'=> '2026-10-21',
                'status'          => 'baru',
            ],
            [
                'nomor_agenda'    => 'AG-010/MKDI/X/2026',
                'nomor_surat'     => 'B-512/KEMENPUPR/10/2026',
                'asal_surat'      => 'Kementerian PUPR',
                'perihal'         => 'Progres Pembangunan Jaringan Irigasi Cetak Sawah Baru',
                'tanggal_surat'   => '2026-10-21',
                'tanggal_diterima'=> '2026-10-22',
                'status'          => 'baru',
            ],
        ];

        foreach ($daftarSurat as $idx => $data) {
            $surat = SuratMasuk::firstOrCreate(
                ['nomor_agenda' => $data['nomor_agenda']],
                array_merge($data, [
                    'drive_link'    => $dummyDrive,
                    'created_by_id' => $tu->id,
                ])
            );

            // Bersihkan disposisi lama jika ada
            Disposisi::where('surat_masuk_id', $surat->id)->delete();

            // Skenario 1: Surat Selesai (Disposisi Berjenjang TU -> Deputi -> Asdep -> Pegawai + Nota Dinas)
            if ($data['status'] === 'selesai') {
                $d1 = Disposisi::create([
                    'surat_masuk_id'  => $surat->id,
                    'parent_dispo_id' => null,
                    'pengirim_id'     => $tu->id,
                    'penerima_id'     => $deputi->id,
                    'instruksi'       => 'Mohon petunjuk dan arahan Ibu Deputi.',
                    'sifat'           => 'segera',
                    'status'          => 'diproses',
                    'dibaca_at'       => now()->subDays(3),
                ]);

                $d2 = Disposisi::create([
                    'surat_masuk_id'  => $surat->id,
                    'parent_dispo_id' => $d1->id,
                    'pengirim_id'     => $deputi->id,
                    'penerima_id'     => $asdep->id,
                    'instruksi'       => 'Tindak lanjuti dan siapkan bahan laporan.',
                    'tenggat_waktu'   => now()->addDays(2)->toDateString(),
                    'sifat'           => 'segera',
                    'status'          => 'diproses',
                    'dibaca_at'       => now()->subDays(2),
                ]);

                $d3 = Disposisi::create([
                    'surat_masuk_id'  => $surat->id,
                    'parent_dispo_id' => $d2->id,
                    'pengirim_id'     => $asdep->id,
                    'penerima_id'     => $pegawai->id,
                    'instruksi'       => 'Susun konsep Nota Dinas dan bahan kajian teknis.',
                    'tenggat_waktu'   => now()->addDay()->toDateString(),
                    'sifat'           => 'biasa',
                    'perlu_st'        => true,
                    'status'          => 'selesai',
                    'dibaca_at'       => now()->subDay(),
                ]);

                if (class_exists(NotaDinas::class)) {
                    NotaDinas::updateOrCreate(
                        ['disposisi_id' => $d3->id],
                        [
                            'nomor_nd'         => 'ND-0' . ($idx + 1) . '/MKDI/2026',
                            'perihal'          => 'Laporan Hasil Tindak Lanjut ' . $surat->perihal,
                            'ringkasan_hasil'  => 'Telah diselesaikan penyusunan kajian teknis dan bahan paparan pimpinan.',
                            'drive_link_hasil' => $dummyDrive,
                            'pembuat_id'       => $pegawai->id,
                        ]
                    );
                }
            }
            // Skenario 2: Surat Sedang Didisposisi (Disposisi TU -> Deputi -> Asdep)
            elseif ($data['status'] === 'didisposisi') {
                $d1 = Disposisi::create([
                    'surat_masuk_id'  => $surat->id,
                    'parent_dispo_id' => null,
                    'pengirim_id'     => $tu->id,
                    'penerima_id'     => $deputi->id,
                    'instruksi'       => 'Mohon petunjuk dan arahan Ibu Deputi.',
                    'sifat'           => 'biasa',
                    'status'          => 'diproses',
                    'dibaca_at'       => now()->subDays(1),
                ]);

                Disposisi::create([
                    'surat_masuk_id'  => $surat->id,
                    'parent_dispo_id' => $d1->id,
                    'pengirim_id'     => $deputi->id,
                    'penerima_id'     => $asdep->id,
                    'instruksi'       => 'Pelajari, koordinasikan dengan instansi pengirim, dan laporkan.',
                    'tenggat_waktu'   => now()->addDays(3)->toDateString(),
                    'sifat'           => 'biasa',
                    'status'          => 'baru',
                ]);
            }
            // Skenario 3: Surat Baru Masuk (Disposisi Awal TU -> Deputi)
            else {
                Disposisi::create([
                    'surat_masuk_id'  => $surat->id,
                    'parent_dispo_id' => null,
                    'pengirim_id'     => $tu->id,
                    'penerima_id'     => $deputi->id,
                    'instruksi'       => 'Surat baru diteruskan. Mohon petunjuk arahan Ibu Deputi.',
                    'sifat'           => 'biasa',
                    'status'          => 'baru',
                ]);
            }
        }
    }
}