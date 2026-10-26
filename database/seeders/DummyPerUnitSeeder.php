<?php

namespace Database\Seeders;

use App\Models\Disposisi;
use App\Models\Laporan;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Data dummy persuratan per unit: Surat Masuk, Disposisi Digital (+ laporan), dan Surat Keluar.
 *
 * Unit yang diisi = unit yang punya akun TU. Tiap unit mendapat:
 *  - 3 Surat Masuk (baru / sedang diproses / selesai) yang diinput TU unit itu,
 *    disposisi awal ke pejabat (Eselon 1/2) di unit yang sama,
 *    lalu diteruskan sampai pegawai di unit Eselon 2 terkait;
 *  - 3 Surat Keluar (draft / diajukan / terbit) atas nama TU unit itu.
 *
 * Aman dijalankan berulang (tidak menggandakan data).
 * Jalankan: php artisan db:seed --class=DummyPerUnitSeeder
 * Butuh UnitSeeder dan UserSeeder sudah dijalankan.
 */
class DummyPerUnitSeeder extends Seeder
{
    private const ASAL = [
        'Kementerian Pertanian',
        'Badan Pangan Nasional',
        'Perum BULOG',
        'Badan Pusat Statistik',
        'Kementerian Perdagangan',
    ];

    private const TOPIK = [
        'Permohonan data produksi dan stok pangan',
        'Undangan rapat koordinasi lintas kementerian',
        'Penyampaian laporan evaluasi program pangan',
    ];

    private const SIFAT = ['biasa', 'segera', 'rahasia'];

    private const PETUNJUK_DUMMY = ['Tindak lanjuti sesuai ketentuan', 'Koordinasikan', 'Untuk diketahui'];

    public function run(): void
    {
        $units = Unit::whereIn('id', User::where('role', 'tu')->whereNotNull('unit_id')->pluck('unit_id'))
            ->orderBy('id')
            ->get();

        if ($units->isEmpty()) {
            $this->command?->warn('Belum ada akun TU yang terhubung ke unit. Jalankan UnitSeeder dan UserSeeder dulu.');

            return;
        }

        DB::transaction(function () use ($units) {
            foreach ($units as $i => $unit) {
                $this->isiUnit($unit, $i);
            }
        });
    }

    private function isiUnit(Unit $unit, int $urutanUnit): void
    {
        $tu = User::where('unit_id', $unit->id)->where('role', 'tu')->orderBy('id')->first();
        $pejabat = User::where('unit_id', $unit->id)->whereIn('role', ['eselon1', 'eselon2'])->orderBy('id')->first();

        if (!$tu || !$pejabat) {
            $this->command?->warn("Unit {$unit->kode}: dilewati (belum ada TU atau pejabat Eselon 1/2).");

            return;
        }

        $rantai = $this->susunRantai($pejabat);
        $slug = strtoupper(Str::slug($unit->kode));

        $this->isiSuratMasuk($unit, $tu, $rantai, $slug, $urutanUnit);
        $this->isiSuratKeluar($unit, $tu, $slug);
    }

    /**
     * Urutan penerima disposisi: pejabat unit, (Eselon 2 bila pejabat Eselon 1), lalu pegawai.
     * Eselon 1 hanya boleh meneruskan ke Eselon 2, Eselon 2 hanya ke pegawai di unitnya sendiri.
     *
     * @return User[]
     */
    private function susunRantai(User $pejabat): array
    {
        $rantai = [$pejabat];
        $level2 = $pejabat;

        if ($pejabat->role === 'eselon1') {
            $level2 = User::where('role', 'eselon2')->orderBy('id')->first();

            if ($level2) {
                $rantai[] = $level2;
            }
        }

        if ($level2 && $level2->role === 'eselon2' && $level2->unit_id) {
            $staf = User::where('role', 'user')->where('unit_id', $level2->unit_id)->orderBy('id')->first();

            if ($staf) {
                $rantai[] = $staf;
            }
        }

        return $rantai;
    }

    private function isiSuratMasuk(Unit $unit, User $tu, array $rantai, string $slug, int $urutanUnit): void
    {
        $skenario = ['baru', 'diproses', 'selesai'];
        $jalur = $rantai[0]->role === 'eselon1' ? 'deputi' : 'eselon2';

        foreach ($skenario as $n => $kasus) {
            $surat = SuratMasuk::firstOrCreate(
                ['nomor_agenda' => sprintf('DMY-%s-%02d', $slug, $n + 1)],
                [
                    'nomor_surat' => sprintf('B-%d/DMY/%s/2026', 100 + ($urutanUnit * 10) + $n, $slug),
                    'asal_surat' => self::ASAL[($urutanUnit + $n) % count(self::ASAL)],
                    // Nama unit di perihal supaya mudah dicek: data tiap unit tidak tercampur
                    'perihal' => self::TOPIK[$n] . ' (' . $unit->kode . ')',
                    'tanggal_surat' => Carbon::now()->subDays(10 - $n * 3)->toDateString(),
                    'tanggal_diterima' => Carbon::now()->subDays(9 - $n * 3)->toDateString(),
                    'drive_link' => 'https://drive.google.com/file/d/dummy-' . strtolower($slug) . '-' . ($n + 1) . '/view',
                    'created_by_id' => $tu->id,
                    'unit_id' => $unit->id,
                    'jalur_tujuan' => $jalur,
                    'status' => match ($kasus) {
                        'baru' => 'baru',
                        'diproses' => 'didisposisi',
                        'selesai' => 'selesai',
                    },
                ]
            );

            if (!$surat->wasRecentlyCreated) {
                continue;
            }

            $this->buatRantaiDisposisi($surat, $tu, $rantai, $kasus, $urutanUnit + $n);
        }
    }

    private function buatRantaiDisposisi(SuratMasuk $surat, User $tu, array $rantai, string $kasus, int $acak): void
    {
        $jumlah = count($rantai);
        $parent = null;
        $pengirim = $tu;
        $terakhir = null;

        foreach ($rantai as $i => $penerima) {
            $adalahAkar = $i === 0;
            $adalahUjung = $i === $jumlah - 1;

            if ($kasus === 'baru' && !$adalahAkar) {
                break; // surat baru: baru sampai ke pejabat
            }

            $status = match ($kasus) {
                'baru' => 'baru',
                'selesai' => 'selesai',
                default => $adalahUjung ? ($adalahAkar ? 'dibaca' : 'baru') : 'diproses',
            };

            $petunjuk = $adalahAkar ? [] : self::PETUNJUK_DUMMY;

            $dispo = Disposisi::create([
                'surat_masuk_id' => $surat->id,
                'parent_dispo_id' => $parent?->id,
                'pengirim_id' => $pengirim->id,
                'penerima_id' => $penerima->id,
                'instruksi' => $adalahAkar
                    ? 'Surat baru diterima. Mohon petunjuk dan arahan.'
                    : implode(', ', $petunjuk),
                'petunjuk' => $petunjuk ?: null,
                // Selang-seling: ada yang masih jauh, ada yang sudah lewat tenggat
                'tenggat_waktu' => $kasus === 'diproses'
                    ? Carbon::now()->addDays($acak % 2 === 0 ? 7 : -2)->toDateString()
                    : null,
                'sifat' => self::SIFAT[$acak % count(self::SIFAT)],
                'perlu_st' => false,
                'is_tembusan' => false,
                'status' => $status,
                'dibaca_at' => $status === 'baru' ? null : now(),
            ]);

            $parent = $dispo;
            $pengirim = $penerima;
            $terakhir = $dispo;
        }

        // Surat selesai: pelaksana di ujung rantai mengirim laporan pekerjaan
        if ($kasus === 'selesai' && $terakhir) {
            Laporan::create([
                'disposisi_id' => $terakhir->id,
                'pelapor_id' => $terakhir->penerima_id,
                'isi' => 'Dummy: tindak lanjut ' . $surat->nomor_agenda . ' sudah dikerjakan dan hasilnya dilaporkan.',
                'drive_link' => 'https://drive.google.com/file/d/dummy-laporan-' . $surat->id . '/view',
            ]);
        }
    }

    private function isiSuratKeluar(Unit $unit, User $tu, string $slug): void
    {
        $dokumen = [
            [
                'jenis' => 'nota_dinas',
                'status' => 'draft',
                'nomor' => null,
                'hal' => 'Permohonan persetujuan kegiatan',
                'payload' => [
                    'template' => 'es2',
                    'kode_klasifikasi' => 'PR.01.02',
                    'isi_ringkasan' => 'Dummy isi nota dinas dari ' . $unit->nama . '.',
                    'perlu_es1' => 0,
                    'dengan_lampiran' => 0,
                ],
            ],
            [
                'jenis' => 'undangan',
                'status' => 'diajukan',
                'nomor' => null,
                'hal' => 'Undangan rapat koordinasi',
                'payload' => [
                    'template' => 'internal',
                    'kode_klasifikasi' => 'PR.02.01',
                    'hari_tanggal' => 'Senin / 2 November 2026',
                    'waktu' => '09.00 WIB s.d. Selesai',
                    'tempat' => 'Ruang Rapat ' . $unit->kode,
                    'acara' => 'Rapat koordinasi tindak lanjut disposisi',
                    'perlu_es1' => 0,
                    'dengan_lampiran' => 0,
                ],
            ],
            [
                'jenis' => 'telaah_staf',
                'status' => 'terbit',
                'nomor' => 'B-DMY/' . $slug . '/2026',
                'hal' => 'Telaah staf usulan kebijakan',
                'payload' => [
                    'kode_klasifikasi' => 'PR.03.01',
                    'perlu_es1' => 0,
                    'dengan_lampiran' => 0,
                ],
            ],
        ];

        foreach ($dokumen as $d) {
            $hal = $d['hal'] . ' (' . $unit->kode . ')';

            SuratKeluar::firstOrCreate(
                ['hal' => $hal, 'created_by' => $tu->id],
                [
                    'jenis_dokumen' => $d['jenis'],
                    'nomor_surat' => $d['nomor'],
                    'sifat' => 'biasa',
                    'tanggal_surat' => Carbon::now()->toDateString(),
                    'tujuan' => 'Pihak terkait',
                    'dari' => $unit->nama,
                    'isi_payload' => $d['payload'],
                    'tembusan' => [],
                    'status_persetujuan' => $d['status'],
                ]
            );
        }
    }
}
