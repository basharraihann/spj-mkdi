@php
    $peserta = $dokumen->payloadList('peserta');
    $poin = $dokumen->payloadList('pelaksanaan');
    $waktu = $dokumen->payload('waktu') ?: $dokumen->payload('waktu_tempat');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Notula {{ $dokumen->hal }}</title>
    <style>
        @page { margin: 2cm 2.5cm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.35; color: #000; }
        td { vertical-align: top; padding: 2px 0; }
    </style>
</head>
<body>
    @include('pdf.partials.kop-kemenko')

    <div style="text-align:center; font-weight:bold; font-size:13pt;">NOTULA</div>
    <div style="text-align:center;">Nomor : {{ $dokumen->nomor_surat ?: '' }}</div>
    <div style="text-align:center; font-weight:bold; margin:6px 0 12px;">{{ $dokumen->hal }}</div>

    <table style="width:100%;">
        <tr><td style="width:18%;">Dasar</td><td style="width:3%;">:</td><td>{{ $dokumen->payload('dasar', '-') }}</td></tr>
        <tr><td>Waktu</td><td>:</td><td>{{ $waktu ?: '-' }}</td></tr>
        <tr><td>Tempat</td><td>:</td><td>{{ $dokumen->payload('tempat', '-') }}</td></tr>
        <tr><td>Agenda</td><td>:</td><td>{{ $dokumen->payload('agenda', '-') }}</td></tr>
        <tr>
            <td>Peserta</td><td>:</td>
            <td>@forelse ($peserta as $i => $x){{ $i + 1 }}. {{ $x }}<br>@empty - @endforelse</td>
        </tr>
    </table>

    <p style="margin:12px 0 4px;">Pelaksanaan :</p>
    <ol style="margin-top:0; text-align:justify;">
        @forelse ($poin as $x)<li>{{ $x }}</li>@empty<li>-</li>@endforelse
    </ol>

    <table style="width:100%; margin-top:24px; page-break-inside:avoid;">
        <tr>
            <td style="width:50%;">Mengetahui,<br>{{ $dokumen->payload('mengetahui_jabatan', 'Jabatan Atasan Langsung') }},<br><br><br><br>{{ $dokumen->payload('mengetahui_nama', '................') }}</td>
            <td style="width:50%;">Notulis,<br><br><br><br><br>{{ $dokumen->payload('notulis', '................') }}</td>
        </tr>
    </table>
</body>
</html>
