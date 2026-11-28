@php
    $c = \Illuminate\Support\Carbon::parse($k['tanggal']);
    $nada = $k['terlambat']
        ? 'merah'
        : match ($k['status']) {
            'selesai' => 'hijau',
            'baru' => 'biru',
            'dibaca' => 'kuning',
            default => 'ungu',
        };
    $label = $k['terlambat'] ? 'Telat' : $k['status_label'];
@endphp

<a href="{{ $k['url'] }}" class="db-item" data-tgl="{{ $k['tanggal'] }}">
    <div class="db-item-tgl">
        <b>{{ $c->format('d') }}</b>
        <span>{{ $c->translatedFormat('D') }}</span>
    </div>
    <div class="db-item-isi">
        <p class="db-item-judul">{{ $k['perihal'] }}</p>
        <div class="db-item-meta">
            <span class="db-pil db-pil-{{ $nada }}">{{ $label }}</span>

            @if ($k['waktu'])
                <span class="db-meta-ik">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="16" rx="3" />
                        <path d="M8 3v4M16 3v4M3 10h18" />
                    </svg>
                    {{ $c->translatedFormat('d M') }}, {{ $k['waktu'] }}
                </span>
            @elseif ($k['batas'])
                <span class="db-meta-ik">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="16" rx="3" />
                        <path d="M8 3v4M16 3v4M3 10h18" />
                    </svg>
                    Batas {{ $k['batas']->translatedFormat('d M') }}
                </span>
            @endif

            @if ($k['tempat'])
                <span class="db-meta-ik">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z" />
                        <circle cx="12" cy="10" r="2.5" />
                    </svg>
                    {{ \Illuminate\Support\Str::limit($k['tempat'], 28) }}
                </span>
            @endif

            @if (!empty($k['link']))
                @php $hostLink = parse_url($k['link'], PHP_URL_HOST) ?: \Illuminate\Support\Str::limit($k['link'], 30); @endphp
                {{-- <span>, bukan <a>: kartu ini sendiri sudah <a>. Dibuka lewat JS di jadwal.blade --}}
                            <span class="db-meta-ik db-zoom" role="link" tabindex="0" data-href="{{ $k['link'] }}"
                                title="{{ $k['link'] }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10 14a5 5 0 007.07 0l3-3a5 5 0 00-7.07-7.07l-1 1M14 10a5 5 0 00-7.07 0l-3 3a5 5 0 007.07 7.07l1-1" />
                                </svg>
                                <span class="db-zoom-teks">{{ $hostLink }}</span>
                            </span>
            @endif

                        @if ($k['bentrok'])
                            <span class="db-pil db-pil-merah">Jam bentrok</span>
                        @endif
        </div>
    </div>
</a>