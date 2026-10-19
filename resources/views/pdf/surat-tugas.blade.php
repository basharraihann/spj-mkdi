@php
    $logoPath = public_path(config('surat_tugas.logo'));
    $logo = file_exists($logoPath)
        ? 'data:image/'.pathinfo($logoPath, PATHINFO_EXTENSION).';base64,'.base64_encode(file_get_contents($logoPath))
        : null;
    $adaTanggal = $st->peserta->contains(fn ($p) => (bool) $p->tanggal_mulai);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Surat Tugas {{ $st->nomor_tampil }}</title>
<style>
    @page { margin: 1.2cm 1.5cm 1.5cm 1.5cm; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 12pt; color: #000; line-height: 1.2; }
    table { border-collapse: collapse; }
    .kop td { vertical-align: middle; }
    .kop-text { text-align: center; padding-left: 8px; }
    .kop-text .b { font-weight: bold; font-size: 14pt; }
    .kop-text .s { font-size: 12pt; }
    .garis { border-top: 3px solid #000; margin: 6px 0 22px; }
    .judul { text-align: center; font-weight: bold; font-size: 12pt; margin: 0; }
    .nomor { text-align: center; margin: 2px 0 18px; }
    .row td { vertical-align: top; padding-bottom: 14px; text-align: justify; }
    .isi { padding: 0 1.2cm 0 1cm; }
    .lbl { width: 21%; }
    .sep { width: 6%; text-align: left; }
    ol { margin: 0; padding-left: 20px; }
    ol li { padding-left: 4px; margin-bottom: 2px; text-align: justify; }
    .memberi { text-align: center; font-weight: bold; margin: 4px 0 14px; }
    .ttd { margin-left: 57%; margin-top: 8px; }
    .ttd .space { height: 85px; }
    .lampiran { page-break-before: always; }
    .lamp-head td { padding: 0; }
    .lamp-wrap { margin-left: 57%; margin-top: 4px; }
    .daftar { width: 100%; margin-top: 8px; }
    .daftar th, .daftar td { border: 1px solid #000; padding: 3px 6px; vertical-align: top; }
    .daftar th { text-align: center; }
</style>
</head>
<body>

<table class="kop" width="100%">
    <tr>
        <td width="95">@if ($logo)<img src="{{ $logo }}" width="90">@endif</td>
        <td class="kop-text">
            <div class="b">{{ config('surat_tugas.instansi') }}</div>
            <div class="b">{{ config('surat_tugas.sub_instansi') }}</div>
            <div class="s">{{ config('surat_tugas.alamat') }}</div>
            <div class="s">Email: {{ config('surat_tugas.email') }}</div>
        </td>
    </tr>
</table>
<div class="garis"></div>
<div class="isi">

<p class="judul">SURAT TUGAS</p>
<p class="nomor">NOMOR: {{ $st->nomor_tampil }}</p>

<table width="100%">
    @if ($st->menimbang)
    <tr class="row">
        <td class="lbl">Menimbang</td><td class="sep">:</td>
        <td>{{ $st->menimbang }}</td>
    </tr>
    @endif
    @if (!empty($st->dasar))
    <tr class="row">
        <td class="lbl">Dasar</td><td class="sep">:</td>
        <td><ol>@foreach ($st->dasar as $d)<li>{{ $d }}</li>@endforeach</ol></td>
    </tr>
    @endif
</table>

<p class="memberi">MEMBERI TUGAS</p>

<table width="100%">
    <tr class="row">
        <td class="lbl">Kepada</td><td class="sep">:</td>
        <td>{{ $st->kepada }}</td>
    </tr>
    <tr class="row">
        <td class="lbl">Untuk</td><td class="sep">:</td>
        <td><ol>@foreach ($st->untuk as $u)<li>{{ $u }}</li>@endforeach</ol></td>
    </tr>
</table>

<div class="ttd">
    <div>{{ $st->tempat }}, {{ $st->tanggal_indo }}</div>
    <div>{{ $st->jabatan_penandatangan }}</div>
    <div class="space"></div>
    <div>{{ $st->nama_penandatangan }}</div>
    @if ($st->nip_penandatangan)<div>NIP. {{ $st->nip_penandatangan }}</div>@endif
</div>

{{-- Lampiran --}}
<div class="lampiran">
    <div class="lamp-wrap">
    <div>Lampiran Surat Tugas</div>
    <table class="lamp-head">
        <tr><td width="70">Nomor</td><td width="12">:</td><td>{{ $st->nomor_tampil }}</td></tr>
        <tr><td>Tanggal</td><td>:</td><td>{{ $st->tanggal_indo }}</td></tr>
    </table>
    </div>

    <p class="judul" style="margin-top:22px;">DAFTAR PESERTA YANG DITUGASKAN</p>

    <table class="daftar">
        <thead>
            <tr>
                <th width="35">No</th>
                <th>Nama/NIP</th>
                <th width="{{ $adaTanggal ? '30%' : '38%' }}">Jabatan</th>
                @if ($adaTanggal)<th width="22%">Tanggal Tugas</th>@endif
            </tr>
        </thead>
        <tbody>
            @foreach ($st->peserta as $i => $p)
            <tr>
                <td style="text-align:center">{{ $i + 1 }}</td>
                <td>{{ $p->nama }}@if ($p->nip)<br>NIP. {{ $p->nip }}@endif</td>
                <td>{{ $p->jabatan }}</td>
                @if ($adaTanggal)<td>{{ $p->tanggal_tugas_indo ?? '-' }}</td>@endif
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="ttd" style="margin-top:24px;">
        <div>{{ $st->jabatan_penandatangan }}</div>
        <div class="space"></div>
        <div>{{ $st->nama_penandatangan }}</div>
    </div>
</div>

</div>
</body>
</html>