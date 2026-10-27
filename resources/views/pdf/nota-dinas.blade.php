<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Dinas</title>
    <style>
        /* Mengikuti Template Nota Dinas 2026: Arial 12pt, margin atas 2,25 / kanan 2 / bawah 3,5 / kiri 2,5 cm */
        @page { margin: 2.25cm 2cm 3.5cm 2.5cm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12pt; line-height: 1.3; color: #000; }
        .header { text-align: center; text-transform: uppercase; margin-bottom: 14px; }
        .title { text-align: center; }
        .meta-table { width: 100%; border-collapse: collapse; margin: 14px 0; }
        .meta-table td { padding: 2px 0; vertical-align: top; }
        .content { text-align: justify; margin-bottom: 12px; }
        .ttd-container { width: 50%; margin-left: 50%; text-align: center; margin-top: 24px; page-break-inside: avoid; }
        .tembusan { margin-top: 40px; page-break-inside: avoid; }
    </style>
</head>
<body>

    @php
        $tplNd = $dokumen->isi_payload['template'] ?? null;
        $es1 = $tplNd === 'es1_deputi2';
        $jabatanTtd = $dokumen->isi_payload['jabatan_penandatangan'] ?? $dokumen->dari;
    @endphp
    <div class="header">
        DEPUTI BIDANG KOORDINASI USAHA PANGAN DAN PERTANIAN
        @if ($tplNd === 'es2')
            <br>{{ strtoupper($dokumen->isi_payload['jabatan_penandatangan'] ?? 'SEKRETARIAT DEPUTI BIDANG KOORDINASI USAHA PANGAN DAN PERTANIAN') }}
        @endif
    </div>

    <div class="title">NOTA DINAS</div>
    <div class="title" style="margin-bottom: 6px;">
        NOMOR : {{ $dokumen->nomor_surat ?: '...../PR.02.06/2026' }}
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%;">Yth.</td>
            <td style="width: 3%;">:</td>
            <td>{{ $dokumen->tujuan }}</td>
        </tr>
        <tr>
            <td>Dari</td>
            <td>:</td>
            <td>{{ $dokumen->dari }}</td>
        </tr>
        @if (!empty($dokumen->isi_payload['lampiran']))
        <tr>
            <td>Lampiran</td>
            <td>:</td>
            <td>{{ $dokumen->isi_payload['lampiran'] }}</td>
        </tr>
        @endif
        <tr>
            <td>Hal</td>
            <td>:</td>
            <td>{{ $dokumen->hal }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ $dokumen->tanggal_surat->locale('id')->translatedFormat('j F Y') }}</td>
        </tr>
    </table>

    <div class="content">
        {!! nl2br(e($dokumen->isi_payload['isi_ringkasan'] ?? '')) !!}
    </div>

    @if ($tplNd)
    <div class="content" style="text-indent: 30px;">
        Demikian atas perhatian dan arahan {{ $es1 ? 'Bapak Sekretaris Kementerian Koordinator' : 'Ibu Deputi' }}, kami ucapkan terima kasih.
    </div>
    @endif

    <div class="ttd-container">
        <div>{{ $jabatanTtd }},</div>
        <br><br><br><br>
        <div>{{ $dokumen->isi_payload['nama_penandatangan'] ?? '................' }}</div>
    </div>

    @if (!empty($dokumen->tembusan))
    <div style="clear: both;"></div>
    <div class="tembusan">
        Tembusan:
        @if (count($dokumen->tembusan) === 1)
            <div>{{ $dokumen->tembusan[0] }}</div>
        @else
            <ol style="margin-top: 0; padding-left: 20px;">
                @foreach ($dokumen->tembusan as $t)
                    <li>{{ $t }}</li>
                @endforeach
            </ol>
        @endif
    </div>
    @endif

</body>
</html>
