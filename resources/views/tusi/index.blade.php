<x-app-layout title="Master Tusi">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Master Tusi') }}
        </h2>
    </x-slot>

    @php
        $cols = 'lg:grid-cols-[6.5rem_minmax(0,1fr)_5.5rem_7.5rem_5.5rem_10.5rem]';

        $totalTusi = $items->count();
        $totalAktif = $items->where('aktif', true)->count();
        $totalNonaktif = $totalTusi - $totalAktif;
        $totalSt = $items->sum('surat_tugas_count');

        $tambah = old('_from') === 'tambah';

        $inputClass =
            'w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition';
        $labelClass = 'text-xs font-semibold text-gray-600';
        $mobileLabelClass = 'lg:hidden text-[11px] font-semibold text-gray-500 mb-1 block';
        $switchClass =
            "relative w-9 h-5 shrink-0 rounded-full bg-gray-300 transition peer-checked:bg-emerald-500 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-500 peer-focus-visible:ring-offset-2 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:shadow-sm after:transition peer-checked:after:translate-x-4";
    @endphp

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto">

        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-4 sm:p-6 space-y-5">

            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="min-w-0">
                    <h3 class="text-lg font-bold text-gray-800">Master Tusi</h3>
                    <p class="text-sm text-gray-500">Kelola daftar tugas dan fungsi (tusi) yang dipakai pada surat tugas.
                    </p>
                </div>
                <span
                    class="text-xs font-semibold text-gray-500 bg-gray-50 border border-gray-100 rounded-full px-3 py-1.5"
                    aria-live="polite">
                    <span id="tusi-visible-count">{{ $totalTusi }}</span> dari {{ $totalTusi }} tusi
                </span>
            </div>

            @if (session('success'))
                <div role="status"
                    class="flex items-start gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-800">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div role="alert"
                    class="flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-700">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="rounded-xl bg-gray-50 border border-gray-100 px-3 sm:px-4 py-3">
                    <p class="text-xs font-medium text-gray-600">Total tusi</p>
                    <p class="text-xl font-bold text-gray-900 mt-0.5 tabular-nums">{{ $totalTusi }}</p>
                </div>
                <div class="rounded-xl bg-emerald-50/60 border border-emerald-100 px-3 sm:px-4 py-3">
                    <p class="text-xs font-medium text-gray-600">Aktif</p>
                    <p class="text-xl font-bold text-emerald-700 mt-0.5 tabular-nums">{{ $totalAktif }}</p>
                </div>
                <div class="rounded-xl bg-amber-50/60 border border-amber-100 px-3 sm:px-4 py-3">
                    <p class="text-xs font-medium text-gray-600">Nonaktif</p>
                    <p class="text-xl font-bold text-amber-700 mt-0.5 tabular-nums">{{ $totalNonaktif }}</p>
                </div>
                <div class="rounded-xl bg-blue-50/60 border border-blue-100 px-3 sm:px-4 py-3">
                    <p class="text-xs font-medium text-gray-600">Dipakai di surat tugas</p>
                    <p class="text-xl font-bold text-blue-700 mt-0.5 tabular-nums">{{ $totalSt }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('tusi.store') }}"
                class="rounded-xl border border-blue-100 bg-blue-50/40 p-4 space-y-3">
                @csrf
                <input type="hidden" name="_from" value="tambah">
                <div class="flex items-center gap-2">
                    <span
                        class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-blue-600 text-white"
                        aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </span>
                    <h4 class="text-sm font-bold text-gray-800">Tambah tusi baru</h4>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-12 gap-3 sm:items-end">
                    <div class="col-span-1 sm:col-span-2 flex flex-col gap-1">
                        <label for="new-kode" class="{{ $labelClass }}">Kode</label>
                        <input id="new-kode" name="kode" value="{{ $tambah ? old('kode') : '' }}" placeholder="Mis. T01" required
                            class="{{ $inputClass }}">
                    </div>
                    <div class="col-span-1 sm:col-span-2 flex flex-col gap-1 sm:order-3">
                        <label for="new-urutan" class="{{ $labelClass }}">Urutan</label>
                        <input id="new-urutan" name="urutan" type="number" min="0" value="{{ $tambah ? old('urutan') : '' }}"
                            placeholder="0" class="{{ $inputClass }}">
                    </div>
                    <div class="col-span-2 sm:col-span-4 flex flex-col gap-1 sm:order-2">
                        <label for="new-nama" class="{{ $labelClass }}">Nama tusi</label>
                        <input id="new-nama" name="nama" value="{{ $tambah ? old('nama') : '' }}"
                            placeholder="Nama tugas dan fungsi" required class="{{ $inputClass }}">
                    </div>
                    <div class="col-span-1 sm:col-span-2 flex items-center sm:order-4 sm:h-[38px]">
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="aktif" value="1" class="peer sr-only"
                                @checked($tambah ? old('aktif') : true)>
                            <span class="{{ $switchClass }}"></span>
                            <span class="text-sm font-medium text-gray-700">Aktif</span>
                        </label>
                    </div>
                    <div class="col-span-1 sm:col-span-2 sm:order-5">
                        <button type="submit"
                            class="inline-flex w-full justify-center items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm shadow-md shadow-blue-600/20 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 cursor-pointer">
                            Tambah
                        </button>
                    </div>
                </div>

                @if ($tambah && $errors->any())
                    <div role="alert"
                        class="flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
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
            </form>

            <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="tusi-filter-q" class="{{ $labelClass }}">Cari</label>
                    <input type="text" id="tusi-filter-q" autocomplete="off" placeholder="Kode atau nama tusi..."
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-64 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                </div>

                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="tusi-filter-status" class="{{ $labelClass }}">Status</label>
                    <select id="tusi-filter-status"
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-44 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>

                <button type="button" id="tusi-filter-reset"
                    class="hidden text-sm text-gray-500 hover:text-gray-700 transition px-2 py-2 rounded-md cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                    Reset
                </button>
            </div>

            <div class="border border-gray-100 rounded-xl overflow-hidden">

                <div
                    class="hidden lg:grid {{ $cols }} gap-4 bg-gray-50 px-4 py-3 text-xs font-semibold text-gray-500">
                    <div>Kode</div>
                    <div>Nama tusi</div>
                    <div>Urutan</div>
                    <div>Status</div>
                    <div>Surat tugas</div>
                    <div class="text-right">Aksi</div>
                </div>

                <div id="tusi-list" class="divide-y divide-gray-100">
                    @forelse ($items as $t)
                        @php
                            $fid = 'form-tusi-' . $t->id;
                            $edit = old('_from') === $fid;
                        @endphp

                        <div class="js-tusi-row bg-white hover:bg-gray-50/60 transition px-4 py-4 grid grid-cols-2 gap-x-4 gap-y-3 lg:items-center {{ $cols }}"
                            data-search="{{ mb_strtolower($t->kode . ' ' . $t->nama) }}"
                            data-status="{{ $t->aktif ? 'aktif' : 'nonaktif' }}">

                            <form id="{{ $fid }}" method="POST" action="{{ route('tusi.update', $t) }}"
                                class="hidden">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="_from" value="{{ $fid }}">
                            </form>

                            <div class="col-span-1 min-w-0">
                                <label for="kode-{{ $t->id }}" class="{{ $mobileLabelClass }}">Kode</label>
                                <input id="kode-{{ $t->id }}" form="{{ $fid }}" name="kode"
                                    value="{{ $edit ? old('kode') : $t->kode }}" data-field data-original="{{ $t->kode }}" class="{{ $inputClass }} font-semibold">
                            </div>

                            <div class="col-span-1 min-w-0 lg:order-3">
                                <label for="urutan-{{ $t->id }}"
                                    class="{{ $mobileLabelClass }}">Urutan</label>
                                <input id="urutan-{{ $t->id }}" form="{{ $fid }}" name="urutan"
                                    type="number" min="0" value="{{ $edit ? old('urutan') : $t->urutan }}" data-field data-original="{{ $t->urutan }}"
                                    class="{{ $inputClass }} tabular-nums">
                            </div>

                            <div class="col-span-2 lg:col-span-1 min-w-0 lg:order-2">
                                <label for="nama-{{ $t->id }}" class="{{ $mobileLabelClass }}">Nama
                                    tusi</label>
                                <input id="nama-{{ $t->id }}" form="{{ $fid }}" name="nama"
                                    value="{{ $edit ? old('nama') : $t->nama }}" data-field data-original="{{ $t->nama }}" class="{{ $inputClass }}">
                            </div>

                            <div class="col-span-1 min-w-0 lg:order-4">
                                <p class="{{ $mobileLabelClass }}">Status</p>
                                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" form="{{ $fid }}" name="aktif" value="1"
                                        data-field data-original="{{ $t->aktif ? '1' : '0' }}" class="peer sr-only"
                                        @checked($edit ? old('aktif') : $t->aktif)>
                                    <span class="{{ $switchClass }}"></span>
                                    <span class="text-xs font-medium text-gray-600">Aktif</span>
                                </label>
                            </div>

                            <div class="col-span-1 min-w-0 lg:order-5">
                                <p class="{{ $mobileLabelClass }}">Surat tugas</p>
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold whitespace-nowrap tabular-nums {{ $t->surat_tugas_count > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-gray-50 text-gray-500 border border-gray-200' }}">
                                    {{ $t->surat_tugas_count }} ST
                                </span>
                            </div>

                            <div class="col-span-2 lg:col-span-1 flex items-center gap-2 lg:justify-end lg:order-6">
                                <button type="submit" form="{{ $fid }}" disabled
                                    title="Simpan perubahan {{ $t->nama }}"
                                    class="js-save inline-flex flex-1 lg:flex-none items-center justify-center px-3 py-2 lg:py-1 text-[11px] font-semibold text-white bg-blue-600 hover:bg-blue-700 border border-blue-600 rounded-md transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-1 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-blue-600">
                                    Simpan
                                </button>

                                @if ($t->surat_tugas_count > 0)
                                    <span class="flex flex-1 lg:flex-none"
                                        title="Sudah dipakai {{ $t->surat_tugas_count }} surat tugas. Nonaktifkan saja, jangan dihapus.">
                                        <button type="button" disabled
                                            class="inline-flex flex-1 items-center justify-center px-3 py-2 lg:py-1 text-[11px] font-semibold text-gray-400 bg-gray-50 border border-gray-200 rounded-md cursor-not-allowed">
                                            Hapus
                                        </button>
                                    </span>
                                @else
                                <form method="POST" action="{{ route('tusi.destroy', $t) }}"
                                    class="js-delete-tusi flex flex-1 lg:flex-none m-0"
                                    data-name="{{ $t->nama }}" data-st="{{ $t->surat_tugas_count }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus {{ $t->nama }}"
                                        class="inline-flex flex-1 items-center justify-center px-3 py-2 lg:py-1 text-[11px] font-semibold text-red-500 bg-white hover:bg-red-50 border border-red-400 rounded-md transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                                        Hapus
                                    </button>
                                </form>
                                @endif
                            </div>

                            @if ($edit && $errors->any())
                                <div role="alert"
                                    class="col-span-2 lg:col-span-6 lg:order-7 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                                    <ul class="space-y-0.5">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="px-5 py-14 text-center text-gray-500">
                            <p class="text-sm font-semibold text-gray-800">Belum ada data tusi.</p>
                            <p class="text-xs text-gray-500 mt-1">Isi form "Tambah tusi baru" di atas, atau jalankan
                                <code class="px-1 py-0.5 rounded bg-gray-100 text-gray-700">TusiSeeder</code>.
                            </p>
                        </div>
                    @endforelse
                </div>

                <div id="tusi-no-result" class="hidden px-5 py-14 text-center text-gray-500">
                    <p class="text-sm font-semibold text-gray-800">Tidak ada tusi yang cocok dengan filter.</p>
                    <p class="text-xs text-gray-500 mt-1">Ubah kata kunci atau klik Reset untuk menampilkan semua.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            document.querySelectorAll('.js-delete-tusi').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    const st = parseInt(form.dataset.st || '0', 10);
                    let msg = 'Apakah Anda yakin ingin menghapus tusi "' + form.dataset.name + '"?';
                    if (st > 0) {
                        msg += '\n\nTusi ini dipakai pada ' + st + ' surat tugas.';
                    }
                    if (!confirm(msg)) {
                        e.preventDefault();
                    }
                });
            });

            const rows = Array.from(document.querySelectorAll('.js-tusi-row'));
            if (rows.length === 0) return;

            rows.forEach(function (row) {
                const btn = row.querySelector('.js-save');
                const fields = Array.from(row.querySelectorAll('[data-field]'));

                function check() {
                    const dirty = fields.some(function (f) {
                        return f.type === 'checkbox'
                            ? (f.checked ? '1' : '0') !== f.dataset.original
                            : f.value !== (f.dataset.original || '');
                    });
                    btn.disabled = !dirty;
                }

                fields.forEach(function (f) {
                    f.addEventListener('input', check);
                    f.addEventListener('change', check);
                });

                check();
            });

            const qInput = document.getElementById('tusi-filter-q');
            const statusSelect = document.getElementById('tusi-filter-status');
            const resetBtn = document.getElementById('tusi-filter-reset');
            const list = document.getElementById('tusi-list');
            const noResult = document.getElementById('tusi-no-result');
            const countEl = document.getElementById('tusi-visible-count');

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