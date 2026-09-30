<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Honorarium Narasumber - {{ $entry->uraian_kegiatan }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 14mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .judul {
            text-align: center;
            font-weight: bold;
            font-size: 11.5px;
            line-height: 1.6;
        }

        .judul-upper {
            text-align: center;
            font-weight: bold;
            font-size: 11.5px;
            line-height: 1.6;
            text-transform: uppercase;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 10px;
        }

        table.data th,
        table.data td {
            border: 1px solid #000;
            padding: 4px 5px;
            vertical-align: middle;
            font-size: 10.5px;
        }

        table.data td.col-no {
            padding: 4px 2px;
        }

        table.data th {
            text-align: center;
            font-weight: bold;
        }

        /* Tinggi baris halaman 1 (2 baris teks: nama dan NPWP) */
        table.data td.row-p1 {
            height: 32px;
        }

        /* Tinggi baris halaman per narasumber */
        table.data td.row-indiv {
            height: 40px;
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
            font-size: 10.5px;
            white-space: nowrap;
        }

        table.data table.acc td.num {
            text-align: right;
        }

        table.ttd {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.ttd td {
            border: none;
            vertical-align: top;
            font-size: 10.5px;
            line-height: 1.6;
            padding: 0;
        }

        table.ttd-indiv {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table.ttd-indiv td {
            border: none;
            vertical-align: top;
            font-size: 10.5px;
            line-height: 1.6;
            padding: 0;
        }

        .ttd-space {
            height: 58px;
        }

        .ttd-space-sm {
            height: 58px;
        }

        .page-break {
            page-break-after: always;
        }

        .section-gap {
            margin-top: 42px;
        }
    </style>
</head>

<body>
    @php
        // Nominal gaya akuntansi, format angka mengikuti contoh: 1,800,000
        $rp = fn($n) => '<table class="acc"><tr><td>Rp</td><td class="num">' . number_format((int) $n, 0, '.', ',') . '</td></tr></table>';

        // NIP 18 digit ditulis berkelompok: 19870212 201402 1 001
        $formatNipSpace = function ($nip) {
            $d = preg_replace('/\D/', '', (string) $nip);

            return strlen($d) === 18
                ? substr($d, 0, 8) . ' ' . substr($d, 8, 6) . ' ' . substr($d, 14, 1) . ' ' . substr($d, 15, 3)
                : ($nip ?: '-');
        };

        // NIP format tanpa spasi: 199504082020121001
        $formatNipDot = fn($nip) => $nip ? preg_replace('/\D/', '', (string) $nip) : '-';

        $tanggal = $entry->tanggal;
        $kota = $entry->kota ?: 'Jakarta';
        $kotaTanggalJudul = $kota . ', ' . ($tanggal ? $tanggal->translatedFormat('j F Y') : '');
        $kotaTanggalUpper = mb_strtoupper($kota . ', ' . ($tanggal ? $tanggal->translatedFormat('d F Y') : ''));
        $tanggalTtd = ($tanggal ?? $entry->created_at ?? now())->translatedFormat('d F Y');
        $bulanTahun = $tanggal ? $tanggal->translatedFormat('F Y') : now()->translatedFormat('F Y');

        $totalBruto = $peserta->sum(fn($p) => $p->bruto);
        $totalPajak = $peserta->sum(fn($p) => $p->pajak);
        $totalNetto = $totalBruto - $totalPajak;
    @endphp

    {{-- ================= HALAMAN 1: DAFTAR HONORARIUM NARASUMBER (REKAP KOLEKTIF) ================= --}}
    <div class="judul">DAFTAR HONORARIUM NARASUMBER</div>
    <div class="judul">{{ $entry->uraian_kegiatan }}</div>
    <div class="judul">{{ $kotaTanggalJudul }}</div>

    <table class="data">
        <thead>
            <tr>
                <th style="width:3%">No</th>
                <th style="width:35%">Nama</th>
                <th style="width:8%">Sebagai</th>
                <th style="width:11%">Tarif</th>
                <th style="width:4%">Jam</th>
                <th style="width:13%">Bruto</th>
                <th style="width:13%">Pajak</th>
                <th style="width:13%">Netto</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($peserta as $i => $p)
                <tr>
                    <td class="c col-no row-p1">{{ $i + 1 }}</td>
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

            <tr>
                <td colspan="5" class="c"><strong>Jumlah</strong></td>
                <td><strong>{!! $rp($totalBruto) !!}</strong></td>
                <td><strong>{!! $rp($totalPajak) !!}</strong></td>
                <td><strong>{!! $rp($totalNetto) !!}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Tanda tangan: hanya PPK, blok di sisi kanan --}}
    <table class="ttd">
        <tr>
            <td style="width:70%;"></td>
            <td style="width:30%;">
                Jakarta, {{ $tanggalTtd }}<br>
                Pejabat Pembuat Komitmen
                <div class="ttd-space"></div>
                <strong>{{ $ppk->nama ?? '(...........................)' }}</strong><br>
                NIP {{ $formatNipSpace($ppk->nip ?? null) }}
            </td>
        </tr>
    </table>

    {{-- ================= HALAMAN 2 DAN SETERUSNYA: LEMBAR INDIVIDUAL PER NARASUMBER ================= --}}
    @foreach ($peserta as $i => $p)
        <div class="page-break"></div>

        {{-- BAGIAN ATAS: DAFTAR HONORARIUM NARASUMBER --}}
        <div class="judul-upper">DAFTAR HONORARIUM NARASUMBER</div>
        <div class="judul-upper">{{ mb_strtoupper($entry->uraian_kegiatan) }}</div>
        <div class="judul-upper">{{ $kotaTanggalUpper }}</div>

        <table class="data">
            <thead>
                <tr>
                    <th style="width:3%">No</th>
                    <th style="width:26%">Nama</th>
                    <th style="width:8%">Sebagai</th>
                    <th style="width:4%">OJ</th>
                    <th style="width:11%">Honor</th>
                    <th style="width:11%">Bruto</th>
                    <th style="width:10%">Pajak</th>
                    <th style="width:11%">Netto</th>
                    <th style="width:16%">Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="c col-no row-indiv">{{ $i + 1 }}</td>
                    <td class="l">
                        {{ $p->nama }}
                        @if (!empty($p->npwp))
                            <br>NPWP: {{ $p->npwp }}
                        @endif
                    </td>
                    <td class="c">{{ $p->sebagai ?: 'Narasumber' }}</td>
                    <td class="c">{{ $p->oj }}</td>
                    <td>{!! $rp($p->honor) !!}</td>
                    <td>{!! $rp($p->bruto) !!}</td>
                    <td>{!! $rp($p->pajak) !!}</td>
                    <td>{!! $rp($p->netto) !!}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        {{-- Tanda tangan 2 Kolom: Bendahara (Kiri) & PPK (Kanan) --}}
        <table class="ttd-indiv">
            <tr>
                <td style="width:35%; vertical-align: bottom;">
                    &nbsp;<br>
                    Bendahara Pengeluaran
                </td>
                <td style="width:30%;"></td>
                <td style="width:35%; vertical-align: bottom;">
                    Jakarta, {{ $tanggalTtd }}<br>
                    Pejabat Pembuat Komitmen
                </td>
            </tr>
            <tr>
                <td style="width:35%; vertical-align: top;">
                    <div class="ttd-space-sm"></div>
                    <strong>{{ $bendahara->nama ?? 'Raka Panji Wibowo' }}</strong><br>
                    NIP. {{ $formatNipDot($bendahara->nip ?? null) }}
                </td>
                <td style="width:30%;"></td>
                <td style="width:35%; vertical-align: top;">
                    <div class="ttd-space-sm"></div>
                    <strong>{{ $ppk->nama ?? 'Arif Wibowo, SH, MH' }}</strong><br>
                    NIP. {{ $formatNipDot($ppk->nip ?? null) }}
                </td>
            </tr>
        </table>

        {{-- BAGIAN BAWAH: DAFTAR HADIR NARASUMBER --}}
        <div class="section-gap"></div>
        <div class="judul-upper">DAFTAR HADIR NARASUMBER</div>

        <table class="data">
            <thead>
                <tr>
                    <th style="width:3%">No</th>
                    <th style="width:27%">Nama</th>
                    <th style="width:18%">Instansi</th>
                    <th style="width:5%">Gol</th>
                    <th style="width:20%">Jabatan</th>
                    <th style="width:27%">Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="c col-no row-indiv">{{ $i + 1 }}</td>
                    <td class="l">{{ $p->nama }}</td>
                    <td class="c">{{ $p->instansi ?: '-' }}</td>
                    <td class="c">{{ $p->golongan ?: '-' }}</td>
                    <td class="c">{{ $p->jabatan ?: '-' }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @endforeach
</body>

</html>