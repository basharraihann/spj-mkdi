<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar Agenda Perjalanan Dinas</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Kartu utama: judul + search bar + tabel --}}
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-6 space-y-6">

                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Agenda</h3>
                        <p class="text-sm text-gray-400">Kelola seluruh agenda perjalanan dinas beserta rincian biaya
                            dan
                            kelengkapan dokumennya.</p>
                    </div>
                    <span
                        class="text-xs font-semibold text-gray-400 bg-gray-50 border border-gray-100 rounded-full px-3 py-1.5">
                        <span id="agenda-visible-count">{{ $agendas->count() }}</span> dari {{ $agendas->count() }}
                        agenda
                    </span>
                </div>

                {{-- Search & filter bar: instan, tanpa reload --}}
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500">Uraian Kegiatan</label>
                        <input type="text" id="agenda-filter-q" autocomplete="off"
                            placeholder="Ketik uraian kegiatan..."
                            class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-52 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500">Tujuan</label>
                        <input type="text" id="agenda-filter-tujuan" autocomplete="off" placeholder="Ketik tujuan..."
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
                        <select id="agenda-filter-bulan"
                            class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-40 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                            <option value="">Semua Bulan</option>
                            @foreach ($namaBulan as $num => $label)
                                <option value="{{ $num }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" id="agenda-filter-reset"
                        class="text-sm text-gray-400 hover:text-gray-600 transition px-1">
                        Reset
                    </button>

                    <div class="flex-1"></div>

                    <a href="{{ route('agendas.create') }}"
                        class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm shadow-md shadow-blue-600/20 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Agenda Baru
                    </a>
                </div>

                {{-- Tabel --}}
                <div class="border border-gray-100 rounded-xl overflow-hidden">
                    <table class="w-full text-sm table-fixed">
                        <colgroup>
                            <col class="w-8">
                            <col>
                            <col class="w-36">
                            <col class="w-32">
                            <col class="w-24">
                            <col class="w-24">
                            <col class="w-24">
                            <col class="w-20">
                        </colgroup>
                        <thead>
                            <tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-400">
                                <th class="text-center px-2 py-3 font-semibold">No</th>
                                <th class="text-left px-3 py-3 font-semibold">Uraian Kegiatan</th>
                                <th class="text-left px-3 py-3 font-semibold">Tujuan / Kota</th>
                                <th class="text-left px-3 py-3 font-semibold">Tanggal</th>
                                <th class="text-right px-3 py-3 font-semibold">Biaya ASN</th>
                                <th class="text-right px-3 py-3 font-semibold">Biaya Non-ASN</th>
                                <th class="text-center px-3 py-3 font-semibold">Dokumen</th>
                                <th class="text-center px-3 py-3 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="agenda-table-body">
                            @forelse ($agendas as $i => $agenda)
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

                                    $searchBlob = strtolower($agenda->uraian_kegiatan . ' ' . $agenda->tujuan . ' ' . $agenda->kota_tujuan);
                                    $bulanAgenda = $agenda->tanggal_mulai->format('n');
                                @endphp
                                <tr class="js-agenda-row hover:bg-gray-50/60 transition align-top" data-search="{{ $searchBlob }}"
                                    data-tujuan="{{ strtolower($agenda->tujuan) }}" data-bulan="{{ $bulanAgenda }}">
                                    <td class="px-2 py-3 text-center text-gray-400 tabular-nums js-agenda-number">
                                        {{ $i + 1 }}
                                    </td>
                                    <td class="px-3 py-3 font-semibold text-gray-800">
                                        <span class="line-clamp-2 break-words">{{ $agenda->uraian_kegiatan }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-gray-500">
                                        <div class="font-medium text-gray-700 truncate">{{ $agenda->tujuan }}</div>
                                        <div class="text-xs text-gray-400 truncate">{{ $agenda->kota_tujuan ?: '—' }}</div>
                                    </td>
                                    <td class="px-3 py-3 text-gray-500 text-xs leading-snug">
                                        {{ $agenda->tanggal_mulai->translatedFormat('d M Y') }}
                                        <span class="text-gray-300">–</span>
                                        {{ $agenda->tanggal_selesai->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-3 py-3 text-right tabular-nums text-xs leading-snug {{ $agenda->biaya_asn == 0 ? 'text-gray-300' : 'text-gray-700 font-medium' }}">
                                        Rp {{ number_format($agenda->biaya_asn, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3 text-right tabular-nums text-xs leading-snug {{ $agenda->biaya_non_asn == 0 ? 'text-gray-300' : 'text-gray-700 font-medium' }}">
                                        Rp {{ number_format($agenda->biaya_non_asn, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex flex-col items-center gap-1 w-full">
                                            <span class="text-[11px] font-semibold {{ $dokTeksWarna }}">
                                                {{ $dokLengkap }}/{{ $dokTotal }}
                                            </span>
                                            <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                                <div class="h-full {{ $dokWarna }} rounded-full transition-all"
                                                    style="width: {{ $persen }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-2 py-3">
                                        <div class="flex flex-col items-center justify-center gap-1.5">
                                            <a href="{{ route('agendas.show', $agenda) }}" title="Lihat"
                                                class="inline-flex items-center justify-center w-full px-2 py-1 rounded-md text-[11px] font-semibold border border-blue-500 text-blue-600 bg-white hover:bg-blue-50 transition">
                                                Lihat
                                            </a>
                                            <x-delete-button :action="route('agendas.destroy', $agenda)" :label="'agenda ini'" :id="'agenda-' . $agenda->id"
                                                class="inline-flex items-center justify-center w-full px-2 py-1 rounded-md text-[11px] font-semibold border border-red-400 text-red-500 bg-white hover:bg-red-50 transition">
                                                Hapus
                                            </x-delete-button>
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
                                            <span>Belum ada agenda.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div id="agenda-no-result" class="hidden px-5 py-14 text-center text-gray-400">
                        <div class="flex flex-col items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Belum ada agenda yang cocok.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search instan client-side --}}
    <script>
        (function () {
            const qInput = document.getElementById('agenda-filter-q');
            const tujuanInput = document.getElementById('agenda-filter-tujuan');
            const bulanSelect = document.getElementById('agenda-filter-bulan');
            const resetBtn = document.getElementById('agenda-filter-reset');

            const rows = Array.from(document.querySelectorAll('.js-agenda-row'));
            const noResult = document.getElementById('agenda-no-result');
            const tableBody = document.getElementById('agenda-table-body');
            const visibleCountEl = document.getElementById('agenda-visible-count');

            if (rows.length === 0) return; // belum ada data sama sekali

            function applyFilter() {
                const q = qInput.value.trim().toLowerCase();
                const tujuan = tujuanInput.value.trim().toLowerCase();
                const bulan = bulanSelect.value;

                let visibleCount = 0;

                rows.forEach(function (row) {
                    const matchQ = q === '' || row.dataset.search.includes(q);
                    const matchTujuan = tujuan === '' || row.dataset.tujuan.includes(tujuan);
                    const matchBulan = bulan === '' || row.dataset.bulan === bulan;

                    const visible = matchQ && matchTujuan && matchBulan;
                    row.style.display = visible ? '' : 'none';

                    if (visible) {
                        visibleCount++;
                        const numberCell = row.querySelector('.js-agenda-number');
                        if (numberCell) numberCell.textContent = visibleCount;
                    }
                });

                if (visibleCountEl) visibleCountEl.textContent = visibleCount;
                if (noResult) noResult.classList.toggle('hidden', visibleCount > 0);
                if (tableBody) tableBody.style.display = visibleCount > 0 ? '' : 'none';
            }

            qInput.addEventListener('input', applyFilter);
            tujuanInput.addEventListener('input', applyFilter);
            bulanSelect.addEventListener('change', applyFilter);

            resetBtn.addEventListener('click', function () {
                qInput.value = '';
                tujuanInput.value = '';
                bulanSelect.value = '';
                applyFilter();
            });
        })();
    </script>
</x-app-layout>