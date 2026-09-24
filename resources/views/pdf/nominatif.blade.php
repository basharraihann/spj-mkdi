<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 8mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        .kop {
            text-align: center;
            margin-bottom: 2px;
        }

        .kop h4 {
            margin: 0;
            font-size: 11px;
        }

        .rupiah {
            text-align: right;
            margin-bottom: 3px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
            font-size: 11px;
            line-height: 1.3;
            word-wrap: normal;
            overflow-wrap: normal;
            word-break: normal;
        }

        th {
            background: #eee;
            font-size: 11px;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .col-nama {
            text-align: left;
        }

        .row-data td {
            height: 56px;
        }

        .ket-col {
            vertical-align: middle;
            text-align: center;
            line-height: 1.3;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .ket-inner {
            text-align: center;
        }

        th.ket-col {
            vertical-align: middle;
        }

        .ttd-wrap {
            width: 100%;
            margin-top: 25px;
        }

        .ttd-wrap table {
            border: none;
            table-layout: auto;
        }

        .ttd-wrap td {
            border: none;
            text-align: left;
            vertical-align: top;
            padding: 0 10px;
            font-size: 11px;
            line-height: 1.35;
        }

        .ttd-wrap td:nth-child(2) {
            padding-left: 70px;
        }

        .ttd-wrap td:nth-child(3) {
            padding-left: 100px;
        }

        .ttd-header {
            min-height: 45px;
            display: block;
        }

        .ttd-space {
            height: 75px;
            display: block;
        }
    </style>
</head>

<body>
    <div class="kop">
        <h4>DAFTAR NOMINATIF</h4>
        <h4>PEMBAYARAN PERJALANAN DINAS LUAR KOTA</h4>
    </div>
    <div class="rupiah">(Dalam Rupiah)</div>

    @php
        $labelKomponen = [
            'tiket' => 'Tiket',
            'lumpsum' => 'Lumpsum',
            'dukungan_transportasi' => 'Dukungan Transport',
            'transportasi_darat' => 'Transport Darat',
            'transportasi_lokal' => 'Transport Lokal',
            'hotel' => 'Hotel',
            'penginapan_30' => 'Peng. 30%',
            'peng_riil' => 'Peng. Riil',
            'representatif' => 'Representatif',
            'belanja_bahan' => 'Belanja Bahan',
            'honor_narsum' => 'Honor Narsum',
        ];

        $urutanKolom = [
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

        $komponenAktif = $agenda->komponen_biaya ?? [];

        $kolomAktif = collect($urutanKolom)
            ->filter(fn($key) => in_array($key, $komponenAktif))
            ->values();

        $jumlahKolomBiaya = $kolomAktif->count();

        $lebarKolomBiayaDefault = 7;
        $lebarKolomBiayaOverride = [
            'dukungan_transportasi' => 9,
            'representatif' => 10.5,
        ];

        $lebarPerKolomBiaya = $kolomAktif->mapWithKeys(
            fn($key) => [$key => $lebarKolomBiayaOverride[$key] ?? $lebarKolomBiayaDefault],
        );
        $totalLebarKolomBiaya = $lebarPerKolomBiaya->sum();

        $lebarTetapLain = 2.5 + 6.5 + 8 + 3 + 3.5 + 3.5 + 5.5;

        $sisaLebar = 100 - $lebarTetapLain - $totalLebarKolomBiaya;

        $lebarTanggal = max(round($sisaLebar * 0.35, 2), 8);
        $lebarKeterangan = max(round($sisaLebar - $lebarTanggal, 2), 12);

        // Tujuan tampil: gabungan Kab/Kota + Provinsi.
        // $agenda->tujuan = Provinsi (kolom lama), $agenda->kota_tujuan = Kab/Kota (kolom baru, nullable).
        $tujuanTampil = trim(
            collect([$agenda->kota_tujuan ?? null, $agenda->tujuan ?? null])
                ->filter()
                ->implode(', '),
        );
    @endphp

    <table>
        <colgroup>
            <col style="width:2.5%">
            <col style="width:6.5%">
            <col style="width:8%">
            <col style="width:3%">
            <col style="width:3.5%">
            <col style="width:3.5%">
            <col style="width:{{ $lebarTanggal }}%">
            @foreach ($kolomAktif as $key)
                <col style="width:{{ $lebarPerKolomBiaya[$key] }}%">
            @endforeach
            <col style="width:5.5%">
            <col style="width:{{ $lebarKeterangan }}%">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2" style="width:2.5%">No</th>
                <th rowspan="2" style="width:6%">Nama</th>
                <th rowspan="2" style="width:8%">Jabatan</th>
                <th rowspan="2" style="width:3%">Gol.</th>
                <th rowspan="2" style="width:6.5%">Tujuan</th>
                <th rowspan="2" style="width:3.5%">Lama</th>
                <th rowspan="2" style="width:{{ $lebarTanggal }}%">Tanggal Perjalanan</th>
                @if ($kolomAktif->isNotEmpty())
                    <th colspan="{{ $kolomAktif->count() }}">Rincian Biaya</th>
                @endif
                <th rowspan="2" style="width: 6.5%">Jumlah</th>
                <th rowspan="2" class="ket-col" style="width:{{ $lebarKeterangan }}%">Keterangan</th>
            </tr>
            <tr>
                @foreach ($kolomAktif as $key)
                    <th>{{ $labelKomponen[$key] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $pegawaiList = $pegawaiList->sort(function ($a, $b) {
                    $urutanA = $a->urutan ?? PHP_INT_MAX;
                    $urutanB = $b->urutan ?? PHP_INT_MAX;

                    return $urutanA <=> $urutanB ?: $b->golongan_rank <=> $a->golongan_rank;
                })->values();

                $totals = $kolomAktif->mapWithKeys(fn($key) => [$key => 0])->all();
                $totals['jumlah'] = 0;

                // Keterangan: judul/uraian kegiatan aja, TANPA nambahin provinsi/kab-kota
                // tujuan di akhir (uraian_kegiatan biasanya udah nyebut lokasinya sendiri).
                $ketList = $pegawaiList->map(function ($p) use ($agenda) {
                    return $p->pivot->keterangan
                        ?? ('Perjalanan dinas dalam rangka ' . $agenda->uraian_kegiatan);
                })->values();

                $totalRows = $ketList->count();
                $ketRowspan = [];
                $ketRender = [];

                $i = 0;
                while ($i < $totalRows) {
                    $j = $i;
                    while ($j + 1 < $totalRows && $ketList[$j + 1] === $ketList[$i]) {
                        $j++;
                    }
                    $ketRender[$i] = true;
                    $ketRowspan[$i] = $j - $i + 1;
                    for ($k = $i + 1; $k <= $j; $k++) {
                        $ketRender[$k] = false;
                    }
                    $i = $j + 1;
                }

                $lamaHari = $agenda->tanggal_mulai->diffInDays($agenda->tanggal_selesai) + 1;
            @endphp
            @forelse ($pegawaiList as $i => $p)
                @php
                    $jumlah = 0;
                    foreach ($kolomAktif as $key) {
                        $nilai = $p->pivot->{$key} ?? 0;
                        $totals[$key] += $nilai;
                        $jumlah += $nilai;
                    }
                    $totals['jumlah'] += $jumlah;
                @endphp
                <tr class="row-data">
                    <td>{{ $i + 1 }}</td>
                    <td class="col-nama">{{ $p->nama_gelar ?? $p->nama }}</td>
                    <td>{{ $p->jabatan }}</td>
                    <td>{{ $p->golongan ?? '-' }}</td>
                    <td>{{ $tujuanTampil }}</td>
                    <td>{{ $lamaHari }} hari</td>
                    <td>{{ $agenda->tanggal_mulai->translatedFormat('d F Y') }} s.d
                        {{ $agenda->tanggal_selesai->translatedFormat('d F Y') }}
                    </td>
                    @foreach ($kolomAktif as $key)
                        <td class="text-right">{{ number_format($p->pivot->{$key} ?? 0) }}</td>
                    @endforeach
                    <td class="text-right">{{ number_format($jumlah) }}</td>
                    @if ($ketRender[$i] ?? true)
                        <td class="ket-col" align="center" rowspan="{{ $ketRowspan[$i] ?? 1 }}">
                            <div class="ket-inner">{{ $ketList[$i] }}</div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 9 + $kolomAktif->count() }}" class="text-left">Tidak ada peserta
                        {{ $statusLabel }} pada agenda ini.
                    </td>
                </tr>
            @endforelse
            <tr>
                <td colspan="7"><strong>Jumlah</strong></td>
                @foreach ($kolomAktif as $key)
                    <td class="text-right"><strong>{{ number_format($totals[$key]) }}</strong></td>
                @endforeach
                <td class="text-right"><strong>{{ number_format($totals['jumlah']) }}</strong></td>
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
                        Pejabat Pembuat Komitmen<br>
                    </div>
                    <div class="ttd-space"></div>
                    <strong>{{ $ppk->nama_gelar ?? $ppk->nama ?? '(...........................)' }}</strong><br>
                    NIP. {{ $ppk->nip ?? '-' }}
                </td>
                <td width="33%">
                    <div class="ttd-header">
                        Lunas dibayar<br>
                        Bendahara Pengeluaran<br>
                        Kementerian Koordinator Bidang Pangan
                    </div>
                    <div class="ttd-space"></div>
                    <strong>{{ $bendahara->nama_gelar ?? $bendahara->nama ?? '(...........................)' }}</strong><br>
                    NIP. {{ $bendahara->nip ?? '-' }}
                </td>
                <td width="33%">
                    <div class="ttd-header">
                        Jakarta, {{ now()->translatedFormat('d F Y') }}<br>
                        Penanggung Jawab Kegiatan
                    </div>
                    <div class="ttd-space"></div>
                    <strong>{{ $penanggungJawab->nama_gelar ?? $penanggungJawab->nama ?? '(...........................)' }}</strong><br>
                    NIP. {{ $penanggungJawab->nip ?? '-' }}
                </td>
            </tr>
        </table>
    </div>

</body>

</html>