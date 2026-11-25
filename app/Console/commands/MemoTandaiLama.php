<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Sekali jalan setelah migrasi status memo.
 *
 * Kolom status anggaran baru (default "outstanding") membuat SEMUA memo lama ikut dihitung
 * outstanding, padahal kebanyakan sudah masuk SAKTI / SP2D dan sudah ada di realisasi FA.
 * Akibatnya sisa efektif MAK terlihat jauh lebih kecil dari seharusnya.
 *
 * Perintah ini menandai memo lama (tanggal <= {sampai}) sebagai SP2D (atau SAKTI) dan
 * mencatatnya di log. Tanpa --jalankan hanya simulasi (tidak mengubah database).
 *
 * Contoh:
 *   php artisan memo:tandai-lama 2026-11-02            -> simulasi
 *   php artisan memo:tandai-lama 2026-11-02 --jalankan -> benar-benar diubah
 */
class MemoTandaiLama extends Command
{
    protected $signature = 'memo:tandai-lama
        {sampai : Memo bertanggal sampai tanggal ini (Y-m-d), biasanya tanggal laporan FA terakhir}
        {--status=sp2d : sakti atau sp2d}
        {--jalankan : Tanpa opsi ini hanya simulasi}';

    protected $description = 'Tandai memo lama yang masih outstanding sebagai SAKTI/SP2D (penyesuaian data awal)';

    public function handle(): int
    {
        $sampai = $this->argument('sampai');
        $status = $this->option('status');
        $jalan = (bool) $this->option('jalankan');

        if (!in_array($status, ['sakti', 'sp2d'], true)) {
            $this->error('--status harus sakti atau sp2d.');
            return self::FAILURE;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $sampai)) {
            $this->error('Format tanggal harus Y-m-d, mis. 2026-11-02.');
            return self::FAILURE;
        }

        $tahap = $status === 'sp2d' ? 'ppspm' : 'sakti';
        $alasan = "Penyesuaian data lama: memo s.d. {$sampai} dianggap sudah {$status}";

        $entries = DB::table('memo_entries')
            ->where('status_anggaran', 'outstanding')
            ->whereDate('tanggal_memo', '<=', $sampai)
            ->get(['id', 'nomor_memo', 'nominal']);

        $agendaRows = [];
        foreach (['pns', 'non_pns'] as $j) {
            foreach (DB::table('agendas')
                ->where("status_{$j}", 'outstanding')
                ->whereNotNull("nomor_memo_{$j}")->where("nomor_memo_{$j}", '!=', '')
                ->whereDate('created_at', '<=', $sampai)
                ->get(['id', "nomor_memo_{$j} as nomor"]) as $a) {
                $agendaRows[] = ['id' => $a->id, 'jenis' => $j, 'nomor' => $a->nomor];
            }
        }

        $this->info(($jalan ? '' : '[SIMULASI] ') . $entries->count() . ' memo mandiri dan '
            . count($agendaRows) . ' memo agenda akan ditandai ' . strtoupper($status) . '.');

        if (!$jalan) {
            $this->line('Tambahkan --jalankan untuk menyimpan.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($entries, $agendaRows, $status, $tahap, $alasan, $sampai) {
            $now = now();

            foreach ($entries as $e) {
                DB::table('memo_entries')->where('id', $e->id)->update([
                    'status_anggaran' => $status,
                    'tgl_sakti' => $sampai,
                    'tgl_sp2d' => $status === 'sp2d' ? $sampai : null,
                ]);
                $this->catat('entry', $e->id, null, $status, $alasan, $now);
                $this->tahap("entry-{$e->id}", $tahap, $now);
            }

            foreach ($agendaRows as $a) {
                $j = $a['jenis'];
                DB::table('agendas')->where('id', $a['id'])->update([
                    "status_{$j}" => $status,
                    "tgl_sakti_{$j}" => $sampai,
                    "tgl_sp2d_{$j}" => $status === 'sp2d' ? $sampai : null,
                ]);
                $this->catat('agenda', $a['id'], $j, $status, $alasan, $now);
                $this->tahap("agenda-{$a['id']}-{$j}", $tahap, $now);
            }
        });

        $this->info('Selesai.');
        return self::SUCCESS;
    }

    private function catat(string $tipe, int $id, ?string $jenis, string $ke, string $alasan, $now): void
    {
        DB::table('memo_status_logs')->insert([
            'memo_type' => $tipe,
            'memo_id' => $id,
            'jenis' => $jenis,
            'dari' => 'outstanding',
            'ke' => $ke,
            'alasan' => $alasan,
            'user_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function tahap(string $key, string $tahap, $now): void
    {
        DB::table('memo_progres')->updateOrInsert(
            ['memo_key' => $key],
            ['tahap' => $tahap, 'updated_at' => $now, 'created_at' => $now]
        );
    }
}
