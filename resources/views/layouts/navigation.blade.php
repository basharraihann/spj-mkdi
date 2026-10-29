<style>
    .sb-aside {
        background-image: linear-gradient(180deg, #ffffff 0%, #ffffff 55%, #eef3fb 100%);
        border-color: #e2e8f0;
    }

    .sb-active {
        background-color: #e6edf8;
        color: #1e3a8a;
    }

    .sb-nav {
        scrollbar-width: none;
    }

    .sb-nav::-webkit-scrollbar {
        display: none;
    }

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

    <!-- Desktop collapse toggle -->
    <button @click="sidebarCollapsed = !sidebarCollapsed"
        class="hidden lg:flex absolute -right-2.5 top-1/2 -translate-y-1/2 z-50 h-16 w-5 items-center justify-center rounded-full bg-white border border-slate-200 shadow-sm text-slate-400 hover:bg-slate-50 hover:text-slate-700 hover:border-slate-300 transition-all duration-150 group">
        <svg class="h-3.5 w-3.5 transition-transform duration-200 group-hover:scale-110"
            :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
    </button>

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
                $user = Auth::user();

                $tugasMenunggu = (Auth::check() && class_exists(\App\Models\LaporanStTugas::class))
                    ? \App\Models\LaporanStTugas::where('user_id', Auth::id())
                        ->where('status', \App\Models\LaporanStTugas::STATUS_MENUNGGU)
                        ->whereHas('suratTugas', fn($q) => $q->where('status', '!=', 'batal'))
                        ->count()
                    : 0;

                $disposisiBaru = (Auth::check() && class_exists(\App\Models\Disposisi::class))
                    ? \App\Models\Disposisi::where('penerima_id', Auth::id())->where('status', 'baru')->count()
                    : 0;

                // Persetujuan ST yang sedang menunggu giliran user ini.
                // Dihitung hanya bila semua tahap sebelumnya sudah disetujui (sama dengan aturan "Giliran sekarang").
                // Admin melihat semua tahap yang sedang menunggu, karena admin boleh bertindak di tiap tahap.
                $kontrolMenunggu = (Auth::check() && class_exists(\App\Models\SuratTugasKontrol::class))
                    ? \App\Models\SuratTugasKontrol::where('status', 'menunggu')
                        ->when(!$user->isAdmin(), fn($q) => $q->where('petugas_id', $user->id))
                        ->get()
                        ->filter(fn($k) => \App\Models\SuratTugasKontrol::where('surat_tugas_id', $k->surat_tugas_id)
                            ->where('urutan', '<', $k->urutan)
                            ->where('status', '!=', 'disetujui')
                            ->doesntExist())
                        ->count()
                    : 0;

                // Semua menu didefinisikan di sini; yang tampil disaring lewat
                // User::bolehMenu($key) berdasarkan config/menu.php.
                $allGroups = [
                    [
                        'label' => 'Tata Kelola & Persuratan',
                        'items' => [
                            [
                                'key' => 'surat_masuk',
                                'route' => 'surat-masuk.index',
                                'active' => request()->routeIs('surat-masuk.*'),
                                'label' => __('Surat Masuk'),
                                'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'
                            ],
                            [
                                'key' => 'disposisi',
                                'route' => 'disposisi.index',
                                'active' => request()->routeIs('disposisi.*'),
                                'label' => __('Disposisi Digital'),
                                'badge' => $disposisiBaru,
                                'icon' => 'M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8'
                            ],
                            [
                                'key' => 'surat_keluar',
                                'route' => 'surat-keluar.index',
                                'active' => request()->routeIs('surat-keluar.*'),
                                'label' => __('Surat Keluar'),
                                'icon' => 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8'
                            ],
                            [
                                'key' => 'monitoring',
                                'route' => 'monitoring.index',
                                'active' => request()->routeIs('monitoring.*'),
                                'label' => __('Monitoring'),
                                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
                            ],
                        ],
                    ],
                    [
                        'label' => 'SPJ',
                        'items' => [
                            [
                                'key' => 'dashboard',
                                'route' => 'dashboard',
                                'active' => request()->routeIs('dashboard'),
                                'label' => __('Dashboard'),
                                'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
                            ],
                            [
                                'key' => 'surat_tugas',
                                'route' => 'surat-tugas.index',
                                'active' => request()->routeIs('surat-tugas.*'),
                                'label' => __('Surat Tugas'),
                                'badge' => $kontrolMenunggu,
                                'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'
                            ],
                            [
                                'key' => 'tugas_saya',
                                'route' => 'tugas-saya.index',
                                'active' => request()->routeIs('tugas-saya.*'),
                                'label' => __('Tugas Saya'),
                                'badge' => $tugasMenunggu,
                                'icon' => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4'
                            ],
                            [
                                'key' => 'agendas',
                                'route' => 'agendas.index',
                                'active' => request()->routeIs('agendas.*'),
                                'label' => __('Agenda'),
                                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
                            ],
                            [
                                'key' => 'memo',
                                'route' => 'memo.index',
                                'active' => request()->routeIs('memo.*'),
                                'label' => __('Memorandum'),
                                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
                            ],
                            [
                                'key' => 'nominatif',
                                'route' => 'nominatif.index',
                                'active' => request()->routeIs('nominatif.*'),
                                'label' => __('Nominatif'),
                                'icon' => 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'
                            ],
                            [
                                'key' => 'pegawais',
                                'route' => 'pegawais.index',
                                'active' => request()->routeIs('pegawais.*'),
                                'label' => __('Pegawai'),
                                'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4'
                            ],
                            [
                                'key' => 'mak_options',
                                'route' => 'mak-options.index',
                                'active' => request()->routeIs('mak-options.*'),
                                'label' => __('Nomor MAK'),
                                'icon' => 'M9 7h6m-6 4h6m-6 4h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 01-2-2z'
                            ],
                        ],
                    ],
                    [
                        'label' => 'Anggaran',
                        'items' => [
                            [
                                'key' => 'anggaran',
                                'route' => 'anggaran.index',
                                'active' => request()->routeIs('anggaran.*') && !request()->routeIs('anggaran.pemetaan*'),
                                'label' => __('Realisasi Anggaran'),
                                'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'
                            ],
                            [
                                'key' => 'anggaran_pemetaan',
                                'route' => 'anggaran.pemetaan',
                                'active' => request()->routeIs('anggaran.pemetaan*'),
                                'label' => __('Pemetaan Unit'),
                                'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
                            ],
                        ],
                    ],
                    [
                        'label' => null,
                        'items' => [
                            [
                                'key' => 'tusi',
                                'route' => 'tusi.index',
                                'active' => request()->routeIs('tusi.*'),
                                'label' => __('Master Tusi'),
                                'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'
                            ],
                            [
                                'key' => 'pemetaan_akun',
                                'route' => 'pemetaan-akun.index',
                                'active' => request()->routeIs('pemetaan-akun.*'),
                                'label' => __('Pemetaan Akun'),
                                'icon' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'
                            ],
                            [
                                'key' => 'users',
                                'route' => 'users.index',
                                'active' => request()->routeIs('users.*'),
                                'label' => __('User Manajemen'),
                                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
                            ],
                        ],
                    ],
                ];

                // Saring menu per role. Item admin-only (anggaran_pemetaan, tusi,
                // pemetaan_akun, users) otomatis hilang untuk role selain admin
                // karena key-nya tidak ada di config/menu.php.
                $groups = [];
                foreach ($allGroups as $grp) {
                    $items = array_values(array_filter(
                        $grp['items'],
                        fn($item) => $user?->bolehMenu($item['key'])
                    ));

                    if ($items) {
                        $groups[] = ['label' => $grp['label'], 'items' => $items];
                    }
                }

                $footer = [
                    [
                        'route' => 'profile.edit',
                        'active' => request()->routeIs('profile.*'),
                        'label' => __('Profile'),
                        'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
                    ],
                ];
            @endphp

            @foreach ($groups as $group)
                <div x-data="{ open: true }" class="sb-group {{ $loop->first ? '' : 'pt-3' }}">

                    @if ($group['label'])
                        <button type="button" @click="open = !open" x-show="sidebarOpen || !sidebarCollapsed" x-cloak
                            class="sb-head w-full flex items-center justify-between px-4 py-1.5 mb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500 hover:text-slate-700 transition">
                            <span>{{ $group['label'] }}</span>
                            <svg class="h-3.5 w-3.5 transition-transform duration-150" :class="open ? '' : '-rotate-90'"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        @unless ($loop->first)
                            <div x-show="!(sidebarOpen || !sidebarCollapsed)" x-cloak class="mb-2 border-t border-slate-200"></div>
                        @endunless
                    @elseif (!$loop->first)
                        <div class="mb-2 border-t border-slate-200"></div>
                    @endif

                    <div x-show="open || !(sidebarOpen || !sidebarCollapsed)" class="space-y-1">
                        @foreach ($group['items'] as $item)
                            <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                                :title="sidebarCollapsed ? '{{ $item['label'] }}' : ''"
                                class="sb-link relative flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition
                                                    {{ $item['active'] ? 'sb-active' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}"
                                :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                                </svg>
                                <span x-show="sidebarOpen || !sidebarCollapsed" x-cloak>{{ $item['label'] }}</span>

                                @if (!empty($item['badge']))
                                    <span x-show="sidebarOpen || !sidebarCollapsed" x-cloak
                                        class="ml-auto inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-semibold leading-5 text-white">
                                        {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                                    </span>
                                    <span x-show="!(sidebarOpen || !sidebarCollapsed)" x-cloak
                                        class="absolute right-3 top-1.5 h-2 w-2 rounded-full bg-red-500"></span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <!-- Profile + Log out -->
        <div class="sb-foot px-4 pb-4 pt-3 border-t border-slate-200 space-y-1">
            @foreach ($footer as $item)
                <a href="{{ route($item['route']) }}" :title="sidebarCollapsed ? '{{ $item['label'] }}' : ''"
                    class="sb-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition
                                {{ $item['active'] ? 'sb-active' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}" :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                    <span x-show="sidebarOpen || !sidebarCollapsed" x-cloak>{{ $item['label'] }}</span>
                </a>
            @endforeach

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" :title="sidebarCollapsed ? 'Log Out' : ''"
                    class="sb-link w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition text-slate-600 hover:bg-red-50 hover:text-red-600"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span x-show="sidebarOpen || !sidebarCollapsed" x-cloak>Log Out</span>
                </button>
            </form>
        </div>

    </div>
</aside>