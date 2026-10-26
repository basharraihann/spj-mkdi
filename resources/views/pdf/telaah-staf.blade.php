<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Telaah Staf</title>
    <style>
        @page { margin: 2cm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.5; }
        .title { text-align: center; font-weight: bold; font-size: 13pt; text-transform: uppercase; }
        .section-title { font-weight: bold; text-transform: uppercase; margin-top: 10px; }
        .section-content { text-align: justify; margin-left: 15px; margin-bottom: 10px; }
    </style>
</head>
<body>

    <div class="title">TELAAH STAF</div>
    <div class="title" style="font-size: 11pt; margin-bottom: 20px;">TENTANG<br>{{ strtoupper($dokumen->hal) }}</div>

    <div class="section-title">I. Persoalan</div>
    <div class="section-content">{!! nl2br(e($dokumen->isi_payload['persoalan'] ?? '-')) !!}</div>

    <div class="section-title">II. Praanggapan</div>
    <div class="section-content">{!! nl2br(e($dokumen->isi_payload['praanggapan'] ?? '-')) !!}</div>

    <div class="section-title">III. Fakta yang Mempengaruhi</div>
    <div class="section-content">{!! nl2br(e($dokumen->isi_payload['fakta'] ?? '-')) !!}</div>

    <div class="section-title">IV. Analisis</div>
    <div class="section-content">{!! nl2br(e($dokumen->isi_payload['analisis'] ?? '-')) !!}</div>

    <div class="section-title">V. Kesimpulan</div>
    <div class="section-content">{!! nl2br(e($dokumen->isi_payload['kesimpulan'] ?? '-')) !!}</div>

    <div class="section-title">VI. Saran</div>
    <div class="section-content">{!! nl2br(e($dokumen->isi_payload['saran'] ?? '-')) !!}</div>

    <div style="float: right; width: 45%; margin-top: 30px;">
        <p>{{ $dokumen->isi_payload['jabatan_pembuat'] ?? ($dokumen->isi_payload['jabatan_penandatangan'] ?? 'Nama Jabatan Pembuat Telaah Staf') }},</p>
        <br><br><br>
        <p style="font-weight: bold; text-decoration: underline;">{{ $dokumen->isi_payload['nama_pembuat'] ?? ($dokumen->isi_payload['nama_penandatangan'] ?? '................') }}</p>
    </div>

</body>
</html>