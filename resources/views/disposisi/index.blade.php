@php
    $activeTab = $tab ?? 'semua';
    $q = $q ?? '';
    $adaFilter = $activeTab !== 'semua' || $q !== '';

    $opsiStatus = [
        'semua' => 'Semua',
        'baru' => 'Baru',
        'dibaca' => 'Dibaca',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
    ];

    $chipStatus = [
        'baru' => ['Baru', 'bg-blue-100 text-blue-700'],
        'dibaca' => ['Dibaca', 'bg-gray-100 text-gray-600'],
        'diproses' => ['Diproses', 'bg-amber-100 text-amber-700'],
        'selesai' => ['Selesai', 'bg-green-100 text-green-700'],
    ];

    $chipSifat = [
        'biasa' => 'bg-gray-100 text-gray-600',
        'segera' => 'bg-red-100 text-red-600',
        'rahasia' => 'bg-amber-100 text-amber-700',
    ];

    // Kelas label khusus tampilan mobile (satu baris = satu kartu)
    $mobileLabel = "max-lg:flex max-lg:items-center max-lg:justify-between max-lg:gap-4 max-lg:text-right max-lg:py-1 max-lg:px-0 max-lg:before:content-[attr(data-label)] max-lg:before:text-[10px] max-lg:before:font-semibold max-lg:before:uppercase max-lg:before:tracking-wider max-lg:before:text-gray-400";
@endphp

<x-app-layout title="Disposisi">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inbox Disposisi Digital</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Kartu utama: judul + filter + tabel --}}
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-4 sm:p-6 space-y-6">

                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div class="min-w-0">
                        <h3 class="text-lg font-bold text-gray-800">Disposisi</h3>
                        <p class="text-sm text-gray-400">Daftar lembar disposisi masuk yang memerlukan tindak lanjut Anda.</p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        @if (($jumlah['baru'] ?? 0) > 0)
                            <a href="{{ route('disposisi.index', ['tab' => 'baru']) }}"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 bg-blue-50 border border-blue-100 rounded-full px-3 py-1.5 hover:bg-blue-100 transition">
                                <span class="h-1.5 w-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                {{ $jumlah['baru'] }} baru
                            </a>
                        @endif
                        <span class="text-xs font-semibold text-gray-400 bg-gray-50 border border-gray-100 rounded-full px-3 py-1.5">
                            {{ $disposisi->total() }} disposisi
                        </span>
                    </div>
                </div>

                {{-- Filter bar --}}
                <form action="{{ route('disposisi.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
                    <div class="flex flex-col gap-1 w-full sm:w-auto">
                        <label for="dispo-filter-q" class="text-xs font-semibold text-gray-500">Perihal / Agenda / Asal Surat</label>
                        <input type="text" id="dispo-filter-q" name="q" value="{{ $q }}" autocomplete="off"
                            placeholder="Ketik kata kunci..."
                            class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-72 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                    </div>

                    <div class="flex flex-col gap-1 flex-1 sm:flex-none">
                        <label for="dispo-filter-tab" class="text-xs font-semibold text-gray-500">Status</label>
                        <select id="dispo-filter-tab" name="tab" onchange="this.form.submit()"
                            class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-44 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                            @foreach ($opsiStatus as $key => $label)
                                <option value="{{ $key }}" @selected($activeTab === $key)>
                                    {{ $label }} ({{ $jumlah[$key] ?? 0 }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if ($adaFilter)
                        <a href="{{ route('disposisi.index') }}"
                            class="text-sm text-gray-400 hover:text-gray-600 transition px-1 py-2 sm:py-0">
                            Reset
                        </a>
                    @endif

                    <div class="hidden sm:block flex-1"></div>

                    <button type="submit"
                        class="inline-flex w-full sm:w-auto justify-center items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm shadow-md shadow-blue-600/20 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        Cari
                    </button>
                </form>

                {{-- Tabel responsif murni Tailwind --}}
                <div class="border border-gray-100 rounded-xl overflow-hidden">
                    <table class="w-full text-sm table-fixed max-lg:block max-lg:w-full">
                        <colgroup class="max-lg:hidden">
                            <col class="w-10">
                            <col>
                            <col class="w-44">
                            <col class="w-24">
                            <col class="w-24">
                            <col class="w-28">
                            <col class="w-28">
                            <col class="w-20">
                        </colgroup>
                        <thead class="max-lg:hidden">
                            <tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-400">
                                <th class="text-center px-2 py-3 font-semibold">No</th>
                                <th class="text-left px-3 py-3 font-semibold">Perihal Surat</th>
                                <th class="text-left px-3 py-3 font-semibold">Dari / Asal Surat</th>
                                <th class="text-center px-3 py-3 font-semibold">Sifat</th>
                                <th class="text-center px-3 py-3 font-semibold">Status</th>
                                <th class="text-left px-3 py-3 font-semibold">Tenggat</th>
                                <th class="text-left px-3 py-3 font-semibold">Diterima</th>
                                <th class="text-center px-3 py-3 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 max-lg:block max-lg:w-full">
                            @forelse ($disposisi as $i => $dispo)
                                @php
                                    $surat = $dispo->suratMasuk;
                                    $isBaru = $dispo->status === 'baru';
                                    $isSelesai = $dispo->status === 'selesai';

                                    $tenggat = $dispo->tenggat_waktu;
                                    $terlambat = $tenggat && !$isSelesai && $tenggat->copy()->startOfDay()->lt(today());
                                    $hariIni = $tenggat && !$isSelesai && $tenggat->copy()->startOfDay()->eq(today());

                                    $petunjuk = is_array($dispo->petunjuk) ? $dispo->petunjuk : [];
                                @endphp

                                {{-- BARIS UTAMA --}}
                                <tr class="js-dispo-toggle bg-white hover:bg-gray-50/60 transition align-top cursor-pointer max-lg:block max-lg:p-3">

                                    {{-- No --}}
                                    <td class="px-2 py-3 text-center text-gray-400 tabular-nums max-lg:flex max-lg:items-center max-lg:justify-between max-lg:py-1 max-lg:px-0">
                                        <div class="max-lg:text-[11px] max-lg:font-semibold max-lg:before:content-['No._']">{{ $disposisi->firstItem() + $i }}</div>
                                        <svg class="js-toggle-chevron h-3 w-3 mx-auto mt-1 text-gray-300 transition-all duration-200 max-lg:m-0 max-lg:h-4 max-lg:w-4"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </td>

                                    {{-- Perihal --}}
                                    <td class="px-3 py-3 max-lg:block max-lg:py-1 max-lg:px-0">
                                        <div class="flex items-start gap-2">
                                            @if ($isBaru)
                                                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-blue-600 animate-pulse"></span>
                                            @endif
                                            <span class="line-clamp-2 break-words {{ $isBaru ? 'font-bold text-gray-900' : 'font-semibold text-gray-800' }}">
                                                {{ $surat->perihal ?? '-' }}
                                            </span>
                                        </div>
                                        <div class="mt-0.5 flex items-center gap-1.5 text-[11px] text-gray-400 {{ $isBaru ? 'pl-3.5' : '' }}">
                                            <span class="font-mono">{{ $surat->nomor_agenda ?? '-' }}</span>
                                            @if ($dispo->is_tembusan)
                                                <span class="inline-flex items-center bg-violet-100 text-violet-700 text-[10px] font-medium px-2 py-0.5 rounded-full">Tembusan</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Dari / Asal --}}
                                    <td data-label="Dari / Asal" class="px-3 py-3 text-gray-500 {{ $mobileLabel }}">
                                        <div class="max-lg:w-1/2 min-w-0">
                                            <div class="font-medium text-gray-700 truncate">{{ $dispo->pengirim->name ?? 'Staf TU' }}</div>
                                            <div class="text-xs text-gray-400 truncate">{{ $surat->asal_surat ?? '—' }}</div>
                                        </div>
                                    </td>

                                    {{-- Sifat --}}
                                    <td data-label="Sifat" class="px-3 py-3 text-center {{ $mobileLabel }}">
                                        <span class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-full capitalize {{ $chipSifat[$dispo->sifat] ?? $chipSifat['biasa'] }}">
                                            {{ $dispo->sifat ?: 'biasa' }}
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td data-label="Status" class="px-3 py-3 text-center {{ $mobileLabel }}">
                                        <span class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $chipStatus[$dispo->status][1] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $chipStatus[$dispo->status][0] ?? ucfirst($dispo->status) }}
                                        </span>
                                    </td>

                                    {{-- Tenggat --}}
                                    <td data-label="Tenggat" class="px-3 py-3 text-xs leading-snug {{ $mobileLabel }}">
                                        <div class="max-lg:w-1/2">
                                            @if ($tenggat)
                                                <span class="{{ $terlambat ? 'text-red-600 font-semibold' : ($hariIni ? 'text-amber-600 font-semibold' : 'text-gray-500') }}">
                                                    {{ $tenggat->format('d M Y') }}
                                                </span>
                                                @if ($terlambat)
                                                    <span class="block text-[10px] font-semibold text-red-500">Terlambat</span>
                                                @elseif ($hariIni)
                                                    <span class="block text-[10px] font-semibold text-amber-500">Hari ini</span>
                                                @endif
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Diterima --}}
                                    <td data-label="Diterima" class="px-3 py-3 text-xs text-gray-500 leading-snug {{ $mobileLabel }}">
                                        <div class="max-lg:w-1/2" title="{{ $dispo->created_at?->format('d M Y, H:i') }}">
                                            {{ $dispo->created_at?->diffForHumans() }}
                                        </div>
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="px-2 py-3 js-no-toggle max-lg:block max-lg:pt-2 max-lg:px-0">
                                        <a href="{{ route('disposisi.show', $dispo->id) }}" title="Buka"
                                            class="inline-flex items-center justify-center w-full px-2 py-2 lg:py-1 rounded-md text-[11px] font-semibold border border-blue-500 text-blue-600 bg-white hover:bg-blue-50 transition">
                                            Buka
                                        </a>
                                    </td>
                                </tr>

                                {{-- BARIS DETAIL --}}
                                <tr class="js-dispo-detail hidden bg-blue-50/40 max-lg:[&:not(.hidden)]:block">
                                    <td class="align-top pt-3 max-lg:hidden">
                                        <svg class="h-4 w-4 mx-auto text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 12h11m0 0l-4-4m4 4l-4 4" />
                                        </svg>
                                    </td>

                                    <td colspan="7" class="px-3 py-4 max-lg:block max-lg:p-4">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-400 mb-3">Detail Disposisi</p>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-400 uppercase tracking-wide text-[10px] mb-1">Nomor Surat</p>
                                                <p class="text-gray-700 font-medium break-words">{{ $surat->nomor_surat ?? '—' }}</p>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-400 uppercase tracking-wide text-[10px] mb-1">Asal Surat</p>
                                                <p class="text-gray-700 font-medium break-words">{{ $surat->asal_surat ?? '—' }}</p>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-400 uppercase tracking-wide text-[10px] mb-1">Petunjuk</p>
                                                @if (count($petunjuk))
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach ($petunjuk as $pt)
                                                            <span class="inline-flex items-center bg-blue-100 text-blue-700 text-[10px] font-medium px-2 py-0.5 rounded-full">{{ $pt }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="text-gray-400">—</p>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-400 uppercase tracking-wide text-[10px] mb-1">Catatan</p>
                                                <p class="text-gray-700 font-medium break-words whitespace-pre-line">{{ \Illuminate\Support\Str::limit($dispo->instruksi ?: '—', 200) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-14 text-center text-gray-400">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span>{{ $adaFilter ? 'Belum ada disposisi yang cocok.' : 'Belum ada disposisi masuk.' }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($disposisi->total() > 0)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <p class="text-xs text-gray-400">
                            Menampilkan <span class="font-semibold text-gray-500">{{ $disposisi->firstItem() }}–{{ $disposisi->lastItem() }}</span>
                            dari <span class="font-semibold text-gray-500">{{ $disposisi->total() }}</span> disposisi
                        </p>
                        @if ($disposisi->hasPages())
                            <div>{{ $disposisi->links() }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        (function () {
            const rows = Array.from(document.querySelectorAll('.js-dispo-toggle'));
            if (rows.length === 0) return;

            function setOpen(row, open) {
                const detailRow = row.nextElementSibling;
                const chevron = row.querySelector('.js-toggle-chevron');

                if (detailRow && detailRow.classList.contains('js-dispo-detail')) {
                    detailRow.classList.toggle('hidden', !open);
                }
                row.classList.toggle('bg-blue-50/50', open);
                row.classList.toggle('bg-white', !open);
                if (chevron) {
                    chevron.classList.toggle('rotate-90', open);
                    chevron.classList.toggle('text-blue-500', open);
                    chevron.classList.toggle('text-gray-300', !open);
                }
                row.dataset.open = open ? '1' : '0';
            }

            rows.forEach(function (row) {
                row.addEventListener('click', function (e) {
                    if (e.target.closest('.js-no-toggle')) return;
                    setOpen(row, row.dataset.open !== '1');
                });
            });
        })();
    </script>
</x-app-layout>