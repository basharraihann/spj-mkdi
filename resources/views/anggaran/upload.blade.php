<x-app-layout title="Upload Anggaran">

    <style>
        .card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 0 0 1px rgb(226 232 240 / .8), 0 1px 2px rgb(15 23 42 / .04);
        }

        .dz {
            transition: border-color .15s ease, background-color .15s ease;
        }

        .dz.is-drag {
            border-color: #2563eb;
            background-color: rgb(239 246 255);
        }

        .dz:focus-within {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgb(37 99 235 / .15);
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spin {
            animation: spin .8s linear infinite;
        }

        @media (prefers-reduced-motion: reduce) {

            .dz,
            .spin {
                transition: none;
                animation: none;
            }
        }
    </style>

    @php
        // Semua waktu ditampilkan dalam WIB (Asia/Jakarta), apa pun zona waktu aplikasi.
        $wib = fn($d) => $d ? \Illuminate\Support\Carbon::parse($d)->timezone('Asia/Jakarta')->locale('id') : null;
        $active = $uploads->firstWhere('is_active', true);
        $activeAt = $active ? $wib($active->created_at) : null;
    @endphp

    <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- ===== Judul (pita putih di bawah navbar, sama seperti Monitor Anggaran) ===== --}}
        <div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-8 border-b border-slate-200 bg-white px-4 py-5 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-slate-800">Upload Data Anggaran</h2>
                    <p class="mt-1 max-w-3xl text-sm text-slate-500">Perbarui Laporan Ketersediaan Dana dari SAKTI. Data
                        yang berhasil diproses langsung menjadi data aktif.</p>
                </div>
                <a href="{{ route('anggaran.index') }}"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
                    <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Monitor Anggaran
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <svg class="mt-0.5 h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <svg class="mt-0.5 h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>{{ session('error') }} Data aktif sebelumnya tidak berubah.</span>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">

            {{-- ===== Form upload ===== --}}
            <div class="card p-5 sm:p-6 lg:col-span-3">
                <div class="flex items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-slate-800">Perbarui data</h3>
                        <p class="text-sm text-slate-500">Pilih file Laporan Ketersediaan Dana Detail dari SAKTI.</p>
                    </div>
                </div>

                <form id="up-form" action="{{ route('anggaran.upload.store') }}" method="POST"
                    enctype="multipart/form-data" class="mt-5">
                    @csrf

                    <label id="up-dz" for="up-file"
                        class="dz flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/60 px-6 py-10 text-center hover:border-blue-400 hover:bg-blue-50/40">
                        <svg class="h-9 w-9 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.3" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <span class="text-sm font-medium text-slate-700">Tarik file ke sini, atau <span class="text-blue-600 underline underline-offset-2">pilih dari komputer</span></span>
                        <span class="text-xs text-slate-400">Format .xlsx atau .xls, maksimal 20 MB</span>
                        <input id="up-file" type="file" name="file" accept=".xlsx,.xls" required class="sr-only">
                    </label>

                    {{-- File terpilih --}}
                    <div id="up-chosen" class="mt-3 hidden items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p id="up-name" class="truncate text-sm font-medium text-slate-800"></p>
                            <p id="up-size" class="text-xs text-slate-400"></p>
                        </div>
                        <button type="button" id="up-clear"
                            class="shrink-0 rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-500 transition hover:bg-slate-50 hover:text-slate-700">Ganti</button>
                    </div>

                    <p id="up-err" class="mt-2 hidden text-sm text-red-600" role="alert"></p>
                    @error('file')
                        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <button id="up-submit" type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                            <svg id="up-spin" class="spin hidden h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                            </svg>
                            <span id="up-label">Upload dan proses</span>
                        </button>
                        <p class="text-xs text-slate-400">Kalau gagal diproses, data aktif tetap aman.</p>
                    </div>
                </form>
            </div>

            {{-- ===== Data aktif ===== --}}
            <div class="card p-5 sm:p-6 lg:col-span-2">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-base font-semibold text-slate-800">Data aktif</h3>
                    @if ($active)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-100">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Dipakai di Monitor
                        </span>
                    @endif
                </div>

                @if ($active)
                    <dl class="mt-4 divide-y divide-slate-100 text-sm">
                        <div class="flex items-start justify-between gap-4 py-2.5">
                            <dt class="text-slate-500">Periode</dt>
                            <dd class="text-right font-medium text-slate-800">{{ $active->periode ?: '-' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 py-2.5">
                            <dt class="shrink-0 text-slate-500">File</dt>
                            <dd class="min-w-0 break-all text-right text-slate-800">{{ $active->nama_file }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 py-2.5">
                            <dt class="text-slate-500">Diunggah</dt>
                            <dd class="text-right text-slate-800">
                                <span class="block font-medium">{{ $activeAt->translatedFormat('l, j F Y') }}</span>
                                <span class="block tabular-nums text-xs text-slate-500">{{ $activeAt->format('H:i:s') }} WIB · {{ $activeAt->diffForHumans() }}</span>
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 py-2.5">
                            <dt class="text-slate-500">Oleh</dt>
                            <dd class="text-right text-slate-800">{{ $active->uploader->name ?? '-' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 py-2.5">
                            <dt class="text-slate-500">Jumlah item</dt>
                            <dd class="text-right tabular-nums text-slate-800">{{ number_format($active->jumlah_item, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 py-2.5">
                            <dt class="text-slate-500">Total pagu</dt>
                            <dd class="text-right font-semibold tabular-nums text-slate-900">Rp {{ number_format($active->total_pagu, 0, ',', '.') }}</dd>
                        </div>
                    </dl>
                @else
                    <div class="mt-4 rounded-xl bg-slate-50 px-4 py-8 text-center">
                        <p class="text-sm font-medium text-slate-700">Belum ada data aktif</p>
                        <p class="mt-1 text-xs text-slate-400">Upload file pertama dari SAKTI untuk mulai memakai Monitor Anggaran.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== Riwayat upload ===== --}}
        <div class="card overflow-hidden">
            <div class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h3 class="text-base font-semibold text-slate-800">Riwayat upload</h3>
                    <p class="text-sm text-slate-500">Waktu dalam WIB (Asia/Jakarta). Sistem menyimpan 5 upload terakhir, yang lebih lama dihapus otomatis.</p>
                </div>
            </div>

            <div class="overflow-x-auto border-t border-slate-100">
                <table class="w-full min-w-[860px] text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500">
                        <tr>
                            <th class="px-5 py-2.5 text-left font-medium">Waktu upload</th>
                            <th class="px-4 py-2.5 text-left font-medium">Periode</th>
                            <th class="px-4 py-2.5 text-left font-medium">File</th>
                            <th class="px-4 py-2.5 text-left font-medium">Oleh</th>
                            <th class="px-4 py-2.5 text-right font-medium">Item</th>
                            <th class="px-4 py-2.5 text-right font-medium">Total pagu</th>
                            <th class="px-5 py-2.5 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($uploads as $u)
                            @php $t = $wib($u->created_at); @endphp
                            <tr class="{{ $u->is_active ? 'bg-blue-50/40' : '' }}">
                                <td class="whitespace-nowrap px-5 py-3 align-top">
                                    <span class="block font-medium text-slate-800">{{ $t->translatedFormat('l, j F Y') }}</span>
                                    <span class="block tabular-nums text-xs text-slate-500">{{ $t->format('H:i:s') }} WIB · {{ $t->diffForHumans() }}</span>
                                </td>
                                <td class="px-4 py-3 align-top text-slate-700">{{ $u->periode ?: '-' }}</td>
                                <td class="max-w-[320px] break-all px-4 py-3 align-top text-slate-700">{{ $u->nama_file }}</td>
                                <td class="px-4 py-3 align-top text-slate-700">{{ $u->uploader->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-right align-top tabular-nums text-slate-700">{{ number_format($u->jumlah_item, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right align-top tabular-nums text-slate-700">Rp {{ number_format($u->total_pagu, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 align-top">
                                    @if ($u->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-100">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">Arsip</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <p class="text-sm font-medium text-slate-600">Belum ada upload</p>
                                    <p class="mt-1 text-xs text-slate-400">Riwayat akan muncul di sini setelah file pertama diproses.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var form = document.getElementById('up-form');
            var file = document.getElementById('up-file');
            var dz = document.getElementById('up-dz');
            var chosen = document.getElementById('up-chosen');
            var nameEl = document.getElementById('up-name');
            var sizeEl = document.getElementById('up-size');
            var err = document.getElementById('up-err');
            var btn = document.getElementById('up-submit');
            var MAX = 20 * 1024 * 1024;

            function fmtSize(b) {
                return b >= 1048576 ? (b / 1048576).toFixed(1).replace('.', ',') + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB';
            }
            function showError(msg) {
                err.textContent = msg;
                err.classList.toggle('hidden', !msg);
            }
            function reset() {
                file.value = '';
                chosen.classList.add('hidden');
                chosen.classList.remove('flex');
                dz.classList.remove('hidden');
                showError('');
            }
            function check() {
                var f = file.files[0];
                if (!f) { reset(); return; }
                if (!/\.(xlsx|xls)$/i.test(f.name)) { reset(); showError('File harus berformat .xlsx atau .xls.'); return; }
                if (f.size > MAX) { reset(); showError('Ukuran file maksimal 20 MB.'); return; }
                showError('');
                nameEl.textContent = f.name;
                sizeEl.textContent = fmtSize(f.size);
                chosen.classList.remove('hidden');
                chosen.classList.add('flex');
                dz.classList.add('hidden');
            }

            file.addEventListener('change', check);
            document.getElementById('up-clear').addEventListener('click', function () { reset(); file.click(); });

            ['dragenter', 'dragover'].forEach(function (ev) {
                dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.add('is-drag'); });
            });
            ['dragleave', 'drop'].forEach(function (ev) {
                dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.remove('is-drag'); });
            });
            dz.addEventListener('drop', function (e) {
                if (e.dataTransfer && e.dataTransfer.files.length) {
                    file.files = e.dataTransfer.files;
                    check();
                }
            });

            form.addEventListener('submit', function (e) {
                if (!file.files.length) { e.preventDefault(); showError('Pilih file Excel terlebih dahulu.'); return; }
                btn.disabled = true;
                document.getElementById('up-spin').classList.remove('hidden');
                document.getElementById('up-label').textContent = 'Memproses...';
            });
        })();
    </script>
</x-app-layout>