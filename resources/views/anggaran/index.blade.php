<x-app-layout title="Anggaran">


    <style>
        /* Tabel: di layar kecil bisa digeser ke samping, di desktop muat penuh */
        .ang-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .ang-scroll table {
            min-width: 1000px;
        }

        @media (min-width: 1024px) {
            .ang-scroll {
                overflow-x: visible;
            }

            .ang-scroll table {
                min-width: 0;
            }
        }




        .tree-row {
            transition: background-color .12s ease;
        }

        .tree-row:hover {
            background-color: rgb(243 244 246 / .8);
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

        @media (prefers-reduced-motion: reduce) {

            .tree-row,
            .chev,
            .tree-row .row-actions {
                transition: none;
            }
        }
    </style>

    <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- ===== Judul & aksi ===== --}}
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-blue-600">Monitor Anggaran</p>
                <h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-800">Laporan Ketersediaan Dana</h2>
                <p id="ang-meta" class="mt-1.5 text-sm text-slate-500">Memuat data...</p>
                <p id="ang-updated" class="mt-1.5 hidden flex items-center gap-1.5 text-xs text-slate-400">
                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <span id="ang-updated-text"></span>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0"> <a id="btn-export"
                    href="{{ route('anggaran.export') }}"
                    class="inline-flex justify-center items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm">
                    <svg class="w-4 h-4 text-slate-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Export Excel
                </a>

                @if ($canUpload)
                    <a href="{{ route('anggaran.upload') }}"
                        class="inline-flex justify-center items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 shrink-0 transition shadow-sm">
                        <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        Upload / Perbarui Data
                    </a>
                @endif
            </div>
        </div>

        <p id="ang-empty"
            class="hidden py-16 text-center text-gray-400 text-sm bg-white border border-gray-100 rounded-2xl">
            Belum ada data anggaran. @if ($canUpload) Silakan upload file dari SAKTI. @endif
        </p>

        <div id="ang-body" class="hidden space-y-6">

            {{-- ===== Ringkasan ===== --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600"><svg
                            class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                        </svg></span>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500">Total Anggaran (Pagu)</p>
                        <p id="s-pagu" class="mt-1 truncate text-lg font-bold text-slate-900 tabular-nums">-</p>
                    </div>
                </div>
                <<<<<<< HEAD <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                    <p class="text-xs text-gray-500">Total Realisasi</p>
                    <p id="s-real" class="mt-1 text-lg font-semibold text-gray-900 tabular-nums">-</p>
                    =======
                    <div class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                        <span
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"><svg
                                class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                            </svg></span>
                        <div class="min-w-0">
                            <p class="text-xs text-slate-500">Sudah Terpakai (Realisasi)</p>
                            <p id="s-real" class="mt-1 truncate text-lg font-bold text-slate-900 tabular-nums">-</p>
                        </div>
                        >>>>>>> bf9473bfdd345f35b1eddc489786f687b58e4092
                    </div>
                    <div class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                        <span
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-violet-50 text-violet-600"><svg
                                class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg></span>
                        <div class="min-w-0">
                            <p class="text-xs text-slate-500">Sisa Anggaran</p>
                            <p id="s-sisa" class="mt-1 truncate text-lg font-bold text-slate-900 tabular-nums">-</p>
                        </div>
                    </div>
                    <<<<<<< HEAD <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                        <p class="text-xs text-gray-500">Penyerapan (% Realisasi)</p>
                        <p id="s-pct" class="mt-1 text-lg font-semibold text-gray-900 tabular-nums">-</p>
                        <div class="mt-2 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                            <div id="s-bar" class="h-full rounded-full bg-indigo-600" style="width:0%"></div>
                            =======
                            <div
                                class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                                <span
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600"><svg
                                        class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185ZM9.75 9h.008v.008H9.75V9Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008V13.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-slate-500">Penyerapan (% Terpakai)</p>
                                    <p id="s-pct" class="mt-1 text-lg font-bold text-slate-900 tabular-nums">-</p>
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                        <div id="s-bar" class="h-full rounded-full bg-blue-600" style="width:0%"></div>
                                    </div>
                                    >>>>>>> bf9473bfdd345f35b1eddc489786f687b58e4092
                                </div>
                            </div>
                        </div>

                        {{-- ===== Filter ===== --}}
                        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4">
                            <div class="border-b border-gray-100 pb-4">
                                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                    <label class="text-xs font-semibold text-slate-800">Unit Kerja <span id="unit-count"
                                            class="font-normal text-gray-400"></span></label>
                                    <div class="inline-flex items-center rounded-xl bg-slate-100 p-0.5">
                                        <button type="button" id="btn-unit-all" style="background:#55739f;color:#fff"
                                            class="rounded-[10px] px-3.5 py-1.5 text-xs font-medium shadow-sm transition hover:opacity-90">Pilih
                                            Semua</button>
                                        <button type="button" id="btn-unit-none"
                                            class="rounded-[10px] px-3.5 py-1.5 text-xs font-medium text-slate-600 transition hover:text-slate-900">Kosongkan</button>
                                    </div>
                                </div>
                                <div id="unit-checks" class="space-y-2"></div>
                            </div>
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-end">
                                <div class="lg:col-span-4">
                                    <label class="block text-xs text-gray-500 mb-1 font-medium">Cari kode /
                                        uraian</label>
                                    <div class="relative">
                                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                        </svg>
                                        <input type="text" id="ang-q"
                                            placeholder="mis. 524, CL.7458, ABB.001 atau konsumsi" autocomplete="off"
                                            class="w-full rounded-xl border border-slate-200 py-2.5 pl-9 pr-3 text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white">
                                    </div>
                                </div>
                                <div class="lg:col-span-3">
                                    <label class="block text-xs text-gray-500 mb-1 font-medium">Akun</label>
                                    <select id="f-akun"
                                        class="w-full truncate rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white"></select>
                                </div>
                                <div class="lg:col-span-3">
                                    <label class="block text-xs text-gray-500 mb-1 font-medium">Kegiatan</label>
                                    <select id="f-keg"
                                        class="w-full truncate rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white"></select>
                                </div>
                                <div class="lg:col-span-2 flex items-center gap-2">
                                    <button type="button" id="btn-adv" aria-expanded="false"
                                        class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                                        </svg>
                                        Filter Lanjutan
                                        <span id="adv-badge"
                                            class="hidden rounded-full bg-blue-600 px-1.5 text-[10px] font-semibold leading-4 text-white"></span>
                                    </button>
                                    <button type="button" id="btn-reset"
                                        class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition"><svg
                                            class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>Reset</button>
                                </div>
                            </div>

                            <div id="adv-panel"
                                class="hidden grid grid-cols-1 sm:grid-cols-3 gap-3 border-t border-gray-100 pt-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1 font-medium">RO / SubOutput</label>
                                    <select id="f-ro"
                                        class="w-full truncate rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white"></select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1 font-medium">Komponen</label>
                                    <select id="f-komp"
                                        class="w-full truncate rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white"></select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1 font-medium">SubKomponen</label>
                                    <select id="f-sub"
                                        class="w-full truncate rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white"></select>
                                </div>
                            </div>

                        </div>

                        {{-- ===== Per unit kerja (dikelompokkan per induk) ===== --}}
                        <div id="section-unit" class="hidden space-y-3">
                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-700">Anggaran per Unit Kerja</h3>
                                    <p id="unit-sub" class="text-xs text-gray-400">Kartu muncul untuk unit yang
                                        dicentang pada
                                        filter Unit Kerja di atas.</p>
                                </div>
                            </div>
                            <div id="unit-cards"></div>
                        </div>

                        {{-- ===== Tabel hierarki ===== --}}
                        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm [overflow:clip]">
                            <div
                                class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 px-5 py-3 border-b border-gray-100">
                                <div id="ang-hint"
                                    class="text-xs text-gray-500 flex flex-wrap items-center gap-x-4 gap-y-1"></div>
                                <div class="flex flex-wrap items-center gap-2 shrink-0"> <button type="button"
                                        id="btn-expand"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 shadow-sm hover:bg-gray-50 transition">
                                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                        </svg>
                                        Buka semua
                                    </button>
                                    <button type="button" id="btn-collapse"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 shadow-sm hover:bg-gray-50 transition">
                                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5 5.25 5.25" />
                                        </svg>
                                        Tutup semua
                                    </button>
                                    <label class="inline-flex items-center gap-2 text-xs text-gray-500">Level
                                        <select id="f-level"
                                            class="rounded-lg border border-gray-200 bg-white py-1.5 pl-3 pr-8 text-xs text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
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
                                class="hidden px-5 py-2.5 bg-gray-50 border-b border-gray-200 text-xs text-gray-700 flex flex-wrap items-center gap-x-2 gap-y-1">
                            </div>

                            {{-- Tabel muat penuh di lebar kartu: kolom berukuran tetap (persen), tanpa scroll samping
                            --}}
                            <div class="ang-scroll">
                                <table class="w-full table-fixed text-[11px] leading-tight">
                                    <colgroup>
                                        <col style="width:13%">
                                        <col style="width:23.5%">
                                        <col style="width:9%">
                                        <col style="width:10%">
                                        <col style="width:10%">
                                        <col style="width:9%">
                                        <col style="width:10%">
                                        <col style="width:5.5%">
                                        <col style="width:10%">
                                    </colgroup>
                                    <thead style="background:#eef1f6"
                                        class="text-[11px] text-slate-700 sticky top-0 z-10 shadow-[0_1px_0_0_#d1d5db]">
                                        <tr>
                                            <th class="text-left px-2 py-1.5 font-semibold">Kode</th>
                                            <th class="text-left px-2 py-1.5 font-semibold">Uraian</th>
                                            <th class="text-left px-2 py-1.5 font-semibold">Unit</th>
                                            <th class="text-right px-2 py-1.5 font-semibold">Pagu Revisi</th>
                                            <th class="text-right px-2 py-1.5 font-semibold">Real. Lalu</th>
                                            <th class="text-right px-2 py-1.5 font-semibold">Real. Ini</th>
                                            <th class="text-right px-2 py-1.5 font-semibold">Real. s.d.</th>
                                            <th class="text-right px-2 py-1.5 font-semibold">%</th>
                                            <th class="text-right px-2 py-1.5 font-semibold">Sisa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ang-tb" class="divide-y divide-gray-200"></tbody>
                                </table>
                                <p id="ang-norow" class="hidden py-14 text-center text-sm text-gray-400">Tidak ada baris
                                    yang cocok.
                                    Ubah kata kunci atau reset filter.</p>
                            </div>

                            <div
                                class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-3 border-t border-gray-100">
                                <p id="ang-cnt" class="text-xs text-gray-500"></p>
                                <div class="flex flex-wrap items-center justify-center gap-2"> <select id="f-limit"
                                        aria-label="Baris per halaman"
                                        class="rounded-lg border border-gray-200 bg-white py-1.5 pl-3 pr-8 text-xs text-gray-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
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
<<<<<<< HEAD
                const pctTxt = (x) => x.toFixed(1).replace('.', ',') + ' %';
                // Angka kartu ditampilkan penuh, tanpa pembulatan
                const short = (n) => 'Rp ' + rp(n);
=======
            // Persen selalu 2 desimal (mis. 72,50 %)
            const pctTxt = (x) => x.toFixed(2).replace('.', ',') + '%';
            // Semua angka ringkas dalam miliar, 2 desimal (nilai penuh ada di tooltip)
            const short = (n) => (n / 1e9).toFixed(2).replace('.', ',');
>>>>>>> bf9473bfdd345f35b1eddc489786f687b58e4092

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

                // Tampilan netral: hanya level atas yang diberi latar abu-abu tipis
                const LV_STYLE = { prog: { row: '' }, keg: { row: '' }, ro: { row: '' }, komp: { row: '' }, sub: { row: '' }, akun: { row: '' }, item: { row: '' } };

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
                                    hay: (kode + ' ' + raw + ' ' + uraian).toLowerCase(),
                                    v: zero(), children: [], map: new Map(), parent: node,
                                    us: new Set(), ss: new Set(),
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
                            hay: (kode + ' ' + uraian).toLowerCase(), v, children: [], parent: node,
                            us: new Set([it.unit]), ss: it.sub_unit ? new Set([it.sub_unit]) : new Set(),
                        };
                        node.children.push(leaf);
                        byId.set(leaf.id, leaf);
                    });
                    return root;
                }

                // Tandai: self = cocok kata kunci, under = dirinya/induknya cocok (turunan), vis = ditampilkan
                function mark(n, terms, under) {
                    n.self = !!terms && terms.some((t) => n.hay.includes(t));
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
                        const seen = new Set();
                        let html = '<option value="">Semua</option>';
                        items.forEach((it) => {
                            const kode = it[k + '_kode'];
                            if (!inUnit(it) || !match(it, i) || seen.has(kode)) return;
                            seen.add(kode);
                            const full = padKode(k, String(kode ?? '')) + ' – ' + (it[k + '_uraian'] || '');
                            html += `<option value="${esc(kode)}" title="${esc(full)}">${esc(clip(full, 80))}</option>`;
                        });
                        if (F[k] && !seen.has(F[k])) F[k] = '';   // pilihan lama tidak ada di unit ini -> kosongkan
                        const sel = $('f-' + k);
                        sel.innerHTML = html;
                        sel.value = F[k];
                    });
                }

                // Urutan unit: Asdep 1..5, lalu Sesdep, lalu lainnya; "Belum dipetakan" selalu terakhir
                const unitRank = (s) => /^Asdep/i.test(s) ? 0 : /^Sesdep/i.test(s) ? 1 : 2;
                const cmpUnit = (a, b) => a === BELUM ? 1 : b === BELUM ? -1
                    : (unitRank(a) - unitRank(b)) || a.localeCompare(b, 'id', { numeric: true });

                const getTerms = () => {
                    const q = $('ang-q').value.trim();
                    return q ? q.toLowerCase().split(/[,;\n]+/).map((t) => t.trim()).filter(Boolean) : [];
                };
                // Item dianggap cocok bila dirinya atau salah satu induknya (program..akun) memuat kata kunci
                const matchQ = (it, terms) => {
                    if (!terms.length) return true;
                    const parts = [];
                    LEVELS.forEach((lv) => { const raw = lvKode(it, lv); parts.push(padKode(lv.key, raw), raw, lvUraian(it, lv)); });
                    parts.push(String(it.item_kode ?? ''), String(it.item_uraian ?? ''));
                    const hay = parts.join(' | ').toLowerCase();
                    return terms.some((t) => hay.includes(t));
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
                        return `<label class="inline-flex cursor-pointer select-none items-center gap-2 rounded-lg border px-3 py-1 text-xs transition ${on ? 'border-blue-400 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'} ${u === BELUM && !on ? 'border-amber-200 text-amber-800' : ''}">
                        <input type="checkbox" value="${esc(u)}" ${on ? 'checked' : ''} class="h-3.5 w-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">${esc(u)}</label>`;
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

                // ---- kartu unit kerja (hanya untuk unit yang dicentang) ----
                // Dihitung dari data yang lolos filter Kegiatan..Akun, TANPA filter unit.
                function renderUnitCards() {
                    const section = $('section-unit');
                    section.classList.toggle('hidden', U.units.size === 0);
                    if (!U.units.size) { $('unit-cards').innerHTML = ''; return; }

                    const terms = getTerms();
                    const by = new Map();
                    items.filter((it) => match(it, 5) && U.units.has(it.unit) && matchQ(it, terms)).forEach((it) => {
                        const v = vals(it);
                        let a = by.get(it.unit);
                        if (!a) { a = { unit: it.unit, pagu: 0, sd: 0, subs: new Map() }; by.set(it.unit, a); }
                        a.pagu += v.pagu; a.sd += v.sd;
                        if (it.sub_unit) {
                            let x = a.subs.get(it.sub_unit);
                            if (!x) { x = { pagu: 0, sd: 0 }; a.subs.set(it.sub_unit, x); }
                            x.pagu += v.pagu; x.sd += v.sd;
                        }
                    });
                    $('unit-sub').textContent = terms.length
                        ? 'Hasil pencarian "' + terms.join(', ') + '" per unit (angka hanya dari kode yang cocok).'
                        : 'Kartu muncul untuk unit yang dicentang pada filter Unit Kerja di atas.';
                    const empty = (a) => terms.length && !a.pagu && !a.sd;
                    const list = orderUnits([...U.units]).map((u) => by.get(u) || { unit: u, pagu: 0, sd: 0, subs: new Map() });
                    const single = list.length === 1;
                    const sumP = list.reduce((t, a) => t + a.pagu, 0);
                    const sumR = list.reduce((t, a) => t + a.sd, 0);

                    const money = (v) => `<span title="Rp ${rp(v)}">${short(v)}</span>`;
                    const bar = (p, h = 'h-1.5') => `<div class="${h} w-full overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full rounded-full bg-blue-500 transition-all" style="width:${Math.min(100, p)}%"></div></div>`;
                    const chevron = (o) => `<svg class="chev w-3.5 h-3.5 ${o ? 'is-open' : ''}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>`;
                    const stat = (label, v) => `<div>
                    <p class="text-[10px] uppercase tracking-wide text-gray-400">${label}</p>
                    <p class="mt-0.5 text-[13px] font-semibold tabular-nums text-gray-900">${money(v)}</p></div>`;
                    const subLabel = (unit, name) => /\(langsung\)/.test(name) ? 'Langsung ke ' + unit : name.replace(/\s+D\d+$/i, '');
                    const sortedSubs = (a) => [...a.subs.entries()].sort((x, y) => cmpUnit(x[0], y[0]));

                    // ---------- Satu unit dipilih: kartu lebar + rincian bisa diklik ----------
                    const wideCard = (a) => {
                        if (empty(a)) return `<div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                        <h4 class="text-lg font-semibold text-gray-900">${esc(a.unit)}</h4>
                        <p class="mt-2 text-sm text-gray-400">Tidak ada kode yang cocok dengan pencarian di unit ini.</p></div>`;
                        const p = a.pagu ? a.sd / a.pagu * 100 : 0;
                        const warn = a.unit === BELUM;
                        const subs = sortedSubs(a);
                        // Mode banyak unit: kartu tertutup dulu, klik header untuk buka rincian
                        const collapsible = !single && subs.length > 0;
                        const expanded = !collapsible || openUnits.has(a.unit);
                        const tog = collapsible ? `data-toggle="${esc(a.unit)}" role="button" aria-expanded="${expanded}"` : '';
                        const subCard = ([name, x]) => {
                            const sp = x.pagu ? x.sd / x.pagu * 100 : 0;
                            const on = U.sub === name;
                            return `<div data-sub="${esc(name)}" aria-selected="${on}"
                            class="cursor-pointer rounded-lg border px-3.5 py-3 transition ${on ? 'border-blue-400 bg-blue-50/60 ring-1 ring-blue-400' : 'border-gray-100 hover:border-gray-200 hover:bg-gray-50'}">
                            <div class="flex items-baseline justify-between gap-3">
                                <span class="truncate text-xs font-medium ${on ? 'text-blue-700' : 'text-gray-700'}">${esc(subLabel(a.unit, name))}</span>
                                <span class="shrink-0 text-sm font-semibold tabular-nums text-gray-900">${pctTxt(sp)}</span>
                            </div>
                            <div class="mt-2">${bar(sp, 'h-1')}</div>
                            <div class="mt-2 flex justify-between text-[11px] tabular-nums text-gray-500">
                                <span>Pagu <b class="font-medium text-gray-700">${short(x.pagu)}</b></span>
                                <span>Sisa <b class="font-medium text-gray-700">${short(x.pagu - x.sd)}</b></span>
                            </div>
                        </div>`;
                        };
                        return `<div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                        <div ${tog} class="flex flex-wrap items-end justify-between gap-x-10 gap-y-4 ${collapsible ? 'cursor-pointer' : ''}">
                            <div>
                                <p class="text-[11px] uppercase tracking-wide text-gray-400">Unit terpilih</p>
                                <h4 class="mt-0.5 flex items-center gap-2 text-lg font-semibold ${warn ? 'text-amber-800' : 'text-gray-900'}">${collapsible ? `<span class="text-gray-400">${chevron(expanded)}</span>` : ''}${esc(a.unit)}</h4>
                                ${warn ? '<p class="mt-1 text-[11px] font-medium text-amber-700">Belum dipetakan oleh admin</p>' : ''}
                            </div>
                            <div class="flex flex-wrap items-end gap-x-8 gap-y-3">
                                ${stat('Pagu', a.pagu)}${stat('Realisasi', a.sd)}${stat('Sisa', a.pagu - a.sd)}
                                <div class="text-right">
                                    <p class="text-[10px] uppercase tracking-wide text-gray-400">Penyerapan</p>
                                    <p class="text-2xl font-semibold tabular-nums tracking-tight text-gray-900">${pctTxt(p)}</p>
                                </div>
                            </div>
                        </div>
                        <div ${tog} class="mt-4 ${collapsible ? 'cursor-pointer' : ''}">${bar(p)}</div>
                        ${subs.length && expanded ? `<div class="mt-5 border-t border-gray-100 pt-4">
                            <p class="mb-2.5 text-[11px] text-gray-400">Rincian Asdep / Sesdep &middot; klik untuk mempersempit tabel, klik lagi untuk membatalkan</p>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">${subs.map(subCard).join('')}</div>
                        </div>` : ''}
                    </div>`;
                    };

                    // ---------- Beberapa unit dipilih: kartu ringkas berdampingan ----------
                    // Angka disusun sebagai baris berlabel (kiri: label, kanan: angka) supaya angka penuh tidak saling menimpa.
                    const dRow = (label, v, strong) => `<div class="flex items-baseline justify-between gap-3 py-2">
                    <dt class="text-xs text-gray-500">${label}</dt>
                    <dd class="tabular-nums text-[13px] ${strong ? 'font-semibold text-gray-900' : 'font-medium text-gray-700'}" title="${short(v)}">${short(v)}</dd></div>`;
                    const compactCard = (a) => {
                        if (empty(a)) return `<div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/60 p-5">
                        <h4 class="truncate text-sm font-semibold text-gray-400">${esc(a.unit)}</h4>
                        <p class="mt-3 text-xs text-gray-400">Tidak ada kode yang cocok.</p></div>`;
                        const p = a.pagu ? a.sd / a.pagu * 100 : 0;
                        const warn = a.unit === BELUM;
                        const subs = sortedSubs(a);
                        const isOpen = openUnits.has(a.unit);
                        const subHtml = isOpen && subs.length ? `<div class="mt-4 space-y-3 border-t border-gray-100 pt-4">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-gray-400">Rincian</p>
                        ${subs.map(([name, x]) => {
                            const sp = x.pagu ? x.sd / x.pagu * 100 : 0;
                            return `<div>
                                <div class="flex items-baseline justify-between gap-3">
                                    <span class="truncate text-xs text-gray-600">${esc(subLabel(a.unit, name))}</span>
                                    <span class="shrink-0 text-xs font-semibold tabular-nums text-gray-800">${pctTxt(sp)}</span>
                                </div>
                                <div class="mt-1.5">${bar(sp, 'h-1')}</div>
                                <div class="mt-1 flex justify-between text-[11px] tabular-nums text-gray-400">
                                    <span>Pagu ${short(x.pagu)}</span><span>Sisa ${short(x.pagu - x.sd)}</span>
                                </div>
                            </div>`;
                        }).join('')}</div>` : '';
                        return `<div class="overflow-hidden rounded-xl border bg-white shadow-sm transition hover:shadow-md ${warn ? 'border-amber-200' : 'border-gray-200'}">
                        <div class="h-1 ${warn ? 'bg-amber-400' : 'bg-indigo-500'}"></div>
                        <div class="p-5">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="truncate text-sm font-semibold tracking-wide ${warn ? 'text-amber-800' : 'text-gray-900'}">${esc(a.unit)}</h4>
                                ${subs.length ? `<button type="button" data-toggle="${esc(a.unit)}" aria-label="Lihat rincian ${esc(a.unit)}" aria-expanded="${isOpen}"
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:bg-gray-50 hover:text-gray-700 transition">${chevron(isOpen)}</button>` : ''}
                            </div>
                            <div class="mt-4 flex items-end justify-between">
                                <p class="text-3xl font-semibold tabular-nums tracking-tight text-gray-900">${pctTxt(p)}</p>
                                <p class="pb-1 text-[11px] uppercase tracking-wider text-gray-400">Penyerapan</p>
                            </div>
                            <div class="mt-3">${bar(p)}</div>
                            <dl class="mt-4 divide-y divide-gray-100 border-t border-gray-100">
                                ${dRow('Pagu', a.pagu)}${dRow('Realisasi', a.sd)}${dRow('Sisa', a.pagu - a.sd, true)}
                            </dl>
                            ${warn ? '<p class="mt-2 text-[11px] font-medium text-amber-700">Belum dipetakan oleh admin</p>' : ''}
                            ${subHtml}
                        </div>
                    </div>`;
                    };

                    const totalP = sumP ? sumR / sumP * 100 : 0;
                    $('unit-cards').innerHTML = single
                        ? wideCard(list[0]) + '<p class="mt-2 text-[11px] text-gray-400">Angka dalam miliar rupiah (arahkan kursor ke angka untuk nilai penuh).</p>'
                        : `<div class="space-y-4">${list.map(wideCard).join('')}</div>
                       <div class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-lg bg-gray-50 px-4 py-2.5 text-xs text-gray-600">
                           <span>Total ${list.length} unit terpilih: Pagu <b class="text-gray-900">${short(sumP)}</b> · Terpakai <b class="text-gray-900">${short(sumR)}</b> · Sisa <b class="text-gray-900">${short(sumP - sumR)}</b> · <b class="text-gray-900">${pctTxt(totalP)}</b></span>
                           <span class="text-gray-400">Angka dalam miliar rupiah. Klik kartu unit untuk membuka rincian Asdep/Sesdep.</span>
                       </div>`;
                }

                // highlight kata kunci pada teks
                function hl(text, re) {
                    text = String(text ?? '');
                    if (!re) return esc(text);
                    let out = '', last = 0, m;
                    re.lastIndex = 0;
                    while ((m = re.exec(text))) {
                        if (m[0] === '') { re.lastIndex++; continue; }
                        out += esc(text.slice(last, m.index)) + '<mark class="bg-yellow-200 text-gray-900 rounded px-0.5">' + esc(m[0]) + '</mark>';
                        last = m.index + m[0].length;
                    }
                    return out + esc(text.slice(last));
                }

                const ancestorsOf = (n) => { const a = []; let p = n.parent; while (p && p.depth >= 0) { a.unshift(p); p = p.parent; } return a; };

                function renderPathBar() {
                    const bar = $('path-bar');
                    const sel = selectedId ? byId.get(selectedId) : null;
                    if (!sel) { bar.classList.add('hidden'); bar.innerHTML = ''; return; }
                    const chain = [...ancestorsOf(sel), sel];
                    const crumbs = chain.map((n, i) => {
                        const last = i === chain.length - 1;
                        return `<button type="button" data-pick="${esc(n.id)}" class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 ${last ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 hover:bg-gray-100'} transition">
                        <span class="opacity-70">${esc(n.lv.label)}</span><span class="font-mono">${esc(n.kode || '—')}</span></button>`;
                    }).join('<svg class="w-3 h-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>');
                    const childInfo = sel.children.length ? `<span class="text-gray-500">· ${sel.children.length} ${esc(sel.children[0].lv.label.toLowerCase())} di bawahnya</span>` : '';
                    const canFocus = sel.depth >= 1 && sel.depth <= 5;
                    bar.innerHTML = `<span class="font-medium">Jalur:</span>${crumbs}${childInfo}
                    <span class="ml-auto flex items-center gap-2">
                        ${canFocus ? '<button type="button" data-focus-sel class="rounded-md border border-gray-200 bg-white px-2 py-0.5 hover:bg-gray-100 transition">Fokus ke sini</button>' : ''}
                        <button type="button" data-clear-sel class="rounded-md border border-gray-200 bg-white px-2 py-0.5 hover:bg-gray-100 transition">Lepas pilihan</button>
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
                    updateExportLink();

                    // 4) petunjuk di atas tabel
                    const nMatch = all.filter((n) => n.self).length;
                    $('ang-hint').innerHTML = qActive
                        ? `<span class="font-medium text-gray-700">${nMatch} baris cocok</span>
                       <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-yellow-200 inline-block"></span>Cocok</span>
                       <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-gray-300 inline-block"></span>Induk (jalur ke atas)</span>
                       <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-white border border-gray-300 inline-block"></span>Turunan (semua di bawahnya)</span>`
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
                    const dash = '<span class="text-gray-300">–</span>';

                    $('ang-norow').classList.toggle('hidden', totalRows > 0);

                    $('ang-tb').innerHTML = pageRows.map((r) => {
                        const v = r.v;
                        const st = LV_STYLE[r.lv.key];
                        const pct = v.pagu > 0 ? v.sd / v.pagu : 0;
                        const isGroup = r.lv.key !== 'item';
                        const bold = r.depth === 0 ? 'font-semibold' : '';
                        const numBold = r.depth === 0 ? 'font-semibold' : '';
                        const hasKids = r.children.length > 0;
                        const expandable = hasKids && r.depth < maxDepth;
                        const opened = expandable && isOpen(r);

                        // warna latar: pilihan > cocok > jenis level
                        let bg = st.row;
                        let ring = '';
                        let relChip = '';
                        if (qActive && r.rel === 'match') { bg = 'bg-yellow-50'; }
                        if (qActive && r.rel === 'parent') { relChip = '<span class="rounded px-1.5 py-0.5 text-[10px] font-medium bg-gray-200 text-gray-600">Induk</span>'; }
                        if (sel) {
                            if (r.id === sel.id) { bg = 'bg-blue-50'; ring = 'ring-1 ring-inset ring-blue-300'; relChip = '<span class="rounded px-1.5 py-0.5 text-[10px] font-medium bg-blue-600 text-white">Dipilih</span>'; }
                            else if (selAnc.has(r.id)) { bg = 'bg-gray-100'; relChip = '<span class="rounded px-1.5 py-0.5 text-[10px] font-medium bg-gray-200 text-gray-700">Induk</span>'; }
                            else if (r.id.startsWith(sel.id + '|')) { bg = 'bg-gray-50'; }
                        }

                        // kolom Unit: terisi bila semua item di bawah baris ini milik satu unit
                        let unitCell = '<span class="text-gray-300">–</span>';
                        if (r.us && r.us.size === 1) {
                            const u = [...r.us][0];
                            const sName = r.ss.size === 1 ? [...r.ss][0] : '';
                            const label = sName ? (/\(langsung\)/.test(sName) ? u : sName.replace(/\s+D\d+$/i, '') + ' ' + u) : u;
                            unitCell = `<span class="${u === BELUM ? 'text-amber-700' : (sName ? 'text-blue-600' : 'text-gray-700')}">${esc(label)}</span>`;
                        } else if (r.us && r.us.size > 1) {
                            unitCell = `<span class="text-gray-400">${r.us.size} unit</span>`;
                        }

                        const chev = expandable
                            ? `<button type="button" data-act="toggle" aria-label="Buka/tutup" aria-expanded="${opened}" class="shrink-0 w-4 h-4 mr-1 rounded flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                               <svg class="chev w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                           </button>`.replace('class="chev w-3 h-3"', `class="chev w-3 h-3 ${opened ? 'is-open' : ''}"`)
                            : '<span class="shrink-0 w-4 h-4 mr-1"></span>';

                        const hint = hasKids
                            ? `${r.children.length} ${r.children[0].lv.label.toLowerCase()}${(r.depth >= maxDepth) ? ' · tersembunyi (batas level)' : ''}`
                            : '';

                        const focusBtn = (r.depth >= 1 && r.depth <= 5)
                            ? `<button type="button" data-act="focus" title="Fokus ke ${esc(r.lv.label)} ini (filter)" class="row-actions ml-auto shrink-0 rounded border border-gray-200 bg-white p-0.5 text-gray-500 hover:text-blue-600 hover:border-blue-300 transition">
                               <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3m0 12v3M3 12h3m12 0h3M12 8.25a3.75 3.75 0 1 0 0 7.5 3.75 3.75 0 0 0 0-7.5Z" /></svg>
                           </button>`
                            : '';

                        // indentasi lebih rapat supaya kolom Kode tidak melebar
                        const indent = 4 + Math.min(r.depth, 7) * 10;
                        const num = 'px-2 py-1 text-right whitespace-nowrap tabular-nums align-middle';

                        return `<tr class="tree-row group cursor-pointer ${bg} ${ring}" data-id="${esc(r.id)}"${hint ? ` title="${esc(hint)}"` : ''}>
                        <td class="py-1 pr-1 align-middle">
                            <div class="flex flex-wrap items-center" style="padding-left:${indent}px">
                                ${chev}<span class="tabular-nums text-gray-800 ${bold}">${hl(r.kode || '—', re)}</span>${relChip ? `<span class="ml-1">${relChip}</span>` : ''}
                            </div>
                        </td>
                        <td class="py-1 px-2 align-middle">
                            <div class="flex items-center gap-1">
                                <span class="min-w-0 leading-snug text-gray-800 ${bold} break-words">${hl(r.uraian, re)}</span>
                                ${focusBtn}
                            </div>
                        </td>
                        <td class="py-1 px-2 align-middle break-words">${unitCell}</td>
                        <td class="${num} ${numBold}">${rp(v.pagu)}</td>
                        <td class="${num} ${numBold}">${v.lalu === null ? dash : rp(v.lalu)}</td>
                        <td class="${num} ${numBold}">${v.ini === null ? dash : rp(v.ini)}</td>
                        <td class="${num} ${numBold}">${rp(v.sd)}</td>
                        <td class="${num}">${(pct * 100).toFixed(2).replace('.', ',')}%</td>
                        <td class="${num} ${numBold}">${rp(v.pagu - v.sd)}</td>
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
                        if (d.upload.at) { $('ang-updated-text').textContent = 'Diperbarui pada ' + d.upload.at; $('ang-updated').classList.remove('hidden'); }
                        $('ang-body').classList.remove('hidden');
                        fillSel();
                        fillUnitSel();
                        render();
                    })
                    .catch(() => { $('ang-meta').textContent = 'Gagal memuat data.'; });
            })();
        </script>
</x-app-layout>