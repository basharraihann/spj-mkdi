<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Kontrol Surat Tugas</title>
    <style>
        @page { margin: 1.8cm 2cm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.35; color: #000; }
        table.kotak { width: 100%; border-collapse: collapse; }
        table.kotak td, table.kotak th { border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
        table.kotak th { background: #eee; text-align: center; }
        .judul { text-align: center; font-weight: bold; font-size: 14pt; margin: 6px 0 12px; }
    </style>
</head>
<body>
    @include('pdf.partials.kop-kemenko')

    <div class="judul">LEMBAR KONTROL SURAT TUGAS</div>

    <table style="width:100%; margin-bottom:10px;">
        <tr><td style="width:22%;">Nomor</td><td style="width:3%;">:</td><td>{{ $st->nomor ?: '(belum bernomor)' }}</td></tr>
        <tr><td>Tanggal</td><td>:</td><td>{{ optional($st->tanggal)->locale('id')->translatedFormat('j F Y') }}</td></tr>
        <tr><td>Menimbang</td><td>:</td><td>{{ \Illuminate\Support\Str::limit((string) $st->menimbang, 200) }}</td></tr>
        <tr><td>Status ST</td><td>:</td><td>{{ $st->status_label }}</td></tr>
    </table>

    <table class="kotak">
        <tr>
            <th style="width:5%;">No</th>
            <th style="width:25%;">Tahapan</th>
            <th style="width:25%;">Nama / Jabatan</th>
            <th style="width:13%;">Status</th>
            <th style="width:14%;">Tanggal</th>
            <th>Catatan / Paraf</th>
        </tr>
        @foreach ($st->kontrol as $k)
            <tr>
                <td style="text-align:center;">{{ $k->urutan }}</td>
                <td>{{ $k->tahap_label }}</td>
                <td>{{ $k->petugas->name ?? '-' }}@if ($k->petugas?->pegawai?->jabatan)<br><span style="font-size:10pt;">{{ $k->petugas->pegawai->jabatan }}</span>@endif</td>
                <td style="text-align:center;">{{ $k->status_label }}</td>
                <td>{{ $k->acted_at ? $k->acted_at->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $k->catatan }}<br><br></td>
            </tr>
        @endforeach
    </table>
</body>
</html>
