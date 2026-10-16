<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\SuratTugas;
use App\Models\SuratTugasCounter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Contoh data: ST 204/KA.00.04/2026 (ST 14 Oktober DJA Jakarta), status Terbit.
 * Aman dijalankan berulang: dilewati kalau nomor 204/KA.00.04/2026 sudah ada.
 *
 * Jalankan:  php artisan db:seed --class=SuratTugasContohSeeder
 */
class SuratTugasContohSeeder extends Seeder
{
    public function run(): void
    {
        $nomor = '204/KA.00.04/2026';

        if (SuratTugas::where('nomor', $nomor)->exists()) {
            $this->command?->info("ST {$nomor} sudah ada, dilewati.");
            return;
        }

        DB::transaction(function () use ($nomor) {
            $st = SuratTugas::create([
                'nomor' => $nomor,
                'nomor_urut' => 204,
                'tahun' => 2026,
                'kode_klasifikasi' => 'KA.00.04',
                'status' => 'terbit',
                'menimbang' => 'Bahwa dalam rangka melaksanakan Undangan Sosialisasi dan Help Desk Standarisasi Informasi Kinerja di Gedung Sutikno Slamet Direktorat Jendral Anggaran di Jakarta dianggap perlu dibuatkan surat tugas untuk melaksanakan tugas tersebut;',
                'dasar' => [
                    'Daftar Isian Pelaksanaan Anggaran (DIPA) Satuan Kerja Kementerian Koordinator Bidang Pangan Tahun 2025 Nomor: SP DIPA-131.01-0/2026 tanggal 1 Desember 2025;',
                    'Surat undangan Direktur Jendral Anggaran Direktur Sistm Penganggaran, Nomor : UND-30/AG/AG.9/2026 tanggal 10 Oktober 2026 perihal Undangan Sosialisasi dan Help Desk Standarisasi Informasi Kinerja di Gedung Sutikno Slamet Direktorat Jendral Anggaran di Jakarta.',
                ],
                'untuk' => [
                    'Melakukan Perjalanan Dinas dalam Undangan Sosialisasi dan Help Desk Standarisasi Informasi Kinerja pada tanggal 14 Oktober 2026 di Gedung Sutikno Slamet Direktorat Jendral Anggaran di Jakarta;',
                    'Selesai melaksanakan tugas agar menyampaikan laporan tertulis sesuai dengan ketentuan yang berlaku;',
                    'Biaya yang diperlukan untuk kegiatan tersebut dibebankan pada Anggaran Kementerian Koordinator Bidang Pangan.',
                ],
                'kepada' => 'Para Pejabat/Staf yang tercantum pada lampiran Surat Tugas ini.',
                'tempat' => 'Jakarta',
                'tanggal' => '2026-10-13',
                'jabatan_penandatangan' => 'Kepala Biro Manajemen Kinerja, Data dan Informasi',
                'nama_penandatangan' => 'Achmad Murman',
                'nip_penandatangan' => null,
                'created_by' => $this->adminId(),
                'diterbitkan_at' => '2026-10-13 09:00:00',
            ]);

            $peserta = [
                ['Didik Syaiful Bachri', '197708101998031003', 'Analis Kebijakan Ahli Madya'],
                ['Irfan Firmansyah', '198610152014021002', 'Perencana Ahli Muda'],
                ['Risna Farlina', null, 'Staf Biro Manajemen Kinerja Data dan Informasi'],
                ['Deaz Setyo Nugroho', null, 'Staf Biro Manajemen Kinerja Data dan Informasi'],
            ];

            foreach ($peserta as $i => [$nama, $nip, $jabatan]) {
                $st->peserta()->create([
                    'pegawai_id' => $this->pegawaiId($nama),
                    'urutan' => $i + 1,
                    'nama' => $nama,
                    'nip' => $nip,
                    'jabatan' => $jabatan,
                ]);
            }

            // Counter 2026 minimal 204 supaya ST berikutnya bernomor 205 (tidak pernah diturunkan)
            $counter = SuratTugasCounter::firstOrCreate(['tahun' => 2026], ['last_number' => 0]);
            if ($counter->last_number < 204) {
                $counter->update(['last_number' => 204]);
            }
        });

        $this->command?->info("ST contoh {$nomor} dibuat (status Terbit). Nomor berikutnya: 205.");
    }

    private function pegawaiId(string $nama): ?int
    {
        try {
            return Pegawai::where('nama', $nama)->value('id');
        } catch (\Throwable $e) {
            return null; // kolom nama beda / tabel kosong: peserta tetap tersimpan sebagai snapshot
        }
    }

    private function adminId(): ?int
    {
        try {
            return User::where('role', 'admin')->value('id') ?? User::value('id');
        } catch (\Throwable $e) {
            return null;
        }
    }
}