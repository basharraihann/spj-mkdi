<?php

namespace Database\Seeders;

use App\Models\Disposisi;
use App\Models\NotaDinas;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PersuratanDummySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat/Update User Dummy dengan Username & Peran Lengkap
        $tu = User::updateOrCreate(
            ['email' => 'tu@kemenkopangan.go.id'],
            [
                'name'     => 'Staf Persuratan / TU',
                'username' => 'tu',
                'password' => Hash::make('password'),
                'role'     => 'tu',
            ]
        );

        $deputi = User::updateOrCreate(
            ['email' => 'deputi@kemenkopangan.go.id'],
            [
                'name'     => 'Ibu Deputi',
                'username' => 'deputi',
                'password' => Hash::make('password'),
                'role'     => 'deputi',
            ]
        );

        $asdep = User::updateOrCreate(
            ['email' => 'asdep@kemenkopangan.go.id'],
            [
                'name'     => 'Bapak Asdep / Sesdep',
                'username' => 'asdep',
                'password' => Hash::make('password'),
                'role'     => 'asdep',
            ]
        );

        $pegawai = User::updateOrCreate(
            ['email' => 'pegawai@kemenkopangan.go.id'],
            [
                'name'     => 'Staf Pelaksana',
                'username' => 'pegawai',
                'password' => Hash::make('password'),
                'role'     => 'pegawai',
            ]
        );

        // 2. Dummy Surat Masuk 1 (Alur Lengkap Sampai Nota Dinas Selesai)
        $surat1 = SuratMasuk::firstOrCreate(
            ['nomor_agenda' => 'AG-001/MKDI/X/2026'],
            [
                'nomor_surat'      => 'B-890/KEMENTAN/10/2026',
                'asal_surat'       => 'Kementerian Pertanian',
                'perihal'          => 'Undangan Rapat Koordinasi Ketersediaan Cadangan Pangan Nasional',
                'tanggal_surat'    => '2026-10-18',
                'tanggal_diterima' => '2026-10-19',
                'drive_link'       => 'https://drive.google.com/file/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/preview',
                'created_by_id'    => $tu->id,
                'status'           => 'selesai',
            ]
        );

        // Disposisi Layer 1: TU -> Deputi
        $dispo1_1 = Disposisi::create([
            'surat_masuk_id'  => $surat1->id,
            'parent_dispo_id' => null,
            'pengirim_id'     => $tu->id,
            'penerima_id'     => $deputi->id,
            'instruksi'       => 'Mohon petunjuk dan arahan Ibu Deputi terkait kehadiran rapat koordinasi.',
            'sifat'           => 'segera',
            'status'          => 'diproses',
            'dibaca_at'       => '2026-10-19 09:00:00',
        ]);

        // Disposisi Layer 2: Deputi -> Asdep
        $dispo1_2 = Disposisi::create([
            'surat_masuk_id'  => $surat1->id,
            'parent_dispo_id' => $dispo1_1->id,
            'pengirim_id'     => $deputi->id,
            'penerima_id'     => $asdep->id,
            'instruksi'       => 'Tindak lanjuti, koordinasikan dengan tim teknis, dan siapkan bahan paparan.',
            'tenggat_waktu'   => '2026-10-23',
            'sifat'           => 'segera',
            'status'          => 'diproses',
            'dibaca_at'       => '2026-10-19 11:30:00',
        ]);

        // Disposisi Layer 3: Asdep -> Pegawai
        $dispo1_3 = Disposisi::create([
            'surat_masuk_id'  => $surat1->id,
            'parent_dispo_id' => $dispo1_2->id,
            'pengirim_id'     => $asdep->id,
            'penerima_id'     => $pegawai->id,
            'instruksi'       => 'Susun bahan pendukung data stok pangan dan konsep Nota Dinas laporan.',
            'tenggat_waktu'   => '2026-10-22',
            'sifat'           => 'biasa',
            'perlu_st'        => true,
            'status'          => 'selesai',
            'dibaca_at'       => '2026-10-19 13:00:00',
        ]);

        // Nota Dinas oleh Pegawai
        NotaDinas::create([
            'disposisi_id'     => $dispo1_3->id,
            'nomor_nd'         => 'ND-045/MKDI/2026',
            'perihal'          => 'Laporan Persiapan Bahan Rakor Ketersediaan Cadangan Pangan',
            'ringkasan_hasil'  => 'Telah diselesaikan penyusunan data ketersediaan beras dan komoditas pangan utama per Oktober 2026 beserta draft bahan paparan.',
            'drive_link_hasil' => 'https://drive.google.com/file/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/preview',
            'pembuat_id'       => $pegawai->id,
        ]);

        // 3. Dummy Surat Masuk 2 (Surat Masuk Baru Menunggu Disposisi Deputi)
        $surat2 = SuratMasuk::firstOrCreate(
            ['nomor_agenda' => 'AG-002/MKDI/X/2026'],
            [
                'nomor_surat'      => '005/BAPANAS/10/2026',
                'asal_surat'       => 'Badan Pangan Nasional',
                'perihal'          => 'Permohonan Data Laporan Evaluasi Distribusi Pangan Wilayah Barat',
                'tanggal_surat'    => '2026-10-20',
                'tanggal_diterima' => '2026-10-21',
                'drive_link'       => 'https://drive.google.com/file/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/preview',
                'created_by_id'    => $tu->id,
                'status'           => 'didisposisi',
            ]
        );

        Disposisi::create([
            'surat_masuk_id'  => $surat2->id,
            'parent_dispo_id' => null,
            'pengirim_id'     => $tu->id,
            'penerima_id'     => $deputi->id,
            'instruksi'       => 'Mohon arahan dan arahan disposisi selanjutnya dari Ibu Deputi.',
            'sifat'           => 'biasa',
            'status'          => 'baru',
        ]);
    }
}