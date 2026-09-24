<div id="rincian-biaya-page">
    <style>
        #rincian-biaya-page {
            font-family: Arial, sans-serif;
            font-size: 13px;
            line-height: 1.5;
        }

        #rincian-biaya-page .kop {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #000;
        }

        #rincian-biaya-page .kop .l1 {
            font-size: 13px;
            font-weight: bold;
        }

        #rincian-biaya-page .kop .l2 {
            font-size: 13px;
        }

        #rincian-biaya-page .kop .l3 {
            font-size: 13px;
            font-weight: bold;
            margin-top: 4px;
        }

        #rincian-biaya-page .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        #rincian-biaya-page .info-table td {
            padding: 1px 0;
            white-space: nowrap;
        }

        #rincian-biaya-page .info-table .lbl {
            width: 165px;
        }

        #rincian-biaya-page .info-table .colon {
            width: 15px;
        }

        #rincian-biaya-page table.main {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        #rincian-biaya-page table.main thead th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 4px 6px;
            font-weight: bold;
        }

        #rincian-biaya-page table.main tbody td {
            padding: 2px 6px;
            border: none;
        }

        #rincian-biaya-page .no-col {
            width: 4%;
        }

        #rincian-biaya-page .ket-col {
            width: 22%;
            text-align: left;
        }

        #rincian-biaya-page .rp-col {
            width: 6%;
        }

        #rincian-biaya-page .jml-col {
            width: 18%;
            text-align: right;
        }

        #rincian-biaya-page .sub-row td {
            color: #333;
        }

        /* === Sub-detail (Uang Harian Dinas Biasa / FullBoard / dst) ===
           .sd-label mengalir normal (rata dengan "Lumpsum" di baris atasnya).
           .sd-fixed dikunci di posisi absolute supaya "1 hari @ Rp..." TIDAK ikut
           bergeser walaupun panjang teks label berubah. */
        #rincian-biaya-page .sub-detail {
            position: relative;
            min-height: 14px;
        }

        #rincian-biaya-page .sub-detail .sd-label {
            white-space: nowrap;
        }

        #rincian-biaya-page .sub-detail .sd-fixed {
            position: absolute;
            left: 170px;
            /* sesuaikan nilai ini kalau posisi nominal masih geser di hasil PDF */
            top: 0;
            white-space: nowrap;
        }

        #rincian-biaya-page .sub-detail .sd-nominal {
            margin-left: 35px;
            /* jarak antara "hari @" dan nominalnya */
        }

        #rincian-biaya-page .text-center {
            text-align: center;
        }

        #rincian-biaya-page .text-right {
            text-align: right;
        }

        #rincian-biaya-page .row-jumlah td {
            border-top: 1px solid #000;
            font-weight: bold;
            padding-top: 4px;
        }

        #rincian-biaya-page .row-terbilang td {
            background: #d9d9d9;
            padding: 3px 6px;
            font-weight: bold;
            /* tambahan ini */
        }

        #rincian-biaya-page .spacer td {
            height: 8px;
            padding: 0;
        }

        #rincian-biaya-page .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        #rincian-biaya-page .ttd-table td {
            border: none;
            vertical-align: top;
            padding: 0 10px;
        }

        #rincian-biaya-page .ttd-table td:nth-child(2) {
            padding-left: 50px;
        }

        #rincian-biaya-page .sign-gap {
            height: 80px;
        }

        #rincian-biaya-page .perhitungan-title {
            text-align: center;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 10px;
        }

        #rincian-biaya-page .perhitungan-table {
            width: 70%;
            border-collapse: collapse;
            margin: 0;
        }

        #rincian-biaya-page .perhitungan-table td {
            border: none;
            padding: 2px 0;
        }

        #rincian-biaya-page .perhitungan-table .lbl {
            width: 50%;
        }

        #rincian-biaya-page .perhitungan-table .colon {
            width: 5%;
        }

        #rincian-biaya-page .perhitungan-table .rp {
            width: 10%;
        }

        #rincian-biaya-page .ppk-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        #rincian-biaya-page .ppk-table td {
            border: none;
            vertical-align: top;
            padding: 0 10px;
        }

        #rincian-biaya-page .ppk-table td:nth-child(2) {
            padding-left: 50px;
        }
    </style>

    <div class="kop">
        <div class="l1">KEMENTERIAN KOORDINATOR BIDANG PANGAN</div>
        <div class="l2">Graha Mandiri, Jl. Imam Bonjol No.61 Jakarta Pusat 10310</div>
        <div class="l3">RINCIAN BIAYA PERJALANAN DINAS</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="lbl">Lampiran SPD Nomor</td>
            <td class="colon">:</td>
            <td>{{ $agenda->nomor_st ?? '' }} /</td>
        </tr>
        <tr>
            <td class="lbl">tanggal</td>
            <td class="colon">:</td>
            <td>{{ $agenda->tanggal_mulai->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    @php
        // Komponen biaya "simple" (satu nilai per peserta) yang mungkin aktif di agenda
        // ini, urut tampilnya di PDF. 'lumpsum' ditangani terpisah di bawah karena
        // punya breakdown hari x rate per jenis UH, bukan satu angka polos.
        $labelKomponen = [
            'tiket' => 'Tiket',
            'dukungan_transportasi' => 'Dukungan Transportasi',
            'transportasi_darat' => 'Transportasi Darat',
            'transportasi_lokal' => 'Transportasi Lokal',
            'hotel' => 'Hotel',
            'penginapan_30' => 'Penginapan 30%',
            'peng_riil' => 'Pengeluaran Riil',
            'representatif' => 'Representatif',
            'belanja_bahan' => 'Belanja Bahan',
            'honor_narsum' => 'Honor Narsum',
        ];

        // Urutan render: Transport dulu (posisi asli), baru sisanya, biar dokumen
        // yang udah biasa dipakai gak berubah drastis urutannya.
        $urutanSimple = [
            'tiket',
            'dukungan_transportasi',
            'transportasi_darat',
            'transportasi_lokal',
            'hotel',
            'penginapan_30',
            'peng_riil',
            'representatif',
            'belanja_bahan',
            'honor_narsum'
        ];

        $komponenAktif = $agenda->komponen_biaya ?? [];
        $uhAktif = $agenda->jenis_uang_harian ?? [];

        $uhFieldMap = [
            'uh_biasa' => ['hari' => 'hari_dinas_biasa', 'rate' => 'rate_dinas_biasa', 'label' => 'Uang Harian Dinas Biasa'],
            'uh_biasa_60' => ['hari' => 'hari_biasa_60', 'rate' => 'rate_biasa_60', 'label' => 'Uang Harian Dinas Biasa 60%'],
            'uh_fullday' => ['hari' => 'hari_fullday', 'rate' => 'rate_fullday', 'label' => 'Uang Harian Fullday'],
            'uh_fullboard' => ['hari' => 'hari_fullboard', 'rate' => 'rate_fullboard', 'label' => 'Uang Harian FullBoard'],
        ];

        // Baris yang beneran dirender, dibangun dulu sebelum ditampilkan — supaya
        // penomoran "No." di kolom kiri urut rapi tanpa lompat/bolong.
        $baris = [];
        foreach ($urutanSimple as $key) {
            if (in_array($key, $komponenAktif)) {
                $baris[] = ['jenis' => 'simple', 'label' => $labelKomponen[$key], 'nilai' => $pivot->{$key} ?? 0];
            }
            // 'peng_riil' nempatin posisi di urutan, tapi Lumpsum disisipin tepat
            // setelah 'tiket' (posisi row 2 asli), bukan ikut $urutanSimple.
            if ($key === 'tiket' && in_array('lumpsum', $komponenAktif)) {
                $subRows = [];
                foreach ($uhAktif as $uhKey) {
                    if (!isset($uhFieldMap[$uhKey])) {
                        continue;
                    }
                    $f = $uhFieldMap[$uhKey];
                    $hari = $pivot->{$f['hari']} ?? 0;
                    $rate = $pivot->{$f['rate']} ?? 0;
                    if ($hari > 0 || $rate > 0) {
                        $subRows[] = ['label' => $f['label'], 'hari' => $hari, 'rate' => $rate];
                    }
                }
                $baris[] = ['jenis' => 'lumpsum', 'label' => 'Lumpsum', 'nilai' => $pivot->lumpsum ?? 0, 'sub' => $subRows];
            }
        }

        // Kosong (bukan "-") kalau nilainya 0, biar match sama format dokumen aslinya.
        $fmt = fn($v) => $v > 0 ? number_format($v) : '';

        // Total dihitung dari komponen yang beneran aktif di agenda ini — bukan
        // dijumlah dari 5 field tetap kayak sebelumnya.
        $jumlahTotal = collect($baris)->sum('nilai');

        // Diasumsikan sudah dibayar lunas di muka; sesuaikan kalau logikanya beda
        $dibayarSemula = $jumlahTotal;
        $sisa = $jumlahTotal - $dibayarSemula;

        // Terbilang dibungkus try/catch: kalau helper aslinya error, tampilkan pesan
        // yang jelas ("(gagal memuat terbilang)") daripada literal "#NAME?" yang membingungkan.
        try {
            $terbilangText = ucwords(\App\Helpers\Terbilang::make($jumlahTotal)) . ' rupiah';
        } catch (\Throwable $e) {
            $terbilangText = '(gagal memuat terbilang)';
        }
    @endphp

    <table class="main">
        <thead>
            <tr>
                <th class="no-col text-left">No.</th>
                <th class="text-center">PERINCIAN BIAYA</th>
                <th class="text-center" colspan="2">JUMLAH</th>
                <th class="ket-col text-left">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @if (empty($baris))
                <tr>
                    <td colspan="5" class="text-center" style="padding: 10px 0; color: #999;">
                        Belum ada komponen biaya yang dipilih untuk agenda ini.
                    </td>
                </tr>
            @endif

            @foreach ($baris as $i => $b)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $b['label'] }}</td>
                    <td class="rp-col">Rp</td>
                    <td class="jml-col">{{ $fmt($b['nilai']) }}</td>
                    <td></td>
                </tr>
                @if ($b['jenis'] === 'lumpsum')
                    @foreach ($b['sub'] as $sub)
                        <tr class="sub-row">
                            <td></td>
                            <td>
                                <div class="sub-detail">
                                    <span class="sd-label">{{ $sub['label'] }}</span>
                                    <span class="sd-fixed">{{ $sub['hari'] }} hari @<span
                                            class="sd-nominal">Rp{{ number_format($sub['rate']) }}</span></span>
                                </div>
                            </td>
                            <td class="rp-col">Rp</td>
                            <td class="jml-col"></td>
                            <td></td>
                        </tr>
                    @endforeach
                    <tr class="spacer">
                        <td colspan="5"></td>
                    </tr>
                @endif
            @endforeach

            <tr class="row-jumlah">
                <td></td>
                <td>Jumlah :</td>
                <td class="rp-col">Rp</td>
                <td class="jml-col">{{ number_format($jumlahTotal) }}</td>
                <td></td>
            </tr>
            <tr class="spacer">
                <td colspan="5"></td>
            </tr>
            <tr class="row-terbilang">
                <td colspan="5"><strong>Terbilang</strong> : {{ $terbilangText }}</td>
            </tr>
        </tbody>
    </table>

    <table class="ttd-table">
        <tr>
            <td width="50%">
                Telah dibayar sejumlah<br>
                Rp {{ number_format($jumlahTotal) }}
            </td>
            <td width="50%">
                Jakarta, {{ now()->translatedFormat('d F Y') }}<br>
                Telah menerima jumlah uang sebesar<br>
                Rp {{ number_format($jumlahTotal) }}
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Yang Menerima,</td>
        </tr>
        <tr>
            <td class="sign-gap" colspan="2"></td>
        </tr>
        <tr>
            <td></td>
            <td>
                <strong>{{ $pegawai->nama_gelar ?? $pegawai->nama }}</strong><br>
                NIP. {{ $pegawai->nip ?? '-' }}
            </td>
        </tr>
    </table>
    <div class="perhitungan-title">PERHITUNGAN SPD RAMPUNG</div>

    <table class="perhitungan-table">
        <tr>
            <td class="lbl">Ditetapkan sejumlah</td>
            <td class="colon">:</td>
            <td class="rp">Rp</td>
            <td class="text-right">{{ number_format($jumlahTotal) }}</td>
        </tr>
        <tr>
            <td class="lbl">Yang telah dibayar semula</td>
            <td class="colon">:</td>
            <td class="rp">Rp</td>
            <td class="text-right">{{ number_format($dibayarSemula) }}</td>
        </tr>
        <tr>
            <td class="lbl">Sisa (kurang) / lebih</td>
            <td class="colon">:</td>
            <td class="rp">Rp</td>
            <td class="text-right">{{ number_format($sisa) }}</td>
        </tr>
    </table>

    <table class="ppk-table">
        <tr>
            <td width="50%"></td>
            <td width="50%">
                Pejabat Pembuat Komitmen Kegiatan
                <br><br><br><br><br>
                <strong>{{ $ppk->nama_gelar ?? $ppk->nama ?? '(...........................)' }}</strong><br>
                NIP {{ $ppk->nip ?? '-' }}
            </td>
        </tr>
    </table>
</div>