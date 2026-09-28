<?php

namespace Database\Seeders;

use App\Models\SbmRate;
use Illuminate\Database\Seeder;

class SbmRateSeeder extends Seeder
{
    /**
     * UH Biasa = kolom "Luar Kota" dari SBM (bukan "Dalam Kota >8 Jam" atau "Diklat").
     *
     * Catatan:
     * - Nama provinsi di sini HARUS SAMA PERSIS dengan yang dipakai di dropdown
     *   "Tujuan" (agendas/create & edit), karena pencocokan rate pakai string
     *   match langsung ke kolom `provinsi`, bukan ID.
     * - UH Biasa 60% TIDAK perlu diisi di sini, otomatis dihitung 60% dari
     *   uh_biasa (lihat SbmRate::getUhBiasa60Attribute()).
     * - UH Fullday (95.000) & UH FullBoard (130.000) flat nasional, diisi lewat
     *   SbmFlatRateSeeder.
     * - Pengeluaran riil sudah dipindah ke tabel peng_riil_rates
     *   (lihat PengRiilRateSeeder).
     */
    private const RATES = [
        'Aceh' => 360000,
        'Sumatera Utara' => 370000,
        'Riau' => 370000,
        'Kepulauan Riau' => 370000,
        'Jambi' => 370000,
        'Sumatera Barat' => 380000,
        'Sumatera Selatan' => 380000,
        'Lampung' => 380000,
        'Bengkulu' => 380000,
        'Bangka Belitung' => 410000,
        'Banten' => 370000,
        'Jawa Barat' => 430000,
        'DKI Jakarta' => 530000,
        'Jawa Tengah' => 370000,
        'DI Yogyakarta' => 420000,
        'Jawa Timur' => 410000,
        'Bali' => 480000,
        'Nusa Tenggara Barat' => 440000,
        'Nusa Tenggara Timur' => 430000,
        'Kalimantan Barat' => 380000,
        'Kalimantan Tengah' => 360000,
        'Kalimantan Selatan' => 380000,
        'Kalimantan Timur' => 430000,
        'Kalimantan Utara' => 430000,
        'Sulawesi Utara' => 370000,
        'Gorontalo' => 370000,
        'Sulawesi Barat' => 410000,
        'Sulawesi Selatan' => 430000,
        'Sulawesi Tengah' => 370000,
        'Sulawesi Tenggara' => 380000,
        'Maluku' => 380000,
        'Maluku Utara' => 430000,
        'Papua' => 580000,
        'Papua Barat' => 480000,
        'Papua Barat Daya' => 480000,
        'Papua Tengah' => 580000,
        'Papua Selatan' => 580000,
        'Papua Pegunungan' => 580000,
    ];

    public function run(): void
    {
        foreach (self::RATES as $provinsi => $uhBiasa) {
            SbmRate::updateOrCreate(
                ['provinsi' => $provinsi],
                ['uh_biasa' => $uhBiasa]
            );
        }
    }
}