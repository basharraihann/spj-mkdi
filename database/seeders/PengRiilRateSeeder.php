<?php

namespace Database\Seeders;

use App\Models\PengRiilRate;
use Illuminate\Database\Seeder;

class PengRiilRateSeeder extends Seeder
{
    /**
     * Rate SATU ARAH dari SBM (tabel "Satuan Biaya Transportasi dari dan/atau
     * ke Terminal Bus/Stasiun/Bandara/Pelabuhan"). Nominal PP (2x nilai ini)
     * dihitung otomatis di aplikasi — lihat PengRiilRate::getRatePpAttribute().
     *
     * TODO: 4 provinsi baru hasil pemekaran Papua belum ada datanya (kepotong
     * di dokumen sumber) — tambahin begitu datanya ada:
     * 'Papua Barat Daya', 'Papua Tengah', 'Papua Selatan', 'Papua Pegunungan'.
     */
    private const PROVINSI = [
        'Aceh' => 123000,
        'Sumatera Utara' => 278000,
        'Riau' => 99000,
        'Kepulauan Riau' => 159000,
        'Jambi' => 133000,
        'Sumatera Barat' => 171000,
        'Sumatera Selatan' => 162000,
        'Lampung' => 162000,
        'Bengkulu' => 106000,
        'Bangka Belitung' => 94000,
        'Banten' => 300000,
        'Jawa Barat' => 180000,
        'DKI Jakarta' => 250000,
        'Jawa Tengah' => 105000,
        'DI Yogyakarta' => 258000,
        'Jawa Timur' => 225000,
        'Bali' => 219000,
        'Nusa Tenggara Barat' => 224000,
        'Nusa Tenggara Timur' => 105000,
        'Kalimantan Barat' => 165000,
        'Kalimantan Tengah' => 130000,
        'Kalimantan Selatan' => 174000,
        'Kalimantan Timur' => 300000,
        'Kalimantan Utara' => 211000,
        'Sulawesi Utara' => 134000,
        'Gorontalo' => 256000,
        'Sulawesi Barat' => 283000,
        'Sulawesi Selatan' => 181000,
        'Sulawesi Tengah' => 149000,
        'Sulawesi Tenggara' => 154000,
        'Maluku' => 279000,
        'Maluku Utara' => 208000,
        'Papua' => 462000,
        'Papua Barat' => 228000,
    ];

    /**
     * Rate satu arah DKI Jakarta -> kab/kota sekitar (tabel "Satuan Biaya
     * Transportasi dari DKI Jakarta ke Kabupaten/Kota Sekitar").
     */
    private const JABODETABEK = [
        'Kota Bekasi' => 256000,
        'Kab. Bekasi' => 256000,
        'Kab. Bogor' => 270000,
        'Kota Bogor' => 270000,
        'Kota Depok' => 248000,
        'Kota Tangerang' => 258000,
        'Kota Tangerang Selatan' => 258000,
        'Kab. Tangerang' => 279000,
        'Kepulauan Seribu' => 386000,
    ];

    public function run(): void
    {
        foreach (self::PROVINSI as $tujuan => $rate) {
            PengRiilRate::updateOrCreate(
                ['kategori' => 'provinsi', 'tujuan' => $tujuan],
                ['rate_one_way' => $rate]
            );
        }

        foreach (self::JABODETABEK as $tujuan => $rate) {
            PengRiilRate::updateOrCreate(
                ['kategori' => 'jabodetabek', 'tujuan' => $tujuan],
                ['rate_one_way' => $rate]
            );
        }
    }
}
