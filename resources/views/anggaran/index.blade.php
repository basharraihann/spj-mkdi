<x-app-layout title="Anggaran">

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Laporan Ketersediaan Dana
        </h2>
    </x-slot>

    <style>
        /* Tabel: di layar kecil bisa digeser ke samping, di desktop muat penuh */
        .ang-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .ang-scroll table {
            min-width: 1250px;
        }

        @media (min-width: 1024px) {
            .ang-scroll {
                overflow-x: visible;
            }

            .ang-scroll table {
                min-width: 0;
            }
        }

        /* Tabel bergaris: garis tipis antar baris dan antar kolom */
        .ang-scroll table {
            border-collapse: collapse;
        }

        .ang-scroll td,
        .ang-scroll th {
            border: 1px solid #e2e8f0;
        }

        .ang-scroll th {
            border-color: rgb(255 255 255 / .25);
        }

        .ang-scroll tr> :first-child {
            border-left: 0;
        }

        .ang-scroll tr> :last-child {
            border-right: 0;
        }

        .ang-scroll thead tr>* {
            border-top: 0;
        }

        .ang-scroll tbody tr:last-child>* {
            border-bottom: 0;
        }

        /* Kartu unit: jumlah kolom menyesuaikan banyaknya unit, kartu selalu mengisi baris */
        .unit-grid {
            display: grid;
            gap: 1rem;
            align-items: start;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }

        .card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 0 0 1px rgb(226 232 240 / .8), 0 1px 2px rgb(15 23 42 / .04);
        }

        .tree-row {
            transition: background-color .12s ease;
        }

        .tree-row:hover {
            background-color: rgb(241 245 249 / .9);
        }

        .tree-row .row-actions {
            opacity: 0;
            transition: opacity .12s ease;
        }

        .tree-row:hover .row-actions,
        .tree-row:focus-within .row-actions {
            opacity: 1;
        }

        .chev {
            transition: transform .15s ease;
        }

        .chev.is-open {
            transform: rotate(90deg);
        }

        .ring-fg {
            transition: stroke-dashoffset .5s ease;
        }

        @media (prefers-reduced-motion: reduce) {

            .tree-row,
            .chev,
            .ring-fg,
            .tree-row .row-actions {
                transition: none;
            }
        }
    </style>

    <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- ===== Subjudul & aksi (judul sekarang ada di slot header) ===== --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="min-w-0">
                <p id="ang-meta" class="text-sm font-medium text-slate-600">Memuat data...</p>
                <p id="ang-updated" class="mt-1 hidden text-xs text-slate-400">
                    <span id="ang-updated-text"></span>
                </p>
            </div>

            <div class="shrink-0">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" id="btn-source"
                        class="inline-flex justify-center items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition shadow-sm">
                        <svg class="w-4 h-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                        Sumber data
                    </button>

                    <a id="btn-export" href="{{ route('anggaran.export') }}"
                        class="inline-flex justify-center items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition shadow-sm">
                        <svg class="w-4 h-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Export Excel
                    </a>

                    @if ($canUpload)
                        <a href="{{ route('anggaran.upload') }}"
                            class="inline-flex justify-center items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 shrink-0 transition shadow-sm">
                            <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            Upload / Perbarui Data
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <p id="ang-empty" class="card hidden py-16 text-center text-slate-400 text-sm">
            Belum ada data anggaran. @if ($canUpload) Silakan upload file dari SAKTI. @endif
        </p>

        <div id="ang-body" class="hidden space-y-6">

            {{-- ===== Ringkasan ===== --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="card flex items-center gap-4 p-5">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100"><svg
                            class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                        </svg></span>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500">Total Anggaran (Pagu)</p>
                        <p id="s-pagu" class="mt-1 break-words font-bold text-slate-900 tabular-nums"
                            style="font-size:clamp(15px,1.3vw,18px)">-</p>
                    </div>
                </div>
                <div class="card flex items-center gap-4 p-5">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100"><svg
                            class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                        </svg></span>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500">Sudah Terpakai (Realisasi)</p>
                        <p id="s-real" class="mt-1 break-words font-bold text-slate-900 tabular-nums"
                            style="font-size:clamp(15px,1.3vw,18px)">-</p>
                    </div>
                </div>
                <div class="card flex items-center gap-4 p-5">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100"><svg
                            class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" />
                        </svg></span>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500">Sisa Anggaran</p>
                        <p id="s-sisa" class="mt-1 break-words font-bold text-slate-900 tabular-nums"
                            style="font-size:clamp(15px,1.3vw,18px)">-</p>
                    </div>
                </div>
                <div class="card flex items-center gap-4 p-5">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100"><svg
                            class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185ZM9.75 9h.008v.008H9.75V9Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008V13.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-slate-500">Penyerapan (% Terpakai)</p>
                        <p id="s-pct" class="mt-1 text-lg font-bold text-slate-900 tabular-nums">-</p>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div id="s-bar" class="h-full rounded-full bg-blue-600 transition-all" style="width:0%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Grafik ===== --}}
            <div class="card p-5 space-y-6">
                <div>
                    <h3 class="text-base font-semibold text-slate-800">Grafik Anggaran</h3>
                    <p class="text-xs text-slate-400">Mengikuti filter Kegiatan sampai Akun dan kolom pencarian. Grafik
                        per unit selalu menampilkan semua unit supaya bisa dibandingkan. Klik batang unit untuk memilih
                        unit (klik lagi untuk melepas), klik batang akun untuk memfilter akun. M = miliar, jt = juta.
                    </p>
                </div>

                <div>
                    <p class="mb-2 text-sm font-medium text-slate-700">Realisasi Seluruh Unit</p>
                    <div class="relative h-24"><canvas id="ch-total"></canvas></div>
                </div>

                <div class="grid grid-cols-1 gap-8 xl:grid-cols-2">
                    <div>
                        <div class="mb-2 flex flex-wrap items-center gap-3">
                            <div class="inline-flex items-center rounded-xl bg-slate-100 p-0.5"
                                title="Biro dan Inspektorat">
                                <button type="button" data-chgroup="1"
                                    class="rounded-[10px] px-3 py-1 text-xs font-medium transition">Gabung</button>
                                <button type="button" data-chgroup="0"
                                    class="rounded-[10px] px-3 py-1 text-xs font-medium transition">Pisah</button>
                            </div>
                            <p class="text-sm font-medium text-slate-700">Realisasi per Unit Kerja (% dari pagu)</p>
                        </div>
                        <div id="ch-unit-wrap" class="relative h-72"><canvas id="ch-unit"></canvas></div>
                    </div>
                    <div>
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-medium text-slate-700">Komposisi Pagu per Unit Kerja</p>
                            <div class="inline-flex items-center rounded-xl bg-slate-100 p-0.5">
                                <button type="button" data-chmode="rp"
                                    class="rounded-[10px] px-3 py-1 text-xs font-medium transition">Nominal</button>
                                <button type="button" data-chmode="pct"
                                    class="rounded-[10px] px-3 py-1 text-xs font-medium transition">Persen</button>
                            </div>
                        </div>
                        <div id="ch-stack-wrap" class="relative h-72"><canvas id="ch-stack"></canvas></div>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-sm font-medium text-slate-700">10 Akun Teratas</p>
                            <p class="text-xs text-slate-400">Bagian ini juga mengikuti filter Unit Kerja.</p>
                        </div>
                        <select id="ch-akun-metric"
                            class="rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs text-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                            <option value="efektif">Sisa Efektif terbesar</option>
                            <option value="out">Outstanding terbesar</option>
                        </select>
                    </div>
                    <div class="relative h-[360px]"><canvas id="ch-akun"></canvas></div>
                </div>
            </div>

            {{-- ===== Filter ===== --}}
            <div class="card p-5 space-y-4">
                <div class="border-b border-slate-100 pb-4">
                    <div class="mb-2.5 flex flex-wrap items-center justify-between gap-2">
                        <label class="text-sm font-semibold text-slate-800">Unit Kerja <span id="unit-count"
                                class="font-normal text-slate-400"></span></label>
                        <div class="inline-flex items-center rounded-xl bg-slate-100 p-0.5">
                            <button type="button" id="btn-unit-all" style="background:#2563eb;color:#fff"
                                class="rounded-[10px] px-3.5 py-1.5 text-xs font-medium shadow-sm transition hover:opacity-90">Pilih
                                semua</button>
                            <button type="button" id="btn-unit-none"
                                class="rounded-[10px] px-3.5 py-1.5 text-xs font-medium text-slate-600 transition hover:text-slate-900">Kosongkan</button>
                        </div>
                    </div>
                    <div id="unit-checks" class="space-y-2"></div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-end">
                    <div class="lg:col-span-3">
                        <label class="block text-xs text-slate-500 mb-1 font-medium">Cari kode / uraian</label>
                        <div class="relative">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                            <input type="text" id="ang-q" placeholder="mis. 524, CL.7458, ABB.001 atau konsumsi"
                                autocomplete="off"
                                class="w-full rounded-xl border border-slate-200 py-2.5 pl-9 pr-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white">
                        </div>
                    </div>
                    <div class="lg:col-span-3">
                        <label class="block text-xs text-slate-500 mb-1 font-medium">Akun</label>
                        <select id="f-akun"
                            class="w-full truncate rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white"></select>
                    </div>
                    <div class="lg:col-span-3">
                        <label class="block text-xs text-slate-500 mb-1 font-medium">Kegiatan</label>
                        <select id="f-keg"
                            class="w-full truncate rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white"></select>
                    </div>
                    <div class="lg:col-span-3 flex min-w-0 flex-wrap items-center gap-2">
                        <button type="button" id="btn-adv" aria-expanded="false"
                            class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                            </svg>
                            Filter lanjutan
                            <span id="adv-badge"
                                class="hidden rounded-full bg-blue-600 px-1.5 text-[10px] font-semibold leading-4 text-white"></span>
                        </button>
                        <button type="button" id="btn-reset"
                            class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition"><svg
                                class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>Reset</button>
                    </div>
                </div>

                <div id="adv-panel" class="hidden grid grid-cols-1 sm:grid-cols-3 gap-3 border-t border-slate-100 pt-4">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1 font-medium">RO / SubOutput</label>
                        <select id="f-ro"
                            class="w-full truncate rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white"></select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1 font-medium">Komponen</label>
                        <select id="f-komp"
                            class="w-full truncate rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white"></select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1 font-medium">SubKomponen</label>
                        <select id="f-sub"
                            class="w-full truncate rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white"></select>
                    </div>
                </div>

            </div>

            {{-- ===== Per unit kerja (dikelompokkan per induk) ===== --}}
            <div id="section-unit" class="hidden space-y-3">
                <div>
                    <h3 class="text-base font-semibold text-slate-800">Anggaran per Unit Kerja</h3>
                    <p id="unit-sub" class="text-xs text-slate-400">Kartu muncul untuk unit yang dicentang pada filter
                        Unit Kerja di atas.</p>
                </div>
                <div id="unit-cards"></div>
            </div>

            {{-- ===== Tabel hierarki ===== --}}
            <div class="card [overflow:clip]">
                <div
                    class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 px-5 py-3 border-b border-slate-100">
                    <div id="ang-hint" class="text-xs text-slate-500 flex flex-wrap items-center gap-x-4 gap-y-1">
                    </div>
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <button type="button" id="btn-expand"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm hover:bg-slate-50 transition">
                            <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                            </svg>
                            Buka semua
                        </button>
                        <button type="button" id="btn-collapse"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm hover:bg-slate-50 transition">
                            <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5 5.25 5.25" />
                            </svg>
                            Tutup semua
                        </button>
                        <label class="inline-flex items-center gap-2 text-xs text-slate-500">Level
                            <select id="f-level"
                                class="rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs text-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                <option value="all">Semua (sampai item)</option>
                                <option value="prog">Program</option>
                                <option value="keg">Kegiatan</option>
                                <option value="ro">RO / Sub Output</option>
                                <option value="komp">Komponen</option>
                                <option value="sub">Sub Komponen</option>
                                <option value="akun">Akun</option>
                                <option value="item">Item</option>
                            </select>
                        </label>
                    </div>
                </div>

                {{-- Jalur induk dari baris yang dipilih --}}
                <div id="path-bar"
                    class="hidden px-5 py-2.5 bg-slate-50 border-b border-slate-200 text-xs text-slate-700 flex flex-wrap items-center gap-x-2 gap-y-1">
                </div>
                {{-- Lebar kolom dipatok dengan persen, kolom angka sengaja dibuat lega supaya angka tebal tidak
                menempel --}}
                <div class="ang-scroll">
                    <table class="w-full table-fixed text-[11.5px] leading-tight tabular-nums">
                        <colgroup>
                            <col style="width:10.5%"> {{-- Kode --}}
                            <col style="width:16%"> {{-- Uraian --}}
                            <col style="width:6%"> {{-- Unit --}}
                            <col style="width:9.5%"> {{-- Pagu Revisi --}}
                            <col style="width:9.3%"> {{-- Real. Lalu --}}
                            <col style="width:8.9%"> {{-- Real. Ini --}}
                            <col style="width:9.5%"> {{-- Real. s.d. --}}
                            <col style="width:5.3%"> {{-- % --}}
                            <col style="width:8.5%"> {{-- Sisa --}}
                            <col style="width:7.8%"> {{-- Outstanding --}}
                            <col style="width:8.7%"> {{-- Sisa Efektif --}}
                        </colgroup>
                        <thead style="background:#2563eb" class="text-[11px] text-white sticky top-0 z-10">
                            <tr>
                                <th class="text-left px-2 py-2 font-semibold">Kode</th>
                                <th class="text-left px-2 py-2 font-semibold">Uraian</th>
                                <th class="text-left px-2 py-2 font-semibold">Unit</th>
                                <th class="text-right px-2 py-2 font-semibold">Pagu Revisi</th>
                                <th class="text-right px-2 py-2 font-semibold">Real. Lalu</th>
                                <th class="text-right px-2 py-2 font-semibold">Real. Ini</th>
                                <th class="text-right px-2 py-2 font-semibold">Real. s.d.</th>
                                <th class="text-right px-2 py-2 font-semibold">%</th>
                                <th class="text-right px-2 py-2 font-semibold">Sisa</th>
                                <th class="text-right px-2 py-2 font-semibold">Outstanding</th>
                                <th class="text-right px-2 py-2 font-semibold">Sisa Efektif</th>
                            </tr>
                        </thead>
                        <tbody id="ang-tb"></tbody>
                    </table>
                    <p id="ang-norow" class="hidden py-14 text-center text-sm text-slate-400">Tidak ada baris yang
                        cocok. Ubah kata kunci atau reset filter.</p>
                </div>

                <div
                    class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-3 border-t border-slate-100">
                    <p id="ang-cnt" class="text-xs text-slate-500"></p>
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <select id="f-limit" aria-label="Baris per halaman"
                            class="rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs text-slate-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                            <option value="10">10 baris</option>
                            <option value="25">25 baris</option>
                            <option value="50">50 baris</option>
                            <option value="100">100 baris</option>
                            <option value="500" selected>500 baris</option>
                            <option value="all">Semua data</option>
                        </select>
                        <button id="btn-prev" type="button"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:hover:bg-white transition">Sebelumnya</button>
                        <span id="page-info" class="sr-only">Halaman 1</span>
                        <div id="page-nums" class="flex items-center gap-1"></div>
                        <button id="btn-next" type="button"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:hover:bg-white transition">Selanjutnya
                            &rsaquo;</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Modal: Sumber data anggaran ===== --}}
    <div id="source-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog"
        aria-modal="true" aria-labelledby="source-title">
        <div id="source-backdrop" class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-7 shadow-xl">
            <div class="flex justify-center">
                <span
                    class="flex h-16 w-16 items-center justify-center rounded-full border-2 border-sky-400 text-sky-500">
                    <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 11.25v5.25m0-9.75h.008v.008H12V6.75Z" />
                    </svg>
                </span>
            </div>
            <h3 id="source-title" class="mt-5 text-center text-2xl font-semibold text-slate-800">Sumber data anggaran
            </h3>
            <p class="mt-5 text-sm leading-relaxed text-slate-600">
                Data di halaman ini diambil dari <b>SAKTI</b> (file Excel <b>FA Detail</b>) <b>di luar Blokir</b>. Nilai
                anggaran yang sedang diblokir tidak termasuk dalam angka di sini.
            </p>
            <ul class="mt-3 list-disc space-y-1.5 pl-5 text-sm leading-relaxed text-slate-600">
                <li><b>Pagu Revisi, Realisasi, Sisa:</b> dari FA Detail SAKTI per tanggal laporan.</li>
                <li><b>Outstanding:</b> memo di aplikasi ini yang belum diinput ke SAKTI.</li>
                <li><b>Sisa Efektif</b> = Sisa &minus; Outstanding, yang masih bisa dipakai untuk memo baru.</li>
            </ul>
            <div class="mt-6 flex justify-center">
                <button type="button" id="btn-source-close"
                    class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition">Mengerti</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const CH = ['keg', 'ro', 'komp', 'sub', 'akun'];
            const $ = (id) => document.getElementById(id);
            const rp = (n) => new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const escRe = (s) => s.replace(/[.*+?^$()|[\]\\]/g, '\\$&').replace(/[{}]/g, '\\$&');
            const F = { keg: '', ro: '', komp: '', sub: '', akun: '' };

            // Filter unit kerja (dari dropdown atau klik kartu).
            // unit = kelompok/induk (mis. D1 atau BIRO UHM), sub = unit kerja di dalamnya (mis. "Asdep 1 D1").
            const U = { units: new Set(), sub: '' };
            const openUnits = new Set();   // kelompok yang rinciannya sedang dibuka
            const BELUM = 'Belum dipetakan';
            const pctTxt = (x) => x.toFixed(2).replace('.', ',') + '%';
            // Angka kartu ditampilkan penuh, tanpa pembulatan
            const short = (n) => 'Rp ' + rp(n);
            // Satu warna biru yang sama (blue-600) untuk semua progress
            const tone = () => '#2563eb';

            let items = [];
            let currentPage = 1;

            // ---- state tree ----
            let open = new Set();          // id node yang terbuka (mode normal)
            let forceClosed = new Set();   // id node yang ditutup manual saat mode cari
            let selectedId = null;         // baris yang diklik (untuk highlight induk/turunan)
            let pendingOpen = 'default';   // 'default' | 'all' | null
            let curRoot = null;
            let byId = new Map();
            let qActive = false;

            const match = (it, upTo) => CH.slice(0, upTo).every((k) => !F[k] || it[k + '_kode'] === F[k]);

            const LEVELS = [
                { key: 'prog', label: 'Program', kode: 'prog_kode', uraian: 'prog_uraian' },
                { key: 'keg', label: 'Kegiatan', kode: 'keg_kode', uraian: 'keg_uraian' },
                { key: 'ro', label: 'Sub Output', kode: 'ro_kode', uraian: 'ro_uraian' },
                { key: 'komp', label: 'Komponen', kode: 'komp_kode', uraian: 'komp_uraian' },
                { key: 'sub', label: 'Sub Komponen', kode: 'sub_kode', uraian: 'sub_uraian' },
                { key: 'akun', label: 'Akun', kode: 'akun_kode', uraian: 'akun_uraian' },
            ];
            const ITEM = { key: 'item', label: 'Item' };
            const LV_DEPTH = { prog: 0, keg: 1, ro: 2, komp: 3, sub: 4, akun: 5, item: 6 };

            // Level atas diberi latar abu-abu tipis supaya hierarki mudah dibaca
            const LV_STYLE = { prog: { row: 'bg-slate-100' }, keg: { row: 'bg-slate-50' }, ro: { row: '' }, komp: { row: '' }, sub: { row: '' }, akun: { row: '' }, item: { row: '' } };

            function lvKode(it, lv) {
                if (lv.key !== 'prog') return String(it[lv.kode] ?? '').trim();
                const p = String(it.prog_kode ?? '').trim();
                if (p) return p;
                const k = String(it.keg_kode ?? '').trim();
                return k.includes('.') ? k.split('.')[0] : 'PROG';
            }

            const NAMA_PROGRAM = {
                CL: 'Program Koordinasi Pelaksanaan Kebijakan',
                WA: 'Program Dukungan Manajemen',
            };
            function lvUraian(it, lv) {
                if (lv.key !== 'prog') return it[lv.uraian] || '';
                const u = String(it.prog_uraian ?? '').trim();
                if (u) return u;
                const k = lvKode(it, lv);
                return NAMA_PROGRAM[k] || (k === 'PROG' ? 'Program Utama' : 'Program ' + k);
            }

            const padKode = (key, k) => (key === 'komp' && /^\d+$/.test(k) && k.length < 3) ? k.padStart(3, '0') : k;
            const numOrNull = (v) => (v === undefined || v === null || v === '') ? null : Number(v);

            // ---- Outstanding (dari memo, nyambung ke MAK) ----
            // OUT: kunci MAK -> total outstanding, dikirim server lewat /anggaran/data.
            // Kunci = Kegiatan.KRO.RO.Komponen.Sub.Akun (6 bagian). Harus sama dengan kunciMak() di AnggaranController.
            let OUT = {};
            const makKey = (it) => {
                const last = (s) => String(s ?? '').split('.').pop();
                return [last(it.keg_kode), it.ro_kode, padKode('komp', String(it.komp_kode ?? '')), last(it.sub_kode), it.akun_kode].join('.');
            };

            function vals(it) {
                const lalu = numOrNull(it.realisasi_lalu), ini = numOrNull(it.realisasi_ini);
                let sd = numOrNull(it.realisasi);
                if (sd === null) sd = (lalu || 0) + (ini || 0);
                return { pagu: Number(it.pagu) || 0, lock: Number(it.lock_pagu) || 0, lalu, ini, sd };
            }
            function addV(to, v) {
                to.pagu += v.pagu; to.lock += v.lock; to.sd += v.sd;
                if (v.lalu !== null) to.lalu = (to.lalu || 0) + v.lalu;
                if (v.ini !== null) to.ini = (to.ini || 0) + v.ini;
            }
            const zero = () => ({ pagu: 0, lock: 0, lalu: null, ini: null, sd: 0 });

            // ---- bangun pohon induk -> anak ----
            function buildTree(list) {
                byId = new Map();
                const root = { id: '', depth: -1, children: [], map: new Map() };
                list.forEach((it, idx) => {
                    const v = vals(it);
                    let node = root;
                    LEVELS.forEach((lv, depth) => {
                        const raw = lvKode(it, lv);
                        let c = node.map.get(raw);
                        if (!c) {
                            const kode = padKode(lv.key, raw);
                            const uraian = lvUraian(it, lv);
                            c = {
                                id: node.id + '|' + raw, lv, depth, kode, uraian,
                                rawF: String(it[lv.kode] ?? ''),
                                // Kode dicocokkan dari awal, uraian dicocokkan "mengandung"
                                kodes: [kode.toLowerCase(), raw.toLowerCase()],
                                ur: uraian.toLowerCase(),
                                v: zero(), children: [], map: new Map(), parent: node,
                                us: new Set(), ss: new Set(),
                                // Outstanding melekat di level Akun (MAK); level atasnya dijumlah di bawah
                                out: lv.key === 'akun' ? (Number(OUT[makKey(it)]) || 0) : null,
                            };
                            node.map.set(raw, c);
                            node.children.push(c);
                            byId.set(c.id, c);
                        }
                        addV(c.v, v);
                        c.us.add(it.unit);
                        if (it.sub_unit) c.ss.add(it.sub_unit);
                        node = c;
                    });
                    const kode = String(it.item_kode ?? '').trim();
                    const uraian = it.item_uraian || '';
                    const leaf = {
                        id: node.id + '|#' + idx, lv: ITEM, depth: LEVELS.length, kode, uraian,
                        kodes: [kode.toLowerCase()], ur: uraian.toLowerCase(),
                        v, children: [], parent: node,
                        us: new Set([it.unit]), ss: it.sub_unit ? new Set([it.sub_unit]) : new Set(),
                        out: null,   // memo menempel di MAK, bukan di item
                    };
                    node.children.push(leaf);
                    byId.set(leaf.id, leaf);
                });

                // Jumlahkan outstanding dari Akun ke atas (Program sampai Sub Komponen). Item tidak ikut dijumlah.
                (function sumOut(n) {
                    if (n.lv && n.lv.key === 'item') return 0;
                    if (n.lv && n.lv.key === 'akun') return n.out;
                    let t = 0;
                    n.children.forEach((c) => { t += sumOut(c); });
                    if (n.depth >= 0) n.out = t;
                    return t;
                })(root);

                return root;
            }

            // Tandai: self = cocok kata kunci, under = dirinya/induknya cocok (turunan), vis = ditampilkan
            function mark(n, terms, under) {
                n.self = !!terms && terms.some((t) => n.ur.includes(t) || n.kodes.some((k) => k && k.startsWith(t)));
                n.under = under || n.self;
                let desc = false;
                n.children.forEach((c) => { if (mark(c, terms, n.under)) desc = true; });
                n.vis = !terms || n.under || desc;
                n.rel = !terms ? '' : (n.self ? 'match' : (n.under ? 'child' : 'parent'));
                return n.self || desc;
            }

            const isOpen = (n) => qActive ? !forceClosed.has(n.id) : open.has(n.id);
            function toggle(n) {
                const set = qActive ? forceClosed : open;
                set.has(n.id) ? set.delete(n.id) : set.add(n.id);
            }
            function openAll() { byId.forEach((n) => { if (n.children.length) open.add(n.id); }); }
            function openDefault() { open = new Set(); byId.forEach((n) => { if (n.children.length && n.depth <= 1) open.add(n.id); }); }

            // Pilihan Kegiatan..Akun hanya memuat data milik unit yang dicentang (bila ada)
            const inUnit = (it) => (!U.units.size || U.units.has(it.unit)) && (!U.sub || it.sub_unit === U.sub);
            const clip = (t, n) => t.length > n ? t.slice(0, n - 1) + '…' : t;
            function fillSel() {
                CH.forEach((k, i) => {
                    const seen = new Map();
                    items.forEach((it) => {
                        const kode = String(it[k + '_kode'] ?? '');
                        if (!inUnit(it) || !match(it, i) || seen.has(kode)) return;
                        seen.set(kode, { kode, tampil: padKode(k, kode), ur: it[k + '_uraian'] || '' });
                    });
                    // Urut per angka (numeric: true supaya 9 < 10 < 100)
                    const list = [...seen.values()].sort((a, b) => a.tampil.localeCompare(b.tampil, 'id', { numeric: true }));
                    if (F[k] && !seen.has(F[k])) F[k] = '';   // pilihan lama tidak ada di unit ini -> kosongkan

                    const sel = $('f-' + k);
                    sel.innerHTML = '<option value="">Semua</option>' +
                        list.map((o) => `<option value="${esc(o.kode)}">${esc(o.tampil + ' – ' + o.ur)}</option>`).join('');
                    sel.value = F[k];
                    buildCombo(sel, list);
                });
            }

            function buildCombo(sel, list) {
                let box = sel._combo;
                if (!box) {
                    box = document.createElement('div');
                    box.className = 'cb relative';
                    box.innerHTML = `
            <button type="button" class="cb-btn flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white py-2.5 px-3 text-left text-sm text-slate-700 shadow-sm transition hover:border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none" aria-haspopup="listbox" aria-expanded="false">
                <span class="cb-label min-w-0 truncate"></span>
                <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"/></svg>
            </button>
            <div class="cb-panel hidden absolute left-0 z-40 mt-1.5 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl ring-1 ring-slate-900/5" style="width:max(100%,min(34rem,94vw))">
                <div class="relative border-b border-slate-200 bg-white p-2">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <input type="search" class="cb-q w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 pl-8 pr-3 text-[13px] text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-500 focus:outline-none" placeholder="Cari kode atau uraian..." autocomplete="off">
                </div>
                <div style="display:grid;grid-template-columns:5rem minmax(0,1fr);gap:.75rem" class="border-b border-slate-200 bg-slate-50 px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-slate-500">
                    <span>Kode</span><span>Uraian</span>
                </div>
                <ul class="cb-list max-h-64 overflow-y-auto" role="listbox"></ul>
                <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-3 py-1">
                    <span class="cb-count text-[11px] text-slate-500"></span>
                    <button type="button" class="cb-close rounded-md px-2 py-0.5 text-[11px] font-medium text-slate-600 hover:bg-slate-200">Tutup</button>
                </div>
            </div>`;
                    sel.style.display = 'none';
                    sel.after(box);
                    sel._combo = box;

                    const btn = box.querySelector('.cb-btn'), panel = box.querySelector('.cb-panel'), q = box.querySelector('.cb-q');
                    const close = () => { panel.classList.add('hidden'); btn.setAttribute('aria-expanded', 'false'); };
                    box._close = close;

                    btn.addEventListener('click', () => {
                        const willOpen = panel.classList.contains('hidden');
                        closeAllCombos();
                        if (!willOpen) return;
                        panel.classList.remove('hidden');
                        btn.setAttribute('aria-expanded', 'true');
                        panel.style.left = '0'; panel.style.right = 'auto';
                        if (panel.getBoundingClientRect().right > window.innerWidth - 8) { panel.style.left = 'auto'; panel.style.right = '0'; }
                        q.value = ''; box._render('');
                        q.focus();
                        const on = box.querySelector('[aria-selected="true"]');
                        if (on) on.scrollIntoView({ block: 'nearest' });
                    });
                    box.querySelector('.cb-close').addEventListener('click', () => { close(); btn.focus(); });
                    q.addEventListener('input', () => box._render(q.value));
                    box.querySelector('.cb-list').addEventListener('click', (e) => {
                        const b = e.target.closest('[data-val]');
                        if (!b) return;
                        close();
                        sel.value = b.dataset.val;
                        sel.dispatchEvent(new Event('change'));
                    });
                }

                box._render = (text) => {
                    const t = String(text || '').trim().toLowerCase();
                    const cur = sel.value;
                    const check = '<svg class="h-3.5 w-3.5 text-blue-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0L3.3 9.7a1 1 0 1 1 1.4-1.4l3.8 3.79 6.8-6.79a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>';
                    const row = (val, kode, ur, semua) => {
                        const on = val === cur;
                        return `<li class="border-b border-slate-100 last:border-0"><button type="button" data-val="${esc(val)}" role="option" aria-selected="${on}"
                style="display:grid;grid-template-columns:5rem minmax(0,1fr) 1rem;gap:.75rem;align-items:center;${on ? 'box-shadow:inset 3px 0 0 #2563eb' : ''}"
                class="w-full px-3 py-1.5 text-left text-xs leading-snug transition ${on ? 'bg-blue-50' : 'hover:bg-slate-50'}">
                <span class="tabular-nums font-semibold ${on ? 'text-blue-700' : 'text-slate-900'}">${esc(kode)}</span>
                <span class="break-words ${semua ? 'text-slate-500' : (on ? 'font-medium text-blue-800' : 'text-slate-700')}">${esc(ur)}</span>
                <span>${on ? check : ''}</span></button></li>`;
                    };
                    const shown = list.filter((o) => !t || o.tampil.toLowerCase().includes(t) || o.ur.toLowerCase().includes(t));
                    box.querySelector('.cb-list').innerHTML =
                        (t ? '' : row('', 'Semua', 'Tampilkan semua, tanpa filter', true)) +
                        (shown.length ? shown.map((o) => row(o.kode, o.tampil, o.ur)).join('')
                            : '<li class="px-3 py-4 text-center text-xs text-slate-400">Tidak ada yang cocok.</li>');
                    box.querySelector('.cb-count').textContent = t
                        ? shown.length + ' dari ' + list.length + ' item'
                        : list.length + ' item';
                };

                const cur = list.find((o) => o.kode === sel.value);
                box.querySelector('.cb-label').innerHTML = cur
                    ? `<span class="font-semibold text-slate-900 tabular-nums">${esc(cur.tampil)}</span><span class="text-slate-400"> &nbsp;|&nbsp; </span>${esc(cur.ur)}`
                    : '<span class="text-slate-600">Semua</span>';
                box._render('');
            }

            // Tutup semua dropdown
            function closeAllCombos() {
                document.querySelectorAll('.cb').forEach((b) => b._close && b._close());
            }
            // Klik di luar menutup. composedPath() tetap benar walau elemen yang diklik sudah dicopot dari DOM.
            document.addEventListener('mousedown', (e) => {
                const path = e.composedPath ? e.composedPath() : [];
                document.querySelectorAll('.cb').forEach((b) => { if (!path.includes(b)) b._close && b._close(); });
            });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAllCombos(); });

            // Urutan unit: Asdep 1..5, lalu Sesdep, lalu lainnya; "Belum dipetakan" selalu terakhir
            const unitRank = (s) => /^Asdep/i.test(s) ? 0 : /^Sesdep/i.test(s) ? 1 : 2;
            const cmpUnit = (a, b) => a === BELUM ? 1 : b === BELUM ? -1
                : (unitRank(a) - unitRank(b)) || a.localeCompare(b, 'id', { numeric: true });

            const getTerms = () => {
                const q = $('ang-q').value.trim();
                return q ? q.toLowerCase().split(/[,;\n]+/).map((t) => t.trim()).filter(Boolean) : [];
            };
            // Kode dicocokkan dari awal; uraian tetap "mengandung".
            // Item dianggap cocok bila dirinya atau salah satu induknya (program..akun) cocok.
            const matchQ = (it, terms) => {
                if (!terms.length) return true;
                const hit = (kodes, ur) => terms.some((t) =>
                    String(ur ?? '').toLowerCase().includes(t) ||
                    kodes.some((k) => String(k ?? '').toLowerCase().startsWith(t)));
                for (const lv of LEVELS) {
                    const raw = lvKode(it, lv);
                    if (hit([padKode(lv.key, raw), raw], lvUraian(it, lv))) return true;
                }
                return hit([String(it.item_kode ?? '').trim()], it.item_uraian);
            };

            const isD = (u) => /^D\d+$/.test(u);
            const orderUnits = (arr) => [...arr].sort((a, b) => (isD(b) - isD(a))
                || (a === BELUM ? 1 : b === BELUM ? -1 : 0)
                || a.localeCompare(b, 'id', { numeric: true }));

            // Daftar centang unit kerja
            function fillUnitSel() {
                const units = orderUnits(new Set(items.map((i) => i.unit)));
                const chip = (u) => {
                    const on = U.units.has(u);
                    return `<label class="inline-flex cursor-pointer select-none items-center gap-2 rounded-lg border px-3 py-1 text-xs transition ${on ? 'border-blue-400 bg-blue-50 text-blue-700 font-medium' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'} ${u === BELUM && !on ? 'border-amber-200 text-amber-800' : ''}">
                        <input type="checkbox" value="${esc(u)}" ${on ? 'checked' : ''} class="h-3.5 w-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">${esc(u)}</label>`;
                };
                const d = units.filter(isD), o = units.filter((u) => !isD(u));
                $('unit-checks').innerHTML =
                    (d.length ? `<div class="flex flex-wrap gap-2">${d.map(chip).join('')}</div>` : '') +
                    (o.length ? `<div class="flex flex-wrap gap-2">${o.map(chip).join('')}</div>` : '');
                $('unit-count').textContent = U.units.size ? `· ${U.units.size} dari ${units.length} dipilih` : '';
            }

            function updateExportLink() {
                const p = new URLSearchParams();
                CH.forEach((k) => { if (F[k]) p.set(k, F[k]); });
                const q = $('ang-q').value.trim();
                if (q) p.set('q', q);
                if ($('f-level').value !== 'all') p.set('level', $('f-level').value);
                if (U.units.size) p.set('unit', [...U.units].join(','));
                if (U.sub) p.set('sub_unit', U.sub);
                const base = @json(route('anggaran.export'));
                $('btn-export').href = base + (p.toString() ? '?' + p.toString() : '');
            }

            // ---- ringkasan per unit (dipakai kartu unit dan grafik) ----
            // Outstanding menempel di MAK, jadi tiap MAK hanya dijumlahkan sekali per unit / sub unit.
            function aggregate(terms, unitOk, keyOf) {
                const by = new Map();
                items.filter((it) => match(it, 5) && (!unitOk || unitOk(it.unit)) && matchQ(it, terms)).forEach((it) => {
                    const v = vals(it), key = makKey(it), o = Number(OUT[key]) || 0;
                    const uk = keyOf ? keyOf(it.unit) : it.unit;
                    let a = by.get(uk);
                    if (!a) { a = { unit: uk, pagu: 0, sd: 0, out: 0, mak: new Set(), subs: new Map() }; by.set(uk, a); }
                    a.pagu += v.pagu; a.sd += v.sd;
                    if (!a.mak.has(key)) { a.mak.add(key); a.out += o; }
                    if (it.sub_unit) {
                        let x = a.subs.get(it.sub_unit);
                        if (!x) { x = { pagu: 0, sd: 0, out: 0, mak: new Set() }; a.subs.set(it.sub_unit, x); }
                        x.pagu += v.pagu; x.sd += v.sd;
                        if (!x.mak.has(key)) { x.mak.add(key); x.out += o; }
                    }
                });
                return by;
            }

            // Baris angka kecil untuk kartu rincian Asdep / Sesdep
            const subRows = (x, soft) => {
                const rows = [['Pagu', x.pagu], ['Sisa', x.pagu - x.sd], ['Outstanding', x.out], ['Sisa Efektif', x.pagu - x.sd - x.out]];
                return `<dl class="mt-2.5 space-y-1 text-[11px] tabular-nums">${rows.map(([l, v], i) => `<div class="flex justify-between gap-2"><dt class="${soft ? 'text-slate-400' : 'text-slate-500'}">${l}</dt><dd class="${i === 3 ? 'font-semibold text-slate-800' : (soft ? 'text-slate-500' : 'font-medium text-slate-700')}">${short(v)}</dd></div>`).join('')}</dl>`;
            };

            // ---- grafik (Chart.js) ----
            // Isi bila ingin nama lengkap unit di grafik, mis.: D1: 'Deputi Bidang Koordinasi ...'
            const NAMA_UNIT = {};
            const unitName = (u) => NAMA_UNIT[u] || u;
            const pct2 = (x) => x.toFixed(2).replace('.', ',') + '%';
            const compact = (n) => {
                const a = Math.abs(n), s = n < 0 ? '-' : '';
                if (a >= 1e12) return s + (a / 1e12).toFixed(1).replace('.', ',') + ' T';
                if (a >= 1e9) return s + (a / 1e9).toFixed(1).replace('.', ',') + ' M';
                if (a >= 1e6) return s + Math.round(a / 1e6) + ' jt';
                return s + rp(a);
            };
            const wrapLbl = (t, n = 22) => {
                const out = []; let cur = '';
                String(t).split(' ').forEach((w) => {
                    if (cur && (cur + ' ' + w).length > n) { out.push(cur); cur = w; } else cur = (cur ? cur + ' ' : '') + w;
                });
                if (cur) out.push(cur);
                return out;
            };
            const CHARTS = {};
            let chMode = 'rp';   // 'rp' = nominal, 'pct' = persen terhadap pagu
            let chGroup = true;  // true = Biro + Inspektorat digabung jadi Sekretariat Kementerian
            const SEKRE = 'SETMENKO';
            const isBiro = (u) => /biro|inspektorat/i.test(u);
            const groupKey = (u) => isBiro(u) ? SEKRE : u;

            // Plugin kecil: tulis angka di batang.
            // Pada batang bertumpuk, angka yang tidak muat di dalam segmen ditulis di kanan batang (diberi warna segmennya).
            const valueLabels = {
                id: 'valueLabels',
                afterDatasetsDraw(chart, args, opt) {
                    if (!opt || !opt.fmt) return;
                    const ctx = chart.ctx, horiz = chart.options.indexAxis === 'y';
                    const spill = {};
                    ctx.save();
                    ctx.font = '600 11px ' + (Chart.defaults.font.family || 'sans-serif');
                    ctx.textBaseline = 'middle';
                    chart.data.datasets.forEach((ds, di) => {
                        const meta = chart.getDatasetMeta(di);
                        if (meta.hidden) return;
                        meta.data.forEach((bar, i) => {
                            const txt = opt.fmt(ds.data[i], di, i);
                            if (!txt) return;
                            const tw = ctx.measureText(txt).width;
                            if (opt.mode === 'center') {
                                const w = Math.abs(bar.x - bar.base);
                                if (horiz && opt.stacked && ds.spill) {
                                    (spill[i] = spill[i] || []).push({ txt: ds.label + ' ' + txt, color: ds.backgroundColor });
                                    return;
                                }
                                if (horiz && w >= tw + 8) {
                                    ctx.fillStyle = '#fff'; ctx.textAlign = 'center';
                                    ctx.fillText(txt, (bar.x + bar.base) / 2, bar.y);
                                } else if (horiz && opt.stacked) {
                                    (spill[i] = spill[i] || []).push({ txt: ds.label + ' ' + txt, color: ds.backgroundColor });
                                } else if (horiz) {
                                    ctx.fillStyle = '#334155'; ctx.textAlign = 'left';
                                    ctx.fillText(txt, bar.x + 6, bar.y);
                                }
                                return;
                            }
                            ctx.fillStyle = '#334155';
                            if (horiz) { ctx.textAlign = 'left'; ctx.fillText(txt, bar.x + 6, bar.y); }
                            else { ctx.textAlign = 'center'; ctx.fillText(txt, bar.x, bar.y - 10); }
                        });
                    });
                    Object.keys(spill).forEach((i) => {
                        let endX = 0, y = 0;
                        chart.data.datasets.forEach((ds, di) => {
                            const m = chart.getDatasetMeta(di);
                            if (m.hidden || !m.data[i]) return;
                            endX = Math.max(endX, m.data[i].x);
                            y = m.data[i].y;
                        });
                        const list = spill[i], lh = 13;
                        ctx.textAlign = 'left';
                        list.forEach((it, k) => {
                            ctx.fillStyle = it.color;
                            ctx.fillText(it.txt, endX + 8, y + (k - (list.length - 1) / 2) * lh);
                        });
                    });
                    ctx.restore();
                },
            };

            // Plugin: pudarkan batang yang tidak sedang dipilih (opsi dimBars.dim(i) -> true = dipudarkan)
            const dimBars = {
                id: 'dimBars',
                afterDatasetsDraw(chart, args, opt) {
                    if (!opt || !opt.dim) return;
                    const horiz = chart.options.indexAxis === 'y', ctx = chart.ctx;
                    ctx.save();
                    ctx.fillStyle = 'rgba(255,255,255,.65)';
                    chart.data.datasets.forEach((ds, di) => {
                        const meta = chart.getDatasetMeta(di);
                        if (meta.hidden) return;
                        meta.data.forEach((bar, i) => {
                            if (!opt.dim(i)) return;
                            if (horiz) ctx.fillRect(Math.min(bar.x, bar.base), bar.y - bar.height / 2, Math.abs(bar.x - bar.base), bar.height);
                            else ctx.fillRect(bar.x - bar.width / 2, Math.min(bar.y, bar.base), bar.width, Math.abs(bar.base - bar.y));
                        });
                    });
                    ctx.restore();
                },
            };

            function mkChart(id, cfg) {
                if (CHARTS[id]) { CHARTS[id].destroy(); delete CHARTS[id]; }
                const el = $(id);
                if (!el) return;
                cfg.options = Object.assign({ responsive: true, maintainAspectRatio: false, animation: false }, cfg.options);
                if (cfg.options.onClick && !cfg.options.onHover) {
                    cfg.options.onHover = (e, els) => { e.native.target.style.cursor = els.length ? 'pointer' : 'default'; };
                }
                cfg.plugins = [dimBars, valueLabels];
                CHARTS[id] = new Chart(el, cfg);
            }

            function syncChMode() {
                document.querySelectorAll('[data-chmode]').forEach((b) => {
                    const on = b.dataset.chmode === chMode;
                    b.style.background = on ? '#2563eb' : '';
                    b.style.color = on ? '#fff' : '';
                    b.classList.toggle('text-slate-600', !on);
                });
                document.querySelectorAll('[data-chgroup]').forEach((b) => {
                    const on = (b.dataset.chgroup === '1') === chGroup;
                    b.style.background = on ? '#2563eb' : '';
                    b.style.color = on ? '#fff' : '';
                    b.classList.toggle('text-slate-600', !on);
                });
            }

            function renderCharts(terms) {
                if (typeof Chart === 'undefined') return;   // CDN gagal dimuat: halaman tetap jalan tanpa grafik
                syncChMode();
                Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
                Chart.defaults.font.size = 11;
                Chart.defaults.color = '#64748b';

                const by = aggregate(terms, null, chGroup ? groupKey : null);
                const units = orderUnits([...by.keys()]);
                const A = (u) => by.get(u);
                const pctOf = (a) => a.pagu ? a.sd / a.pagu * 100 : 0;
                const efektif = (a) => a.pagu - a.sd - a.out;
                const hh = Math.max(280, units.length * 48 + 70);
                $('ch-unit-wrap').style.height = hh + 'px';
                $('ch-stack-wrap').style.height = hh + 'px';

                // Klik batang unit -> centang / lepas unit itu di filter Unit Kerja (kartu & tabel ikut berubah).
                // Batang "Sekretariat" (mode Gabung) mewakili semua Biro + Inspektorat.
                const unitsOfKey = (k) => (chGroup && k === SEKRE)
                    ? [...new Set(items.filter((i) => isBiro(i.unit)).map((i) => i.unit))]
                    : [k];
                const isSel = (k) => U.units.size > 0 && unitsOfKey(k).every((u) => U.units.has(u));
                const pickUnit = (k) => {
                    const us = unitsOfKey(k);
                    if (!us.length) return;
                    if (isSel(k)) us.forEach((u) => U.units.delete(u)); else us.forEach((u) => U.units.add(u));
                    afterUnitsChange();
                };
                // Klik batang akun -> isi filter Akun lalu gulung ke tabel
                const pickAkun = (kode) => {
                    const on = F.akun !== kode;
                    F.akun = on ? kode : '';
                    currentPage = 1;
                    pendingOpen = CH.some((c) => F[c]) ? 'all' : 'default';
                    fillSel();
                    render();
                    if (on) { const t = $('ang-tb').closest('.card'); if (t) t.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
                };

                // 1) Realisasi seluruh unit (batang tunggal 0-100%)
                let P = 0, R = 0;
                by.forEach((a) => { P += a.pagu; R += a.sd; });
                const tot = P ? R / P * 100 : 0;
                mkChart('ch-total', {
                    type: 'bar',
                    data: { labels: ['Seluruh unit'], datasets: [{ data: [tot], backgroundColor: '#2563eb', borderRadius: 4, barThickness: 24 }] },
                    options: {
                        indexAxis: 'y',
                        scales: {
                            x: { min: 0, max: 100, ticks: { stepSize: 10, callback: (v) => v + '%' }, grid: { color: '#e2e8f0' } },
                            y: { display: false },
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: () => 'Realisasi Rp ' + rp(R) + ' dari pagu Rp ' + rp(P) } },
                            valueLabels: { mode: 'center', fmt: (v) => pct2(v) },
                        },
                    },
                });

                // 2) Realisasi per unit (kolom, skala 0-100% supaya tidak menyesatkan)
                mkChart('ch-unit', {
                    type: 'bar',
                    data: { labels: units.map((u) => wrapLbl(unitName(u))), datasets: [{ data: units.map((u) => pctOf(A(u))), backgroundColor: '#2563eb', borderRadius: 4, maxBarThickness: 56 }] },
                    options: {
                        layout: { padding: { top: 22 } },
                        interaction: { mode: 'index', axis: 'x', intersect: false },
                        onClick: (e, els) => { if (els.length) { const k = units[els[0].index]; setTimeout(() => pickUnit(k), 0); } },
                        scales: {
                            y: { min: 0, max: 100, ticks: { callback: (v) => v + '%' }, grid: { color: '#e2e8f0' } },
                            x: { grid: { display: false } },
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    title: (it) => unitName(units[it[0].dataIndex]),
                                    label: (c) => { const a = A(units[c.dataIndex]); return ['Realisasi ' + pct2(c.raw), 'Rp ' + rp(a.sd) + ' dari Rp ' + rp(a.pagu)]; },
                                },
                            },
                            dimBars: { dim: (i) => U.units.size > 0 && !isSel(units[i]) },
                            valueLabels: { fmt: (v) => pct2(v) },
                        },
                    },
                });

                // 3) Komposisi pagu per unit: Realisasi + Outstanding + Sisa Efektif
                const pc = chMode === 'pct';
                const su = [...units].sort((a, b) => efektif(A(a)) - efektif(A(b)));   // sisa efektif terkecil di atas
                const SER = [
                    { label: 'Realisasi', key: 'sd', color: '#0d9488', raw: (a) => a.sd },
                    { label: 'Outstanding', key: 'out', color: '#7c3aed', raw: (a) => a.out },
                    { label: 'Sisa Efektif', key: 'ef', color: '#16a34a', raw: (a) => efektif(a) },
                ];
                mkChart('ch-stack', {
                    type: 'bar',
                    data: {
                        labels: su.map((u) => wrapLbl(unitName(u), 26)),
                        datasets: SER.map((x) => ({
                            label: x.label, backgroundColor: x.color, borderWidth: 0, maxBarThickness: 28, spill: true,
                            data: su.map((u) => { const a = A(u), v = Math.max(0, x.raw(a)); return pc ? (a.pagu ? v / a.pagu * 100 : 0) : v; }),
                        })),
                    },
                    options: {
                        indexAxis: 'y',
                        layout: { padding: { right: 128 } },
                        interaction: { mode: 'index', axis: 'y', intersect: false },
                        onClick: (e, els) => { if (els.length) { const k = su[els[0].index]; setTimeout(() => pickUnit(k), 0); } },
                        scales: {
                            x: { stacked: true, max: pc ? 100 : undefined, grid: { color: '#e2e8f0' }, ticks: { callback: (v) => pc ? v + '%' : compact(v) } },
                            y: { stacked: true, grid: { display: false } },
                        },
                        plugins: {
                            legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8 } },
                            tooltip: {
                                callbacks: {
                                    title: (it) => unitName(su[it[0].dataIndex]),
                                    label: (c) => {
                                        const a = A(su[c.dataIndex]), r = SER[c.datasetIndex].raw(a);
                                        return c.dataset.label + ': Rp ' + rp(r) + (a.pagu ? ' (' + pct2(r / a.pagu * 100) + ')' : '');
                                    },
                                },
                            },
                            dimBars: { dim: (i) => U.units.size > 0 && !isSel(su[i]) },
                            valueLabels: {
                                mode: 'center', stacked: true,
                                // Angka ditulis di kanan batang: Realisasi, Outstanding (hanya bila ada), Sisa Efektif
                                fmt: (v, di, i) => {
                                    const a = A(su[i]), r = SER[di].raw(a);
                                    if (!r) return SER[di].key === 'out' ? '' : (pc ? '0,00%' : '0');   // Outstanding hanya tampil bila ada
                                    if (!pc) return compact(r);
                                    const q = a.pagu ? r / a.pagu * 100 : 0;
                                    return (q > 0 && q < 0.005) ? '<0,01%' : pctTxt(q);
                                },
                            },
                        },
                    },
                });

                // 4) 10 akun teratas (mengikuti filter unit juga). Outstanding dihitung sekali per MAK.
                const mk = new Map();
                items.filter((it) => inUnit(it) && match(it, 4) && matchQ(it, terms)).forEach((it) => {
                    const key = makKey(it), v = vals(it);
                    let m = mk.get(key);
                    if (!m) { m = { kode: String(it.akun_kode ?? ''), ur: it.akun_uraian || '', pagu: 0, sd: 0, out: Number(OUT[key]) || 0 }; mk.set(key, m); }
                    m.pagu += v.pagu; m.sd += v.sd;
                });
                const ak = new Map();
                mk.forEach((m) => {
                    let a = ak.get(m.kode);
                    if (!a) { a = { kode: m.kode, ur: m.ur, out: 0, efektif: 0 }; ak.set(m.kode, a); }
                    a.out += m.out; a.efektif += m.pagu - m.sd - m.out;
                });
                const metric = $('ch-akun-metric').value;
                const top = [...ak.values()].filter((a) => a[metric] > 0).sort((a, b) => b[metric] - a[metric]).slice(0, 10);
                mkChart('ch-akun', {
                    type: 'bar',
                    data: {
                        labels: top.map((a) => clip(a.kode + ' ' + a.ur, 38)),
                        datasets: [{ data: top.map((a) => a[metric]), backgroundColor: metric === 'out' ? '#7c3aed' : '#16a34a', borderRadius: 4, maxBarThickness: 22 }],
                    },
                    options: {
                        indexAxis: 'y',
                        layout: { padding: { right: 70 } },
                        interaction: { mode: 'index', axis: 'y', intersect: false },
                        onClick: (e, els) => { if (els.length) { const a = top[els[0].index]; setTimeout(() => pickAkun(a.kode), 0); } },
                        scales: {
                            x: { grid: { color: '#e2e8f0' }, ticks: { callback: (v) => compact(v) } },
                            y: { grid: { display: false } },
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    title: (it) => { const a = top[it[0].dataIndex]; return a.kode + ' ' + a.ur; },
                                    label: (c) => (metric === 'out' ? 'Outstanding: Rp ' : 'Sisa Efektif: Rp ') + rp(c.raw),
                                },
                            },
                            dimBars: { dim: (i) => !!F.akun && F.akun !== top[i].kode },
                            valueLabels: { fmt: (v) => compact(v) },
                        },
                    },
                });
            }

            // ---- kartu unit kerja (hanya untuk unit yang dicentang) ----
            // Dihitung dari data yang lolos filter Kegiatan..Akun, TANPA filter unit.
            function renderUnitCards() {
                const section = $('section-unit');
                section.classList.toggle('hidden', U.units.size === 0);
                if (!U.units.size) { $('unit-cards').innerHTML = ''; return; }

                const terms = getTerms();
                const by = aggregate(terms, (u) => U.units.has(u));
                $('unit-sub').textContent = terms.length
                    ? 'Hasil pencarian "' + terms.join(', ') + '" per unit (angka hanya dari kode yang cocok).'
                    : 'Kartu muncul untuk unit yang dicentang pada filter Unit Kerja di atas.';
                const empty = (a) => terms.length && !a.pagu && !a.sd;
                const list = orderUnits([...U.units]).map((u) => by.get(u) || { unit: u, pagu: 0, sd: 0, out: 0, subs: new Map() });
                const single = list.length === 1;

                const bar = (p, h = 'h-1.5') => `<div class="${h} w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full transition-all" style="width:${Math.min(100, p)}%;background:${tone(p)}"></div></div>`;
                // Cincin persentase: elemen utama kartu unit
                const ringSvg = (p, size = 76) => {
                    const r = 26, c = 2 * Math.PI * r, off = c * (1 - Math.min(100, p) / 100);
                    return `<svg width="${size}" height="${size}" viewBox="0 0 64 64" role="img" aria-label="Penyerapan ${pctTxt(p)}">
                        <circle cx="32" cy="32" r="${r}" fill="none" stroke="#e2e8f0" stroke-width="6"></circle>
                        <circle class="ring-fg" cx="32" cy="32" r="${r}" fill="none" stroke="${tone(p)}" stroke-width="6" stroke-linecap="round"
                            stroke-dasharray="${c.toFixed(2)}" stroke-dashoffset="${off.toFixed(2)}" transform="rotate(-90 32 32)"></circle>
                        <text x="32" y="36.5" text-anchor="middle" font-size="10" font-weight="700" fill="#0f172a">${pctTxt(p)}</text>
                    </svg>`;
                };
                const chevron = (o) => `<svg class="chev w-3.5 h-3.5 ${o ? 'is-open' : ''}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>`;
                const subLabel = (unit, name) => /\(langsung\)/.test(name) ? 'Tata Usaha dan Sakip ' + unit : name.replace(/\s+D\d+$/i, '');
                const sortedSubs = (a) => [...a.subs.entries()].sort((x, y) => cmpUnit(x[0], y[0]));

                // Baris berlabel (kiri: label, kanan: angka) supaya angka penuh tidak saling menimpa
                // Pagu = biru, Realisasi = teal, Sisa = amber; semua versi tua/redup supaya tetap profesional
                const CLR = { pagu: ['#2563eb', '#1e3a8a'], real: ['#0d9488', '#115e59'], sisa: ['#d97706', '#92400e'], out: ['#7c3aed', '#5b21b6'], efektif: ['#16a34a', '#166534'] };
                const dRow = (label, v, key, strong) => `<div class="flex items-center justify-between gap-3 py-2">
                    <dt class="flex items-center gap-2 text-xs text-slate-500"><span class="h-2 w-2 rounded-full" style="background:${CLR[key][0]}"></span>${label}</dt>
                    <dd class="tabular-nums text-[13px] ${strong ? 'font-semibold' : 'font-medium'}" style="color:${CLR[key][1]}">${short(v)}</dd></div>`;

                // ---------- Satu unit dipilih: kartu lebar + rincian bisa diklik ----------
                const wideCard = (a) => {
                    if (empty(a)) return `<div class="card p-5">
                        <h4 class="text-lg font-semibold text-slate-900">${esc(a.unit)}</h4>
                        <p class="mt-2 text-sm text-slate-400">Tidak ada kode yang cocok dengan pencarian di unit ini.</p></div>`;
                    const p = a.pagu ? a.sd / a.pagu * 100 : 0;
                    const warn = a.unit === BELUM;
                    const subs = sortedSubs(a);
                    const subCard = ([name, x]) => {
                        const sp = x.pagu ? x.sd / x.pagu * 100 : 0;
                        const on = U.sub === name;
                        return `<div data-sub="${esc(name)}" aria-selected="${on}"
                            class="cursor-pointer rounded-xl border px-3.5 py-3 transition ${on ? 'border-blue-400 bg-blue-50/60 ring-1 ring-blue-400' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'}">
                            <div class="flex items-baseline justify-between gap-3">
                                <span class="truncate text-xs font-medium ${on ? 'text-blue-700' : 'text-slate-700'}">${esc(subLabel(a.unit, name))}</span>
                                <span class="shrink-0 text-sm font-semibold tabular-nums text-slate-900">${pctTxt(sp)}</span>
                            </div>
                            <div class="mt-2">${bar(sp, 'h-1')}</div>
                            ${subRows(x)}
                        </div>`;
                    };
                    return `<div class="card p-5">
                        <div class="flex flex-wrap items-center gap-x-8 gap-y-4">
                            <div class="flex items-center gap-4">
                                ${ringSvg(p, 88)}
                                <div>
                                    <p class="text-xs text-slate-400">Unit terpilih</p>
                                    <h4 class="text-xl font-semibold ${warn ? 'text-amber-800' : 'text-slate-900'}">${esc(a.unit)}</h4>
                                    ${warn ? '<p class="mt-0.5 text-[11px] font-medium text-amber-700">Belum dipetakan oleh admin</p>' : '<p class="mt-0.5 text-xs text-slate-500">Penyerapan anggaran</p>'}
                                </div>
                            </div>
                            <dl class="grid min-w-[260px] flex-1 grid-cols-1 divide-y divide-slate-100 rounded-xl bg-slate-50 px-4 sm:max-w-md">
                                ${dRow('Pagu', a.pagu, 'pagu')}${dRow('Realisasi', a.sd, 'real')}${dRow('Sisa', a.pagu - a.sd, 'sisa')}${dRow('Outstanding', a.out, 'out')}${dRow('Sisa Efektif', a.pagu - a.sd - a.out, 'efektif', true)}
                            </dl>
                        </div>
                        ${subs.length ? `<div class="mt-5 border-t border-slate-100 pt-4">
                            <p class="mb-2.5 text-xs text-slate-400">Rincian Asdep / Sesdep. Klik untuk mempersempit tabel, klik lagi untuk membatalkan.</p>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">${subs.map(subCard).join('')}</div>
                        </div>` : ''}
                    </div>`;
                };

                // ---------- Beberapa unit dipilih: kartu berdampingan, lebar menyesuaikan jumlah unit ----------
                const compactCard = (a) => {
                    if (empty(a)) return `<div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-5">
                        <h4 class="truncate text-sm font-semibold text-slate-400">${esc(a.unit)}</h4>
                        <p class="mt-3 text-xs text-slate-400">Tidak ada kode yang cocok.</p></div>`;
                    const p = a.pagu ? a.sd / a.pagu * 100 : 0;
                    const warn = a.unit === BELUM;
                    const subs = sortedSubs(a);
                    const isOpen = openUnits.has(a.unit);
                    const subHtml = isOpen && subs.length ? `<div class="mt-4 space-y-3 border-t border-slate-100 pt-4">
                        <p class="text-xs font-medium text-slate-400">Rincian</p>
                        ${subs.map(([name, x]) => {
                        const sp = x.pagu ? x.sd / x.pagu * 100 : 0;
                        return `<div>
                                <div class="flex items-baseline justify-between gap-3">
                                    <span class="truncate text-xs text-slate-600">${esc(subLabel(a.unit, name))}</span>
                                    <span class="shrink-0 text-xs font-semibold tabular-nums text-slate-800">${pctTxt(sp)}</span>
                                </div>
                                <div class="mt-1.5">${bar(sp, 'h-1')}</div>
                                ${subRows(x, true)}
                            </div>`;
                    }).join('')}</div>` : '';
                    return `<div class="card overflow-hidden p-5 ${warn ? 'ring-1 ring-amber-300' : ''}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h4 class="truncate text-base font-semibold ${warn ? 'text-amber-800' : 'text-slate-900'}">${esc(a.unit)}</h4>
                                <p class="mt-0.5 text-xs ${warn ? 'font-medium text-amber-700' : 'text-slate-400'}">${warn ? 'Belum dipetakan oleh admin' : 'Penyerapan anggaran'}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                ${ringSvg(p, 68)}
                                ${subs.length ? `<button type="button" data-toggle="${esc(a.unit)}" aria-label="Lihat rincian ${esc(a.unit)}" aria-expanded="${isOpen}"
                                    class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:bg-slate-50 hover:text-slate-700 transition">${chevron(isOpen)}</button>` : ''}
                            </div>
                        </div>
                        <dl class="mt-3 divide-y divide-slate-100 rounded-xl bg-slate-50 px-4">
                            ${dRow('Pagu', a.pagu, 'pagu')}${dRow('Realisasi', a.sd, 'real')}${dRow('Sisa', a.pagu - a.sd, 'sisa')}${dRow('Outstanding', a.out, 'out')}${dRow('Sisa Efektif', a.pagu - a.sd - a.out, 'efektif', true)}
                        </dl>
                        ${subHtml}
                    </div>`;
                };

                $('unit-cards').innerHTML = single
                    ? wideCard(list[0]) + '<p class="mt-2 text-[11px] text-slate-400">Angka dalam rupiah.</p>'
                    : `<div class="unit-grid">${list.map(compactCard).join('')}</div>`;
            }

            // highlight kata kunci pada teks (dipakai untuk uraian: "mengandung")
            function hl(text, re) {
                text = String(text ?? '');
                if (!re) return esc(text);
                let out = '', last = 0, m;
                re.lastIndex = 0;
                while ((m = re.exec(text))) {
                    if (m[0] === '') { re.lastIndex++; continue; }
                    out += esc(text.slice(last, m.index)) + '<mark class="bg-yellow-200 text-slate-900 rounded px-0.5">' + esc(m[0]) + '</mark>';
                    last = m.index + m[0].length;
                }
                return out + esc(text.slice(last));
            }

            // highlight kata kunci pada KODE: hanya bagian awal yang cocok (sesuai aturan cocok-dari-awal)
            function hlKode(text, terms) {
                text = String(text ?? '');
                const t = (terms || []).filter((x) => x && text.toLowerCase().startsWith(x)).sort((a, b) => b.length - a.length)[0];
                if (!t) return esc(text);
                return '<mark class="bg-yellow-200 text-slate-900 rounded px-0.5">' + esc(text.slice(0, t.length)) + '</mark>' + esc(text.slice(t.length));
            }

            const ancestorsOf = (n) => { const a = []; let p = n.parent; while (p && p.depth >= 0) { a.unshift(p); p = p.parent; } return a; };

            function renderPathBar() {
                const bar = $('path-bar');
                const sel = selectedId ? byId.get(selectedId) : null;
                if (!sel) { bar.classList.add('hidden'); bar.innerHTML = ''; return; }
                const chain = [...ancestorsOf(sel), sel];
                const crumbs = chain.map((n, i) => {
                    const last = i === chain.length - 1;
                    return `<button type="button" data-pick="${esc(n.id)}" class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 ${last ? 'bg-blue-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-100'} transition">
                        <span class="opacity-70">${esc(n.lv.label)}</span><span class="font-mono">${esc(n.kode || '—')}</span></button>`;
                }).join('<svg class="w-3 h-3 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>');
                const childInfo = sel.children.length ? `<span class="text-slate-500">· ${sel.children.length} ${esc(sel.children[0].lv.label.toLowerCase())} di bawahnya</span>` : '';
                const canFocus = sel.depth >= 1 && sel.depth <= 5;
                bar.innerHTML = `<span class="font-medium">Jalur:</span>${crumbs}${childInfo}
                    <span class="ml-auto flex items-center gap-2">
                        ${canFocus ? '<button type="button" data-focus-sel class="rounded-md border border-slate-200 bg-white px-2 py-0.5 hover:bg-slate-100 transition">Fokus ke sini</button>' : ''}
                        <button type="button" data-clear-sel class="rounded-md border border-slate-200 bg-white px-2 py-0.5 hover:bg-slate-100 transition">Lepas pilihan</button>
                    </span>`;
                bar.classList.remove('hidden');
            }

            // Filter lanjutan (RO, Komponen, SubKomponen): terlipat, otomatis terbuka bila ada yang terisi
            let advPrev = 0;
            function updateAdv() {
                const n = ['ro', 'komp', 'sub'].filter((k) => F[k]).length;
                if (n > advPrev) { $('adv-panel').classList.remove('hidden'); $('btn-adv').setAttribute('aria-expanded', 'true'); }
                advPrev = n;
                const bd = $('adv-badge');
                bd.textContent = n; bd.classList.toggle('hidden', n === 0);
            }

            function render() {
                updateAdv();
                const q = $('ang-q').value.trim();
                const terms = q ? q.toLowerCase().split(/[,;\n]+/).map((t) => t.trim()).filter(Boolean) : [];
                qActive = terms.length > 0;
                const re = qActive ? new RegExp([...terms].sort((a, b) => b.length - a.length).map(escRe).join('|'), 'gi') : null;

                const levelKey = $('f-level').value;
                const maxDepth = levelKey === 'all' ? Infinity : LV_DEPTH[levelKey];
                const limitVal = $('f-limit').value;
                const limit = limitVal === 'all' ? Infinity : parseInt(limitVal, 10);

                // 0) kartu unit (tidak ikut filter unit)
                renderUnitCards();
                renderCharts(terms);

                // 1) bangun pohon dari data yang lolos filter bertingkat + filter unit
                const scoped = items.filter((it) => match(it, 5)
                    && (!U.units.size || U.units.has(it.unit))
                    && (!U.sub || it.sub_unit === U.sub));
                curRoot = buildTree(scoped);
                if (pendingOpen === 'all') { open = new Set(); openAll(); }
                else if (pendingOpen === 'default') openDefault();
                pendingOpen = null;
                if (selectedId && !byId.has(selectedId)) selectedId = null;

                // 2) tandai hasil pencarian (cocok + induk + turunan)
                curRoot.children.forEach((c) => mark(c, qActive ? terms : null, false));

                // 3) kumpulkan semua node yang tampil (untuk ringkasan & grafik) dan baris yang terlihat
                const all = [];
                (function col(n) { n.children.forEach((c) => { if (!c.vis) return; all.push(c); col(c); }); })(curRoot);
                const rows = [];
                (function fl(n) { n.children.forEach((c) => { if (!c.vis) return; rows.push(c); if (c.children.length && c.depth < maxDepth && isOpen(c)) fl(c); }); })(curRoot);

                const leaves = all.filter((n) => n.lv.key === 'item' && (!qActive || n.under));
                let P = 0, R = 0;
                leaves.forEach((n) => { P += n.v.pagu; R += n.v.sd; });
                const pctAll = P ? R / P * 100 : 0;
                $('s-pagu').textContent = 'Rp ' + rp(P);
                $('s-real').textContent = 'Rp ' + rp(R);
                $('s-sisa').textContent = 'Rp ' + rp(P - R);
                $('s-pct').textContent = P ? pctAll.toFixed(2).replace('.', ',') + '%' : '-';
                $('s-bar').style.width = Math.min(100, pctAll) + '%';
                $('s-bar').style.background = tone(pctAll);
                updateExportLink();

                // 4) petunjuk di atas tabel
                const nMatch = all.filter((n) => n.self).length;
                $('ang-hint').innerHTML = qActive
                    ? `<span class="font-medium text-slate-700">${nMatch} baris cocok</span>
                       <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-yellow-200 inline-block"></span>Cocok</span>
                       <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-slate-300 inline-block"></span>Induk (jalur ke atas)</span>
                       <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-white border border-slate-300 inline-block"></span>Turunan (semua di bawahnya)</span>`
                    : `<span>Klik baris untuk melihat induk &amp; turunannya. Klik panah untuk membuka atau menutup cabang.</span>`;

                // 5) halaman
                const totalRows = rows.length;
                const totalPages = limit === Infinity ? 1 : Math.ceil(totalRows / limit) || 1;
                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;
                const startIdx = (currentPage - 1) * limit;
                const endIdx = limit === Infinity ? totalRows : startIdx + limit;
                const pageRows = rows.slice(startIdx, endIdx);

                const sel = selectedId ? byId.get(selectedId) : null;
                const selAnc = sel ? new Set(ancestorsOf(sel).map((a) => a.id)) : new Set();
                const dash = '<span class="text-slate-300">–</span>';

                $('ang-norow').classList.toggle('hidden', totalRows > 0);

                $('ang-tb').innerHTML = pageRows.map((r) => {
                    const v = r.v;
                    const st = LV_STYLE[r.lv.key];
                    const pct = v.pagu > 0 ? v.sd / v.pagu : 0;
                    const bold = r.depth <= 1 ? 'font-semibold' : '';
                    const numBold = r.depth <= 1 ? 'font-semibold' : '';
                    const hasKids = r.children.length > 0;
                    const expandable = hasKids && r.depth < maxDepth;
                    const opened = expandable && isOpen(r);

                    // Sisa Efektif = Sisa - Outstanding. Item tidak punya outstanding (null), jadi tidak dihitung.
                    const sisaEf = (r.out === null || r.out === undefined) ? null : (v.pagu - v.sd - r.out);

                    // warna latar: pilihan > cocok > jenis level
                    let bg = st.row;
                    let ring = '';
                    let relChip = '';
                    if (qActive && r.rel === 'match') { bg = 'bg-yellow-50'; }
                    if (qActive && r.rel === 'parent') { relChip = '<span class="rounded px-1.5 py-0.5 text-[10px] font-medium bg-slate-200 text-slate-600">Induk</span>'; }
                    if (sel) {
                        if (r.id === sel.id) { bg = 'bg-blue-50'; ring = 'ring-1 ring-inset ring-blue-300'; relChip = '<span class="rounded px-1.5 py-0.5 text-[10px] font-medium bg-blue-600 text-white">Dipilih</span>'; }
                        else if (selAnc.has(r.id)) { bg = 'bg-slate-100'; relChip = '<span class="rounded px-1.5 py-0.5 text-[10px] font-medium bg-slate-200 text-slate-700">Induk</span>'; }
                        else if (r.id.startsWith(sel.id + '|')) { bg = 'bg-slate-50'; }
                    }

                    // kolom Unit: terisi bila semua item di bawah baris ini milik satu unit
                    let unitCell = '<span class="text-slate-300">–</span>';
                    if (r.us && r.us.size === 1) {
                        const u = [...r.us][0];
                        const sName = r.ss.size === 1 ? [...r.ss][0] : '';
                        const label = sName ? (/\(langsung\)/.test(sName) ? u : sName.replace(/\s+D\d+$/i, '') + ' ' + u) : u;
                        unitCell = `<span class="${u === BELUM ? 'text-amber-700' : (sName ? 'text-blue-600' : 'text-slate-700')}">${esc(label)}</span>`;
                    } else if (r.us && r.us.size > 1) {
                        unitCell = `<span class="text-slate-400">${r.us.size} unit</span>`;
                    }

                    const chev = expandable
                        ? `<button type="button" data-act="toggle" aria-label="Buka/tutup" aria-expanded="${opened}" class="shrink-0 w-4 h-4 mr-1 rounded flex items-center justify-center text-slate-500 hover:bg-slate-200 transition">
                               <svg class="chev w-3 h-3 ${opened ? 'is-open' : ''}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                           </button>`
                        : '<span class="shrink-0 w-4 h-4 mr-1"></span>';

                    const hint = hasKids
                        ? `${r.children.length} ${r.children[0].lv.label.toLowerCase()}${(r.depth >= maxDepth) ? ' · tersembunyi (batas level)' : ''}`
                        : '';

                    const focusBtn = (r.depth >= 1 && r.depth <= 5)
                        ? `<button type="button" data-act="focus" title="Fokus ke ${esc(r.lv.label)} ini (filter)" class="row-actions ml-auto shrink-0 rounded border border-slate-200 bg-white p-0.5 text-slate-500 hover:text-blue-600 hover:border-blue-300 transition">
                               <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3m0 12v3M3 12h3m12 0h3M12 8.25a3.75 3.75 0 1 0 0 7.5 3.75 3.75 0 0 0 0-7.5Z" /></svg>
                           </button>`
                        : '';

                    // indentasi lebih rapat supaya kolom Kode tidak melebar
                    const indent = 4 + Math.min(r.depth, 7) * 10;
                    const num = 'px-2 py-1.5 text-right whitespace-nowrap tabular-nums align-middle';

                    return `<tr class="tree-row group cursor-pointer ${bg} ${ring}" data-id="${esc(r.id)}"${hint ? ` title="${esc(hint)}"` : ''}>
                        <td class="py-1.5 pr-1 align-middle">
                            <div class="flex flex-wrap items-center" style="padding-left:${indent}px">
                                ${chev}<span class="tabular-nums text-slate-800 ${bold}">${hlKode(r.kode || '—', qActive ? terms : null)}</span>${relChip ? `<span class="ml-1">${relChip}</span>` : ''}
                            </div>
                        </td>
                        <td class="py-1.5 px-2 align-middle">
                            <div class="flex items-center gap-1">
                                <span class="min-w-0 leading-snug text-slate-800 ${bold} break-words">${hl(r.uraian, re)}</span>
                                ${focusBtn}
                            </div>
                        </td>
                        <td class="py-1.5 px-2 align-middle break-words">${unitCell}</td>
                        <td class="${num} ${numBold}">${rp(v.pagu)}</td>
                        <td class="${num} ${numBold}">${v.lalu === null ? dash : rp(v.lalu)}</td>
                        <td class="${num} ${numBold}">${v.ini === null ? dash : rp(v.ini)}</td>
                        <td class="${num} ${numBold}">${rp(v.sd)}</td>
                        <td class="${num}">${(pct * 100).toFixed(2).replace('.', ',')}%</td>
                        <td class="${num} ${numBold}">${rp(v.pagu - v.sd)}</td>
                        <td class="${num} ${numBold} ${r.out > 0 ? 'text-amber-700' : ''}">${r.out > 0 ? rp(r.out) : dash}</td>
                        <td class="${num} ${numBold} ${sisaEf !== null && sisaEf < 0 ? 'text-red-600' : ''}">${sisaEf === null ? dash : rp(sisaEf)}</td>
                    </tr>`;
                }).join('');

                renderPathBar();

                $('ang-cnt').textContent = `Menampilkan ${totalRows === 0 ? 0 : startIdx + 1} - ${Math.min(endIdx, totalRows)} dari ${totalRows} baris`;
                $('page-info').textContent = `Halaman ${currentPage} dari ${totalPages}`;
                $('btn-prev').disabled = currentPage <= 1;
                $('btn-next').disabled = currentPage >= totalPages;

                // nomor halaman (maks 7 tombol, dengan elipsis)
                const nums = [];
                if (totalPages <= 7) { for (let i = 1; i <= totalPages; i++) nums.push(i); }
                else {
                    let a = Math.max(2, currentPage - 1), b = Math.min(totalPages - 1, currentPage + 1);
                    if (currentPage <= 3) { a = 2; b = 4; }
                    if (currentPage >= totalPages - 2) { a = totalPages - 3; b = totalPages - 1; }
                    nums.push(1);
                    if (a > 2) nums.push('…');
                    for (let i = a; i <= b; i++) nums.push(i);
                    if (b < totalPages - 1) nums.push('…');
                    nums.push(totalPages);
                }
                $('page-nums').innerHTML = nums.map((n) => n === '…'
                    ? '<span class="px-1 text-xs text-slate-400">…</span>'
                    : `<button type="button" data-page="${n}" class="h-8 min-w-[2rem] rounded-lg px-2 text-xs font-medium transition ${n === currentPage ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100'}">${n}</button>`).join('');
            }

            // Jadikan node sebagai filter (isi dropdown Kegiatan..Akun sesuai jalur induknya)
            function focusNode(n) {
                CH.forEach((k) => { F[k] = ''; });
                let a = n;
                while (a && a.depth >= 1) { F[CH[a.depth - 1]] = a.rawF; a = a.parent; }
                currentPage = 1;
                pendingOpen = 'all';
                fillSel();
                render();
            }

            function afterUnitsChange() {
                const total = new Set(items.map((i) => i.unit)).size;
                U.sub = '';
                currentPage = 1;
                pendingOpen = (U.units.size && U.units.size < total) ? 'all' : 'default';
                fillSel();
                fillUnitSel();
                render();
            }

            // ---- modal Sumber data ----
            const srcModal = $('source-modal');
            const showSource = () => { srcModal.classList.remove('hidden'); $('btn-source-close').focus(); };
            const hideSource = () => { srcModal.classList.add('hidden'); $('btn-source').focus(); };
            $('btn-source').addEventListener('click', showSource);
            $('btn-source-close').addEventListener('click', hideSource);
            $('source-backdrop').addEventListener('click', hideSource);
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !srcModal.classList.contains('hidden')) hideSource();
            });

            // ---- kontrol grafik ----
            document.querySelectorAll('[data-chmode]').forEach((b) => b.addEventListener('click', () => {
                chMode = b.dataset.chmode;
                renderCharts(getTerms());
            }));
            document.querySelectorAll('[data-chgroup]').forEach((b) => b.addEventListener('click', () => {
                chGroup = b.dataset.chgroup === '1';
                renderCharts(getTerms());
            }));
            $('ch-akun-metric').addEventListener('change', () => renderCharts(getTerms()));

            // ---- event ----
            CH.forEach((k, i) => {
                $('f-' + k).addEventListener('change', function () {
                    F[k] = this.value;
                    for (let j = i + 1; j < CH.length; j++) F[CH[j]] = '';
                    currentPage = 1;
                    pendingOpen = CH.some((c) => F[c]) ? 'all' : 'default';
                    fillSel();
                    render();
                });
            });
            $('unit-checks').addEventListener('change', (e) => {
                const cb = e.target.closest('input[type=checkbox]');
                if (!cb) return;
                cb.checked ? U.units.add(cb.value) : U.units.delete(cb.value);
                afterUnitsChange();
            });
            $('btn-unit-all').addEventListener('click', () => { items.forEach((i) => U.units.add(i.unit)); afterUnitsChange(); });
            $('btn-unit-none').addEventListener('click', () => { U.units.clear(); afterUnitsChange(); });
            $('unit-cards').addEventListener('click', (e) => {
                const t = e.target.closest('[data-toggle]');
                if (t) {                        // panah (mode beberapa unit): buka/tutup rincian
                    const u = t.dataset.toggle;
                    openUnits.has(u) ? openUnits.delete(u) : openUnits.add(u);
                    renderUnitCards();
                    return;
                }
                const b = e.target.closest('[data-sub]');   // mode satu unit: klik Asdep/Sesdep
                if (!b) return;
                U.sub = U.sub === b.dataset.sub ? '' : b.dataset.sub;
                currentPage = 1; pendingOpen = 'all';
                fillSel();
                render();
            });

            $('btn-adv').addEventListener('click', () => {
                const p = $('adv-panel');
                p.classList.toggle('hidden');
                $('btn-adv').setAttribute('aria-expanded', String(!p.classList.contains('hidden')));
            });
            $('f-level').addEventListener('change', () => { currentPage = 1; render(); });
            $('f-limit').addEventListener('change', () => { currentPage = 1; render(); });
            $('ang-q').addEventListener('input', () => { currentPage = 1; forceClosed = new Set(); render(); });
            $('btn-prev').addEventListener('click', () => { if (currentPage > 1) { currentPage--; render(); } });
            $('btn-next').addEventListener('click', () => { currentPage++; render(); });
            $('page-nums').addEventListener('click', (e) => {
                const b = e.target.closest('[data-page]');
                if (!b) return;
                currentPage = parseInt(b.dataset.page, 10);
                render();
            });

            $('btn-expand').addEventListener('click', () => {
                if (qActive) forceClosed = new Set(); else openAll();
                render();
            });
            $('btn-collapse').addEventListener('click', () => {
                if (qActive) { forceClosed = new Set(); byId.forEach((n) => { if (n.children.length) forceClosed.add(n.id); }); }
                else open = new Set();
                render();
            });
            $('btn-reset').addEventListener('click', () => {
                CH.forEach((k) => { F[k] = ''; });
                U.units = new Set(); U.sub = '';
                $('ang-q').value = '';
                $('f-level').value = 'all';
                selectedId = null; forceClosed = new Set();
                currentPage = 1; pendingOpen = 'default';
                fillSel(); fillUnitSel(); render();
            });

            $('ang-tb').addEventListener('click', (e) => {
                const tr = e.target.closest('tr[data-id]');
                if (!tr) return;
                const n = byId.get(tr.dataset.id);
                if (!n) return;
                const act = e.target.closest('[data-act]');
                if (act && act.dataset.act === 'toggle') { toggle(n); render(); return; }
                if (act && act.dataset.act === 'focus') { focusNode(n); return; }
                selectedId = selectedId === n.id ? null : n.id;
                render();
            });

            $('path-bar').addEventListener('click', (e) => {
                const pick = e.target.closest('[data-pick]');
                if (pick) { selectedId = pick.dataset.pick; render(); return; }
                if (e.target.closest('[data-clear-sel]')) { selectedId = null; render(); return; }
                if (e.target.closest('[data-focus-sel]') && selectedId && byId.get(selectedId)) { focusNode(byId.get(selectedId)); }
            });

            // Ambil data
            fetch(@json(route('anggaran.data')), { headers: { Accept: 'application/json' } })
                .then((r) => r.json())
                .then((d) => {
                    if (!d.upload) {
                        $('ang-meta').textContent = 'Belum ada data.';
                        $('ang-empty').classList.remove('hidden');
                        return;
                    }
                    // Peta outstanding per MAK (kosong bila belum ada memo outstanding)
                    OUT = d.outstanding || {};
                    // Server: unit (mis. "Asdep 1 D1") + induk (mis. "D1").
                    // Dashboard: unit = kelompok (induk), sub_unit = unit kerja di dalamnya.
                    items = d.items.map((it) => {
                        const unit = it.unit || BELUM;
                        const induk = it.induk || unit;
                        const sub = unit !== induk ? unit : (/^D\d+$/.test(unit) ? unit + ' (langsung)' : '');
                        return { ...it, unit: induk, sub_unit: sub };
                    });
                    const titleCase = (t) => { t = String(t || ''); return t && t === t.toUpperCase() ? t.toLowerCase().replace(/(^|\s)\S/g, (c) => c.toUpperCase()) : t; };
                    $('ang-meta').textContent = [titleCase(d.upload.satker), d.upload.periode].filter(Boolean).join(' – ');
                    if (d.upload.at) {
                        $('ang-updated-text').innerHTML = 'Data yang diupload: <span class="font-medium text-slate-500">Excel FA Detail dari SAKTI</span> · per ' + esc(d.upload.at);
                        $('ang-updated').classList.remove('hidden');
                    }
                    $('ang-body').classList.remove('hidden');
                    fillSel();
                    fillUnitSel();
                    render();
                })
                .catch(() => { $('ang-meta').textContent = 'Gagal memuat data.'; });
        })();
    </script>
</x-app-layout>