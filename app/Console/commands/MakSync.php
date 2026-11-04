<?php

namespace App\Console\Commands;

use App\Services\MakSyncService;
use Illuminate\Console\Command;
use Throwable;

class MakSync extends Command
{
    protected $signature = 'mak:sync {--dry-run : Hitung saja, tanpa menyimpan}';
    protected $description = 'Segarkan mak_options dari upload FA Detail yang aktif';

    public function handle(MakSyncService $svc): int
    {
        try {
            $r = $svc->sync(null, (bool) $this->option('dry-run'));
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info(($this->option('dry-run') ? '[DRY RUN] ' : '') . "per_tanggal {$r['per_tanggal']}");
        $this->table(['baru', 'diperbarui', 'nonaktif', 'diaktifkan'], [[$r['baru'], $r['diperbarui'], $r['nonaktif'], $r['diaktifkan']]]);
        if (!$r['pagu_cocok']) {
            $this->warn('Total pagu hasil sync TIDAK sama dengan total_pagu upload. Cek datanya.');
        }
        return self::SUCCESS;
    }
}
