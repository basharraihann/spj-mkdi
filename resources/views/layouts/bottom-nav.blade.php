{{-- Bottom navigation: hanya tampil di HP/tablet kecil (< lg). Di desktop memakai sidebar. --}}
@php
    $bn = [
        ['route' => 'dashboard', 'active' => request()->routeIs('dashboard*'), 'label' => 'Beranda',
            'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['route' => 'agendas.index', 'active' => request()->routeIs('agendas.*'), 'label' => 'Agenda',
            'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['route' => 'memo.index', 'active' => request()->routeIs('memo.*'), 'label' => 'Memo',
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['route' => 'nominatif.index', 'active' => request()->routeIs('nominatif.*'), 'label' => 'Nominatif',
            'icon' => 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
    ];
@endphp

<nav class="spj-bottomnav" aria-label="Navigasi utama">
    @foreach ($bn as $item)
        <a href="{{ route($item['route']) }}" class="spj-bn-item {{ $item['active'] ? 'is-active' : '' }}"
            @if ($item['active']) aria-current="page" @endif>
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $item['active'] ? 2.25 : 1.75 }}"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
            </svg>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach

    {{-- Menu: buka drawer sidebar (Anggaran, Pegawai, Nomor MAK, Profile, Log out, dst) --}}
    <button type="button" class="spj-bn-item" :class="sidebarOpen ? 'is-active' : ''" @click="sidebarOpen = true"
        aria-label="Buka menu lainnya">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10" />
        </svg>
        <span>Menu</span>
    </button>
</nav>
