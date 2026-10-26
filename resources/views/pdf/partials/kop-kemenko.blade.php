@php
    $logoPath = public_path(config('surat_tugas.logo', 'images/logo.png'));
    $logoSrc = file_exists($logoPath)
        ? 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoPath))
        : null;
@endphp
<table style="width:100%; border-collapse:collapse; border-bottom:3px solid #000; margin-bottom:14px;">
    <tr>
        <td style="width:95px; vertical-align:middle; padding-bottom:8px;">
            @if ($logoSrc)
                <img src="{{ $logoSrc }}" style="width:85px; height:auto;">
            @endif
        </td>
        <td style="text-align:center; vertical-align:middle; padding-bottom:8px; line-height:1.3;">
            <div style="font-weight:bold; font-size:14pt;">KEMENTERIAN KOORDINATOR BIDANG PANGAN</div>
            <div style="font-weight:bold; font-size:12pt;">REPUBLIK INDONESIA</div>
            <div style="font-size:9pt;">Graha Mandiri, Jl. Imam Bonjol No. 61, Jakarta Pusat 10310</div>
            <div style="font-size:9pt;">Email : kemenkopangan@kemenkopangan.go.id</div>
        </td>
        <td style="width:95px;"></td>
    </tr>
</table>
