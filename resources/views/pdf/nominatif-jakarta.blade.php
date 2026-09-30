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

        h3.judul {
            text-align: center;
            font-size: 12px;
            margin: 0 0 10px 0;
            text-decoration: underline;
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
            height: 80px;
            display: block;
        }
    </style>
</head>

<body>
    @php
        // Nilai 0 / kosong ditampilkan sebagai "-"
        $rupiah = fn($n) => (float) ($n ?? 0) == 0 ? '-' : 'Rp' . number_format($n);
        $angka = fn($n) => (float) ($n ?? 0) == 0 ? '-' : number_format($n);

        // Urutan + label kolom untuk tiap komponen biaya.
        $labelKomponen = [
            'tiket' => 'Tiket',
            'lumpsum' => 'Uang Harian',
            'dukungan_transportasi' => 'Dukungan Transportasi',
            'transportasi_darat' => 'Transportasi Darat',
            'transportasi_lokal' => 'Transportasi Lokal',
            'hotel' => 'Hotel',
            'penginapan_30' => 'Penginapan 30%',
            'peng_riil' => 'Pengeluaran Riil',
            'representatif' => 'Representatif',
            'belanja_bahan' => 'Belanja Bahan',
            'honor_narsum' => 'Honor Narasumber',
        ];

        // Hanya komponen yang dipilih di agenda ini, urutan mengikuti $labelKomponen.
        $aktif = array_values(array_intersect(array_keys($labelKomponen), $agenda->komponen_biaya ?? []));
        $nKomp = count($aktif);
        $tampilJumlah = $nKomp > 1; // kolom Jumlah per orang hanya kalau komponennya lebih dari satu

        // Hitung lebar kolom (dalam %) supaya total selalu 100%.
        $wNo = 3;
        $wNama = 15;
        $wGol = 4;
        $wTtd = 16; // 2 kolom x 8%
        $wKet = $nKomp <= 2 ? 22 : 14;
        $wJumlah = $tampilJumlah ? 9 : 0;
        $sisa = 100 - ($wNo + $wNama + $wGol + $wTtd + $wKet + $wJumlah);
        $wKomp = $nKomp > 0 ? round($sisa / $nKomp, 2) : 0;

        // Total kolom di tabel: No, Nama, Gol + komponen + (Jumlah) + Keterangan + 2 TTD
        $totalKolom = 3 + $nKomp + ($tampilJumlah ? 1 : 0) + 1 + 2;

        $pegawaiList = $pegawaiList->sort(function ($a, $b) {
            $urutanA = $a->urutan ?? PHP_INT_MAX;
            $urutanB = $b->urutan ?? PHP_INT_MAX;
            return $urutanA <=> $urutanB ?: $b->golongan_rank <=> $a->golongan_rank;
        })->values();

        $grandTotal = 0;
        $totalPerKomp = array_fill_keys($aktif, 0);
    @endphp

    <h3 class="judul">DAFTAR PENERIMAAN UANG TRANSPORT</h3>

    <table class="data">
        <thead>
            <tr>
                <th style="width:{{ $wNo }}%">No</th>
                <th style="width:{{ $wNama }}%">NAMA</th>
                <th style="width:{{ $wGol }}%">Gol.</th>
                @foreach ($aktif as $key)
                    <th style="width:{{ $wKomp }}%">{{ $labelKomponen[$key] }}</th>
                @endforeach
                @if ($tampilJumlah)
                    <th style="width:{{ $wJumlah }}%">Jumlah</th>
                @endif
                <th style="width:{{ $wKet }}%">Keterangan</th>
                <th colspan="2" style="width:{{ $wTtd }}%">TANDA TANGAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pegawaiList as $i => $p)
                @php
                    $jumlah = 0;
                    $nilai = [];
                    foreach ($aktif as $key) {
                        $v = (float) ($p->pivot->{$key} ?? 0);
                        $nilai[$key] = $v;
                        $totalPerKomp[$key] += $v;
                        $jumlah += $v;
                    }
                    $grandTotal += $jumlah;
                    $no = $i + 1;
                    $ket = $p->pivot->keterangan ?? $agenda->uraian_kegiatan;
                    $ket = trim((string) $ket) !== '' ? $ket : '-';
                @endphp
                <tr>
                    <td>{{ $no }}</td>
                    <td class="text-left">{{ $p->nama_gelar ?? $p->nama }}</td>
                    <td>{{ $p->golongan ?? '-' }}</td>
                    @foreach ($aktif as $key)
                        <td class="{{ $nilai[$key] == 0 ? '' : 'text-right' }}">{{ $rupiah($nilai[$key]) }}</td>
                    @endforeach
                    @if ($tampilJumlah)
                        <td class="{{ $jumlah == 0 ? '' : 'text-right' }}">{{ $rupiah($jumlah) }}</td>
                    @endif
                    <td class="text-left">{{ $ket }}</td>
                    <td class="ttd-cell">{{ $no % 2 === 1 ? $no . '.' : '' }}</td>
                    <td class="ttd-cell">{{ $no % 2 === 0 ? $no . '.' : '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $totalKolom }}" class="text-left">Tidak ada peserta {{ $statusLabel }} pada agenda ini.
                    </td>
                </tr>
            @endforelse
            <tr>
                <td colspan="3"><strong>JUMLAH</strong></td>
                @foreach ($aktif as $key)
                    <td class="{{ $totalPerKomp[$key] == 0 ? '' : 'text-right' }}">
                        <strong>{{ $angka($totalPerKomp[$key]) }}</strong>
                    </td>
                @endforeach
                @if ($tampilJumlah)
                    <td class="{{ $grandTotal == 0 ? '' : 'text-right' }}"><strong>{{ $angka($grandTotal) }}</strong></td>
                @endif
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
                        Jakarta, {{ now()->locale('id')->translatedFormat('d F Y') }}<br>
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