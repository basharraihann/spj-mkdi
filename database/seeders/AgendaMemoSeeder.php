<?php

namespace Database\Seeders;

use App\Services\NomorMemoService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Data uji agenda + memo untuk setiap unit.
 *
 * - Semua data ditandai "[SEED]" di uraian_kegiatan, jadi seeder aman dijalankan
 *   ulang: baris [SEED] lama dihapus dulu, data asli tidak disentuh.
 * - Memakai DB::table() dengan unit_id eksplisit, karena seeder jalan tanpa login
 *   (hook BelongsToUnit tidak mengisi unit_id dan scope unit tidak aktif).
 * - Nomor memo diambil dari satu urutan global lintas unit, sama seperti
 *   NomorMemoService (tidak ada nomor kembar antar unit).
 *
 * Prasyarat: UnitSeeder dan PegawaiSeeder sudah terisi.
 *
 * Jalankan: php artisan db:seed --class=AgendaMemoSeeder
 */
class AgendaMemoSeeder extends Seeder
{
    private const TANDA = '[SEED]';

    /** Urutan status yang dipakai bergilir supaya semua filter Monitoring punya data. */
    private const STATUS_CYCLE = ['outstanding', 'sakti', 'sp2d', 'batal'];

    /** Kode unit => unit_kerja pegawai (hanya unit yang punya pegawai di PegawaiSeeder). */
    private const UNIT_KERJA = [
        'Biro MKDI' => 'Biro Manajemen Kinerja, Data dan Informasi',
        'Biro KBMN' => 'Biro Keuangan dan BMN',
        'Deputi 2' => 'Deputi Bidang Koordinasi Usaha Pangan dan Pertanian',
        'Sesdep D2' => 'Sekretariat Deputi Bidang Koordinasi Usaha Pangan dan Pertanian',
        'Asdep 1 D2' => 'Asisten Deputi Peningkatan Daya Saing Produk Tanaman Pangan',
        'Asdep 2 D2' => 'Asisten Deputi Peningkatan Daya Saing Produk Pertanian',
        'Asdep 3 D2' => 'Asisten Deputi Pengelolaan Sarana dan Prasarana Produksi Pertanian',
        'Asdep 4 D2' => 'Asisten Deputi Peningkatan Daya Saing Produk Perkebunan dan Hortikultura',
        'Asdep 5 D2' => 'Asisten Deputi Peningkatan Daya Saing Produk Peternakan',
    ];

    /** jenis_memo => uraian contoh (kunci sama dengan MemoEntry::JENIS). */
    private const JENIS = [
        'konsumsi' => 'Konsumsi rapat koordinasi',
        'honorarium' => 'Honorarium narasumber kegiatan',
        'atk' => 'Pengadaan alat tulis kantor kegiatan',
        'seminar_kit' => 'Pengadaan seminar kit peserta',
        'sewa_ruangan' => 'Sewa ruangan pertemuan',
        'fullboard_meeting' => 'Paket fullboard meeting',
        'fullday_meeting' => 'Paket fullday meeting',
    ];

    /**
     * MAK contoh (pengganti tabel mak_options).
     * Format kode: giat(3 segmen).komponen.akun_ap.belanja
     * GANTI dengan kode MAK asli kalau mau data uji yang realistis.
     */
    private const MAK = [
        [
            'mak' => '1234.AB.001.052.A.524111',
            'uraian_giat' => 'Koordinasi Kebijakan',
            'uraian_komponen' => 'Perjalanan Dinas',
            'uraian_akun_ap' => 'Belanja Barang Operasional',
            'uraian_belanja' => 'Belanja Perjalanan Dinas Biasa',
        ],
        [
            'mak' => '1234.AB.001.053.A.521211',
            'uraian_giat' => 'Koordinasi Kebijakan',
            'uraian_komponen' => 'Rapat Koordinasi',
            'uraian_akun_ap' => 'Belanja Barang Operasional',
            'uraian_belanja' => 'Belanja Bahan',
        ],
        [
            'mak' => '1234.AB.001.054.A.522151',
            'uraian_giat' => 'Koordinasi Kebijakan',
            'uraian_komponen' => 'Narasumber',
            'uraian_akun_ap' => 'Belanja Barang Operasional',
            'uraian_belanja' => 'Belanja Jasa Profesi',
        ],
    ];

    /** Biaya contoh per orang untuk agenda perjalanan dinas. */
    private const TIKET = 1800000;
    private const HOTEL = 1500000;
    private const HARI = 3;
    private const RATE_HARIAN = 530000;

    public function run(): void
    {
        $today = Carbon::today();

        $units = DB::table('units')->orderBy('id')->get();
        $pegawai = DB::table('pegawai')->get();
        $maks = collect(self::MAK)->map(fn($m) => (object) $m);

        if ($units->isEmpty() || $pegawai->isEmpty()) {
            $this->command->error('units atau pegawai masih kosong. Jalankan seeder-nya dulu.');

            return;
        }

        $this->bersihkan();

        $bendahara = $pegawai->firstWhere('role_penandatangan', 'Bendahara');
        $verifikasi = $pegawai->firstWhere('role_penandatangan', 'Petugas Verifikasi');
        $ppkMkdi = $pegawai->first(fn($p) => $p->role_penandatangan === 'PPK' && $p->unit_kerja === self::UNIT_KERJA['Biro MKDI']);
        $ppkLain = $pegawai->first(fn($p) => $p->role_penandatangan === 'PPK' && $p->unit_kerja === self::UNIT_KERJA['Sesdep D2']);
        $pjDefault = $pegawai->firstWhere('role_penandatangan', 'Penanggung Jawab Kegiatan');

        $makPerdin = $maks->first(fn($m) => str_contains((string) $m->uraian_belanja, 'Perjalanan Dinas Biasa')) ?? $maks->first();

        // Nomor memo: lanjut dari nomor terbesar yang sudah ada, lintas unit.
        $nomor = NomorMemoService::nomorTerbesar();
        $tahun = $today->year;
        $nomorAwal = $nomor + 1;

        $jumlahAgenda = 0;
        $jumlahMemo = 0;
        $namaJenis = array_keys(self::JENIS);

        foreach ($units as $i => $unit) {
            $unitKerja = self::UNIT_KERJA[$unit->kode] ?? null;
            $anggota = $unitKerja ? $pegawai->where('unit_kerja', $unitKerja)->values() : collect();

            $pj = $anggota->firstWhere('role_penandatangan', 'Penanggung Jawab Kegiatan') ?? $pjDefault;
            $pic = $anggota->first(fn($p) => $p->role_penandatangan === null) ?? $pj;
            $ppk = $unit->kode === 'Biro MKDI' ? ($ppkMkdi ?? $ppkLain) : ($ppkLain ?? $ppkMkdi);

            $penandatangan = [
                'pic_id' => $pic->id ?? null,
                'ppk_id' => $ppk->id ?? null,
                'bendahara_id' => $bendahara->id ?? null,
                'penanggung_jawab_id' => $pj->id ?? null,
                'petugas_verifikasi_id' => $verifikasi->id ?? null,
            ];

            // ---------- Memo mandiri: 2 per unit ----------
            for ($k = 0; $k < 2; $k++) {
                $idx = $i * 2 + $k;
                $jenis = $namaJenis[$idx % count($namaJenis)];
                $mak = $maks[$idx % $maks->count()];
                $status = self::STATUS_CYCLE[$idx % count(self::STATUS_CYCLE)];
                $tanggal = $today->copy()->subDays(25 - $i - ($k * 4));
                [$sakti, $sp2d] = $this->tanggalStatus($status, $tanggal, $today);

                $nomor++;

                DB::table('memo_entries')->insert([
                    'unit_id' => $unit->id,
                    'nomor_urut' => $nomor,
                    'nomor_memo' => "{$nomor}.KU.00.00/{$tahun}",
                    'jenis_memo' => $jenis,
                    'tanggal_memo' => $tanggal->toDateString(),
                    'uraian_kegiatan' => self::TANDA . ' ' . self::JENIS[$jenis] . ' ' . $unit->kode,
                    ...$penandatangan,
                    'mak' => $mak->mak,
                    'nominal' => (1 + (($i + $k) % 6)) * 1250000,
                    'status_anggaran' => $status,
                    'tgl_sakti' => $sakti,
                    'tgl_sp2d' => $sp2d,
                    'keterangan' => 'Data uji dari AgendaMemoSeeder',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $jumlahMemo++;
            }

            // ---------- Agenda perjalanan dinas: hanya unit yang punya pegawai ----------
            $pns = $anggota->where('status_kepegawaian', 'PNS')->take(2)->values();
            $nonPns = $anggota->where('status_kepegawaian', 'Non PNS')->take(1)->values();

            if ($pns->isEmpty() && $nonPns->isEmpty()) {
                continue;
            }

            $mulai = $today->copy()->subDays(40 - $i);
            $selesai = $mulai->copy()->addDays(self::HARI - 1);
            $perOrang = self::TIKET + self::HOTEL + (self::HARI * self::RATE_HARIAN);
            $parts = explode('.', $makPerdin->mak);

            $statusPns = self::STATUS_CYCLE[$i % count(self::STATUS_CYCLE)];
            $statusNon = self::STATUS_CYCLE[($i + 1) % count(self::STATUS_CYCLE)];
            [$saktiPns, $sp2dPns] = $this->tanggalStatus($statusPns, $selesai, $today);
            [$saktiNon, $sp2dNon] = $this->tanggalStatus($statusNon, $selesai, $today);

            $nomorPns = null;
            $nomorNon = null;

            if ($pns->isNotEmpty()) {
                $nomor++;
                $nomorPns = "{$nomor}.KU.00.00/{$tahun}";
            }

            if ($nonPns->isNotEmpty()) {
                $nomor++;
                $nomorNon = "{$nomor}.KU.00.00/{$tahun}";
            }

            $uraian = self::TANDA . ' Koordinasi kebijakan ' . $unit->kode;

            $agendaId = DB::table('agendas')->insertGetId([
                'unit_id' => $unit->id,
                'nomor_st' => 'ST-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT) . "/{$tahun}",
                'uraian_kegiatan' => $uraian,
                'tujuan' => 'Surabaya',
                'kota_tujuan' => 'Surabaya',
                'alat_angkut' => 'udara',
                'tanggal_mulai' => $mulai->toDateString(),
                'tanggal_selesai' => $selesai->toDateString(),
                'status' => 'approved',
                // Format isi JSON belum diketahui (belum ada agenda asli), jadi dikosongkan.
                'komponen_biaya' => json_encode([]),
                'jenis_uang_harian' => json_encode([]),
                'tujuan_memo' => 'Pejabat Pembuat Komitmen',
                'perihal_memo' => 'Permintaan Pembayaran Langsung (LS) Perjalanan Dinas',
                'nomor_memo_pns' => $nomorPns,
                'nomor_memo_non_pns' => $nomorNon,
                'uraian_memo_pns' => $nomorPns ? 'Sehubungan dengan Perjalanan ASN ' . $uraian : null,
                'uraian_memo_non_pns' => $nomorNon ? 'Sehubungan dengan Perjalanan NON ASN ' . $uraian : null,
                'kode_giat' => implode('.', array_slice($parts, 0, 3)),
                'uraian_giat' => $makPerdin->uraian_giat,
                'kode_komponen' => $parts[3] ?? null,
                'uraian_komponen' => $makPerdin->uraian_komponen,
                'kode_akun_ap' => $parts[4] ?? null,
                'uraian_akun_ap' => $makPerdin->uraian_akun_ap,
                'kode_belanja' => $parts[5] ?? null,
                'uraian_belanja' => $makPerdin->uraian_belanja,
                ...$penandatangan,
                'mak' => $makPerdin->mak,
                'anggaran_at' => now(),
                'dari_memo' => 'Pejabat Pembuat Komitmen ' . $unit->nama,
                'nominal_memo_pns' => $pns->isNotEmpty() ? $pns->count() * $perOrang : null,
                'nominal_memo_non_pns' => $nonPns->isNotEmpty() ? $nonPns->count() * $perOrang : null,
                'status_pns' => $statusPns,
                'status_non_pns' => $statusNon,
                'tgl_sakti_pns' => $saktiPns,
                'tgl_sakti_non_pns' => $saktiNon,
                'tgl_sp2d_pns' => $sp2dPns,
                'tgl_sp2d_non_pns' => $sp2dNon,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($pns->concat($nonPns) as $peserta) {
                DB::table('agenda_pegawai')->insert([
                    'agenda_id' => $agendaId,
                    'pegawai_id' => $peserta->id,
                    'tiket' => self::TIKET,
                    'hotel' => self::HOTEL,
                    'lama_hari' => self::HARI,
                    'tanggal_mulai' => $mulai->toDateString(),
                    'tanggal_selesai' => $selesai->toDateString(),
                    'hari_dinas_biasa' => self::HARI,
                    'rate_dinas_biasa' => self::RATE_HARIAN,
                    'lumpsum' => self::HARI * self::RATE_HARIAN,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $jumlahAgenda++;
        }

        $this->command->info("Selesai: {$jumlahAgenda} agenda, {$jumlahMemo} memo mandiri, nomor {$nomorAwal} sampai {$nomor}.");
    }

    /**
     * Hapus data seed lama (bertanda [SEED]) supaya seeder bisa dijalankan ulang.
     */
    private function bersihkan(): void
    {
        $agendaIds = DB::table('agendas')->where('uraian_kegiatan', 'like', self::TANDA . '%')->pluck('id');

        DB::table('agenda_pegawai')->whereIn('agenda_id', $agendaIds)->delete();
        DB::table('agendas')->whereIn('id', $agendaIds)->delete();
        DB::table('memo_entries')->where('uraian_kegiatan', 'like', self::TANDA . '%')->delete();
    }

    /**
     * Tanggal SAKTI dan SP2D sesuai status, tidak melewati hari ini.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function tanggalStatus(string $status, Carbon $dasar, Carbon $today): array
    {
        $sakti = in_array($status, ['sakti', 'sp2d'], true)
            ? $dasar->copy()->addDays(2)->min($today)
            : null;

        $sp2d = $status === 'sp2d'
            ? $dasar->copy()->addDays(5)->min($today)
            : null;

        return [$sakti?->toDateString(), $sp2d?->toDateString()];
    }
}