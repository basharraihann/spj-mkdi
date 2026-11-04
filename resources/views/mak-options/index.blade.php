<x-app-layout title="MAK">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nomor MAK') }}
        </h2>
    </x-slot>

    @php
        $rp = fn($v) => number_format((int) $v, 0, ',', '.');
        $allItems = collect($grouped)->flatMap(fn($g) => $g);
        $periode = $allItems->pluck('periode')->filter()->unique()->values()->implode(', ');
        $totalSisa = $allItems->sum('sisa');

        // Warna sisa: merah = minus, kuning = hampir habis (<= 10% pagu), hijau = masih longgar
        $sisaClass = function ($item) {
            $sisa = (int) $item->sisa;
            $pagu = (int) $item->pagu;
            if ($sisa < 0)
                return 'text-red-600 font-semibold';
            if ($sisa === 0)
                return 'text-gray-400';
            if ($pagu > 0 && $sisa <= $pagu * 0.10)
                return 'text-amber-600 font-semibold';
            return 'text-emerald-600 font-semibold';
        };
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Daftar Nomor MAK</h2>
                    <p class="text-sm text-gray-500">Referensi kode anggaran untuk dipilih di Agenda, lengkap dengan sisa anggarannya.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-medium text-gray-500 bg-gray-50 px-3 py-1.5 rounded-full">
                        {{ $total }} nomor MAK
                    </span>
                    <span class="text-xs font-medium text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-full">
                        Total sisa Rp {{ $rp($totalSisa) }}
                    </span>
                </div>
            </div>

            @if ($periode !== '')
                <div class="mb-5 flex items-start gap-2 rounded-xl bg-blue-50 px-4 py-2.5 text-xs text-blue-700">
                    <svg class="h-4 w-4 shrink-0 mt-px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Angka pagu, realisasi, dan sisa bersumber dari FA Detail periode <b>{{ $periode }}</b>.
                        Sisa = pagu dikurangi realisasi; memorandum yang masih berjalan belum ikut mengurangi.</span>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1">
                    <div class="relative flex-1 w-full sm:max-w-sm">
                        <svg class="h-4 w-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" id="mak-search-input"
                            placeholder="Cari MAK atau uraian..."
                            autocomplete="off"
                            class="w-full rounded-xl border border-gray-200 pl-10 pr-4 py-2.5 text-sm text-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-600 select-none">
                        <input type="checkbox" id="mak-only-sisa" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Hanya yang masih ada sisa
                    </label>
                </div>
                <button type="button" onclick="document.getElementById('modal-create').classList.remove('hidden')"
                    class="inline-flex w-full sm:w-auto justify-center items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 shrink-0 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Nomor MAK Baru
                </button>
            </div>

            <div class="rounded-xl border border-gray-100 overflow-hidden">

                {{-- Judul kolom (lebar kolom sama dengan tabel di tiap kelompok) --}}
                <div class="overflow-x-auto bg-gray-50 border-b border-gray-100">
                    <table class="min-w-full table-fixed text-[11px] uppercase tracking-wide text-gray-500">
                        <colgroup>
                            <col class="w-28 sm:w-32">
                            <col>
                            <col class="hidden md:table-column w-36">
                            <col class="hidden lg:table-column w-36">
                            <col class="w-36">
                            <col class="w-24">
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="py-2 pl-4 sm:pl-10 pr-4 text-left font-semibold">Akun</th>
                                <th class="py-2 px-4 text-left font-semibold">Uraian belanja</th>
                                <th class="hidden md:table-cell py-2 px-4 text-right font-semibold">Pagu (Rp)</th>
                                <th class="hidden lg:table-cell py-2 px-4 text-right font-semibold">Realisasi (Rp)</th>
                                <th class="py-2 px-4 text-right font-semibold">Sisa (Rp)</th>
                                <th class="py-2 px-4 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <div id="mak-groups-container">
                    @forelse ($grouped as $prefix => $items)
                        @php
                            $first = $items->first();
                            $headerSearch = strtolower($prefix . ' ' . $first->uraian_giat . ' ' . $first->uraian_komponen . ' ' . $first->uraian_akun_ap);
                            $groupSisa = $items->sum('sisa');
                        @endphp

                        <div class="js-mak-group border-b border-gray-100 last:border-b-0" data-search="{{ $headerSearch }}">
                            <div class="bg-indigo-50/50 px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-xs font-semibold text-indigo-700 bg-white px-2 py-1 rounded-lg border border-indigo-100">
                                        {{ $prefix }}
                                    </span>
                                    <span class="text-xs text-gray-400">({{ $items->count() }} akun belanja)</span>
                                    <span class="ml-auto text-xs text-gray-500">Sisa kelompok
                                        <b class="{{ $groupSisa < 0 ? 'text-red-600' : 'text-gray-800' }} tabular-nums">Rp {{ $rp($groupSisa) }}</b></span>
                                </div>
                                <p class="text-sm text-gray-700 mt-1 font-medium">{{ $first->uraian_giat ?: '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $first->uraian_komponen ?: '-' }} — {{ $first->uraian_akun_ap ?: '-' }}</p>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full table-fixed text-sm">
                                    <colgroup>
                                        <col class="w-28 sm:w-32">
                                        <col>
                                        <col class="hidden md:table-column w-36">
                                        <col class="hidden lg:table-column w-36">
                                        <col class="w-36">
                                        <col class="w-24">
                                    </colgroup>
                                    <tbody class="divide-y divide-gray-50">
                                        @foreach ($items as $item)
                                            @php $rowSearch = strtolower($item->mak . ' ' . $item->uraian_belanja); @endphp
                                            <tr class="js-mak-row hover:bg-gray-50/60 transition" data-search="{{ $rowSearch }}" data-sisa="{{ (int) $item->sisa }}">
                                                <td class="py-2.5 pl-4 sm:pl-10 pr-4">
                                                    <span class="font-mono text-xs text-gray-500">
                                                        {{ \Illuminate\Support\Str::afterLast($item->mak, '.') }}
                                                    </span>
                                                </td>
                                                <td class="py-2.5 px-4 text-gray-600">
                                                    {{ $item->uraian_belanja ?: '-' }}
                                                </td>
                                                <td class="hidden md:table-cell py-2.5 px-4 text-right tabular-nums text-gray-500">
                                                    {{ $rp($item->pagu) }}
                                                </td>
                                                <td class="hidden lg:table-cell py-2.5 px-4 text-right tabular-nums text-gray-500">
                                                    {{ $rp($item->realisasi) }}
                                                </td>
                                                <td class="py-2.5 px-4 text-right tabular-nums {{ $sisaClass($item) }}">
                                                    {{ $rp($item->sisa) }}
                                                </td>
                                                <td class="py-2.5 px-4 text-right whitespace-nowrap">
                                                    <button type="button"
                                                        class="js-edit text-indigo-600 hover:text-indigo-800 font-medium text-xs mr-3"
                                                        data-action="{{ route('mak-options.update', $item) }}"
                                                        data-mak="{{ $item->mak }}"
                                                        data-uraian_giat="{{ $item->uraian_giat }}"
                                                        data-uraian_komponen="{{ $item->uraian_komponen }}"
                                                        data-uraian_akun_ap="{{ $item->uraian_akun_ap }}"
                                                        data-uraian_belanja="{{ $item->uraian_belanja }}"
                                                        data-pagu="{{ $rp($item->pagu) }}"
                                                        data-realisasi="{{ $rp($item->realisasi) }}"
                                                        data-sisa="{{ $rp($item->sisa) }}"
                                                        data-periode="{{ $item->periode }}">Edit</button>
                                                    <x-delete-button
                                                        :action="route('mak-options.destroy', $item)"
                                                        :label="'nomor MAK ' . $item->mak"
                                                        :id="'mak-option-' . $item->id"
                                                        class="text-red-500 hover:text-red-700 font-medium text-xs" />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-gray-400 text-sm">Belum ada data.</div>
                    @endforelse
                </div>
            </div>

            <p id="mak-no-result" class="hidden py-10 text-center text-gray-400 text-sm">
                Tidak ada MAK yang cocok dengan pencarian.
            </p>
        </div>

        <!-- Modal Edit (satu modal dipakai bersama semua baris) -->
        <div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4">
            <div class="bg-white rounded-2xl p-5 sm:p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-xl">
                <h3 class="font-semibold text-gray-900 mb-1">Edit Nomor MAK</h3>
                <p id="edit-info" class="mb-4 text-xs text-gray-500"></p>
                <form id="form-edit" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="_form" value="edit">
                    @include('mak-options._fields')
                    <div class="flex justify-end gap-2 mt-2">
                        <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                            class="px-4 py-2 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-sm text-white bg-indigo-600 hover:bg-indigo-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Create -->
        <div id="modal-create" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4">
            <div class="bg-white rounded-2xl p-5 sm:p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-xl">
                <h3 class="font-semibold text-gray-900 mb-4">Tambah Nomor MAK</h3>
                <form action="{{ route('mak-options.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_form" value="create">
                    @include('mak-options._fields')
                    <div class="flex justify-end gap-2 mt-2">
                        <button type="button" onclick="document.getElementById('modal-create').classList.add('hidden')"
                            class="px-4 py-2 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-sm text-white bg-indigo-600 hover:bg-indigo-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        (function () {
            const input = document.getElementById('mak-search-input');
            const onlySisa = document.getElementById('mak-only-sisa');
            const groups = Array.from(document.querySelectorAll('.js-mak-group'));
            const noResult = document.getElementById('mak-no-result');

            function apply() {
                const query = input.value.trim().toLowerCase();
                const needSisa = onlySisa.checked;
                let visibleGroupCount = 0;

                groups.forEach(function (group) {
                    const headerMatch = query === '' || group.dataset.search.includes(query);
                    const rows = Array.from(group.querySelectorAll('.js-mak-row'));
                    let anyRowVisible = false;

                    rows.forEach(function (row) {
                        const textOk = headerMatch || row.dataset.search.includes(query);
                        const sisaOk = !needSisa || parseInt(row.dataset.sisa, 10) > 0;
                        const show = textOk && sisaOk;
                        row.style.display = show ? '' : 'none';
                        if (show) anyRowVisible = true;
                    });

                    group.style.display = anyRowVisible ? '' : 'none';
                    if (anyRowVisible) visibleGroupCount++;
                });

                noResult.classList.toggle('hidden', visibleGroupCount > 0);
            }

            input.addEventListener('input', apply);
            onlySisa.addEventListener('change', apply);

            // Modal edit bersama: isi form dari atribut data pada tombol Edit
            const modal = document.getElementById('modal-edit');
            const form = document.getElementById('form-edit');
            const info = document.getElementById('edit-info');
            const fields = ['mak', 'uraian_giat', 'uraian_komponen', 'uraian_akun_ap', 'uraian_belanja'];

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.js-edit');
                if (!btn) return;
                form.action = btn.dataset.action;
                fields.forEach(function (name) {
                    if (form.elements[name]) form.elements[name].value = btn.dataset[name] || '';
                });
                info.textContent = 'Pagu Rp ' + btn.dataset.pagu + ' · Realisasi Rp ' + btn.dataset.realisasi +
                    ' · Sisa Rp ' + btn.dataset.sisa + (btn.dataset.periode ? ' (periode ' + btn.dataset.periode + ')' : '');
                modal.classList.remove('hidden');
            });
        })();
    </script>
</x-app-layout>