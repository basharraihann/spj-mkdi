<aside x-cloak :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'lg:w-20' : 'lg:w-72'
    ]"
    class="fixed inset-y-0 left-0 z-40 w-72 bg-white border-r border-gray-100 flex flex-col transition-all duration-200 ease-in-out">

    <!-- Desktop collapse toggle: sibling langsung dari aside, tidak kena overflow-hidden -->
    <button @click="sidebarCollapsed = !sidebarCollapsed"
        class="hidden lg:flex absolute -right-2.5 top-1/2 -translate-y-1/2 z-50 h-16 w-5 items-center justify-center rounded-full bg-white border border-gray-200 shadow-sm text-gray-400 hover:bg-gray-50 hover:text-gray-700 hover:border-gray-300 transition-all duration-150 group">
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
        <nav class="flex-1 overflow-y-auto px-4 pt-6 lg:pt-10 pb-2 space-y-1">

            @php
                $items = [
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
                        'route' => 'pegawais.index',
                        'active' => request()->routeIs('pegawais.*'),
                        'label' => __('Pegawai'),
                        'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4'
                    ],
                    [
                        'route' => 'memo.index',
                        'active' => request()->routeIs('memo.*'),
                        'label' => __('Memorandum'),
                        'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
                    ],
                ];

                if (Auth::user()?->isAdmin()) {
                    $items[] = [
                        'route' => 'users.index',
                        'active' => request()->routeIs('users.*'),
                        'label' => __('User Manajemen'),
                        'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
                    ];
                }
            @endphp

            @foreach ($items as $item)
                <a href="{{ route($item['route']) }}" :title="sidebarCollapsed ? '{{ $item['label'] }}' : ''"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition
                                                {{ $item['active'] ? 'bg-indigo-50 text-indigo-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' }}"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <!-- User + Logout -->
        <div class="px-4 pb-5 pt-3 border-t border-gray-100">
            <div class="flex items-center gap-3 px-2 py-2" :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                <div
                    class="h-9 w-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-semibold shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1" x-show="!sidebarCollapsed" x-cloak>
                    <div class="flex items-center justify-between gap-1">
                        <div class="text-sm font-medium text-gray-800 truncate">{{ Auth::user()->name }}</div>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold shrink-0 {{ Auth::user()->isAdmin() ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ Auth::user()->isAdmin() ? 'Admin' : 'Staf' }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-400 truncate">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <a href="{{ route('profile.edit') }}"
                class="flex items-center gap-3 px-2 py-2 rounded-xl text-sm text-gray-500 hover:bg-gray-50 hover:text-gray-800"
                :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak>{{ __('Profile') }}</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"
                    class="flex items-center gap-3 px-2 py-2 rounded-xl text-sm text-gray-500 hover:bg-gray-50 hover:text-gray-800"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>{{ __('Log Out') }}</span>
                </a>
            </form>
        </div>

    </div>
</aside>