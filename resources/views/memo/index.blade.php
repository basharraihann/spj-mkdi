<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nomor Memo') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="rounded-xl bg-green-50 border border-green-200 text-green-700 px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

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

            {{-- Filter --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-5">
                <form method="GET" action="{{ route('memo.index') }}"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Cari</label>
                        <input type="text" name="cari" value="{{ request('cari') }}"
                            placeholder="Nomor memo atau uraian kegiatan..."
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Jenis</label>
                        <div class="relative">
                            <select name="jenis"
                                class="w-full appearance-none border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition bg-white">
                                <option value="semua" {{ !request('jenis') || request('jenis') === 'semua' ? 'selected' : '' }}>Semua Jenis</option>
                                <option value="perdin" {{ request('jenis') === 'perdin' ? 'selected' : '' }}>Perjalanan
                                    Dinas</option>
                                <option value="konsumsi" {{ request('jenis') === 'konsumsi' ? 'selected' : '' }}>Konsumsi
                                </option>
                                <option value="honorarium" {{ request('jenis') === 'honorarium' ? 'selected' : '' }}>
                                    Honorarium</option>
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
                            <select name="pic"
                                class="w-full appearance-none border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition bg-white">
                                <option value="">Semua PIC</option>
                                @foreach ($picOptions as $namaPic)
                                    <option value="{{ $namaPic }}" {{ request('pic') === $namaPic ? 'selected' : '' }}>
                                        {{ $namaPic }}
                                    </option>
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
                        <input type="date" name="dari_tanggal" value="{{ request('dari_tanggal') }}"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Sampai Tanggal</label>
                        <input type="date" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                    </div>

                    <div class="lg:col-span-6 flex items-center gap-2 pt-1">
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 bg-gray-900 hover:bg-gray-800 text-white font-semibold px-4 py-2 rounded-lg text-xs transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18M6 9h12M10 13.5h4" />
                            </svg>
                            Terapkan Filter
                        </button>
                        @if (request()->anyFilled(['cari', 'jenis', 'pic', 'dari_tanggal', 'sampai_tanggal']))
                            <a href="{{ route('memo.index') }}"
                                class="text-xs font-medium text-gray-400 hover:text-gray-600 px-3 py-2 transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Daftar nomor memo --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Daftar Nomor Memo</h3>
                        <p class="text-sm text-gray-400 mt-1.5">
                            Diurutkan dari nomor memo terbaru. Menampilkan {{ $memos->count() }} entri.
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
                                        Tidak ada nomor memo yang cocok dengan filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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

            document.querySelectorAll('.delete-memo-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    if (!confirm('Yakin ingin menghapus nomor memo ini? Tindakan ini tidak bisa dibatalkan.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</x-app-layout>