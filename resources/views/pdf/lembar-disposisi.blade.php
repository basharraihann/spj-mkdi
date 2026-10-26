@php
    // Lembar Disposisi mengikuti template no. 8 (Sesdep / Keasdepan Deputi 2).
    $sm = $disposisi->suratMasuk;
    $pengirim = $disposisi->pengirim;
    $pgw = $pengirim?->pegawai;

    // Pilihan petunjuk yang dicentang disimpan di awal kolom instruksi: "[Tindak Lanjut: a, b] catatan"
    $teks = (string) $disposisi->instruksi;
    $dicentang = [];
    $catatan = $teks;
    if (preg_match('/^\[Tindak Lanjut:\s*(.*?)\]\s*(.*)$/s', $teks, $m)) {
        $dicentang = array_map('trim', explode(',', $m[1]));
        $catatan = $m[2];
    }
    if ($catatan === 'Mohon ditindaklanjuti.' && !empty($dicentang)) {
        $catatan = '';
    }

    // Format baru: petunjuk tersimpan di kolom `petunjuk` (array). Catatan kosong bila hanya berisi gabungan petunjuk.
    $petunjukTersimpan = is_array($disposisi->petunjuk) ? $disposisi->petunjuk : [];
    if (!empty($petunjukTersimpan)) {
        $dicentang = $petunjukTersimpan;
        $catatan = $teks === implode(', ', $petunjukTersimpan) ? '' : $teks;
    }

    // Varian unit: Keasdepan bila unit kerja pengirim berawalan "Asisten Deputi", selain itu Sekretariat Deputi
    $unitKerja = (string) ($pgw?->unit_kerja ?? '');
    $asdep = \Illuminate\Support\Str::startsWith($unitKerja, 'Asisten Deputi');
    $barisUnit = $asdep ? strtoupper($unitKerja) : 'SEKRETARIAT DEPUTI BIDANG KOORDINASI USAHA PANGAN DAN PERTANIAN';
    $jabatanTtd = $asdep
        ? ($pgw?->jabatan ?: $unitKerja)
        : ($pgw?->jabatan ?: 'Sekretaris Deputi Bidang Koordinasi Usaha Pangan dan Pertanian');
    $namaTtd = $pgw?->nama ?: ($pengirim->name ?? '');

    // Daftar "Disposisi Kepada": semua penerima dari pengirim yang sama pada surat ini (selain tembusan)
    $penerimaList = \App\Models\Disposisi::with('penerima.pegawai')
        ->where('surat_masuk_id', $disposisi->surat_masuk_id)
        ->where('pengirim_id', $disposisi->pengirim_id)
        ->where(function ($q) { $q->whereNull('is_tembusan')->orWhere('is_tembusan', false); })
        ->get();

    $tgl = fn ($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('j F Y') : '';
    $kolom = array_chunk(\App\Models\Disposisi::PETUNJUK, 8);
    $cek = fn ($o) => in_array($o, $dicentang, true) ? '&#9745;' : '&#9744;';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Disposisi</title>
    <style>
        @page { margin: 1.5cm 1.8cm; }
        body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 10.5pt; color: #000; line-height: 1.3; }
        table { border-collapse: collapse; width: 100%; }
        td { vertical-align: top; padding: 4px 6px; border: 1px solid #000; }
        .judul { text-align: center; font-weight: bold; line-height: 1.5; }
        .kop td { border: none; }
        .petunjuk div { margin-bottom: 3px; }
        .box { font-size: 12pt; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="4" class="judul">
                KEMENTERIAN KOORDINATOR BIDANG PANGAN<br>
                REPUBLIK INDONESIA<br>
                DEPUTI BIDANG KOORDINASI USAHA PANGAN DAN PERTANIAN<br>
                {{ $barisUnit }}<br>
                LEMBAR DISPOSISI
            </td>
        </tr>
        <tr>
            <td style="width:16%;">Sifat</td>
            <td colspan="3">: {{ ucfirst($disposisi->sifat ?? 'biasa') }} <span style="font-size:9pt;">(Rahasia / Segera / Biasa)</span></td>
        </tr>
        <tr>
            <td>No. Agenda</td>
            <td colspan="3">: {{ $sm->nomor_agenda ?? '' }}</td>
        </tr>
        <tr>
            <td>No. Surat</td>
            <td style="width:44%;">: {{ $sm->nomor_surat ?? '' }}</td>
            <td style="width:16%;">Tgl. Penerimaan</td>
            <td>: {{ $tgl($sm->tanggal_diterima ?? null) }}</td>
        </tr>
        <tr>
            <td>Asal Surat</td>
            <td>: {{ $sm->asal_surat ?? '' }}</td>
            <td>Tgl. Surat</td>
            <td>: {{ $tgl($sm->tanggal_surat ?? null) }}</td>
        </tr>
        <tr>
            <td colspan="4">Hal :<br>{{ $sm->perihal ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="4" style="padding:0;">
                <table style="border:none;">
                    <tr>
                        <td class="petunjuk" style="width:27%; border:none; border-right:1px solid #000;">
                            <strong>Petunjuk:</strong>
                            @foreach ($kolom[0] as $o)<div><span class="box">{!! $cek($o) !!}</span> {{ $o }}</div>@endforeach
                        </td>
                        <td class="petunjuk" style="width:30%; border:none; border-right:1px solid #000;">
                            <br>
                            @foreach ($kolom[1] ?? [] as $o)<div><span class="box">{!! $cek($o) !!}</span> {{ $o }}</div>@endforeach
                            @if (!empty($kolom[2]))
                                @foreach ($kolom[2] as $o)<div><span class="box">{!! $cek($o) !!}</span> {{ $o }}</div>@endforeach
                            @endif
                        </td>
                        <td class="petunjuk" style="border:none;">
                            <strong>Disposisi Kepada:</strong>
                            @forelse ($penerimaList as $d)
                                <div>- {{ $d->penerima->pegawai?->nama ?? $d->penerima->name ?? '-' }}@if ($d->penerima?->pegawai?->jabatan), {{ $d->penerima->pegawai->jabatan }}@endif</div>
                            @empty
                                <div>- {{ $disposisi->penerima->name ?? '-' }}</div>
                            @endforelse
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="4" style="height:70px;">
                Catatan :<br>{!! nl2br(e($catatan)) !!}
                @if ($disposisi->tenggat_waktu)<br>Tenggat: {{ $tgl($disposisi->tenggat_waktu) }}@endif
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <div style="width:55%; margin-left:45%;">
                    Jakarta, {{ $tgl($disposisi->created_at) }}<br>
                    {{ $jabatanTtd }},
                    <br><br><br><br>
                    {{ $namaTtd }}
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
