<x-app-layout title="Pemetaan Akun ke Pegawai">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pemetaan Akun ke Pegawai') }}
        </h2>
    </x-slot>

    @php
        // Kolom daftar (dipakai header & baris supaya selalu sejajar)
        $cols = 'lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1.6fr)_9rem_6.5rem]';

        $totalUsers = $users->count();
        $totalDipetakan = $users->whereNotNull('pegawai_id')->count();
        $totalBelum = $totalUsers - $totalDipetakan;
        $totalSaran = $users->filter(fn($x) => !$x->pegawai_id && isset($saran[$x->id]))->count();

        $selectClass =
            'w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 bg-white focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition';
        $labelClass = 'text-xs font-semibold text-gray-600';
        $mobileLabelClass = 'lg:hidden text-[11px] font-semibold text-gray-500 mb-1 block';
    @endphp

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto">

        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-4 sm:p-6 space-y-5">

            {{-- Judul + jumlah --}}
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="min-w-0">
                    <h3 class="text-lg font-bold text-gray-800">Pemetaan Akun ke Pegawai</h3>
                    <p class="text-sm text-gray-500">Hubungkan akun pengguna dengan data pegawai.</p>
                </div>
                <span
                    class="text-xs font-semibold text-gray-500 bg-gray-50 border border-gray-100 rounded-full px-3 py-1.5"
                    aria-live="polite">
                    <span id="map-visible-count">{{ $totalUsers }}</span> dari {{ $totalUsers }} akun
                </span>
            </div>

            {{-- Penjelasan --}}
            <div class="flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50/50 px-4 py-3">
                <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-gray-700 leading-relaxed">
                    Akun yang dipetakan ke pegawai otomatis mendapat tugas <span class="font-semibold">"Isi
                        Laporan"</span> saat ST yang memuat nama pegawai itu diterbitkan.
                    Satu pegawai hanya bisa dipetakan ke satu akun.
                </p>
            </div>

            {{-- Error --}}
            @if ($errors->any())
                <div role="alert"
                    class="flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-xs text-red-700">
                    <svg class="w-4 h-4 shrink-0 mt-px" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                    <ul class="space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Ringkasan --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="rounded-xl bg-gray-50 border border-gray-100 px-3 sm:px-4 py-3">
                    <p class="text-xs font-medium text-gray-600">Total akun</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5 tabular-nums">{{ $totalUsers }}</p>
                </div>
                <div class="rounded-xl bg-emerald-50/60 border border-emerald-100 px-3 sm:px-4 py-3">
                    <p class="text-xs font-medium text-gray-600">Sudah dipetakan</p>
                    <p class="text-xl font-bold text-emerald-700 mt-0.5 tabular-nums">{{ $totalDipetakan }}</p>
                </div>
                <div class="rounded-xl bg-red-50/60 border border-red-100 px-3 sm:px-4 py-3">
                    <p class="text-xs font-medium text-gray-600">Belum dipetakan</p>
                    <p class="text-xl font-bold text-red-700 mt-0.5 tabular-nums">{{ $totalBelum }}</p>
                </div>
                <div class="rounded-xl bg-amber-50/60 border border-amber-100 px-3 sm:px-4 py-3">
                    <p class="text-xs font-medium text-gray-600">Ada saran nama</p>
                    <p class="text-xl font-bold text-amber-700 mt-0.5 tabular-nums">{{ $totalSaran }}</p>
                </div>
            </div>

            {{-- Filter instan, tanpa reload --}}
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="map-filter-q" class="{{ $labelClass }}">Cari</label>
                    <input type="text" id="map-filter-q" autocomplete="off" placeholder="Nama atau email akun..."
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-64 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                </div>

                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="map-filter-status" class="{{ $labelClass }}">Status</label>
                    <select id="map-filter-status"
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-52 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                        <option value="">Semua Status</option>
                        <option value="dipetakan">Sudah dipetakan</option>
                        <option value="belum">Belum dipetakan</option>
                        <option value="saran">Ada saran nama</option>
                    </select>
                </div>

                <button type="button" id="map-filter-reset"
                    class="hidden text-sm text-gray-500 hover:text-gray-700 transition px-2 py-2 rounded-md cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                    Reset
                </button>
            </div>

            {{-- Daftar: seperti tabel di layar lebar, bertumpuk di HP & tablet --}}
            <div class="border border-gray-100 rounded-xl overflow-hidden">

                {{-- Judul kolom (hanya layar lebar) --}}
                <div class="hidden lg:grid {{ $cols }} gap-4 bg-gray-50 px-4 py-3 text-xs font-semibold text-gray-500">
                    <div>Akun</div>
                    <div>Pegawai</div>
                    <div>Status</div>
                    <div class="text-right">Aksi</div>
                </div>

                <div id="map-list" class="divide-y divide-gray-100">
                    @forelse ($users as $u)
                        @php
                            $adaSaran = !$u->pegawai_id && isset($saran[$u->id]);
                            $pilih = old('pegawai_id', $u->pegawai_id ?? ($saran[$u->id] ?? null));
                            $status = $u->pegawai_id ? 'dipetakan' : ($adaSaran ? 'saran' : 'belum');
                        @endphp

                        <form method="POST" action="{{ route('pemetaan-akun.update', $u) }}"
                            class="js-map-row {{ $adaSaran ? 'bg-amber-50/40' : 'bg-white' }} hover:bg-gray-50/60 transition px-4 py-4 grid grid-cols-2 gap-x-4 gap-y-3 lg:items-center {{ $cols }}"
                            data-search="{{ mb_strtolower($u->name . ' ' . $u->email) }}" data-status="{{ $status }}">
                            @csrf
                            @method('PUT')

                            {{-- Akun --}}
                            <div class="col-span-2 lg:col-span-1 flex items-center gap-3 min-w-0">
                                <div
                                    class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 break-words">{{ $u->name }}</p>
                                    <p class="text-xs text-gray-500 break-all">{{ $u->email }}</p>
                                </div>
                            </div>

                            {{-- Pegawai --}}
                            <div class="col-span-2 lg:col-span-1 min-w-0">
                                <label for="pegawai-{{ $u->id }}" class="{{ $mobileLabelClass }}">Pegawai</label>
                                <select id="pegawai-{{ $u->id }}" name="pegawai_id" data-original="{{ $u->pegawai_id }}"
                                    class="js-map-select {{ $selectClass }}">
                                    <option value="">— Belum dipetakan —</option>
                                    @foreach ($pegawai as $p)
                                        @php $dipakaiLain = $terpakai->has($p->id) && (int) $u->pegawai_id !== (int) $p->id; @endphp
                                        <option value="{{ $p->id }}" @selected((int) $pilih === (int) $p->id)
                                            @disabled($dipakaiLain)>
                                            {{ $p->nama_gelar ?: $p->nama }}{{ $p->nip ? ' — ' . $p->nip : '' }}{{ $dipakaiLain ? ' (dipakai ' . $terpakai[$p->id] . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>

                                @if ($adaSaran)
                                    <p class="flex items-start gap-1.5 text-xs text-amber-800 mt-1.5">
                                        <svg class="w-3.5 h-3.5 shrink-0 mt-px" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                        <span>Saran dari kesamaan nama, belum tersimpan. Periksa lalu tekan
                                            Simpan.</span>
                                    </p>
                                @endif
                            </div>

                            {{-- Status --}}
                            <div class="col-span-1 min-w-0">
                                <p class="{{ $mobileLabelClass }}">Status</p>
                                @if ($status === 'dipetakan')
                                    <span
                                        class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        Dipetakan
                                    </span>
                                @elseif ($status === 'saran')
                                    <span
                                        class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Ada saran
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-50 text-gray-600 border border-gray-200">
                                        Belum dipetakan
                                    </span>
                                @endif
                            </div>

                            {{-- Aksi --}}
                            <div class="col-span-1 flex items-center lg:justify-end">
                                <button type="submit" disabled title="Simpan pemetaan {{ $u->name }}"
                                    class="js-map-save inline-flex w-full lg:w-auto items-center justify-center px-4 py-2 lg:py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 border border-blue-600 rounded-md transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-1 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-blue-600">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    @empty
                        <div class="px-5 py-14 text-center text-gray-500">
                            <p class="text-sm font-semibold text-gray-800">Belum ada akun untuk dipetakan.</p>
                            <p class="text-xs text-gray-500 mt-1">Tambahkan akun terlebih dahulu di menu User
                                Manajemen.</p>
                        </div>
                    @endforelse
                </div>

                <div id="map-no-result" class="hidden px-5 py-14 text-center text-gray-500">
                    <p class="text-sm font-semibold text-gray-800">Tidak ada akun yang cocok dengan filter.</p>
                    <p class="text-xs text-gray-500 mt-1">Ubah kata kunci atau klik Reset untuk menampilkan semua.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const rows = Array.from(document.querySelectorAll('.js-map-row'));
            if (rows.length === 0) return;

            // Tombol Simpan aktif hanya jika pilihan berbeda dari data tersimpan
            // (termasuk saran nama yang belum tersimpan)
            rows.forEach(function (row) {
                const select = row.querySelector('.js-map-select');
                const btn = row.querySelector('.js-map-save');

                function check() {
                    btn.disabled = select.value === (select.dataset.original || '');
                }

                select.addEventListener('change', check);
                check();
            });

            // Search & filter instan
            const qInput = document.getElementById('map-filter-q');
            const statusSelect = document.getElementById('map-filter-status');
            const resetBtn = document.getElementById('map-filter-reset');
            const list = document.getElementById('map-list');
            const noResult = document.getElementById('map-no-result');
            const countEl = document.getElementById('map-visible-count');

            function applyFilter() {
                const q = qInput.value.trim().toLowerCase();
                const status = statusSelect.value;
                let visible = 0;

                rows.forEach(function (row) {
                    const ok = (q === '' || row.dataset.search.includes(q))
                        && (status === '' || row.dataset.status === status);

                    row.style.display = ok ? '' : 'none';
                    if (ok) visible++;
                });

                countEl.textContent = visible;
                noResult.classList.toggle('hidden', visible > 0);
                list.style.display = visible > 0 ? '' : 'none';
                resetBtn.classList.toggle('hidden', q === '' && status === '');
            }

            qInput.addEventListener('input', applyFilter);
            statusSelect.addEventListener('change', applyFilter);

            resetBtn.addEventListener('click', function () {
                qInput.value = '';
                statusSelect.value = '';
                applyFilter();
                qInput.focus();
            });
        })();
    </script>
</x-app-layout>