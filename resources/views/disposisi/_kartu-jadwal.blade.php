{{-- Satu kartu jadwal disposisi. Variabel: $k (array dari JadwalPegawaiController@kartu) --}}
@php
    $statusKelas = [
        'baru' => 'kd-st-baru',
        'dibaca' => 'kd-st-dibaca',
        'diteruskan' => 'kd-st-diteruskan',
        'selesai' => 'kd-st-selesai',
    ];
    $sifatKelas = [
        'segera' => 'kd-chip-amber',
        'sangat_segera' => 'kd-chip-red',
        'rahasia' => 'kd-chip-violet',
    ];
    $garis = $k['terlambat'] ? 'jp-g-telat' : 'jp-g-' . $k['status'];
    $tglK = \Illuminate\Support\Carbon::parse($k['tanggal']);
@endphp

<div class="jp-kartu {{ $garis }}" data-href="{{ $k['url'] }}" tabindex="0" role="link">
    <div class="jp-badges">
        <span class="kd-chip jp-chip-jenis">{{ $k['jenis_label'] }}</span>
        <span class="kd-st {{ $statusKelas[$k['status']] ?? 'kd-st-keluar' }}">{{ $k['status_label'] }}</span>
        @if ($k['terlambat'])
            <span class="kd-chip kd-chip-red">Telat</span>
        @endif
        @if (isset($sifatKelas[$k['sifat']]))
            <span class="kd-chip {{ $sifatKelas[$k['sifat']] }}">{{ $k['sifat_label'] }}</span>
        @endif
        @if ($k['unit'])
            <span class="kd-chip jp-chip-unit">{{ $k['unit'] }}</span>
        @endif
        @if ($k['bentrok'])
            <span class="kd-chip kd-chip-amber" title="Jam kegiatan ini bertabrakan dengan kegiatan lain di hari yang sama">⚠ Jam bentrok</span>
        @endif
    </div>

    <a href="{{ $k['url'] }}" class="jp-judul">{{ $k['perihal'] }}</a>

    <div class="jp-baris">
        <svg class="jp-ik" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        <b class="jp-tebal-biru">{{ $tglK->translatedFormat('d M Y') }}</b>
        @if ($k['waktu'])
            <span>· {{ $k['waktu'] }}</span>
        @endif
        @if ($k['batas'])
            <span class="{{ $k['terlambat'] ? 'jp-merah' : '' }}">· Batas {{ $k['batas']->translatedFormat('d M Y') }}</span>
        @endif
    </div>

    @if ($k['tempat'] || $k['link'])
        <div class="jp-baris">
            <svg class="jp-ik" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            @if ($k['tempat'])
                <span>{{ $k['tempat'] }}</span>
            @endif
            @if ($k['link'])
                <a href="{{ $k['link'] }}" target="_blank" rel="noopener noreferrer" class="jp-link">{{ \Illuminate\Support\Str::limit($k['link'], 60) }}</a>
            @endif
        </div>
    @endif

    @if ($k['instruksi'])
        <div class="jp-baris">
            <svg class="jp-ik" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
            <span><b>Instruksi:</b> {{ implode(', ', $k['instruksi']) }}@if ($k['dari']) · dari {{ $k['dari'] }}@endif</span>
        </div>
    @endif

    @if (count($k['ditandai']))
        <div class="jp-baris">
            <svg class="jp-ik" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            <span><b>Ditandai:</b> {{ implode(', ', $k['ditandai']) }}</span>
        </div>
    @endif
</div>
