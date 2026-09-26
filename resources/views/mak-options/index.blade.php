<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nomor MAK') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Daftar Nomor MAK</h2>
                    <p class="text-sm text-gray-500">Referensi kode anggaran untuk dipilih di Agenda.</p>
                </div>
                <span class="text-xs font-medium text-gray-400 bg-gray-50 px-3 py-1.5 rounded-full">
                    {{ $total }} nomor MAK
                </span>
            </div>

            <div class="flex items-center justify-between gap-4 mb-6">
                <div class="relative flex-1 max-w-sm">
                    <svg class="h-4 w-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="mak-search-input"
                        placeholder="Cari MAK atau uraian..."
                        autocomplete="off"
                        class="w-full rounded-xl border border-gray-200 pl-10 pr-4 py-2.5 text-sm text-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                </div>
                <button type="button" onclick="document.getElementById('modal-create').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 shrink-0 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Nomor MAK Baru
                </button>
            </div>

            <div id="mak-groups-container" class="rounded-xl border border-gray-100 overflow-hidden">
                @forelse ($grouped as $prefix => $items)
                    @php
                        $first = $items->first();
                        $headerSearch = strtolower($prefix . ' ' . $first->uraian_giat . ' ' . $first->uraian_komponen . ' ' . $first->uraian_akun_ap);
                    @endphp

                    <div class="js-mak-group border-b border-gray-100 last:border-b-0" data-search="{{ $headerSearch }}">
                        <div class="bg-indigo-50/50 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-semibold text-indigo-700 bg-white px-2 py-1 rounded-lg border border-indigo-100">
                                    {{ $prefix }}
                                </span>
                                <span class="text-xs text-gray-400">({{ $items->count() }} akun belanja)</span>
                            </div>
                            <p class="text-sm text-gray-700 mt-1 font-medium">{{ $first->uraian_giat ?: '-' }}</p>
                            <p class="text-xs text-gray-500">{{ $first->uraian_komponen ?: '-' }} — {{ $first->uraian_akun_ap ?: '-' }}</p>
                        </div>

                        <table class="min-w-full text-sm">
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($items as $item)
                                    @php $rowSearch = strtolower($item->mak . ' ' . $item->uraian_belanja); @endphp
                                    <tr class="js-mak-row hover:bg-gray-50/60 transition" data-search="{{ $rowSearch }}">
                                        <td class="py-2.5 pl-10 pr-4 w-40">
                                            <span class="font-mono text-xs text-gray-500">
                                                {{ \Illuminate\Support\Str::afterLast($item->mak, '.') }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-4 text-gray-600">
                                            {{ $item->uraian_belanja ?: '-' }}
                                        </td>
                                        <td class="py-2.5 px-4 text-right whitespace-nowrap w-28">
                                            <button type="button"
                                                onclick="document.getElementById('modal-edit-{{ $item->id }}').classList.remove('hidden')"
                                                class="text-indigo-600 hover:text-indigo-800 font-medium text-xs mr-3">Edit</button>
                                            <x-delete-button
                                                :action="route('mak-options.destroy', $item)"
                                                :label="'nomor MAK ' . $item->mak"
                                                :id="'mak-option-' . $item->id"
                                                class="text-red-500 hover:text-red-700 font-medium text-xs" />
                                        </td>
                                    </tr>

                                    <div id="modal-edit-{{ $item->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4">
                                        <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl">
                                            <h3 class="font-semibold text-gray-900 mb-4">Edit Nomor MAK</h3>
                                        <form action="{{ route('mak-options.update', $item) }}" method="POST" class="space-y-4">
    @csrf @method('PUT')
    <input type="hidden" name="_form" value="edit-{{ $item->id }}">
    @include('mak-options._fields', ['item' => $item])
                                                <div class="flex justify-end gap-2 mt-2">
                                                    <button type="button" onclick="document.getElementById('modal-edit-{{ $item->id }}').classList.add('hidden')"
                                                        class="px-4 py-2 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Batal</button>
                                                    <button type="submit" class="px-4 py-2 rounded-xl text-sm text-white bg-indigo-600 hover:bg-indigo-700">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @empty
                    <div class="py-10 text-center text-gray-400 text-sm">Belum ada data.</div>
                @endforelse
            </div>

            <p id="mak-no-result" class="hidden py-10 text-center text-gray-400 text-sm">
                Tidak ada MAK yang cocok dengan pencarian.
            </p>
        </div>

        <!-- Modal Create tetap sama -->
        <div id="modal-create" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4">
            <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl">
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
            const groups = Array.from(document.querySelectorAll('.js-mak-group'));
            const noResult = document.getElementById('mak-no-result');

            input.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();
                let visibleGroupCount = 0;

                groups.forEach(function (group) {
                    const headerMatch = query === '' || group.dataset.search.includes(query);
                    const rows = Array.from(group.querySelectorAll('.js-mak-row'));
                    let anyRowVisible = false;

                    rows.forEach(function (row) {
                        const rowMatch = headerMatch || row.dataset.search.includes(query);
                        row.style.display = rowMatch ? '' : 'none';
                        if (rowMatch) anyRowVisible = true;
                    });

                    group.style.display = anyRowVisible ? '' : 'none';
                    if (anyRowVisible) visibleGroupCount++;
                });

                noResult.classList.toggle('hidden', visibleGroupCount > 0);
            });
        })();
    </script>
</x-app-layout>