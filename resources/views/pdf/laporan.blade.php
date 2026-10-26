<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan {{ $dokumen->hal }}</title>
    <style>
        @page { margin: 2cm 2.5cm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.45; color: #000; }
        .judul { font-weight: bold; margin-top: 12px; }
        .isi { text-align: justify; margin-left: 18px; }
    </style>
</head>
<body>
    @include('pdf.partials.kop-kemenko')

    <div style="text-align:center; font-weight:bold; font-size:13pt;">LAPORAN</div>
    <div style="text-align:center;">Nomor: {{ $dokumen->payload('nomor_st') ?: $dokumen->nomor_surat }}</div>
    <div style="text-align:center; font-weight:bold; margin-top:6px;">TENTANG</div>
    <div style="text-align:center; font-weight:bold; margin-bottom:10px;">{{ strtoupper($dokumen->hal) }}</div>

    @foreach ([['pendahuluan', 'A. Pendahuluan'], ['kegiatan', 'B. Kegiatan yang Dilaksanakan'], ['hasil', 'C. Hasil yang Dicapai'], ['kesimpulan_saran', 'D. Kesimpulan dan Saran'], ['penutup', 'E. Penutup']] as [$k, $label])
        <div class="judul">{{ $label }}</div>
        <div class="isi">
            @if ($k === 'kegiatan' && $dokumen->payload('nomor_st'))
                Dilaksanakan berdasarkan Surat Tugas {{ $dokumen->payload('nomor_st') }}.<br>
            @endif
            {!! nl2br(e($dokumen->payload($k, '-'))) !!}
        </div>
    @endforeach

    <div style="width:50%; margin-left:50%; margin-top:22px; page-break-inside:avoid;">
        Dibuat di Jakarta<br>
        pada tanggal {{ $dokumen->tanggal_surat->locale('id')->translatedFormat('j F Y') }}<br>
        {{ $dokumen->payload('jabatan_penandatangan', 'Pejabat Pembuat Laporan') }},
        <br><br><br><br>
        {{ $dokumen->payload('nama_penandatangan', '................') }}
    </div>
</body>
</html>
