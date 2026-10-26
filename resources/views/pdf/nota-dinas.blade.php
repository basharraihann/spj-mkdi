<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Dinas</title>
    <style>
        @page { margin: 2cm 2.5cm 2cm 2.5cm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.4; color: #000; }
        .header { text-align: center; text-transform: uppercase; font-weight: bold; font-size: 11pt; margin-bottom: 15px; }
        .title { text-align: center; font-weight: bold; font-size: 13pt; text-decoration: underline; margin-bottom: 15px; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .meta-table td { padding: 3px 0; vertical-align: top; font-size: 11pt; }
        .border-line { border-bottom: 2px solid #000; margin-bottom: 15px; }
        .content { text-align: justify; margin-bottom: 20px; text-indent: 30px; }
        .ttd-container { float: right; width: 45%; text-align: left; margin-top: 30px; }
        .tembusan { margin-top: 50px; font-size: 10pt; }
    </style>
</head>
<body>

    @php
        $tplNd = $dokumen->isi_payload['template'] ?? null;
        $es1 = $tplNd === 'es1_deputi2';
    @endphp
    <div class="header">
        DEPUTI BIDANG KOORDINASI USAHA PANGAN DAN PERTANIAN
        @if ($tplNd === 'es2')
            <br>{{ strtoupper($dokumen->isi_payload['jabatan_penandatangan'] ?? 'SEKRETARIAT DEPUTI BIDANG KOORDINASI USAHA PANGAN DAN PERTANIAN') }}
        @endif
    </div>

    <div class="title">NOTA DINAS</div>
    <div style="text-align: center; font-size: 11pt; margin-top: -10px; margin-bottom: 15px;">
        NOMOR : {{ $dokumen->nomor_surat ?? '...../PR.02.06/2026' }}
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
            <td><strong>{{ $dokumen->hal }}</strong></td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ $dokumen->tanggal_surat->locale('id')->translatedFormat('j F Y') }}</td>
        </tr>
    </table>

    <div class="border-line"></div>

    <div class="content">
        {!! nl2br(e($dokumen->isi_payload['isi_ringkasan'] ?? '')) !!}
    </div>

    @if ($tplNd)
    <div class="content">
        Demikian atas perhatian dan arahan {{ $es1 ? 'Bapak Sekretaris Kementerian Koordinator' : 'Ibu Deputi' }}, kami ucapkan terima kasih.
    </div>
    @endif

    <div class="ttd-container">
        <p>{{ $dokumen->isi_payload['jabatan_penandatangan'] ?? $dokumen->dari }},</p>
        <br><br><br><br>
        <p style="font-weight: bold; text-decoration: underline;">{{ $dokumen->isi_payload['nama_penandatangan'] ?? '................' }}</p>
        <p>NIP. {{ $dokumen->isi_payload['nip_penandatangan'] ?? '................' }}</p>
    </div>

    @if (!empty($dokumen->tembusan))
    <div style="clear: both;"></div>
    <div class="tembusan">
        <strong>Tembusan:</strong>
        <ol style="margin-top: 3px; padding-left: 20px;">
            @foreach ($dokumen->tembusan as $t)
                <li>{{ $t }}</li>
            @endforeach
        </ol>
    </div>
    @endif

</body>
</html>