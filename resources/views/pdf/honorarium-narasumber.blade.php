<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Honorarium Narasumber - {{ $entry->uraian_kegiatan }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 14mm 14mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .judul {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            line-height: 1.7;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 14px;
        }

        table.data th,
        table.data td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: middle;
            font-size: 11px;
        }

        table.data th {
            text-align: center;
            font-weight: bold;
        }

        /* Tinggi baris halaman 1 (2 baris teks: nama dan NPWP) */
        table.data td.row-p1 {
            height: 34px;
        }

        /* Tinggi baris halaman 2 (ruang tanda tangan) */
        table.data td.row-tall {
            height: 50px;
        }

        .c {
            text-align: center;
        }

        .l {
            text-align: left;
        }

        /* Nominal gaya akuntansi: "Rp" rata kiri, angka rata kanan */
        table.data table.acc {
            width: 100%;
            border-collapse: collapse;
        }

        table.data table.acc td {
            border: none;
            padding: 0;
            font-size: 11px;
        }

        table.data table.acc td.num {
            text-align: right;
        }

        table.ttd {
            width: 100%;
            border-collapse: collapse;
            margin-top: 26px;
        }

        table.ttd td {
            border: none;
            vertical-align: top;
            font-size: 11px;
            line-height: 1.7;
            padding: 0;
        }

        .ttd-space {
            height: 50px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    @php
        // Nominal gaya akuntansi, format angka mengikuti contoh: 1,800,000
        $rp = fn($n) => '<table class="acc"><tr><td>Rp</td><td class="num">' . number_format((int) $n, 0, '.', ',') . '</td></tr></table>';

        // NIP 18 digit ditulis berkelompok: 19870212 201402 1 001
        $formatNip = function ($nip) {
            $d = preg_replace('/\D/', '', (string) $nip);

            return strlen($d) === 18
                ? substr($d, 0, 8) . ' ' . substr($d, 8, 6) . ' ' . substr($d, 14, 1) . ' ' . substr($d, 15, 3)
                : ($nip ?: '-');
        };

        $tanggal = $entry->tanggal;
        $tanggalJudul = 'Jakarta, ' . $tanggal->translatedFormat('j F Y');
        $bulanTahun = $tanggal->translatedFormat('F Y');

        $totalBruto = $peserta->sum(fn($p) => $p->bruto);
        $totalPajak = $peserta->sum(fn($p) => $p->pajak);
        $totalNetto = $totalBruto - $totalPajak;
    @endphp

    {{-- ================= HALAMAN 1: DAFTAR HONORARIUM NARASUMBER ================= --}}
    <div class="judul">DAFTAR HONORARIUM NARASUMBER</div>
    <div class="judul">{{ $entry->uraian_kegiatan }}</div>
    <div class="judul">{{ $tanggalJudul }}</div>

    <table class="data">
        <colgroup>
            <col style="width:3%">
            <col style="width:22%">
            <col style="width:12%">
            <col style="width:15%">
            <col style="width:7%">
            <col style="width:13.5%">
            <col style="width:14%">
            <col style="width:13.5%">
        </colgroup>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Sebagai</th>
                <th>Tarif</th>
                <th>Jam</th>
                <th>Bruto</th>
                <th>Pajak</th>
                <th>Netto</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($peserta as $i => $p)
                <tr>
                    <td class="c row-p1">{{ $i + 1 }}</td>
                    <td class="l">
                        {{ $p->nama }}
                        @if (!empty($p->npwp))
                            <br>NPWP: {{ $p->npwp }}
                        @endif
                    </td>
                    <td class="c">{{ $p->sebagai ?: 'Narasumber' }}</td>
                    <td>{!! $rp($p->honor) !!}</td>
                    <td class="c">{{ $p->oj }}</td>
                    <td>{!! $rp($p->bruto) !!}</td>
                    <td>{!! $rp($p->pajak) !!}</td>
                    <td>{!! $rp($p->netto) !!}</td>
                </tr>
            @endforeach

            @if ($peserta->count() > 1)
                <tr>
                    <td colspan="5" class="c"><strong>Jumlah</strong></td>
                    <td><strong>{!! $rp($totalBruto) !!}</strong></td>
                    <td><strong>{!! $rp($totalPajak) !!}</strong></td>
                    <td><strong>{!! $rp($totalNetto) !!}</strong></td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- Tanda tangan: hanya PPK, blok di sisi kanan --}}
    <table class="ttd">
        <tr>
            <td style="width:72%;"></td>
            <td style="width:28%;">
                Jakarta,&nbsp;&nbsp;&nbsp;&nbsp;{{ $bulanTahun }}<br>
                Pejabat Pembuat Komitmen<br>
                Biro Manajemen Kinerja, Data,<br>
                dan Informasi
                <div class="ttd-space"></div>
                {{ $ppk->nama ?? '(...........................)' }}<br>
                NIP&nbsp;&nbsp;{{ $formatNip($ppk->nip ?? null) }}
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    {{-- ================= HALAMAN 2: DAFTAR HADIR NARASUMBER ================= --}}
    <div class="judul">DAFTAR HADIR NARASUMBER</div>

    <table class="data">
        <colgroup>
            <col style="width:4%">
            <col style="width:26%">
            <col style="width:22%">
            <col style="width:6%">
            <col style="width:22%">
            <col style="width:20%">
        </colgroup>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Instansi</th>
                <th>Gol</th>
                <th>Jabatan</th>
                <th>Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($peserta as $i => $p)
                <tr>
                    <td class="c row-tall">{{ $i + 1 }}</td>
                    <td class="l">{{ $p->nama }}</td>
                    <td class="c">{{ $p->instansi ?: '-' }}</td>
                    <td class="c">{{ $p->golongan ?: '-' }}</td>
                    <td class="c">{{ $p->jabatan ?: '-' }}</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>