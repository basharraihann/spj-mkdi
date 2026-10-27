<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Telaah Staf</title>
    <style>
        /* Mengikuti Template Telaah Staf (Agustus 2026): Bookman Old Style 12pt, margin atas 2 / kanan 2 / bawah 2,5 / kiri 3 cm, butir A-F */
        @page { margin: 2cm 2cm 2.5cm 3cm; }
        body { font-family: "Bookman Old Style", "DejaVu Serif", Georgia, serif; font-size: 12pt; line-height: 1.5; }
        .title { text-align: center; font-weight: bold; }
        .section-title { margin-top: 8px; }
        .section-content { text-align: justify; margin-left: 22px; margin-bottom: 8px; }
    </style>
</head>
<body>

    <div class="title">TELAAH STAF</div>
    <div class="title" style="margin-bottom: 6px;">TENTANG</div>
    <div style="text-align: center; margin-bottom: 18px;">{{ $dokumen->hal }}</div>

    @foreach ([['persoalan', 'Persoalan'], ['praanggapan', 'Praanggapan'], ['fakta', 'Fakta yang Mempengaruhi'], ['analisis', 'Analisis'], ['kesimpulan', 'Kesimpulan'], ['saran', 'Saran']] as $i => [$k, $label])
        <div class="section-title">{{ chr(65 + $i) }}.&nbsp;&nbsp;{{ $label }}</div>
        <div class="section-content">{!! nl2br(e($dokumen->isi_payload[$k] ?? '-')) !!}</div>
    @endforeach

    <div style="width: 50%; margin-left: 50%; margin-top: 30px; page-break-inside: avoid;">
        <div>{{ $dokumen->isi_payload['jabatan_pembuat'] ?? ($dokumen->isi_payload['jabatan_penandatangan'] ?? 'Nama Jabatan Pembuat Telaah Staf') }},</div>
        <br><br><br>
        <div>{{ $dokumen->isi_payload['nama_pembuat'] ?? ($dokumen->isi_payload['nama_penandatangan'] ?? '................') }}</div>
    </div>

</body>
</html>
