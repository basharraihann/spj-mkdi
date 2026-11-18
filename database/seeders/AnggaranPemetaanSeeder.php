<?php

namespace Database\Seeders;

use App\Models\AnggaranPemetaan;
use Illuminate\Database\Seeder;

/**
 * Pemetaan RO -> Unit kerja, bersumber dari sheet "MAPPING RO ANGGARAN" (versi yang sudah dirapikan
 * dan dicocokkan dengan Laporan FA Detail: 240 subkomponen, semuanya terpetakan).
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
        // Urutan RO = urutan unit: Asdep 1..5, lalu Sesdep (RO terakhir).
        // D3 berbeda: sheet D3A4 = ABS.005, D3A5 = ABS.006, dan D3A6 (Sesdep) = ABR.004.
        $deputi = [
            'CL.7458' => ['D1', ['ABB.001', 'ABB.002', 'ABR.003', 'ABR.004', 'ABR.005', 'ABR.006']],
            'CL.7459' => ['D2', ['ABR.001', 'ABR.002', 'ABR.003', 'ABR.004', 'ABR.005', 'ABR.006']],
            'CL.7460' => ['D3', ['ABR.001', 'ABR.002', 'ABR.003', 'ABS.005', 'ABS.006', 'ABR.004']],
            'CL.7461' => ['D4', ['ABR.001', 'ABR.002', 'ABR.003', 'ABR.004', 'ABR.005', 'ABR.006']],
        ];
        foreach ($deputi as $keg => [$d, $ros]) {
            foreach ($ros as $i => $ro) {
                $unit = $i === 5 ? "Sesdep $d" : 'Asdep ' . ($i + 1) . " $d";
                $r[] = [$keg, $ro, '', '', $unit, $d];
            }
        }

        // ---- CL: komponen / subkomponen yang membedakan unit (Biro & Sesdep) ----
        // Kunci tetap keg + RO; komponen atau subkomponen yang menentukan unit.
        // Format sub_kode mengikuti EBA.962: "<komponen>.<sub>" (mis. 073.AA).

        // D1 - ABR.003 (Asdep 3 D1): komponen 073, subkomponen AA..CC; DD milik BIRO UHM
        foreach (['AA', 'BB', 'CC'] as $s) {
            $r[] = ['CL.7458', 'ABR.003', '', "073.$s", 'Asdep 3 D1', 'D1'];
        }
        $r[] = ['CL.7458', 'ABR.003', '', '073.DD', 'BIRO UHM', null];

        // D1 - ABR.006 (Sesdep D1): per komponen
        $r[] = ['CL.7458', 'ABR.006', '051', '', 'Sesdep D1', 'D1'];
        foreach ([
            ['071', 'INSPEKTORAT'],
            ['072', 'BIRO HKS'],
            ['073', 'BIRO KBMN'],
            ['074', 'BIRO SDMO'],
            ['075', 'BIRO MKDI'],
            ['076', 'INSPEKTORAT'],
            ['077', 'BIRO MKDI'],
        ] as [$k, $unit]) {
            $r[] = ['CL.7458', 'ABR.006', $k, '', $unit, null];
        }

        // D2 - ABR.006: dipecah per komponen (sesuai sheet D2A1, D2A2, D2A4, D2A6)
        //   Sesdep D2 : komponen 051, 054, 071
        //   Asdep 2 D2: komponen 072 | Asdep 4 D2: komponen 073 | Asdep 1 D2: komponen 074
        foreach (['051', '054', '071'] as $k) {
            $r[] = ['CL.7459', 'ABR.006', $k, '', 'Sesdep D2', 'D2'];
        }
        $r[] = ['CL.7459', 'ABR.006', '072', '', 'Asdep 2 D2', 'D2'];
        $r[] = ['CL.7459', 'ABR.006', '073', '', 'Asdep 4 D2', 'D2'];
        $r[] = ['CL.7459', 'ABR.006', '074', '', 'Asdep 1 D2', 'D2'];
        foreach ([
            ['075', 'BIRO MKDI'],
            ['076', 'BIRO MKDI'],
            ['077', 'BIRO UHM'],
            ['078', 'BIRO UHM'],
        ] as [$k, $unit]) {
            $r[] = ['CL.7459', 'ABR.006', $k, '', $unit, null];
        }
        // Pengecualian subkomponen (aturan subkomponen mengalahkan aturan komponen/RO di atas):
        //  - 071.CC di ABR.006 dipindah dari Sesdep D2 ke INSPEKTORAT
        //  - 071.AA di ABR.005 (RO Asdep 5 D2) dikerjakan Sesdep D2
        $r[] = ['CL.7459', 'ABR.006', '', '071.CC', 'INSPEKTORAT', null];
        $r[] = ['CL.7459', 'ABR.005', '', '071.AA', 'Sesdep D2', 'D2'];

        // D4 - ABR.006 (Sesdep D4): komponen 076, subkomponen AC / FF / MR
        $r[] = ['CL.7461', 'ABR.006', '', '076.AC', 'Sesdep D4', 'D4'];
        $r[] = ['CL.7461', 'ABR.006', '', '076.FF', 'Sesdep D4', 'D4'];
        $r[] = ['CL.7461', 'ABR.006', '', '076.MR', 'INSPEKTORAT', null];

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

        // EBA.994: per komponen, dengan pengecualian per subkomponen.
        //   komponen 001 -> BIRO KBMN (001.0A..0D)
        //   komponen 002 -> BIRO UHM (0A,0C..0H,0J,0K,AP), kecuali 002.0B & 002.0I -> BIRO KBMN
        $r[] = ['', 'EBA.994', '001', '', 'BIRO KBMN', null];
        $r[] = ['', 'EBA.994', '002', '', 'BIRO UHM', null];
        $r[] = ['', 'EBA.994', '', '002.0B', 'BIRO KBMN', null];
        $r[] = ['', 'EBA.994', '', '002.0I', 'BIRO KBMN', null];

        // EBA.962: per subkomponen. Subkomponen Deputi masuk ke Sesdep Deputi tersebut
        // (tab D1A6..D4A6 di sheet); induknya D1..D4.
        // 0A..0F, 0O, 0P, 0Q, AP semuanya ada di sheet BUHM (tidak ada unit Sahli di sheet).
        $s962 = [
            '0A' => 'BIRO UHM',
            '0B' => 'BIRO UHM',
            '0C' => 'BIRO UHM',
            '0D' => 'BIRO UHM',
            '0E' => 'BIRO UHM',
            '0F' => 'BIRO UHM',
            '0G' => 'BIRO HKS',
            '0H' => 'INSPEKTORAT',
            '0I' => 'BIRO SDMO',
            '0J' => 'BIRO MKDI',
            '0K' => 'Sesdep D1',
            '0L' => 'Sesdep D3',
            '0M' => 'Sesdep D4',
            '0N' => 'Sesdep D2',
            '0O' => 'BIRO UHM',
            '0P' => 'BIRO UHM',
            '0Q' => 'BIRO UHM',
            'AP' => 'BIRO UHM',
        ];
        foreach ($s962 as $k => $unit) {
            $r[] = ['', 'EBA.962', '', "962.$k", $unit, preg_match('/^Sesdep (D[1-4])$/', $unit, $mm) ? $mm[1] : null];
        }

        // EBD.Z24: per subkomponen (komponen 953)
        $s953 = [
            '0A' => 'BIRO MKDI',
            '0B' => 'BIRO MKDI',
            '0C' => 'BIRO MKDI',
            '0D' => 'BIRO MKDI',
            '0E' => 'BIRO MKDI',
            '0F' => 'INSPEKTORAT',
            '0G' => 'Sesdep D1',
            '0H' => 'Sesdep D2',
            '0I' => 'Sesdep D3',
            '0J' => 'Sesdep D4',
            'AP' => 'BIRO MKDI',
        ];
        foreach ($s953 as $k => $unit) {
            $r[] = ['', 'EBD.Z24', '', "953.$k", $unit, preg_match('/^Sesdep (D[1-4])$/', $unit, $mm) ? $mm[1] : null];
        }

        return $r;
    }
}