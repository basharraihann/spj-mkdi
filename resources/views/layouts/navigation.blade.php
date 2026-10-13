<style>
    /* ===== Tema: gradien putih -> biru ===== */
    .sb-aside {
        background-image: linear-gradient(180deg, #ffffff 0%, #ffffff 55%, #eef3fb 100%);
        border-color: #e2e8f0;
    }

    /* Menu aktif: sederhana, latar biru sangat muda + teks navy */
    .sb-active {
        background-color: #e6edf8;
        color: #1e3a8a;
    }

    /* ===== Sidebar muat tanpa scroll ===== */
    .sb-nav {
        scrollbar-width: none;
    }

    .sb-nav::-webkit-scrollbar {
        display: none;
    }

    /* Layar pendek: rapatkan otomatis */
    @media (max-height: 820px) {
        .sb-nav {
            padding-top: .5rem !important;
        }

        .sb-group {
            padding-top: .25rem !important;
        }

        .sb-head {
            padding-top: .125rem !important;
            padding-bottom: .125rem !important;
            margin-bottom: 0 !important;
        }

        .sb-link {
            padding-top: .375rem !important;
            padding-bottom: .375rem !important;
        }

        .sb-foot {
            padding-top: .5rem !important;
            padding-bottom: .5rem !important;
        }
    }
</style>
<aside x-cloak :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'lg:w-20' : 'lg:w-72'
    ]"
    class="sb-aside fixed inset-y-0 left-0 z-40 w-72 border-r flex flex-col transition-all duration-200 ease-in-out">

    <!-- Desktop collapse toggle: sibling langsung dari aside, tidak kena overflow-hidden -->
    <button @click="sidebarCollapsed = !sidebarCollapsed"
        class="hidden lg:flex absolute -right-2.5 top-1/2 -translate-y-1/2 z-50 h-16 w-5 items-center justify-center rounded-full bg-white border border-slate-200 shadow-sm text-slate-400 hover:bg-slate-50 hover:text-slate-700 hover:border-slate-300 transition-all duration-150 group">
        <svg class="h-3.5 w-3.5 transition-transform duration-200 group-hover:scale-110"
            :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
    </button>
    <!-- Wrapper isi sidebar: overflow-hidden dipindah ke sini biar tombol toggle di atas tidak ikut kepotong -->
    <div class="flex flex-col h-full overflow-hidden">

        <!-- Mobile close -->
        <div class="flex justify-end px-4 pt-4 lg:hidden">
            <button @click="sidebarOpen = false" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Nav Links -->
        <nav class="sb-nav flex-1 min-h-0 overflow-y-auto overflow-x-hidden px-4 pt-4 pb-2">

            @php
                // Menu dikelompokkan. 'label' => null berarti item tanpa judul kelompok.
                $groups = [
                    [
                        'label' => 'SPJ',
                        'items' => [
                            [
                                'route' => 'dashboard',
                                'active' => request()->routeIs('dashboard'),
                                'label' => __('Dashboard'),
                                'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
                            ],
                            [
                                'route' => 'agendas.index',
                                'active' => request()->routeIs('agendas.*'),
                                'label' => __('Agenda'),
                                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
                            ],
                            [
                                'route' => 'memo.index',
                                'active' => request()->routeIs('memo.*'),
                                'label' => __('Memorandum'),
                                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
                            ],
                            [
                                'route' => 'nominatif.index',
                                'active' => request()->routeIs('nominatif.*'),
                                'label' => __('Nominatif'),
                                'icon' => 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'
                            ],
                            [
                                'route' => 'pegawais.index',
                                'active' => request()->routeIs('pegawais.*'),
                                'label' => __('Pegawai'),
                                'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4'
                            ],
                            [
                                'route' => 'mak-options.index',
                                'active' => request()->routeIs('mak-options.*'),
                                'label' => __('Nomor MAK'),
                                'icon' => 'M9 7h6m-6 4h6m-6 4h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z'
                            ],
                        ],
                    ],
                    [
                        'label' => 'Anggaran',
                        'items' => [
                            [
                                'route' => 'anggaran.index',
                                // Jangan ikut menyala di halaman Pemetaan Unit
                                'active' => request()->routeIs('anggaran.*') && !request()->routeIs('anggaran.pemetaan*'),
                                'label' => __('Realisasi Anggaran'),
                                'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'
                            ],
                        ],
                    ],
                ];

                if (Auth::user()?->isAdmin()) {
                    // Pemetaan Unit: masuk ke kelompok "Anggaran" (indeks 1), hanya terlihat oleh admin
                    $groups[1]['items'][] = [
                        'route' => 'anggaran.pemetaan',
                        'active' => request()->routeIs('anggaran.pemetaan*'),
                        'label' => __('Pemetaan Unit'),
                        'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
                    ];

                    $groups[] = [
                        'label' => null,
                        'items' => [
                            [
                                'route' => 'users.index',
                                'active' => request()->routeIs('users.*'),
                                'label' => __('User Manajemen'),
                                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
                            ],
                        ],
                    ];
                }

                // Menu bawah: Profile + Log out
                $footer = [
                    [
                        'route' => 'profile.edit',
                        'active' => request()->routeIs('profile.*'),
                        'label' => __('Profile'),
                        'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
                    ],
                    [
                        'route' => 'logout',
                        'post' => true,
                        'active' => false,
                        'label' => __('Log Out'),
                        'icon' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1'
                    ],
                ];
            @endphp

            @foreach ($groups as $group)
                {{-- open: kelompok terlipat/terbuka. Saat sidebar mode ikon (collapsed), semua item
                tetap tampil supaya masih bisa diklik. --}}
                <div x-data="{ open: true }" class="sb-group {{ $loop->first ? '' : 'pt-3' }}">

                    @if ($group['label'])
                        {{-- Sidebar melebar: judul kelompok + panah lipat --}}
                        <button type="button" @click="open = !open" x-show="sidebarOpen || !sidebarCollapsed" x-cloak
                            class="sb-head w-full flex items-center justify-between px-4 py-1.5 mb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500 hover:text-slate-700 transition">
                            <span>{{ $group['label'] }}</span>
                            <svg class="h-3.5 w-3.5 transition-transform duration-150" :class="open ? '' : '-rotate-90'"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        {{-- Sidebar mode ikon: garis pemisah antar kelompok --}}
                        @unless ($loop->first)
                            <div x-show="!(sidebarOpen || !sidebarCollapsed)" x-cloak class="mb-2 border-t border-slate-200"></div>
                        @endunless
                    @elseif (!$loop->first)
                        <div class="mb-2 border-t border-slate-200"></div>
                    @endif

                    <div x-show="open || !(sidebarOpen || !sidebarCollapsed)" class="space-y-1">
                        @foreach ($group['items'] as $item)
                            <a href="{{ route($item['route']) }}" :title="sidebarCollapsed ? '{{ $item['label'] }}' : ''"
                                class="sb-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition
                                                                    {{ $item['active'] ? 'sb-active' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}"
                                :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                                </svg>
                                <span x-show="sidebarOpen || !sidebarCollapsed" x-cloak>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <!-- Profile + Log out -->
        <div class="sb-foot px-4 pb-4 pt-3 border-t border-slate-200 space-y-1">
            @foreach ($footer as $item)
                @if (!empty($item['post']))
                    <form method="POST" action="{{ route($item['route']) }}">
                        @csrf
                        <button type="submit" :title="sidebarCollapsed ? '{{ $item['label'] }}' : ''"
                            class="sb-link w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition text-slate-600 hover:bg-red-50 hover:text-red-600"
                            :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                            </svg>
                            <span x-show="sidebarOpen || !sidebarCollapsed" x-cloak>{{ $item['label'] }}</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route($item['route']) }}" :title="sidebarCollapsed ? '{{ $item['label'] }}' : ''"
                        class="sb-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition
                                                            {{ $item['active'] ? 'sb-active' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}"
                        :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                        <span x-show="sidebarOpen || !sidebarCollapsed" x-cloak>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </div>

    </div>
</aside>