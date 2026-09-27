<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>SPD Lengkap - {{ $pegawai->nama_gelar ?? $pegawai->nama }} - {{ $agenda->tujuan }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    <div class="page-break">
        @include('pdf.spd', ['agenda' => $agenda, 'pegawai' => $pegawai, 'ppk' => $ppk, 'nomorSt' => $nomorSt])
    </div>

    <div class="page-break">
        @include('pdf.rincian-biaya', ['agenda' => $agenda, 'pegawai' => $pegawai, 'pivot' => $pivot, 'ppk' => $ppk, 'bendahara' => $bendahara])
    </div>

    <div>
        @include('pdf.pengeluaran-riil', ['agenda' => $agenda, 'pegawai' => $pegawai, 'pivot' => $pivot, 'ppk' => $ppk])
    </div>

</body>

</html>