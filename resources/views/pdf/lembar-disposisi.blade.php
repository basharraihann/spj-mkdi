<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Lembar Disposisi - {{ $surat->nomor_surat }}</title>
    <style>
        @page {
            margin: 40px 50px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .logo {
            width: 80px;
            height: auto;
            padding-right: 20px;
        }

        .org-name {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            line-height: 1.3;
        }

        .org-address {
            text-align: center;
            font-size: 11px;
            line-height: 1.4;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            letter-spacing: 1px;
            margin: 6px 0 12px;
        }

        table.grid {
            width: 100%;
            border-collapse: collapse;
        }

        table.grid td,
        table.grid th {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
        }

        table.grid th {
            background: #eeeeee;
            text-align: center;
            font-size: 10.5px;
        }

        .lbl {
            width: 110px;
            color: #333;
        }

        .muted {
            color: #555;
            font-size: 10px;
        }

        .cek {
            font-family: DejaVu Sans, sans-serif;
        }
    </style>
</head>

<body>
    @include('pdf._tanda-sistem')

    <table class="header-table">
        <tr>
            <td style="text-align:left;">
                <table style="margin:0 0 0 30px; border-collapse:collapse;">
                    <tr>
                        <td class="logo"><img src="{{ public_path('images/logo.png') }}" class="logo"></td>
                        <td>
                            <div class="org-name">KEMENTERIAN KOORDINATOR BIDANG PANGAN<br>REPUBLIK INDONESIA</div>
                            <div class="org-address">Graha Mandiri, Jl. Imam Bonjol No. 61, Jakarta Pusat 10310<br>Email:
                                kemenkopangan@kemenkopangan.go.id</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="title">LEMBAR DISPOSISI</div>

    <table class="grid">
        <tr>
            <td class="lbl">Surat dari</td>
            <td>{{ $surat->asal_surat }}</td>
            <td class="lbl">Diterima tanggal</td>
            <td>{{ $surat->tanggal_terima?->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="lbl">Nomor surat</td>
            <td>{{ $surat->nomor_surat }}</td>
            <td class="lbl">Nomor agenda</td>
            <td>{{ $surat->nomor_agenda ?: '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">Tanggal surat</td>
            <td>{{ $surat->tanggal_surat?->translatedFormat('d F Y') ?? '-' }}</td>
            <td class="lbl">Sifat</td>
            <td>
                @foreach (\App\Models\DisposisiSurat::SIFAT as $k => $v)
                    <span class="cek">{{ $surat->sifat === $k ? '☑' : '☐' }}</span> {{ $v }}&nbsp;&nbsp;
                @endforeach
            </td>
        </tr>
        <tr>
            <td class="lbl">Perihal</td>
            <td colspan="3">{{ $surat->perihal }}</td>
        </tr>
        @if ($surat->tanggal_kegiatan)
            <tr>
                <td class="lbl">Jadwal kegiatan</td>
                <td colspan="3">{{ $surat->tanggal_kegiatan->translatedFormat('l, d F Y') }}{{ $surat->waktu_label ? ', pukul ' . $surat->waktu_label : '' }}{{ $surat->tempat ? ' · ' . $surat->tempat : '' }}</td>
            </tr>
        @endif
        <tr>
            <td class="lbl">Lampiran</td>
            <td colspan="3">{{ $surat->lampiran ?: '-' }}</td>
        </tr>
    </table>

    <table class="grid" style="margin-top: 12px;">
        <thead>
            <tr>
                <th style="width: 22px;">No</th>
                <th style="width: 120px;">Dari</th>
                <th style="width: 120px;">Kepada</th>
                <th>Instruksi / Catatan</th>
                <th style="width: 80px;">Tanggal</th>
                <th style="width: 90px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($baris as $i => $b)
                @php $d = $b['d']; @endphp
                <tr>
                    <td style="text-align:center;">{{ $i + 1 }}</td>
                    <td>{{ $d->dari?->name ?? '-' }}<div class="muted">{{ $d->dari?->unit?->nama }}</div></td>
                    <td style="padding-left: {{ 6 + $b['level'] * 10 }}px;">
                        @if ($b['level'] > 0)
                            <span class="muted cek">&#8627;</span>
                        @endif
                        {{ $d->kepada?->name ?? '-' }}<div class="muted">{{ $d->kepada?->unit?->nama }}</div>
                    </td>
                    <td>
                        {{ implode(', ', $d->instruksi_label) }}
                        @if ($d->catatan)
                            <div style="margin-top:3px;">{{ $d->catatan }}</div>
                        @endif
                        @if ($d->batas_waktu)
                            <div class="muted">Batas waktu: {{ $d->batas_waktu->translatedFormat('d F Y') }}</div>
                        @endif
                        @if ($d->laporan)
                            <div style="margin-top:3px;"><b>Laporan:</b> {{ $d->laporan }}</div>
                        @endif
                    </td>
                    <td>{{ $d->created_at?->translatedFormat('d/m/Y') }}</td>
                    <td>{{ $d->status_label }}
                        @if ($d->selesai_at)
                            <div class="muted">{{ $d->selesai_at->translatedFormat('d/m/Y') }}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding: 30px;">Belum ada disposisi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="muted" style="margin-top: 10px;">
        Dicatat oleh {{ $surat->pembuat?->name ?? '-' }}{{ $surat->unit ? ' (' . $surat->unit->nama . ')' : '' }}
        · Status surat: {{ $surat->status === 'selesai' ? 'Selesai' : 'Proses' }}
    </p>
</body>

</html>
