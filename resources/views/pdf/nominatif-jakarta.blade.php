<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Nominatif {{ $statusLabel }} - {{ $agenda->tujuan }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 8mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.data td,
        table.data th {
            border: 1px solid #000;
            padding: 2px 4px;
            font-size: 10px;
            line-height: 1.25;
            vertical-align: middle;
            text-align: center;
        }

        table.data th {
            background: #eee;
            font-weight: normal;
        }

        .idx td {
            font-size: 10px;
        }

        .text-left {
            text-align: left !important;
        }

        .text-right {
            text-align: right !important;
        }

        .ttd-cell {
            text-align: left !important;
            vertical-align: top !important;
            height: 28px;
        }

        .ttd-wrap {
            width: 100%;
            margin-top: 25px;
        }

        .ttd-wrap table {
            width: 100%;
            border: none;
        }

        .ttd-wrap td {
            border: none;
            text-align: left;
            vertical-align: top;
            padding: 0 10px;
            font-size: 11px;
            line-height: 1.35;
        }

        .ttd-header {
            min-height: 45px;
            display: block;
        }

        .ttd-space {
            height: 60px;
            display: block;
        }
    </style>
</head>

<body>
    @php
        $rupiah = fn($n) => 'Rp' . number_format($n ?? 0);

        $komponen = [
            'tiket',
            'lumpsum',
            'dukungan_transportasi',
            'transportasi_darat',
            'transportasi_lokal',
            'hotel',
            'penginapan_30',
            'peng_riil',
            'representatif',
            'belanja_bahan',
            'honor_narsum',
        ];
        $aktif = array_intersect($komponen, $agenda->komponen_biaya ?? []);

        $pegawaiList = $pegawaiList->sort(function ($a, $b) {
            $urutanA = $a->urutan ?? PHP_INT_MAX;
            $urutanB = $b->urutan ?? PHP_INT_MAX;
            return $urutanA <=> $urutanB ?: $b->golongan_rank <=> $a->golongan_rank;
        })->values();

        $grandTotal = 0;
    @endphp

    <table class="data">
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:20%">NAMA</th>
                <th style="width:6%">Gol.</th>
                <th style="width:11%">Transport</th>
                <th style="width:35%">Keterangan</th>
                <th colspan="2" style="width:24%">TANDA TANGAN</th>
            </tr>
            <tr class="idx">
                <td>(a)</td>
                <td>(b)</td>
                <td>(c)</td>
                <td>(d)</td>
                <td>(e)</td>
                <td colspan="2">(f)</td>
            </tr>
        </thead>
        <tbody>
            @forelse ($pegawaiList as $i => $p)
                @php
                    $jumlah = 0;
                    foreach ($aktif as $key) {
                        $jumlah += (float) ($p->pivot->{$key} ?? 0);
                    }
                    $grandTotal += $jumlah;
                    $no = $i + 1;
                    $ket = $p->pivot->keterangan ?? $agenda->uraian_kegiatan;
                @endphp
                <tr>
                    <td>{{ $no }}</td>
                    <td class="text-left">{{ $p->nama_gelar ?? $p->nama }}</td>
                    <td>{{ $p->golongan ?? '-' }}</td>
                    <td class="text-right">{{ $rupiah($jumlah) }}</td>
                    <td class="text-left">{{ $ket }}</td>
                    <td class="ttd-cell">{{ $no % 2 === 1 ? $no : '' }}</td>
                    <td class="ttd-cell">{{ $no % 2 === 0 ? $no : '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-left">Tidak ada peserta {{ $statusLabel }} pada agenda ini.</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="3"><strong>JUMLAH</strong></td>
                <td class="text-right"><strong>{{ number_format($grandTotal) }}</strong></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="ttd-wrap">
        <table>
            <tr>
                <td width="33%">
                    <div class="ttd-header">
                        a.n Kuasa Pengguna Anggaran<br>
                        Pejabat Pembuat Komitmen
                    </div>
                    <div class="ttd-space"></div>
                    {{ $ppk->nama_gelar ?? $ppk->nama ?? '(...........................)' }}<br>
                    NIP {{ $ppk->nip ?? '-' }}
                </td>
                <td width="33%">
                    <div class="ttd-header">
                        Lunas dibayar<br>
                        Bendahara Pengeluaran<br>
                        Kementerian Koordinator Bidang Pangan
                    </div>
                    <div class="ttd-space"></div>
                    {{ $bendahara->nama_gelar ?? $bendahara->nama ?? '(...........................)' }}<br>
                    NIP {{ $bendahara->nip ?? '-' }}
                </td>
                <td width="33%">
                    <div class="ttd-header">
                        Penanggung Jawab Kegiatan
                    </div>
                    <div class="ttd-space"></div>
                    {{ $penanggungJawab->nama_gelar ?? $penanggungJawab->nama ?? '(...........................)' }}<br>
                    NIP {{ $penanggungJawab->nip ?? '-' }}
                </td>
            </tr>
        </table>
    </div>
</body>

</html>