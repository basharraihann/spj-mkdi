<?php

namespace Database\Seeders;

use App\Models\AnggaranPemetaan;
use Illuminate\Database\Seeder;

/**
 * Pemetaan RO -> Unit kerja, bersumber dari sheet "MAPPING RO ANGGARAN" (73 aturan).
 *   unit  = unit kerja persis seperti di sheet (mis. "Asdep 1 D1", "BIRO UHM")
 *   induk = kelompok di atasnya (mis. "D1"); kosong bila unit tidak punya induk
 *   sub_unit tidak dipakai (NULL).
 *
 * Dijalankan ulang = menyamakan unit & induk dengan sheet untuk kunci yang sama
 * (keg + ro + komp + sub). Aturan lain yang Anda tambah manual tidak dihapus.
 */
class AnggaranPemetaanSeeder extends Seeder
{
    public function run(): void
    {
        $baru = 0;
        foreach ($this->rules() as [$keg, $ro, $komp, $sub, $unit, $induk]) {
            $m = AnggaranPemetaan::updateOrCreate(
                ['keg_kode' => $keg, 'ro_kode' => $ro, 'komp_kode' => $komp, 'sub_kode' => $sub],
                ['unit' => $unit, 'induk' => $induk, 'sub_unit' => null]
            );
            if ($m->wasRecentlyCreated) {
                $baru++;
            }
        }
        $this->command?->info("Pemetaan: $baru aturan baru, sisanya disamakan dengan sheet.");
    }

    /** @return array<int, array{0:string,1:string,2:string,3:string,4:string,5:?string}> [keg, ro, komp, sub, unit, induk] */
    private function rules(): array
    {
        $r = [];

        // ---- CL: kegiatan menentukan Deputi (induk), RO menentukan Asdep/Sesdep (unit) ----
        $deputi = [
            'CL.7458' => ['D1', ['ABB.001', 'ABB.002', 'ABR.003', 'ABR.004', 'ABR.005', 'ABR.006']],
            'CL.7459' => ['D2', ['ABR.001', 'ABR.002', 'ABR.003', 'ABR.004', 'ABR.005', 'ABR.006']],
            'CL.7460' => ['D3', ['ABR.001', 'ABR.002', 'ABR.003', 'ABR.004', 'ABS.005', 'ABS.006']],
            'CL.7461' => ['D4', ['ABR.001', 'ABR.002', 'ABR.003', 'ABR.004', 'ABR.005', 'ABR.006']],
        ];
        foreach ($deputi as $keg => [$d, $ros]) {
            foreach ($ros as $i => $ro) {
                $unit = $i === 5 ? "Sesdep $d" : 'Asdep ' . ($i + 1) . " $d";
                $r[] = [$keg, $ro, '', '', $unit, $d];
            }
        }
        $r[] = ['', 'FAN.ZZ1', '', '', 'Sesdep D1', 'D1'];

        // ---- WA: per RO ----
        $perRo = [
            ['AAG.E01', 'BIRO HKS'],
            ['AEC.001', 'BIRO HKS'],
            ['BMA.001', 'BIRO MKDI'],
            ['EBA.958', 'BIRO UHM'],
            ['EBA.959', 'BIRO UHM'],
            ['EBA.960', 'BIRO SDMO'],
            ['EBA.Z01', 'BIRO HKS'],
            ['EBA.Z06', 'BIRO KBMN'],
            ['EBB.951', 'BIRO UHM'],
            ['EBC.954', 'BIRO SDMO'],
            ['EBC.996', 'BIRO SDMO'],
            ['EBD.Z23', 'BIRO MKDI'],
            ['EBD.Z26', 'BIRO KBMN'],
            ['EBD.Z28', 'INSPEKTORAT'],
            ['EBD.Z29', 'INSPEKTORAT'],
            ['EBD.Z30', 'INSPEKTORAT'],
            ['EBD.Z33', 'BIRO UHM'],
        ];
        foreach ($perRo as [$ro, $unit]) {
            $r[] = ['', $ro, '', '', $unit, null];
        }

        // EBA.994: per komponen
        $r[] = ['', 'EBA.994', '001', '', 'BIRO UHM', null];
        $r[] = ['', 'EBA.994', '002', '', 'BIRO KBMN', null];

        // EBA.962: per subkomponen. Unit Deputi (D1..D4) induknya D1..D4 itu sendiri.
        $s962 = [
            '0A' => 'BIRO UHM',
            '0B' => 'BIRO UHM',
            '0C' => 'BIRO UHM',
            '0D' => 'Sahli',
            '0E' => 'Sahli',
            '0F' => 'Sahli',
            '0G' => 'BIRO HKS',
            '0H' => 'INSPEKTORAT',
            '0I' => 'BIRO SDMO',
            '0J' => 'BIRO MKDI',
            '0K' => 'D1',
            '0L' => 'D3',
            '0M' => 'D4',
            '0N' => 'D2',
            '0O' => 'BIRO UHM',
            '0P' => 'BIRO UHM',
            '0Q' => 'BIRO UHM',
            'AP' => 'BIRO UHM',
        ];
        foreach ($s962 as $k => $unit) {
            $r[] = ['', 'EBA.962', '', "962.$k", $unit, preg_match('/^D[1-4]$/', $unit) ? $unit : null];
        }

        // EBD.Z24: per subkomponen (komponen 953)
        $s953 = [
            '0A' => 'BIRO MKDI',
            '0B' => 'BIRO MKDI',
            '0C' => 'BIRO MKDI',
            '0D' => 'BIRO MKDI',
            '0E' => 'BIRO MKDI',
            '0F' => 'INSPEKTORAT',
            '0G' => 'D1',
            '0H' => 'D2',
            '0I' => 'D3',
            '0J' => 'D4',
            'AP' => 'BIRO MKDI',
        ];
        foreach ($s953 as $k => $unit) {
            $r[] = ['', 'EBD.Z24', '', "953.$k", $unit, preg_match('/^D[1-4]$/', $unit) ? $unit : null];
        }

        return $r;
    }
}