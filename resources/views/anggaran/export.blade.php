<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 10pt; }
        th { background-color: #4f46e5; color: #ffffff; border: 1px solid #3730a3; padding: 8px; font-weight: bold; text-align: center; }
        td { border: 1px solid #d1d5db; padding: 6px; text-align: left; vertical-align: top; }
        .num { text-align: right; mso-number-format: "\#\,\#\#0"; }
        .pct { text-align: right; mso-number-format: "0\.0\%"; }
        .center { text-align: center; }
        .title { font-size: 14pt; font-weight: bold; margin-bottom: 4px; }
        .subtitle { font-size: 10pt; color: #4b5563; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="title">LAPORAN REALISASI ANGGARAN PER AKUN</div>
    @if ($activeUpload)
        <div class="subtitle">
            Satker: {{ $activeUpload->satker_nama }} | Periode: {{ $activeUpload->periode }} | File: {{ $activeUpload->nama_file }} | Diperbarui: {{ $activeUpload->created_at->format('d/m/Y H:i') }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Kegiatan</th>
                <th>RO / SubOutput</th>
                <th>Komponen</th>
                <th>SubKomponen</th>
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                <th>Kode Item</th>
                <th>Uraian Item</th>
                <th>Pagu (Rp)</th>
                <th>Realisasi (Rp)</th>
                <th>Sisa (Rp)</th>
                <th>% Realisasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                @php
                    $pagu = $item->pagu ?? 0;
                    $realisasi = $item->realisasi ?? 0;
                    $sisa = $pagu - $realisasi;
                    $pct = $pagu > 0 ? ($realisasi / $pagu) : 0;
                @endphp
                <tr>
                    <td class="center">'{{ $item->keg_kode }}</td>
                    <td class="center">'{{ $item->ro_kode }}</td>
                    <td class="center">'{{ $item->komp_kode }}</td>
                    <td class="center">'{{ $item->sub_kode }}</td>
                    <td class="center">'{{ $item->akun_kode }}</td>
                    <td>{{ $item->akun_uraian }}</td>
                    <td class="center">'{{ $item->item_kode }}</td>
                    <td>{{ $item->item_uraian }}</td>
                    <td class="num">{{ $pagu }}</td>
                    <td class="num">{{ $realisasi }}</td>
                    <td class="num">{{ $sisa }}</td>
                    <td class="pct">{{ $pct }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="center">Tidak ada data anggaran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>