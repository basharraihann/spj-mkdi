<?php

namespace Database\Seeders;

use App\Models\SbmRate;
use Illuminate\Database\Seeder;

class SbmRateSeeder extends Seeder
{
    /**
     * UH Biasa = kolom "Luar Kota" dari SBM (bukan "Dalam Kota >8 Jam" atau "Diklat",
     * itu di luar scope chip yang ada sekarang).
     *
     * Peng. Riil MASIH NULL SEMUA — data "Uang Representasi"-nya belum dikirim.
     * Update array di bawah begitu datanya ada (dari halaman yang sama di dokumen
     * SBM, biasanya section berikutnya setelah tabel Uang Harian).
     *
     * Catatan:
     * - Nama provinsi di sini HARUS SAMA PERSIS dengan yang dipakai di dropdown
     *   "Tujuan" (agendas/create & edit), karena pencocokan rate pakai string
     *   match langsung ke kolom `provinsi`, bukan ID.
     * - UH Biasa 60% TIDAK perlu diisi di sini — otomatis dihitung 60% dari
     *   uh_biasa (lihat SbmRate::getUhBiasa60Attribute()).
     * - UH Fullday (95.000) & UH FullBoard (130.000) juga TIDAK di sini — itu
     *   flat nasional, diisi lewat SbmFlatRateSeeder.
     */
    private const RATES = [
        'Aceh' => ['uh_biasa' => 360000, 'peng_riil' => null],
        'Sumatera Utara' => ['uh_biasa' => 370000, 'peng_riil' => null],
        'Riau' => ['uh_biasa' => 370000, 'peng_riil' => null],
        'Kepulauan Riau' => ['uh_biasa' => 370000, 'peng_riil' => null],
        'Jambi' => ['uh_biasa' => 370000, 'peng_riil' => null],
        'Sumatera Barat' => ['uh_biasa' => 380000, 'peng_riil' => null],
        'Sumatera Selatan' => ['uh_biasa' => 380000, 'peng_riil' => null],
        'Lampung' => ['uh_biasa' => 380000, 'peng_riil' => null],
        'Bengkulu' => ['uh_biasa' => 380000, 'peng_riil' => null],
        'Bangka Belitung' => ['uh_biasa' => 410000, 'peng_riil' => null],
        'Banten' => ['uh_biasa' => 370000, 'peng_riil' => null],
        'Jawa Barat' => ['uh_biasa' => 430000, 'peng_riil' => null],
        'DKI Jakarta' => ['uh_biasa' => 530000, 'peng_riil' => null],
        'Jawa Tengah' => ['uh_biasa' => 370000, 'peng_riil' => null],
        'DI Yogyakarta' => ['uh_biasa' => 420000, 'peng_riil' => null],
        'Jawa Timur' => ['uh_biasa' => 410000, 'peng_riil' => null],
        'Bali' => ['uh_biasa' => 480000, 'peng_riil' => null],
        'Nusa Tenggara Barat' => ['uh_biasa' => 440000, 'peng_riil' => null],
        'Nusa Tenggara Timur' => ['uh_biasa' => 430000, 'peng_riil' => null],
        'Kalimantan Barat' => ['uh_biasa' => 380000, 'peng_riil' => null],
        'Kalimantan Tengah' => ['uh_biasa' => 360000, 'peng_riil' => null],
        'Kalimantan Selatan' => ['uh_biasa' => 380000, 'peng_riil' => null],
        'Kalimantan Timur' => ['uh_biasa' => 430000, 'peng_riil' => null],
        'Kalimantan Utara' => ['uh_biasa' => 430000, 'peng_riil' => null],
        'Sulawesi Utara' => ['uh_biasa' => 370000, 'peng_riil' => null],
        'Gorontalo' => ['uh_biasa' => 370000, 'peng_riil' => null],
        'Sulawesi Barat' => ['uh_biasa' => 410000, 'peng_riil' => null],
        'Sulawesi Selatan' => ['uh_biasa' => 430000, 'peng_riil' => null],
        'Sulawesi Tengah' => ['uh_biasa' => 370000, 'peng_riil' => null],
        'Sulawesi Tenggara' => ['uh_biasa' => 380000, 'peng_riil' => null],
        'Maluku' => ['uh_biasa' => 380000, 'peng_riil' => null],
        'Maluku Utara' => ['uh_biasa' => 430000, 'peng_riil' => null],
        'Papua' => ['uh_biasa' => 580000, 'peng_riil' => null],
        'Papua Barat' => ['uh_biasa' => 480000, 'peng_riil' => null],
        'Papua Barat Daya' => ['uh_biasa' => 480000, 'peng_riil' => null],
        'Papua Tengah' => ['uh_biasa' => 580000, 'peng_riil' => null],
        'Papua Selatan' => ['uh_biasa' => 580000, 'peng_riil' => null],
        'Papua Pegunungan' => ['uh_biasa' => 580000, 'peng_riil' => null],
    ];

    public function run(): void
    {
        foreach (self::RATES as $provinsi => $rate) {
            SbmRate::updateOrCreate(
                ['provinsi' => $provinsi],
                ['uh_biasa' => $rate['uh_biasa'], 'peng_riil' => $rate['peng_riil'] ?? null]
            );
        }
    }
}