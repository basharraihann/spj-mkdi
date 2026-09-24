<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 40px 60px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #000;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo {
            width: 110px;
            height: auto;
            padding-right: 30px;
        }

        .org-name {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            line-height: 1.3;
        }

        .org-address {
            text-align: center;
            font-size: 13px;
            line-height: 1.5;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin: 14px 0 2px;
        }

        .subtitle {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 14px;
        }

        table.info {
            width: 100%;
            margin-bottom: 10px;
        }

        .divider {
            border-bottom: 2px solid #000;
            margin: 4px 0 14px;
        }

        table.info td {
            vertical-align: top;
            padding: 1px 0;
        }

        table.info td.label {
            width: 90px;
        }

        table.info td.colon {
            width: 12px;
        }

        .isi {
            text-align: justify;
            text-indent: 2em;
            margin: 14px 0 6px;
            line-height: 1.6;
        }

        .isi-closing {
            text-indent: 2em;
            margin: 0 0 20px;
            line-height: 1.6;
        }

        /* Blok tanda tangan: rata kanan, tidak lagi di tengah halaman */
        .ttd-block {
            margin-top: 10px;
            width: 280px;
            margin-left: auto;
            margin-right: 0;
            text-align: left;
            line-height: 1.5;
        }

        .ttd-block .signer-name {
            padding-top: 80px;
            font-weight: bold;
            text-decoration: underline;
        }

        table.lampiran {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        table.lampiran th,
        table.lampiran td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 13px;
        }

        table.lampiran th {
            background: #f0f0f0;
            text-align: center;
        }

        table.terbilang {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        table.terbilang td {
            border: none;
            padding: 6px 8px;
            font-size: 12px;
        }

        table.terbilang td.label {
            width: 140px;
        }

        table.terbilang td.colon {
            width: 14px;
        }

        table.terbilang td.value {
            background: #e0e0e0;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .page-break {
            page-break-after: always;
        }

        .ttd-row {
            width: 100%;
            margin-top: 30px;
            margin-right: 20px;
        }

        .ttd-row td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding-top: 10px;
        }

        /* Jarak baris kedua (Mengetahui/menyetujui & Petugas Verifikasi) ke baris
           pertama (Bendahara & Penanggungjawab) — atur di sini, terpisah dari
           baris pertama. */
        .ttd-row tr:nth-child(2) td {
            padding-top: 50px;
        }

        .ttd-jabatan {
            min-height: 32px;
        }

        .ttd-nip {
            margin-top: 2px;
        }

        .ttd-name {
            margin-top: 80px;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    {{-- ================= HALAMAN 1: MEMORANDUM ================= --}}
    <table class="header-table">
        <tr>
            <td style="text-align:left;">
                <table style="margin:0 0 0 40px; border-collapse:collapse;">
                    <tr>
                        <td class="logo">
                            <img src="{{ public_path('images/logo.png') }}" class="logo">
                        </td>
                        <td>
                            <div class="org-name">KEMENTERIAN KOORDINATOR BIDANG PANGAN<br>REPUBLIK INDONESIA</div>
                            <div class="org-address">Graha Mandiri, Jl. Imam Bonjol No. 61, Jakarta Pusat
                                10310<br>Email:
                                kemenkopangan@kemenkopangan.go.id</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="title">MEMORANDUM</div>
    <div class="subtitle">Nomor : {{ $nomorMemo }}</div>

    <table class="info">
        <tr>
            <td class="label">Yth</td>
            <td class="colon">:</td>
            <td>Kuasa Pengguna Anggaran</td>
        </tr>
        <tr>
            <td class="label">Dari</td>
            <td class="colon">:</td>
            <td>{{ $agenda->dari_memo ?: 'Pejabat Pembuat Komitmen Biro Manajemen Kinerja, Data dan Informasi' }}</td>
        </tr>
        <tr>
            <td class="label">Hal</td>
            <td class="colon">:</td>
            <td>Permintaan Pembayaran Langsung (LS) Perjalanan Dinas</td>
        </tr>
        <tr>
            <td class="label">Lampiran</td>
            <td class="colon">:</td>
            <td>
                1. Surat Keputusan<br>
                2. Rincian Biaya Kegiatan<br>
                3. Daftar Nominatif
            </td>
        </tr>
        <tr>
            <td class="label">Tanggal</td>
            <td class="colon">:</td>
            <td>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    @php
        // Hindari kalimat "Sehubungan dengan Sehubungan dengan ..." jika data
        // $uraianMemo sudah memuat frasa "Sehubungan dengan" di depannya.
        $uraianBersih = trim(preg_replace('/^sehubungan\s+dengan\s+/i', '', trim($uraianMemo)));
    @endphp
    <div class="isi">
        Sehubungan dengan {{ $uraianBersih }}, dengan ini kami mengajukan permohonan dana sebesar
        Rp{{ number_format($totalBiaya, 0, ',', '.') }} yang dibebankan pada APBN satuan kerja
        Kementerian Koordinator Bidang Pangan tahun anggaran {{ \Carbon\Carbon::now()->year }}
        dengan MAK.{{ $agenda->mak }}
    </div>

    <div class="isi-closing">Atas perhatian dan kerjasamanya, kami ucapkan terimakasih.</div>

    {{-- Blok tanda tangan diposisikan di sisi kanan halaman, mengikuti jabatan lengkap pada field "Dari" --}}
    <div class="ttd-block">
        <div class="jabatan">Pejabat Pembuat Komitmen,</div>
        <div class="signer-name">{{ $agenda->ppk->nama ?? '.....................' }}</div>
    </div>

    <div class="page-break"></div>

    {{-- ================= HALAMAN 2: LAMPIRAN SURAT ================= --}}
    <div style="font-weight:bold; font-size:14px; margin-bottom:10px;">LAMPIRAN SURAT</div>

    <table class="info" style="margin-bottom: 6px;">
        <tr>
            <td class="label">Nomor</td>
            <td class="colon">:</td>
            <td>{{ $nomorMemo }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal</td>
            <td class="colon">:</td>
            <td>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <table class="lampiran">
        <thead>
            <tr>
                <th style="width:110px;">Kegiatan, Output, Komponen, Sub</th>
                <th>Uraian</th>
                <th style="width:90px;">Jumlah (Rp.)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $agenda->kode_giat }}</td>
                <td>{{ $agenda->uraian_giat }}</td>
                <td></td>
            </tr>
            <tr>
                <td>{{ $agenda->kode_komponen }}</td>
                <td>{{ $agenda->uraian_komponen }}</td>
                <td></td>
            </tr>
            <tr>
                <td>{{ $agenda->kode_akun_ap }}</td>
                <td>{{ $agenda->uraian_akun_ap }}</td>
                <td></td>
            </tr>
            <tr>
                <td>{{ $agenda->kode_belanja }}</td>
                <td>
                    {{ $agenda->uraian_belanja }}
                    {{-- Rincian biaya yang dipilih (mis. Pengeluaran Riil, Uang Harian, dsb) --}}
                    @if(!empty($rincianBiaya))
                        <br>
                        @foreach ($rincianBiaya as $itemBiaya)
                            - {{ $itemBiaya }}<br>
                        @endforeach
                    @endif
                </td>
                <td class="text-right"><strong>Rp.{{ number_format($totalBiaya, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    @php
        // Fallback: kalau controller belum kirim $terbilang, hitung otomatis di
        // sini dari $totalBiaya, supaya tidak pernah kosong/error.
        if (empty($terbilang)) {
            $terbilangFn = function ($angka) use (&$terbilangFn) {
                $angka = (int) abs($angka);
                $huruf = [
                    '',
                    'Satu',
                    'Dua',
                    'Tiga',
                    'Empat',
                    'Lima',
                    'Enam',
                    'Tujuh',
                    'Delapan',
                    'Sembilan',
                    'Sepuluh',
                    'Sebelas'
                ];

                if ($angka < 12) {
                    return $huruf[$angka];
                } elseif ($angka < 20) {
                    return $terbilangFn($angka - 10) . ' Belas';
                } elseif ($angka < 100) {
                    return trim($terbilangFn((int) ($angka / 10)) . ' Puluh ' . $terbilangFn($angka % 10));
                } elseif ($angka < 200) {
                    return trim('Seratus ' . $terbilangFn($angka - 100));
                } elseif ($angka < 1000) {
                    return trim($terbilangFn((int) ($angka / 100)) . ' Ratus ' . $terbilangFn($angka % 100));
                } elseif ($angka < 2000) {
                    return trim('Seribu ' . $terbilangFn($angka - 1000));
                } elseif ($angka < 1000000) {
                    return trim($terbilangFn((int) ($angka / 1000)) . ' Ribu ' . $terbilangFn($angka % 1000));
                } elseif ($angka < 1000000000) {
                    return trim($terbilangFn((int) ($angka / 1000000)) . ' Juta ' . $terbilangFn($angka % 1000000));
                } elseif ($angka < 1000000000000) {
                    return trim($terbilangFn((int) ($angka / 1000000000)) . ' Miliar ' . $terbilangFn($angka % 1000000000));
                }

                return trim($terbilangFn((int) ($angka / 1000000000000)) . ' Triliun ' . $terbilangFn($angka % 1000000000000));
            };

            $terbilang = trim(preg_replace('/\s+/', ' ', $terbilangFn($totalBiaya))) . ' Rupiah';
        }
    @endphp
    <table class="terbilang">
        <tr>
            <td class="label">Terbilang</td>
            <td class="colon">:</td>
            <td class="value"><strong>{{ $terbilang }}</strong></td>
        </tr>
    </table>

    <table class="ttd-row">
        <tr>
            <td>
                <div class="ttd-jabatan">Bendahara Pengeluaran,</div>
                <div class="ttd-name">{{ $agenda->bendahara->nama ?? '.....................' }}</div>
                @if(!empty($agenda->bendahara->nip))
                    <div class="ttd-nip">NIP {{ $agenda->bendahara->nip }}</div>
                @endif
            </td>
            <td>
                <div class="ttd-jabatan">Penanggung Jawab Kegiatan,</div>
                <div class="ttd-name">{{ $agenda->penanggungJawab->nama ?? '.....................' }}</div>
                @if(!empty($agenda->penanggungJawab->nip))
                    <div class="ttd-nip">NIP {{ $agenda->penanggungJawab->nip }}</div>
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <div class="ttd-jabatan">Mengetahui/menyetujui<br>Pejabat Pembuat Komitmen</div>
                <div class="ttd-name">{{ $agenda->ppk->nama ?? '.....................' }}</div>
                @if(!empty($agenda->ppk->nip))
                    <div class="ttd-nip">NIP {{ $agenda->ppk->nip }}</div>
                @endif
            </td>
            <td>
                <div class="ttd-jabatan">Petugas Verifikasi,</div>
                <div class="ttd-name">{{ $agenda->petugasVerifikasi->nama ?? '.....................' }}</div>
                @if(!empty($agenda->petugasVerifikasi->nip))
                    <div class="ttd-nip">NIP {{ $agenda->petugasVerifikasi->nip }}</div>
                @endif
            </td>
        </tr>
    </table>

</body>

</html>