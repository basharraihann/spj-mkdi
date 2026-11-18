<?php

namespace App\Services;

use App\Models\AnggaranUpload;
use App\Models\MakOption;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Menyegarkan mak_options dari upload FA Detail yang aktif.
 * Nomor MAK = Kegiatan(setelah titik terakhir).RO(sudah memuat KRO).Komponen.Sub(setelah titik terakhir).Akun
 * contoh: CL.7458 + ABB.001 + 053 + 053.AA + 524111 => 7458.ABB.001.053.AA.524111
 */
class MakSyncService
{
    /** @return array{baru:int, diperbarui:int, nonaktif:int, diaktifkan:int, pagu_cocok:bool, per_tanggal:string} */
    public function sync(?AnggaranUpload $upload = null, bool $dryRun = false): array
    {
        $upload ??= AnggaranUpload::where('is_active', true)->latest('id')->first();
        if (!$upload) {
            throw new RuntimeException('Belum ada upload FA yang aktif.');
        }

        $tgl = $upload->per_tanggal
            ? Carbon::parse($upload->per_tanggal)
            : self::tanggalDariNamaFile((string) $upload->nama_file);
        if (!$tgl) {
            throw new RuntimeException('Tanggal laporan (per_tanggal) belum diisi dan tidak bisa dibaca dari nama file.');
        }
        $perTanggal = $tgl->toDateString();
        $periode = trim(preg_replace('/^periode\s+/i', '', (string) $upload->periode));

        $rows = DB::table('anggaran_items')
            ->where('anggaran_upload_id', $upload->id)
            ->selectRaw('keg_kode, ro_kode, komp_kode, sub_kode, akun_kode,
                SUM(pagu) AS pagu, SUM(realisasi) AS realisasi,
                MAX(ro_uraian) AS ro_uraian, MAX(komp_uraian) AS komp_uraian,
                MAX(sub_uraian) AS sub_uraian, MAX(akun_uraian) AS akun_uraian')
            ->groupBy('keg_kode', 'ro_kode', 'komp_kode', 'sub_kode', 'akun_kode')
            ->get();

        if ($rows->isEmpty()) {
            throw new RuntimeException('Upload aktif tidak punya item.');
        }

        $result = ['baru' => 0, 'diperbarui' => 0, 'nonaktif' => 0, 'diaktifkan' => 0,
            'pagu_cocok' => true, 'per_tanggal' => $perTanggal];

        DB::transaction(function () use ($rows, $upload, $periode, $perTanggal, $dryRun, &$result) {
            $existing = MakOption::get()->keyBy('mak');
            $seen = [];

            foreach ($rows as $r) {
                $mak = implode('.', [
                    Str::afterLast(trim($r->keg_kode), '.'),
                    trim($r->ro_kode),
                    trim($r->komp_kode),
                    Str::afterLast(trim($r->sub_kode), '.'),
                    trim($r->akun_kode),
                ]);
                $seen[$mak] = true;

                $pagu = (int) $r->pagu;
                $real = (int) $r->realisasi;
                $angka = [
                    'pagu' => $pagu,
                    'realisasi' => $real,
                    'sisa' => $pagu - $real,
                    'periode' => $periode,
                    'per_tanggal' => $perTanggal,
                    'is_active' => true,
                ];

                if ($row = $existing->get($mak)) {
                    if (!$row->is_active) $result['diaktifkan']++;
                    $result['diperbarui']++;
                    // uraian yang sudah ada tidak ditimpa
                    if (!$dryRun) $row->update($angka);
                } else {
                    $result['baru']++;
                    if (!$dryRun) {
                        MakOption::create($angka + [
                            'mak' => $mak,
                            'uraian_giat' => $r->ro_uraian,
                            'uraian_komponen' => $r->komp_uraian,
                            'uraian_akun_ap' => $r->sub_uraian,
                            'uraian_belanja' => $r->akun_uraian,
                            'sumber' => 'fa',
                        ]);
                    }
                }
            }

            // MAK dari FA yang hilang di FA baru: nonaktif, bukan dihapus. MAK manual tidak disentuh.
            $hilang = $existing->filter(fn($m) => $m->sumber === 'fa' && $m->is_active && !isset($seen[$m->mak]));
            $result['nonaktif'] = $hilang->count();
            if (!$dryRun && $hilang->isNotEmpty()) {
                MakOption::whereIn('id', $hilang->pluck('id'))->update(['is_active' => false]);
            }

            $result['pagu_cocok'] = (int) $rows->sum('pagu') === (int) $upload->total_pagu;
        });

        return $result;
    }

    /** "ALL_Laporan Fa Detail_021126_14.22.xlsx" => 2026-11-02 (format ddmmyy) */
    public static function tanggalDariNamaFile(string $nama): ?Carbon
    {
        if (!preg_match('/_(\d{2})(\d{2})(\d{2})_/', $nama, $m)) {
            return null;
        }
        try {
            return Carbon::createFromFormat('d/m/y', "{$m[1]}/{$m[2]}/{$m[3]}")->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
