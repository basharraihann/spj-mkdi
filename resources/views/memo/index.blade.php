<x-app-layout title="Memorandum">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Memorandum') }}
        </h2>
    </x-slot>

    @php
        // Hitung data tampilan sekali saja
        $groups = $groups->map(function ($g) {
            $tanggal = $g['tanggal_memo'] ?? null;
            $tanggalIso = optional($tanggal)->format('Y-m-d');
            $tanggalTampil = optional($tanggal)->translatedFormat('d M Y');
            $tanggalTampilLengkap = optional($tanggal)->translatedFormat('d F Y');

            $searchBlob = strtolower(collect([
                collect($g['items'])->pluck('nomor_memo')->implode(' '),
                $g['uraian_kegiatan'] ?? '',
                $g['pic'] ?? '',
                $tanggalIso,
                $tanggalTampil,
                $tanggalTampilLengkap,
            ])->filter()->implode(' '));

            $jenisClass = match ($g['jenis']) {
                'perdin' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'konsumsi' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                default => 'bg-amber-50 text-amber-700 border-amber-200',
            };

            $deleteLabel = $g['agenda_id']
                ? 'agenda ini beserta seluruh nomor memonya (PNS dan Non PNS)'
                : 'nomor memo ini';

            return $g + [
                'tanggal_iso' => $tanggalIso,
                'tanggal_tampil' => $tanggalTampil,
                'search_blob' => $searchBlob,
                'jenis_class' => $jenisClass,
                'delete_label' => $deleteLabel,
                'delete_text' => $g['agenda_id'] ? 'Hapus agenda' : 'Hapus memo',
            ];
        });

        $statusClass = fn($status) => $status === 'PNS'
            ? 'bg-blue-50 text-blue-700 border-blue-200'
            : 'bg-orange-50 text-orange-700 border-orange-200';
    @endphp

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-5">

        @if (session('error'))
            <div class="rounded-xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- ============ BAGIAN ATAS: judul, nomor berikutnya, filter ============ --}}
        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-4 sm:p-6 space-y-5">

            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="min-w-0">
                    <h3 class="text-lg font-bold text-gray-800">Memorandum</h3>
                    <p class="text-sm text-gray-500">Diurutkan dari nomor memo terbaru.</p>
                </div>
                <span
                    class="text-xs font-semibold text-gray-500 bg-gray-50 border border-gray-100 rounded-full px-3 py-1.5"
                    aria-live="polite">
                    <span id="memo-visible-count">{{ $groups->count() }}</span> dari {{ $groups->count() }} kegiatan
                </span>
            </div>

            {{-- Nomor memo berikutnya + tombol buat --}}
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl bg-blue-50 border border-blue-100 px-4 py-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-blue-700">Nomor memo berikutnya</p>
                    <p class="text-xl font-bold text-gray-900 tabular-nums break-all">
                        {{ $nomorBerikutnya['nomor'] }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">Dihitung otomatis dari nomor terbesar yang sudah dipakai.
                    </p>
                </div>
                <a href="{{ route('memo.create') }}"
                    class="inline-flex w-full sm:w-auto shrink-0 items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm shadow-md shadow-blue-600/20 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Memorandum
                </a>
            </div>

            {{-- Filter instan, tanpa reload --}}
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="memo-filter-cari" class="text-xs font-semibold text-gray-600">Cari</label>
                    <input type="text" id="memo-filter-cari" autocomplete="off"
                        placeholder="Nomor memo, uraian, atau PIC..."
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-64 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                </div>

                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="memo-filter-jenis" class="text-xs font-semibold text-gray-600">Jenis</label>
                    <select id="memo-filter-jenis"
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-44 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                        <option value="semua" selected>Semua Jenis</option>
                        <option value="perdin">Perjalanan Dinas</option>
                        <option value="konsumsi">Konsumsi</option>
                        <option value="honorarium">Honorarium</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="memo-filter-pic" class="text-xs font-semibold text-gray-600">PIC</label>
                    <select id="memo-filter-pic"
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-44 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                        <option value="">Semua PIC</option>
                        @foreach ($picOptions as $namaPic)
                            <option value="{{ $namaPic }}">{{ $namaPic }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1 w-[calc(50%-0.375rem)] sm:w-auto min-w-0">
                    <label for="memo-filter-dari" class="text-xs font-semibold text-gray-600">Dari tanggal</label>
                    <input type="date" id="memo-filter-dari"
                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-full sm:w-40 min-w-0 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                </div>

                <div class="flex flex-col gap-1 w-[calc(50%-0.375rem)] sm:w-auto min-w-0">
                    <label for="memo-filter-sampai" class="text-xs font-semibold text-gray-600">Sampai tanggal</label>
                    <input type="date" id="memo-filter-sampai"
                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-full sm:w-40 min-w-0 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                </div>

                <button type="button" id="memo-filter-reset"
                    class="text-sm text-gray-500 hover:text-gray-700 transition px-1 py-2 cursor-pointer">
                    Reset
                </button>
            </div>
        </div>

        {{-- ============ DAFTAR: satu kartu per kegiatan ============ --}}
        <div class="space-y-3">

            {{-- Judul kolom (hanya layar lebar, sejajar dengan isi kartu) --}}
            <div
                class="hidden xl:grid grid-cols-[minmax(0,1fr)_36rem] gap-6 px-5 text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                <div>Kegiatan</div>
                <div class="grid grid-cols-[minmax(0,1fr)_8rem_9.5rem] gap-3 px-3">
                    <div>Nomor Memo</div>
                    <div class="text-right">Nominal</div>
                    <div class="text-right">Aksi</div>
                </div>
            </div>

            <div id="memo-list" class="space-y-3">
                @forelse ($groups as $g)
                    <article
                        class="js-memo-item bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5 grid gap-4 xl:grid-cols-[minmax(0,1fr)_36rem] xl:gap-6"
                        data-search="{{ $g['search_blob'] }}" data-jenis="{{ $g['jenis'] }}" data-pic="{{ $g['pic'] }}"
                        data-tanggal="{{ $g['tanggal_iso'] }}">

                        {{-- Info kegiatan --}}
                        <div class="flex gap-3 min-w-0">
                            <span
                                class="js-memo-number inline-flex h-7 min-w-7 px-2 items-center justify-center rounded-lg bg-gray-100 text-xs font-semibold text-gray-500 tabular-nums shrink-0">{{ $loop->iteration }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                                    <span
                                        class="inline-flex items-center whitespace-nowrap px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $g['jenis_class'] }}">
                                        {{ $g['jenis_label'] }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $g['tanggal_tampil'] ?: '-' }}</span>
                                </div>

                                <h3 class="text-sm font-semibold text-gray-800 leading-snug break-words mt-2">
                                    {{ $g['uraian_kegiatan'] ?? '-' }}
                                </h3>

                                <dl class="mt-2 space-y-1 text-xs">
                                    <div class="flex gap-2">
                                        <dt class="w-12 shrink-0 text-[10px] font-semibold uppercase tracking-wide text-gray-400 pt-px">PIC</dt>
                                        <dd class="text-gray-700 font-medium break-words min-w-0">{{ $g['pic'] ?: '-' }}</dd>
                                    </div>
                                    <div class="flex gap-2">
                                        <dt class="w-12 shrink-0 text-[10px] font-semibold uppercase tracking-wide text-gray-400 pt-px">MAK</dt>
                                        <dd class="text-gray-700 font-medium font-mono text-xs break-all min-w-0">
                                            {{ $g['mak'] ?? '-' }}
                                        </dd>
                                    </div>
                                </dl>

                                <form action="{{ route('memo.destroy', $g['delete_id']) }}" method="POST"
                                    class="delete-memo-form inline-flex mt-3 m-0" data-label="{{ $g['delete_label'] }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus {{ $g['delete_label'] }}"
                                        class="inline-flex items-center justify-center px-3 py-2 xl:py-1 text-[11px] font-semibold text-red-500 bg-white hover:bg-red-50 border border-red-400 rounded-md transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                                        {{ $g['delete_text'] }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Nomor memo per status (PNS / Non PNS) --}}
                        <div class="space-y-2 min-w-0 xl:pt-0">
                            @foreach ($g['items'] as $memo)
                                <div
                                    class="rounded-xl bg-gray-50 border border-gray-200 p-3 flex flex-wrap items-center gap-x-3 gap-y-3 xl:grid xl:grid-cols-[minmax(0,1fr)_8rem_9.5rem]">

                                    {{-- Nomor memo + status --}}
                                    <div class="min-w-0 flex flex-col items-start gap-1.5">
                                        @if ($memo['agenda_id'])
                                            <a href="{{ route('agendas.show', $memo['agenda_id']) }}"
                                                class="font-semibold text-xs text-gray-800 tabular-nums hover:text-blue-600 hover:underline break-all">
                                                {{ $memo['nomor_memo'] }}
                                            </a>
                                        @else
                                            <span class="font-semibold text-xs text-gray-800 tabular-nums break-all">
                                                {{ $memo['nomor_memo'] }}
                                            </span>
                                        @endif

                                        @if ($memo['status'])
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $statusClass($memo['status']) }}">
                                                {{ $memo['status'] }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Nominal --}}
                                    <span
                                        class="text-xs font-medium text-gray-700 text-right tabular-nums whitespace-nowrap ml-auto xl:ml-0">
                                        Rp {{ number_format($memo['nominal'] ?? 0, 0, ',', '.') }}
                                    </span>

                                    {{-- PDF / Edit --}}
                                    <div class="w-full sm:w-auto xl:w-full flex items-center gap-1.5 xl:justify-end">
                                        <a href="{{ $memo['pdf_url'] }}" target="_blank" rel="noopener"
                                            title="Buka PDF {{ $memo['status'] ?? '' }}"
                                            class="inline-flex flex-1 sm:flex-none items-center justify-center gap-1 px-2.5 py-2 xl:py-1 text-[11px] font-semibold text-blue-600 bg-white hover:bg-blue-50 border border-blue-500 rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            PDF
                                        </a>

                                        @if ($memo['editable'])
                                            <a href="{{ $memo['edit_url'] }}"
                                                class="inline-flex flex-1 sm:flex-none items-center justify-center px-3 py-2 xl:py-1 text-[11px] font-semibold text-gray-600 bg-white hover:bg-gray-100 border border-gray-300 rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                                                Edit
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div class="bg-white rounded-2xl border border-gray-200 px-4 py-14 text-center">
                        <p class="text-sm font-semibold text-gray-800">Belum ada nomor memo</p>
                        <p class="text-xs text-gray-500 mt-1">Klik "Buat Memorandum" untuk membuat yang pertama.</p>
                    </div>
                @endforelse
            </div>

            <div id="memo-no-result" class="hidden bg-white rounded-2xl border border-gray-200 px-4 py-14 text-center">
                <p class="text-sm font-semibold text-gray-800">Tidak ada nomor memo yang cocok</p>
                <p class="text-xs text-gray-500 mt-1">Ubah filter atau klik Reset untuk menampilkan semua.</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Setiap form hapus diberi id unik, lalu konfirmasi lewat confirmDelete()
            document.querySelectorAll('.delete-memo-form').forEach(function (form, index) {
                if (!form.id) {
                    form.id = 'delete-memo-form-' + index;
                }

                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const label = form.dataset.label || 'nomor memo ini';
                    confirmDelete(form.id, label);
                });
            });
        });
    </script>

    {{-- Filter instan client-side --}}
    <script>
        (function () {
            const cariInput = document.getElementById('memo-filter-cari');
            const jenisSelect = document.getElementById('memo-filter-jenis');
            const picSelect = document.getElementById('memo-filter-pic');
            const dariInput = document.getElementById('memo-filter-dari');
            const sampaiInput = document.getElementById('memo-filter-sampai');
            const resetBtn = document.getElementById('memo-filter-reset');

            const items = Array.from(document.querySelectorAll('.js-memo-item'));
            const noResult = document.getElementById('memo-no-result');
            const countEl = document.getElementById('memo-visible-count');

            if (items.length === 0) return; // belum ada data, tidak perlu filter

            function applyFilter() {
                const cari = cariInput.value.trim().toLowerCase();
                const jenis = jenisSelect.value;
                const pic = picSelect.value;
                const dari = dariInput.value; // YYYY-MM-DD, bisa dibandingkan sebagai string
                const sampai = sampaiInput.value;

                let visible = 0;

                items.forEach(function (el) {
                    const matchCari = cari === '' || el.dataset.search.includes(cari);
                    const matchJenis = jenis === 'semua' || el.dataset.jenis === jenis;
                    const matchPic = pic === '' || el.dataset.pic === pic;

                    const tgl = el.dataset.tanggal || '';
                    const matchDari = dari === '' || (tgl !== '' && tgl >= dari);
                    const matchSampai = sampai === '' || (tgl !== '' && tgl <= sampai);

                    const ok = matchCari && matchJenis && matchPic && matchDari && matchSampai;
                    el.style.display = ok ? '' : 'none';

                    if (ok) {
                        visible++;
                        el.querySelector('.js-memo-number').textContent = visible;
                    }
                });

                countEl.textContent = visible;
                noResult.classList.toggle('hidden', visible > 0);
            }

            cariInput.addEventListener('input', applyFilter);
            jenisSelect.addEventListener('change', applyFilter);
            picSelect.addEventListener('change', applyFilter);
            dariInput.addEventListener('change', applyFilter);
            sampaiInput.addEventListener('change', applyFilter);

            resetBtn.addEventListener('click', function () {
                cariInput.value = '';
                jenisSelect.value = 'semua';
                picSelect.value = '';
                dariInput.value = '';
                sampaiInput.value = '';
                applyFilter();
            });
        })();
    </script>
</x-app-layout>