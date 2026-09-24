<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nomor Memo') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if (session('success'))
                <div class="rounded-xl bg-green-50 border border-green-200 text-green-700 px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Nomor memo terkini + tombol buat mandiri --}}
            <div class="relative overflow-hidden bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="absolute inset-y-0 left-0 w-1.5 bg-blue-600"></div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 px-8 py-8 pl-10">
                    <div>
                        <p class="text-xs font-semibold text-blue-600 tracking-wide">Nomor Memo Berikutnya</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2 tabular-nums">
                            {{ $nomorBerikutnya['nomor'] }}
                        </p>
                        <p class="text-sm text-gray-400 mt-3 max-w-md leading-relaxed">
                            Dihitung otomatis dari nomor terbesar yang sudah pernah dipakai (agenda maupun mandiri).
                        </p>
                    </div>
                    <a href="{{ route('memo.create') }}"
                        class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl text-sm shadow-md shadow-blue-600/20 transition flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Buat Nomor Memo
                    </a>
                </div>
            </div>

            {{-- Daftar nomor memo --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800">Daftar Nomor Memo</h3>
                    <p class="text-sm text-gray-400 mt-1.5">
                        Rekap seluruh nomor memo (PNS & Non PNS dari agenda, serta yang dibuat mandiri).
                    </p>
                </div>

                <table class="w-full text-sm table-fixed">
                    <colgroup>
                        <col style="width: 4%">
                        <col style="width: 14%">
                        <col style="width: 10%">
                        <col style="width: 30%">
                        <col style="width: 12%">
                        <col style="width: 15%">
                        <col style="width: 15%">
                    </colgroup>
                    <thead>
                        <tr class="bg-gray-50/80 text-gray-500 text-left border-b border-gray-100">
                            <th class="px-4 py-3.5 font-medium">No</th>
                            <th class="px-4 py-3.5 font-medium">Nomor Memo</th>
                            <th class="px-4 py-3.5 font-medium">Tanggal</th>
                            <th class="px-4 py-3.5 font-medium">Nama Kegiatan</th>
                            <th class="px-4 py-3.5 font-medium">PIC</th>
                            <th class="px-4 py-3.5 font-medium">MAK</th>
                            <th class="px-4 py-3.5 font-medium text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($memos as $i => $memo)
                            <tr class="hover:bg-gray-50/70 transition align-top">
                                <td class="px-4 py-5 text-gray-400">{{ $i + 1 }}</td>
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

                                        @if ($memo['status'])
                                            <span
                                                class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full
                                                                                    {{ $memo['status'] === 'PNS' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600' }}">
                                                {{ $memo['status'] }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">
                                                Mandiri
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-5 text-gray-500">
                                    {{ optional($memo['tanggal_memo'])->translatedFormat('d F Y') }}
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
                                <td class="px-4 py-5 text-gray-600 truncate" title="{{ $memo['pic'] }}">
                                    {{ $memo['pic'] }}
                                </td>
                                <td class="px-4 py-5 text-gray-500 text-xs break-all" title="{{ $memo['mak'] ?? '-' }}">
                                    {{ $memo['mak'] ?? '-' }}
                                </td>
                                <td class="px-4 py-5 text-gray-900 font-semibold text-right tabular-nums">
                                    Rp{{ number_format($memo['nominal'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                                    Belum ada nomor memo yang diisi di agenda manapun.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
        });
    </script>
</x-app-layout>