<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pernyataan</title>
    <style>
        @page { margin: 2cm 2cm 2.5cm 3cm; }
        body { font-family: "Bookman Old Style", "DejaVu Serif", Georgia, serif; font-size: 12pt; line-height: 1.5; color: #000; }
        td { vertical-align: top; padding: 1px 0; }
    </style>
</head>
<body>
    @include('pdf.partials.kop-kemenko')

    <div style="text-align:center; font-weight:bold; font-size:13pt; text-decoration:underline;">SURAT PERNYATAAN</div>
    <div style="text-align:center; margin-bottom:14px;">NOMOR: {{ $dokumen->nomor_surat ?: '.../' . ($dokumen->payload('kode_klasifikasi') ?: 'HM.00.00') . '/' . $dokumen->tanggal_surat->format('Y') }}</div>

    <p>Saya yang bertanda tangan di bawah ini,</p>
    <table style="width:100%; margin-left:20px;">
        <tr><td style="width:28%;">Nama</td><td style="width:3%;">:</td><td>{{ $dokumen->payload('nama_menyatakan', '') }}</td></tr>
        <tr><td>NIP</td><td>:</td><td>{{ $dokumen->payload('nip_menyatakan', '') }}</td></tr>
        <tr><td>Pangkat/Golongan</td><td>:</td><td>{{ $dokumen->payload('pangkat_golongan', '') }}</td></tr>
        <tr><td>Jabatan</td><td>:</td><td>{{ $dokumen->payload('jabatan_menyatakan', '') }}</td></tr>
    </table>

    <p style="text-align:justify;">dengan ini menyatakan bahwa {!! nl2br(e($dokumen->payload('isi_pernyataan', '...'))) !!}</p>
    <p style="text-align:justify;">Demikian surat pernyataan ini saya buat dengan sesungguhnya untuk keperluan {{ $dokumen->payload('keperluan', '...') }}.</p>

    <div style="width:50%; margin-left:50%; margin-top:20px; page-break-inside:avoid;">
        Jakarta, {{ $dokumen->tanggal_surat->locale('id')->translatedFormat('j F Y') }}<br>
        {{ $dokumen->payload('jabatan_penandatangan', 'Pejabat Eselon II') }},
        <br><br><br><br>
        {{ $dokumen->payload('nama_penandatangan', 'Nama Pejabat') }}
    </div>
</body>
</html>
