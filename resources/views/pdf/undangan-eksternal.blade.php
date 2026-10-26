@php
    // Dipakai untuk tiga varian: internal, eksternal (berlogo), surat_dinas (format undangan).
    // Template 9 internal tidak memakai kop logo; eksternal & surat dinas memakai kop Kemenko.
    $tpl = $dokumen->payload('template', 'internal');
    $pakaiKop = in_array($tpl, ['eksternal', 'surat_dinas'], true);
    $prefix = ['biasa' => 'B', 'terbatas' => 'T', 'rahasia' => 'R'][$dokumen->sifat] ?? 'B';
    $nomor = $dokumen->nomor_surat ?: $prefix . '-.../' . ($dokumen->payload('kode_klasifikasi') ?: 'HM.02') . '/' . $dokumen->tanggal_surat->format('Y');
    $merah = $dokumen->sifat === 'rahasia' ? 'color:#c00000;' : '';
    $jabatan = $dokumen->payload('jabatan_penandatangan') ?: 'Sekretaris Deputi Bidang Koordinasi Usaha Pangan dan Pertanian';
    $daftar = $dokumen->payloadList('daftar_undangan');
    $tglId = $dokumen->tanggal_surat->locale('id')->translatedFormat('j F Y');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $dokumen->hal }}</title>
    <style>
        @page { margin: 2cm 2.5cm 2cm 2.5cm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.35; color: #000; }
        td { vertical-align: top; }
        .meta td { padding: 1px 0; }
        .justify { text-align: justify; }
        .judul-instansi { text-align:center; font-weight:bold; text-transform:uppercase; line-height:1.3; margin-bottom:14px; }
    </style>
</head>
<body>

    @if ($pakaiKop)
        @include('pdf.partials.kop-kemenko')
    @else
        <div class="judul-instansi">
            DEPUTI BIDANG KOORDINASI USAHA PANGAN DAN PERTANIAN<br>
            {{ $dokumen->payload('jabatan_penandatangan') ? strtoupper($dokumen->payload('jabatan_penandatangan')) : 'SEKRETARIS DEPUTI BIDANG KOORDINASI USAHA PANGAN DAN PERTANIAN' }}
        </div>
    @endif

    <table class="meta" style="width:100%;">
        <tr>
            <td style="width:15%;">Nomor</td><td style="width:3%;">:</td>
            <td style="{{ $merah }}">{{ $nomor }}</td>
            <td style="text-align:right; width:30%;">Jakarta, {{ $tglId }}</td>
        </tr>
        <tr><td>Sifat</td><td>:</td><td colspan="2">{{ ucfirst($dokumen->sifat) }}</td></tr>
        @if ($dokumen->dengan_lampiran)
            <tr><td>Lampiran</td><td>:</td><td colspan="2">1 (Satu) Berkas</td></tr>
        @endif
        <tr><td>Hal</td><td>:</td><td colspan="2">{{ $dokumen->hal }}</td></tr>
    </table>

    <p style="margin-top:14px;">Yth. {{ $dokumen->tujuan }}</p>

    <p class="justify">{{ $dokumen->payload('pembuka') ?: 'Dalam rangka ..., bersama ini kami mengundang Saudara untuk hadir dalam rapat yang akan dilaksanakan pada:' }}</p>

    <table class="meta" style="width:100%; margin-left:30px;">
        <tr><td style="width:22%;">hari/tanggal</td><td style="width:3%;">:</td><td>{{ $dokumen->payload('hari_tanggal', '...') }}</td></tr>
        <tr><td>waktu</td><td>:</td><td>{{ $dokumen->payload('waktu', 'Pukul ...') }}</td></tr>
        <tr><td>tempat</td><td>:</td><td>{{ $dokumen->payload('tempat', '...') }}</td></tr>
        <tr><td>acara</td><td>:</td><td>{{ $dokumen->payload('acara', '...') }}</td></tr>
    </table>

    <p class="justify" style="margin-top:12px;">Demikian atas perhatian dan kerja sama Saudara, kami ucapkan terima kasih.</p>

    <div style="width:50%; margin-left:50%; margin-top:18px; page-break-inside:avoid;">
        {{ $jabatan }},
        <br><br><br><br>
        {{ $dokumen->payload('nama_penandatangan', '................') }}
    </div>

    @if (!empty($dokumen->tembusan))
        <div style="margin-top:24px; font-size:11pt; page-break-inside:avoid;">
            Tembusan:
            <ol style="margin:2px 0 0 0; padding-left:20px;">
                @foreach ($dokumen->tembusan as $t)<li>{{ $t }}</li>@endforeach
            </ol>
        </div>
    @endif

    @if ($dokumen->dengan_lampiran)
        <div style="page-break-before:always;">
            <div style="text-align:center;">- 2 -</div>
            <p style="margin-top:12px;"><strong>Lampiran Surat</strong></p>
            <table class="meta">
                <tr><td style="width:90px;">Nomor</td><td>: {{ $nomor }}</td></tr>
                <tr><td>Tanggal</td><td>: {{ $tglId }}</td></tr>
            </table>
            <p style="text-align:center; font-weight:bold; margin-top:14px;">DAFTAR PEJABAT / PEGAWAI YANG DIUNDANG</p>
            <ol>
                @forelse ($daftar as $d)<li>{{ $d }}</li>@empty<li>...</li>@endforelse
            </ol>
            <div style="width:50%; margin-left:50%; margin-top:18px;">
                {{ $jabatan }},
                <br><br><br><br>
                {{ $dokumen->payload('nama_penandatangan', '................') }}
            </div>
        </div>
    @endif

</body>
</html>
