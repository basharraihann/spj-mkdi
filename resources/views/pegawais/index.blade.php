<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar Pegawai</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-6 space-y-6">

                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Pegawai</h3>
                        <p class="text-sm text-gray-400">Kelola data pegawai yang bisa ditugaskan ke agenda perjalanan
                            dinas.</p>
                    </div>
                    <span
                        class="text-xs font-semibold text-gray-400 bg-gray-50 border border-gray-100 rounded-full px-3 py-1.5">
                        {{ $totalPegawai }} pegawai
                    </span>
                </div>

                {{-- Search & filter bar: instan via JS, tidak reload halaman --}}
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500">Nama / NIP</label>
                        <input type="text" id="pegawai-search-q" placeholder="Cari nama atau NIP..." autocomplete="off"
                            class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-56 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500">Jabatan</label>
                        <input type="text" id="pegawai-search-jabatan" placeholder="Ketik jabatan..." autocomplete="off"
                            class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-52 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500">Status</label>
                        <div class="relative">
                            <select id="pegawai-search-status"
                                class="appearance-none border border-gray-200 rounded-lg pl-3.5 pr-8 py-2 text-sm w-36 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white text-gray-600">
                                <option value="">Semua Status</option>
                                @foreach (\App\Models\Pegawai::STATUS_KEPEGAWAIAN as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-3.5 w-3.5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <button type="button" id="pegawai-search-reset"
                        class="text-sm text-gray-400 hover:text-gray-600 transition px-1">
                        Reset
                    </button>

                    <div class="flex-1"></div>

                    <a href="{{ route('pegawais.create') }}"
                        class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm shadow-md shadow-blue-600/20 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Pegawai Baru
                    </a>
                </div>

                @if ($totalPegawai === 0)
                    <div class="px-5 py-14 text-center text-gray-400">
                        <div class="flex flex-col items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 100-8 4 4 0 000 8zm6 4v-2a4 4 0 00-3-3.87M7 10a4 4 0 100-8 4 4 0 000 8z" />
                            </svg>
                            <span>Belum ada pegawai yang cocok.</span>
                        </div>
                    </div>
                @else
                    <div id="pegawai-groups-container">
                        {{-- Section per unit kerja --}}
                        @foreach ($unitGroups as $unitIndex => $group)
                            <div class="js-pegawai-unit border border-gray-100 rounded-2xl overflow-hidden mb-5 last:mb-0"
                                x-data="{ openUnit: true }">
                                <button type="button" @click="openUnit = !openUnit"
                                    class="w-full flex items-center justify-between gap-4 px-5 py-4 bg-gray-50/70 text-left">
                                    <div class="flex items-center gap-2.5">
                                        <span
                                            class="h-6 w-6 rounded-md bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                            </svg>
                                        </span>
                                        <h4 class="text-sm font-bold text-gray-700">{{ $group['unit'] }}</h4>
                                        <span class="text-xs text-gray-400">{{ $group['total'] }} orang</span>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 text-gray-400 transition-transform flex-shrink-0"
                                        :class="{ 'rotate-180': openUnit }" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="openUnit" x-cloak x-transition.duration.200ms class="p-5 space-y-5">
                                    {{-- Sub-section: PNS --}}
                                    @if ($group['pnsList']->isNotEmpty())
                                        <div class="js-pegawai-subsection space-y-3">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="h-6 w-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.75h-.152c-3.196 0-6.1-1.248-8.25-3.286z" />
                                                    </svg>
                                                </span>
                                                <h5 class="text-sm font-bold text-gray-700">PNS (ASN)</h5>
                                                <span class="text-xs text-gray-400">{{ $group['pnsList']->count() }} orang ·
                                                    default urut golongan tertinggi, seret
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="inline h-3 w-3 -mt-0.5"
                                                        viewBox="0 0 24 24" fill="currentColor">
                                                        <circle cx="9" cy="6" r="1.5" />
                                                        <circle cx="9" cy="12" r="1.5" />
                                                        <circle cx="9" cy="18" r="1.5" />
                                                        <circle cx="15" cy="6" r="1.5" />
                                                        <circle cx="15" cy="12" r="1.5" />
                                                        <circle cx="15" cy="18" r="1.5" />
                                                    </svg> untuk atur manual</span>
                                            </div>
                                            @include('pegawais._table', ['list' => $group['pnsList']])
                                        </div>
                                    @endif

                                    {{-- Sub-section: Non PNS --}}
                                    @if ($group['nonPnsList']->isNotEmpty())
                                        <div class="js-pegawai-subsection space-y-3">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="h-6 w-6 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                </span>
                                                <h5 class="text-sm font-bold text-gray-700">Non PNS</h5>
                                                <span class="text-xs text-gray-400">{{ $group['nonPnsList']->count() }} orang
                                                    · seret buat atur manual</span>
                                            </div>
                                            @include('pegawais._table', ['list' => $group['nonPnsList']])
                                        </div>
                                    @endif

                                    {{-- Sub-section: Belum diisi statusnya --}}
                                    @if ($group['belumDiisiList']->isNotEmpty())
                                        <div class="js-pegawai-subsection space-y-3">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="h-6 w-6 rounded-md bg-gray-100 text-gray-400 flex items-center justify-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                                    </svg>
                                                </span>
                                                <h5 class="text-sm font-bold text-gray-700">Belum Diisi Status</h5>
                                                <span class="text-xs text-gray-400">{{ $group['belumDiisiList']->count() }}
                                                    orang · lengkapi status kepegawaiannya lewat Edit</span>
                                            </div>
                                            @include('pegawais._table', ['list' => $group['belumDiisiList']])
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <p id="pegawai-no-result" class="hidden py-10 text-center text-gray-400 text-sm">
                        Tidak ada pegawai yang cocok dengan pencarian.
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Drag & drop urutan manual --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = '{{ csrf_token() }}';

            document.querySelectorAll('.sortable-body').forEach(function (tbody) {
                Sortable.create(tbody, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'bg-blue-50',
                    onEnd: function () {
                        Array.from(tbody.children).forEach(function (row, index) {
                            const numberCell = row.querySelector('.row-number');
                            if (numberCell) numberCell.textContent = index + 1;
                        });

                        const ids = Array.from(tbody.children).map(function (row) {
                            return row.dataset.id;
                        });

                        fetch('{{ route('pegawais.reorder') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ ids: ids }),
                        }).catch(function () {
                            alert('Gagal menyimpan urutan baru. Coba muat ulang halaman.');
                        });
                    },
                });
            });
        });
    </script>

    {{-- Search instan client-side --}}
    <script>
        (function () {
            const qInput = document.getElementById('pegawai-search-q');
            const jabatanInput = document.getElementById('pegawai-search-jabatan');
            const statusSelect = document.getElementById('pegawai-search-status');
            const resetBtn = document.getElementById('pegawai-search-reset');
            const noResult = document.getElementById('pegawai-no-result');

            if (!qInput) return; // halaman kosong (0 pegawai), gak perlu filter

            function applyFilter() {
                const q = qInput.value.trim().toLowerCase();
                const jabatanQ = jabatanInput.value.trim().toLowerCase();
                const status = statusSelect.value;

                let anyUnitVisible = false;

                document.querySelectorAll('.js-pegawai-unit').forEach(function (unitEl) {
                    let anySubsectionVisible = false;

                    unitEl.querySelectorAll('.js-pegawai-subsection').forEach(function (subEl) {
                        let anyRowVisible = false;

                        subEl.querySelectorAll('.js-pegawai-row').forEach(function (row) {
                            const matchQ = q === '' || row.dataset.search.includes(q);
                            const matchJabatan = jabatanQ === '' || row.dataset.jabatan.includes(jabatanQ);
                            const matchStatus = status === '' || row.dataset.status === status;
                            const visible = matchQ && matchJabatan && matchStatus;

                            row.style.display = visible ? '' : 'none';
                            if (visible) anyRowVisible = true;
                        });

                        subEl.style.display = anyRowVisible ? '' : 'none';
                        if (anyRowVisible) anySubsectionVisible = true;
                    });

                    unitEl.style.display = anySubsectionVisible ? '' : 'none';
                    if (anySubsectionVisible) anyUnitVisible = true;
                });

                if (noResult) noResult.classList.toggle('hidden', anyUnitVisible);
            }

            qInput.addEventListener('input', applyFilter);
            jabatanInput.addEventListener('input', applyFilter);
            statusSelect.addEventListener('change', applyFilter);

            resetBtn.addEventListener('click', function () {
                qInput.value = '';
                jabatanInput.value = '';
                statusSelect.value = '';
                applyFilter();
            });
        })();
    </script>
</x-app-layout>