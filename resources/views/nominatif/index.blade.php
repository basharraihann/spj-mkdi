<x-app-layout title="Nominatif">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        // Hitung data tampilan sekali saja
        $groups = $groups->map(function ($g) {
            $statuses = '|' . collect($g['items'])->pluck('status')->implode('|') . '|';
            $search = strtolower(
                $g['uraian_kegiatan'] . ' ' . $g['tujuan'] . ' ' .
                collect($g['items'])->map(fn($i) => $i['status'] . ($i['status'] === 'Honorarium' ? ' narasumber' : ''))->implode(' ')
            );

            return $g + ['statuses' => $statuses, 'search' => $search];
        });

        $badgeClass = fn($status) => $status === 'PNS'
            ? 'bg-blue-50 text-blue-700 border-blue-200'
            : ($status === 'Honorarium'
                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                : 'bg-amber-50 text-amber-700 border-amber-200');

        $statusLabel = fn($status) => $status === 'Honorarium' ? 'Honorarium' : $status;
    @endphp

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-5">


        {{-- ============ BAGIAN ATAS: judul + filter dalam satu kartu ringkas ============ --}}
        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-4 sm:p-6 space-y-5">

            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="min-w-0">
                    <h3 class="text-lg font-bold text-gray-800">Nominatif</h3>
                    <p class="text-sm text-gray-500">Nominatif perjalanan dinas dari agenda, dan daftar honorarium
                        narasumber yang dibuat mandiri.</p>
                </div>
                <span
                    class="text-xs font-semibold text-gray-500 bg-gray-50 border border-gray-100 rounded-full px-3 py-1.5"
                    aria-live="polite">
                    <span id="nom-visible-count">{{ $groups->count() }}</span> dari {{ $groups->count() }} kegiatan
                </span>
            </div>

            <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="nom-filter-q" class="text-xs font-semibold text-gray-600">Uraian / Tujuan</label>
                    <input type="text" id="nom-filter-q" autocomplete="off" placeholder="Ketik uraian atau tujuan..."
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-72 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                </div>

                <div class="flex flex-col gap-1 flex-1 sm:flex-none">
                    <label for="nom-filter-status" class="text-xs font-semibold text-gray-600">Status</label>
                    <select id="nom-filter-status"
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-56 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                        <option value="">Semua Status</option>
                        <option value="PNS">PNS</option>
                        <option value="Non PNS">Non PNS</option>
                        <option value="Honorarium">Honorarium Narasumber</option>
                    </select>
                </div>

                <button type="button" id="nom-filter-reset"
                    class="text-sm text-gray-500 hover:text-gray-700 transition px-1 py-2 sm:py-0 cursor-pointer">
                    Reset
                </button>

                <div class="hidden sm:block flex-1"></div>

                <a href="{{ route('nominatif.create') }}"
                    class="inline-flex w-full sm:w-auto justify-center items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm shadow-md shadow-blue-600/20 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Nominatif Honorarium
                </a>
                <a href="{{ route('nominatif.custom.create') }}"
                    class="inline-flex items-center gap-1.5 border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold px-4 py-2 rounded-xl text-sm">
                    + Nominatif Custom
                </a>
            </div>
        </div>

        {{-- ============ DAFTAR: satu kartu per kegiatan ============ --}}
        <div class="space-y-3">

            {{-- Judul kolom (hanya layar sangat lebar, sejajar dengan isi kartu) --}}
            <div
                class="hidden xl:grid grid-cols-[minmax(0,1fr)_37rem] gap-6 px-5 text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                <div>Kegiatan</div>
                <div class="grid grid-cols-[9.5rem_3.5rem_minmax(0,1fr)_12.5rem] gap-3 px-3">
                    <div>Status</div>
                    <div class="text-center">Orang</div>
                    <div class="text-right">Total</div>
                    <div class="text-right">Aksi</div>
                </div>
            </div>

            <div id="nom-list" class="space-y-3">
                @forelse ($groups as $g)
                    <article
                        class="js-nom-item bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5 grid gap-4 xl:grid-cols-[minmax(0,1fr)_37rem] xl:gap-6"
                        data-search="{{ $g['search'] }}" data-status="{{ $g['statuses'] }}">

                        {{-- Info kegiatan --}}
                        <div class="flex gap-3 min-w-0">
                            <span
                                class="js-nom-number inline-flex h-7 min-w-7 px-2 items-center justify-center rounded-lg bg-gray-100 text-xs font-semibold text-gray-500 tabular-nums shrink-0">{{ $loop->iteration }}</span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-gray-800 leading-snug break-words">
                                    {{ $g['uraian_kegiatan'] }}
                                </h3>
                                <dl class="mt-2 space-y-1 text-xs">
                                    <div class="flex gap-2">
                                        <dt
                                            class="w-16 shrink-0 text-[10px] font-semibold uppercase tracking-wide text-gray-400 pt-px">
                                            Tujuan</dt>
                                        <dd class="text-gray-700 font-medium break-words min-w-0">{{ $g['tujuan'] ?: '-' }}
                                        </dd>
                                    </div>
                                    <div class="flex gap-2">
                                        <dt
                                            class="w-16 shrink-0 text-[10px] font-semibold uppercase tracking-wide text-gray-400 pt-px">
                                            Tanggal</dt>
                                        <dd class="text-gray-700 font-medium break-words min-w-0">{{ $g['tanggal'] ?: '-' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        {{-- Nominatif per status --}}
                        <div class="space-y-2 min-w-0">
                            @foreach ($g['items'] as $r)
                                <div class="js-nom-sub rounded-xl bg-gray-50 border border-gray-200 p-3 flex flex-wrap items-center gap-x-3 gap-y-3 xl:grid xl:grid-cols-[9.5rem_3.5rem_minmax(0,1fr)_12.5rem]"
                                    data-item-status="{{ $r['status'] }}">

                                    <span
                                        class="inline-flex items-center justify-self-start whitespace-nowrap px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $badgeClass($r['status']) }}">
                                        {{ $statusLabel($r['status']) }}
                                    </span>

                                    <span class="text-xs text-gray-500 xl:text-center tabular-nums">
                                        {{ $r['jumlah_peserta'] }}<span class="xl:hidden"> orang</span>
                                    </span>

                                    <span
                                        class="text-xs font-medium text-gray-700 text-right tabular-nums whitespace-nowrap ml-auto xl:ml-0">
                                        Rp {{ number_format($r['total'], 0, ',', '.') }}
                                    </span>

                                    <div class="w-full sm:w-auto xl:w-full flex items-center gap-1.5 xl:justify-end">
                                        <a href="{{ $r['pdf_url'] }}" target="_blank" rel="noopener"
                                            class="inline-flex flex-1 sm:flex-none items-center justify-center gap-1 px-2.5 py-2 xl:py-1 text-[11px] font-semibold text-blue-600 bg-white hover:bg-blue-50 border border-blue-500 rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                                            title="Buka / unduh PDF {{ $statusLabel($r['status']) }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            PDF
                                        </a>

                                        @if ($r['edit_url'])
                                            <a href="{{ $r['edit_url'] }}"
                                                class="inline-flex flex-1 sm:flex-none items-center justify-center px-2.5 py-2 xl:py-1 text-[11px] font-semibold text-gray-600 bg-white hover:bg-gray-100 border border-gray-300 rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                                                title="Edit nominatif {{ $statusLabel($r['status']) }}">
                                                Edit
                                            </a>
                                        @endif

                                        @if ($r['delete_url'])
                                            <form method="POST" action="{{ $r['delete_url'] }}" class="flex flex-1 sm:flex-none m-0"
                                                onsubmit="return confirm('Hapus nominatif {{ $r['status'] }} ini beserta seluruh pesertanya?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex flex-1 items-center justify-center px-2.5 py-2 xl:py-1 text-[11px] font-semibold text-red-500 bg-white hover:bg-red-50 border border-red-400 rounded-md transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500"
                                                    title="Hapus nominatif {{ $statusLabel($r['status']) }}">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div class="bg-white rounded-2xl border border-gray-200 px-4 py-14 text-center">
                        <p class="text-sm font-semibold text-gray-800">Belum ada nominatif</p>
                        <p class="text-xs text-gray-500 mt-1">Klik "Buat Nominatif" untuk membuat yang pertama.</p>
                    </div>
                @endforelse
            </div>

            <div id="nom-no-result" class="hidden bg-white rounded-2xl border border-gray-200 px-4 py-14 text-center">
                <p class="text-sm font-semibold text-gray-800">Tidak ada nominatif yang cocok</p>
                <p class="text-xs text-gray-500 mt-1">Ubah kata kunci atau klik Reset untuk menampilkan semua.</p>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const qInput = document.getElementById('nom-filter-q');
            const statusSelect = document.getElementById('nom-filter-status');
            const resetBtn = document.getElementById('nom-filter-reset');
            const items = Array.from(document.querySelectorAll('.js-nom-item'));
            const noResult = document.getElementById('nom-no-result');
            const countEl = document.getElementById('nom-visible-count');

            if (items.length === 0) return;

            function applyFilter() {
                const q = qInput.value.trim().toLowerCase();
                const status = statusSelect.value;
                let visible = 0;

                items.forEach(function (item) {
                    const matchesQuery = q === '' || item.dataset.search.includes(q);
                    // data-status berformat "|PNS|Non PNS|" supaya "Non PNS" tidak bentrok dengan "PNS"
                    const matchesStatus = status === '' || item.dataset.status.includes('|' + status + '|');
                    const ok = matchesQuery && matchesStatus;

                    item.style.display = ok ? '' : 'none';
                    if (!ok) return;

                    visible++;
                    item.querySelector('.js-nom-number').textContent = visible;

                    // Saat filter status aktif, tampilkan hanya baris status yang dipilih
                    item.querySelectorAll('.js-nom-sub').forEach(function (sub) {
                        sub.style.display = (status === '' || sub.dataset.itemStatus === status) ? '' : 'none';
                    });
                });

                countEl.textContent = visible;
                noResult.classList.toggle('hidden', visible > 0);
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