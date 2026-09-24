<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar Agenda Perjalanan Dinas</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div
                    class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Kartu utama: judul + search bar + tabel --}}
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-6 space-y-6">

                <div>
                    <h3 class="text-lg font-bold text-gray-800">Agenda</h3>
                    <p class="text-sm text-gray-400">Kelola seluruh agenda perjalanan dinas beserta rincian biaya dan
                        kelengkapan dokumennya.</p>
                </div>

                {{-- Search & filter bar --}}
                <form action="{{ route('agendas.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500">Uraian Kegiatan</label>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik uraian kegiatan..."
                            class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-52 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500">Tujuan</label>
                        <input type="text" name="tujuan" value="{{ request('tujuan') }}" placeholder="Ketik tujuan..."
                            class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-48 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500">Bulan</label>
                        @php
                            $namaBulan = [
                                1 => 'Januari',
                                2 => 'Februari',
                                3 => 'Maret',
                                4 => 'April',
                                5 => 'Mei',
                                6 => 'Juni',
                                7 => 'Juli',
                                8 => 'Agustus',
                                9 => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                            ];
                        @endphp
                        <select name="bulan"
                            class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-40 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                            <option value="">Semua Bulan</option>
                            @foreach ($namaBulan as $num => $label)
                                <option value="{{ $num }}" @selected(request('bulan') == $num)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                        class="inline-flex items-center gap-1.5 border border-blue-500 text-blue-600 bg-white hover:bg-blue-50 font-semibold px-4 py-2 rounded-lg text-sm transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Search
                    </button>

                    @if (request()->filled('q') || request()->filled('tujuan') || request()->filled('bulan'))
                        <a href="{{ route('agendas.index') }}"
                            class="text-sm text-gray-400 hover:text-gray-600 transition px-1">
                            Reset
                        </a>
                    @endif

                    <div class="flex-1"></div>

                    <a href="{{ route('agendas.create') }}"
                        class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm shadow-md shadow-blue-600/20 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Agenda Baru
                    </a>
                </form>

                {{-- Tabel --}}
                <div class="border border-gray-100 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-400">
                                <th class="text-center px-4 py-3.5 font-semibold w-10">No</th>
                                <th class="text-left px-5 py-3.5 font-semibold">Uraian Kegiatan</th>
                                <th class="text-left px-5 py-3.5 font-semibold">Tujuan</th>
                                <th class="text-left px-5 py-3.5 font-semibold">Tanggal</th>
                                <th class="text-center px-5 py-3.5 font-semibold">Biaya ASN</th>
                                <th class="text-center px-5 py-3.5 font-semibold">Biaya Non-ASN</th>
                                <th class="text-center px-5 py-3.5 font-semibold">Kelengkapan Dokumen</th>
                                <th class="text-center px-5 py-3.5 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($agendas as $agenda)
                                @php
                                    $dokLengkap = $agenda->dokumen_uploads_count ?? 0;
                                    $dokTotal = $totalDokumenKategori;
                                    $persen = $dokTotal > 0 ? round(($dokLengkap / $dokTotal) * 100) : 0;

                                    $dokWarna = match (true) {
                                        $dokTotal == 0 => 'bg-gray-200',
                                        $persen >= 100 => 'bg-green-500',
                                        $persen >= 50 => 'bg-amber-400',
                                        default => 'bg-red-400',
                                    };

                                    $dokTeksWarna = match (true) {
                                        $dokTotal == 0 => 'text-gray-400',
                                        $persen >= 100 => 'text-green-600',
                                        $persen >= 50 => 'text-amber-600',
                                        default => 'text-red-500',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-4 py-4 text-center text-gray-400 tabular-nums">
                                        {{ $agendas->firstItem() + $loop->index }}
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-gray-800 max-w-xs">
                                        <span class="line-clamp-2">{{ $agenda->uraian_kegiatan }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-gray-500 whitespace-nowrap">{{ $agenda->tujuan }}</td>
                                    <td class="px-5 py-4 text-gray-500 whitespace-nowrap">
                                        {{ $agenda->tanggal_mulai->translatedFormat('d M Y') }} –
                                        {{ $agenda->tanggal_selesai->translatedFormat('d M Y') }}
                                    </td>
                                    <td
                                        class="px-5 py-4 text-center tabular-nums {{ $agenda->biaya_asn == 0 ? 'text-gray-300' : 'text-gray-700 font-medium' }}">
                                        Rp {{ number_format($agenda->biaya_asn, 0, ',', '.') }}
                                    </td>
                                    <td
                                        class="px-5 py-4 text-center tabular-nums {{ $agenda->biaya_non_asn == 0 ? 'text-gray-300' : 'text-gray-700 font-medium' }}">
                                        Rp {{ number_format($agenda->biaya_non_asn, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-col items-center gap-1 w-28 mx-auto">
                                            <span class="text-xs font-semibold {{ $dokTeksWarna }}">
                                                {{ $dokLengkap }} / {{ $dokTotal }} dokumen
                                            </span>
                                            <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                                <div class="h-full {{ $dokWarna }} rounded-full transition-all"
                                                    style="width: {{ $persen }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('agendas.show', $agenda) }}"
                                                class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-semibold border border-blue-500 text-blue-600 bg-white hover:bg-blue-50 transition">
                                                Lihat
                                            </a>
                                            <form action="{{ route('agendas.destroy', $agenda) }}" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus agenda ini? Data yang sudah dihapus tidak bisa dikembalikan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-semibold border border-red-400 text-red-500 bg-white hover:bg-red-50 transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-14 text-center text-gray-400">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span>Belum ada agenda yang cocok.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="flex justify-center pt-1">
                    {{ $agendas->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>