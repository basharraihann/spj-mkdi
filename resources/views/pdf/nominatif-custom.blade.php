@php
    $items = $entry->items;
    $f = fn($n) => $n ? number_format($n, 0, '.', ',') : '-';
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 40px 45px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #000;
        }

        h4 {
            text-align: center;
            margin: 0;
            font-size: 13px;
        }

        table.grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table.grid td,
        table.grid th {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 13px;
        }

        table.grid th {
            text-align: center;
            padding: 12px 6px;
        }

        .r {
            text-align: right;
        }

        .c {
            text-align: center;
        }

        .b {
            font-weight: bold;
        }

        .gray {
            background: #d9d9d9;
        }

        table.ttd {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }

        table.ttd td {
            width: 33%;
            vertical-align: top;
            padding: 0 6px;
            font-size: 13px;
        }

        .ruang {
            height: 70px;
        }
    </style>
</head>

<body>
    <h4>DAFTAR NOMINATIF</h4>
    <h4>{{ $entry->uraian_kegiatan }}</h4>

    <table class="grid">
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th>URAIAN</th>
                <th style="width:10%">HARGA</th>
                <th style="width:9%">JUMLAH SATUAN</th>
                <th style="width:10%">SUB TOTAL</th>
                <th style="width:9%">PPH 22</th>
                <th style="width:9%">PPH 23</th>
                <th style="width:11%">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $i => $it)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $it->uraian }}</td>
                    <td class="r">{{ $f($it->harga) }}</td>
                    <td class="c">{{ rtrim(rtrim(number_format($it->jumlah, 2, '.', ''), '0'), '.') }} {{ $it->satuan }}
                    </td>
                    <td class="r">{{ $f($it->subtotal) }}</td>
                    <td class="r">{{ $f($it->pph22) }}</td>
                    <td class="r">{{ $f($it->pph23) }}</td>
                    <td class="r b">{{ $f($it->total) }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="5" class="gray">PPN</td>
                <td colspan="3" class="r">{{ $f($entry->totalPpn()) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="gray">PPH 22 &amp; PPH 23</td>
                <td class="r">{{ $f($entry->totalPph22()) }}</td>
                <td class="r">{{ $f($entry->totalPph23()) }}</td>
                <td class="r">{{ $f($entry->totalPph22() + $entry->totalPph23()) }}</td>
            </tr>
            <tr>
                <td colspan="7" class="gray">TOTAL</td>
                <td class="r b">{{ $f($entry->totalAkhir()) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td>Setuju dibayar,<br>Pejabat Pembuat Komitmen</td>
            <td>Lunas dibayar,<br>Bendahara Pengeluaran</td>
            <td>Jakarta, {{ $entry->tanggal?->translatedFormat('j F Y') }}<br>Penanggungjawab Kegiatan</td>
        </tr>
        <tr>
            <td class="ruang" colspan="3"></td>
        </tr>
        <tr>
            <td>{{ $entry->ppk->nama_gelar ?? $entry->ppk->nama }}<br>NIP. {{ $entry->ppk->nip ?? '' }}</td>
            <td>{{ $entry->bendahara->nama_gelar ?? $entry->bendahara->nama }}<br>NIP.
                {{ $entry->bendahara->nip ?? '' }}
            </td>
            <td>{{ $entry->penanggungJawab->nama_gelar ?? $entry->penanggungJawab->nama }}<br>NIP.
                {{ $entry->penanggungJawab->nip ?? '' }}
            </td>
        </tr>
    </table>
</body>

</html>