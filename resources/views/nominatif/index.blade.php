<x-app-layout title="Nominatif">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

        @if (session('success'))
            <div class="flex gap-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        <!-- Header & aksi -->
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Nominatif</h1>
                    <span
                        class="bg-indigo-50 text-indigo-700 text-xs font-semibold px-2.5 py-0.5 rounded-full border border-indigo-100">
                        {{ $groups->count() }} kegiatan
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-1">Nominatif perjalanan dinas dari agenda, dan daftar honorarium
                    narasumber
                    yang dibuat mandiri tanpa agenda.</p>
            </div>
            <div>
                <a href="{{ route('nominatif.create') }}"
                    class="inline-flex w-full sm:w-auto justify-center items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow-sm transition duration-150 ease-in-out hover:shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Nominatif
                </a>
            </div>
        </div>

        <!-- Filter (instan, tanpa reload) -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="nom-filter-q" autocomplete="off"
                        placeholder="Cari uraian kegiatan, tujuan, atau status..."
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>

                <div class="sm:w-48">
                    <select id="nom-filter-status"
                        class="w-full py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        <option value="">-- Semua Status --</option>
                        <option value="PNS">PNS</option>
                        <option value="Non PNS">Non PNS</option>
                        <option value="Honorarium">Honorarium Narasumber</option>
                    </select>
                </div>

                <button type="button" id="nom-filter-reset"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-sm font-medium transition cursor-pointer">
                    Reset
                </button>
            </div>
        </div>

        <!-- Tabel -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 pt-5">
                <span class="text-xs font-semibold text-gray-400">
                    <span id="nom-visible-count">{{ $groups->count() }}</span> dari {{ $groups->count() }} kegiatan
                </span>
            </div>
            <div>
                <table id="nom-table" class="w-full table-fixed text-left text-sm text-gray-600">
                    <thead class="bg-gray-50/70 border-b border-gray-100 text-xs font-semibold uppercase text-gray-500">
                        <tr>
                            <th class="px-3 py-4 text-center w-12">No</th>
                            <th class="px-3 py-4">Uraian Kegiatan</th>
                            <th class="px-3 py-4 w-40">Tujuan</th>
                            <th class="px-3 py-4 w-32">Tanggal</th>
                            <th class="px-3 py-4 w-32">Status</th>
                            <th class="px-3 py-4 text-center w-16">Orang</th>
                            <th class="px-3 py-4 text-right w-32">Total</th>
                            <th class="px-3 py-4 text-right w-44">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($groups as $g)
                            @php
                                $statuses = '|' . collect($g['items'])->pluck('status')->implode('|') . '|';
                                $search = strtolower(
                                    $g['uraian_kegiatan'] . ' ' . $g['tujuan'] . ' ' .
                                    collect($g['items'])->map(fn ($i) => $i['status'] . ($i['status'] === 'Honorarium' ? ' narasumber' : ''))->implode(' ')
                                );
                            @endphp
                            <tr class="js-nom-row hover:bg-gray-50/50 transition align-top"
                                data-search="{{ $search }}" data-status="{{ $statuses }}">
                                <td class="px-4 py-4 text-center text-xs font-semibold text-gray-400 tabular-nums">
                                    <span class="js-nom-number">{{ $loop->iteration }}</span>
                                </td>
                                <td class="px-3 py-4 font-medium text-gray-900 max-w-xs">
                                    <div class="line-clamp-2">{{ $g['uraian_kegiatan'] }}</div>
                                </td>
                                <td class="px-3 py-4">{{ $g['tujuan'] }}</td>
                                <td class="px-3 py-4 text-xs text-gray-500">{{ $g['tanggal'] }}</td>

                                {{-- Status --}}
                                <td class="px-3 py-4">
                                    <div class="flex flex-col gap-1.5">
                                        @foreach ($g['items'] as $r)
                                            <div class="h-8 flex items-center">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $r['status'] === 'PNS' ? 'bg-blue-50 text-blue-700 border-blue-200/60' : ($r['status'] === 'Honorarium' ? 'bg-emerald-50 text-emerald-700 border-emerald-200/60' : 'bg-amber-50 text-amber-700 border-amber-200/60') }}">
                                                    {{ $r['status'] }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                {{-- Orang --}}
                                <td class="px-3 py-4 text-center">
                                    <div class="flex flex-col gap-1.5">
                                        @foreach ($g['items'] as $r)
                                            <div class="h-8 flex items-center justify-center">{{ $r['jumlah_peserta'] }}</div>
                                        @endforeach
                                    </div>
                                </td>

                                {{-- Total --}}
                                <td class="px-3 py-4 text-right whitespace-nowrap">
                                    <div class="flex flex-col gap-1.5">
                                        @foreach ($g['items'] as $r)
                                            <div class="h-8 flex items-center justify-end">
                                                Rp {{ number_format($r['total'], 0, ',', '.') }}
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                {{-- Aksi --}}
                                <td class="px-3 py-4 text-right">
                                    <div class="flex flex-col gap-1.5 items-end">
                                        @foreach ($g['items'] as $r)
                                            <div class="h-8 flex items-center justify-end gap-1.5">
                                                <a href="{{ $r['pdf_url'] }}" target="_blank"
                                                    class="inline-flex items-center gap-1.5 px-3 h-8 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-lg transition"
                                                    title="Buka / Unduh PDF {{ $r['status'] }}">
                                                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                    </svg>
                                                    <span>PDF</span>
                                                </a>

                                                @if ($r['edit_url'])
                                                    <a href="{{ $r['edit_url'] }}"
                                                        class="inline-flex items-center justify-center w-8 h-8 text-gray-500 hover:text-indigo-600 bg-gray-50 hover:bg-indigo-50 border border-gray-200 hover:border-indigo-200 rounded-lg transition"
                                                        title="Edit Nominatif {{ $r['status'] }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </a>
                                                @endif

                                                @if ($r['delete_url'])
                                                    <form method="POST" action="{{ $r['delete_url'] }}" class="inline-flex m-0"
                                                        onsubmit="return confirm('Hapus nominatif {{ $r['status'] }} ini beserta seluruh pesertanya?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="inline-flex items-center justify-center w-8 h-8 text-gray-500 hover:text-rose-600 bg-gray-50 hover:bg-rose-50 border border-gray-200 hover:border-rose-200 rounded-lg transition cursor-pointer"
                                                            title="Hapus Nominatif {{ $r['status'] }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                    <p class="text-sm font-medium">Belum ada nominatif.</p>
                                    <p class="text-xs mt-1">Klik "Buat Nominatif" untuk membuat yang pertama.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div id="nom-no-result" class="hidden px-6 py-12 text-center text-gray-400">
                    <p class="text-sm font-medium">Tidak ada nominatif yang cocok dengan filter.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const qInput = document.getElementById('nom-filter-q');
            const statusSelect = document.getElementById('nom-filter-status');
            const resetBtn = document.getElementById('nom-filter-reset');
            const rows = Array.from(document.querySelectorAll('.js-nom-row'));
            const noResult = document.getElementById('nom-no-result');
            const table = document.getElementById('nom-table');
            const countEl = document.getElementById('nom-visible-count');

            if (rows.length === 0) return;

            function applyFilter() {
                const q = qInput.value.trim().toLowerCase();
                const status = statusSelect.value;
                let visible = 0;

                rows.forEach(function (row) {
                    const matchesQuery = (q === '' || row.dataset.search.includes(q));
                    // data-status berformat "|PNS|Non PNS|" supaya "Non PNS" tidak bentrok dengan "PNS"
                    const matchesStatus = (status === '' || row.dataset.status.includes('|' + status + '|'));
                    const ok = matchesQuery && matchesStatus;

                    row.style.display = ok ? '' : 'none';
                    if (ok) {
                        visible++;
                        const numEl = row.querySelector('.js-nom-number');
                        if (numEl) numEl.textContent = visible;
                    }
                });

                countEl.textContent = visible;
                noResult.classList.toggle('hidden', visible > 0);
                table.style.display = visible > 0 ? '' : 'none';
            }

            qInput.addEventListener('input', applyFilter);
            statusSelect.addEventListener('change', applyFilter);
            resetBtn.addEventListener('click', function () {
                qInput.value = '';
                statusSelect.value = '';
                applyFilter();
            });
        })();
    </script>
</x-app-layout>