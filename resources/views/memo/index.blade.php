<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Memorandum') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('error'))
                <div class="rounded-xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Nomor memo terkini + tombol buat mandiri --}}
            <div class="relative overflow-hidden bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="absolute inset-y-0 left-0 w-1.5 bg-blue-600"></div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 px-8 py-8 pl-10">
                    <div>
                        <p class="text-xs font-semibold text-blue-600 tracking-wide uppercase">Nomor Memo Berikutnya</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2 tabular-nums">
                            {{ $nomorBerikutnya['nomor'] }}
                        </p>
                        <p class="text-sm text-gray-400 mt-3 max-w-md leading-relaxed">
                            Dihitung otomatis dari nomor terbesar yang sudah pernah dipakai.
                        </p>
                    </div>
                    <a href="{{ route('memo.create') }}"
                        class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-semibold px-6 py-3 rounded-xl text-sm shadow-md shadow-blue-600/20 transition flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Buat Memorandum
                    </a>
                </div>
            </div>

            {{-- Filter : instan client-side, tidak reload halaman --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Cari</label>
                        <input type="text" id="memo-filter-cari" autocomplete="off"
                            placeholder="Nomor memo atau uraian kegiatan..."
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Jenis</label>
                        <div class="relative">
                            <select id="memo-filter-jenis"
                                class="w-full appearance-none border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition bg-white">
                                <option value="semua" selected>Semua Jenis</option>
                                <option value="perdin">Perjalanan Dinas</option>
                                <option value="konsumsi">Konsumsi</option>
                                <option value="honorarium">Honorarium</option>
                            </select>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">PIC</label>
                        <div class="relative">
                            <select id="memo-filter-pic"
                                class="w-full appearance-none border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition bg-white">
                                <option value="">Semua PIC</option>
                                @foreach ($picOptions as $namaPic)
                                    <option value="{{ $namaPic }}">{{ $namaPic }}</option>
                                @endforeach
                            </select>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Dari Tanggal</label>
                        <input type="date" id="memo-filter-dari"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Sampai Tanggal</label>
                        <input type="date" id="memo-filter-sampai"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div class="lg:col-span-6 flex items-center gap-2 pt-1">
                        <button type="button" id="memo-filter-reset"
                            class="text-xs font-medium text-gray-400 hover:text-gray-600 px-3 py-2 transition">
                            Reset Filter
                        </button>
                    </div>
                </div>
            </div>

            {{-- Daftar nomor memo --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Daftar Nomor Memo</h3>
                        <p class="text-sm text-gray-400 mt-1.5">
                            Diurutkan dari nomor memo terbaru. Menampilkan
                            <span id="memo-visible-count">{{ $memos->count() }}</span> dari {{ $memos->count() }} entri.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50/80 text-gray-500 text-left border-b border-gray-100">
                                <th class="px-4 py-3.5 font-medium w-10">No</th>
                                <th class="px-4 py-3.5 font-medium w-52">Nomor Memo</th>
                                <th class="px-4 py-3.5 font-medium w-28">Tanggal</th>
                                <th class="px-4 py-3.5 font-medium">Nama Kegiatan</th>
                                <th class="px-4 py-3.5 font-medium w-36">PIC</th>
                                <th class="px-4 py-3.5 font-medium w-44">MAK</th>
                                <th class="px-4 py-3.5 font-medium text-right w-32">Nominal</th>
                                <th class="px-4 py-3.5 font-medium text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="memo-table-body">
                            @forelse ($memos as $i => $memo)
                                @php
                                    $tanggalIso = optional($memo['tanggal_memo'])->format('Y-m-d');
                                    $tanggalTampil = optional($memo['tanggal_memo'])->translatedFormat('d M Y');
                                    $tanggalTampilLengkap = optional($memo['tanggal_memo'])->translatedFormat('d F Y');

                                    $searchBlob = strtolower(collect([
                                        $memo['nomor_memo'] ?? '',
                                        $memo['uraian_kegiatan'] ?? '',
                                        $memo['pic'] ?? '',
                                        $tanggalIso,
                                        $tanggalTampil,
                                        $tanggalTampilLengkap,
                                    ])->filter()->implode(' '));
                                @endphp
                                <tr class="js-memo-row hover:bg-gray-50/70 transition align-top"
                                    data-search="{{ $searchBlob }}" data-jenis="{{ $memo['jenis'] }}"
                                    data-pic="{{ $memo['pic'] }}" data-tanggal="{{ $tanggalIso }}">
                                    <td class="px-4 py-5 text-gray-400 js-memo-number">{{ $i + 1 }}</td>
                                    <td class="px-4 py-5">
                                        <div class="flex flex-col gap-1.5 items-start">
                                            @if ($memo['agenda_id'])
                                                <a href="{{ route('agendas.show', $memo['agenda_id']) }}"
                                                    class="font-semibold text-gray-800 tabular-nums hover:text-blue-600 hover:underline break-all">
                                                    {{ $memo['nomor_memo'] }}
                                                </a>
                                            @else
                                                <span class="font-semibold text-gray-800 tabular-nums break-all">
                                                    {{ $memo['nomor_memo'] }}
                                                </span>
                                            @endif

                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full
                                                                                        @class([
                                                                                            'bg-indigo-50 text-indigo-600' => $memo['jenis'] === 'perdin',
                                                                                            'bg-emerald-50 text-emerald-600' => $memo['jenis'] === 'konsumsi',
                                                                                            'bg-amber-50 text-amber-600' => $memo['jenis'] === 'honorarium',
                                                                                        ])">
                                                    {{ $memo['jenis_label'] }}
                                                </span>

                                                @if ($memo['status'])
                                                    <span
                                                        class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full
                                                                                                                            {{ $memo['status'] === 'PNS' ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600' }}">
                                                        {{ $memo['status'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-5 text-gray-500">
                                        {{ optional($memo['tanggal_memo'])->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 py-5 text-gray-700 leading-relaxed">
                                        <p class="uraian-text line-clamp-2">
                                            {{ $memo['uraian_kegiatan'] ?? '-' }}
                                        </p>
                                        @if (strlen($memo['uraian_kegiatan'] ?? '') > 90)
                                            <button type="button"
                                                class="toggle-uraian text-xs font-medium text-blue-600 hover:text-blue-700 mt-1">
                                                Selengkapnya
                                            </button>
                                        @endif
                                    </td>
                                    <td class="px-4 py-5 text-gray-600" title="{{ $memo['pic'] }}">
                                        <span class="line-clamp-2">{{ $memo['pic'] }}</span>
                                    </td>
                                    <td class="px-4 py-5 text-gray-500 text-xs break-all font-mono"
                                        title="{{ $memo['mak'] ?? '-' }}">
                                        {{ $memo['mak'] ?? '-' }}
                                    </td>
                                    <td class="px-4 py-5 text-gray-900 font-semibold text-right tabular-nums">
                                        Rp{{ number_format($memo['nominal'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-5">
                                        <div class="flex flex-col items-stretch gap-1">
                                            <a href="{{ $memo['pdf_url'] }}" target="_blank"
                                                class="inline-flex items-center justify-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 px-3 py-1.5 rounded-lg transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                PDF
                                            </a>

                                            @if ($memo['editable'])
                                                <a href="{{ $memo['edit_url'] }}"
                                                    class="inline-flex items-center justify-center gap-1.5 text-xs font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 px-3 py-1.5 rounded-lg transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </a>
                                            @endif

                                            <form action="{{ route('memo.destroy', $memo['id']) }}" method="POST"
                                                class="delete-memo-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="w-full inline-flex items-center justify-center gap-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 px-3 py-1.5 rounded-lg transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-16 text-center text-gray-400">
                                        Belum ada nomor memo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <p id="memo-no-result" class="hidden px-6 py-16 text-center text-gray-400">
                        Tidak ada nomor memo yang cocok dengan filter ini.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.toggle-uraian').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const p = btn.previousElementSibling;
                    const expanded = !p.classList.contains('line-clamp-2');
                    p.classList.toggle('line-clamp-2');
                    btn.textContent = expanded ? 'Selengkapnya' : 'Sembunyikan';
                });
            });

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

            const rows = Array.from(document.querySelectorAll('.js-memo-row'));
            const noResult = document.getElementById('memo-no-result');
            const tableBody = document.getElementById('memo-table-body');
            const visibleCountEl = document.getElementById('memo-visible-count');

            if (rows.length === 0) return; // belum ada data, gak perlu filter

            function applyFilter() {
                const cari = cariInput.value.trim().toLowerCase();
                const jenis = jenisSelect.value;
                const pic = picSelect.value;
                const dari = dariInput.value; // format YYYY-MM-DD, cocok buat compare string
                const sampai = sampaiInput.value;

                let visibleCount = 0;

                rows.forEach(function (row, idx) {
                    const matchCari = cari === '' || row.dataset.search.includes(cari);
                    const matchJenis = jenis === 'semua' || row.dataset.jenis === jenis;
                    const matchPic = pic === '' || row.dataset.pic === pic;

                    const tgl = row.dataset.tanggal || '';
                    const matchDari = dari === '' || (tgl !== '' && tgl >= dari);
                    const matchSampai = sampai === '' || (tgl !== '' && tgl <= sampai);

                    const visible = matchCari && matchJenis && matchPic && matchDari && matchSampai;

                    row.style.display = visible ? '' : 'none';

                    if (visible) {
                        visibleCount++;
                        const numberCell = row.querySelector('.js-memo-number');
                        if (numberCell) numberCell.textContent = visibleCount;
                    }
                });

                if (visibleCountEl) visibleCountEl.textContent = visibleCount;
                if (noResult) noResult.classList.toggle('hidden', visibleCount > 0);
                if (tableBody) tableBody.style.display = visibleCount > 0 ? '' : 'none';
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