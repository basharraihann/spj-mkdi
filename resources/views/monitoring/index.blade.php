<x-app-layout title="Monitoring Memo">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitoring Memo & Anggaran') }}
        </h2>
    </x-slot>

    @php
        $rp = fn($n) => ($n < 0 ? '-Rp ' : 'Rp ') . number_format(abs((int) $n), 0, ',', '.');

        $statusLabel = ['outstanding' => 'Outstanding', 'sakti' => 'SAKTI', 'sp2d' => 'SP2D', 'batal' => 'Batal'];
        $statusBadge = fn($s) => match ($s) {
            'sakti' => 'bg-blue-50 text-blue-700 border-blue-200',
            'sp2d' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'batal' => 'bg-gray-100 text-gray-500 border-gray-200',
            default => 'bg-amber-50 text-amber-700 border-amber-200',
        };

        // Hitungan untuk filter tabel MAK
        $hit = [
            'semua' => $maks->where('dipakai', true)->count(),
            'outstanding' => $maks->where('dipakai', true)->where('outstanding', '>', 0)->count(),
            'sakti' => $maks->where('dipakai', true)->where('menunggu_sp2d', '>', 0)->count(),
            'minus' => $maks->where('kondisi', 'minus')->count(),
        ];
        $belumDipakai = $maks->where('dipakai', false)->count();

        $filters = [
            'semua' => 'Semua',
            'outstanding' => 'Ada outstanding',
            'sakti' => 'Menunggu SP2D',
            'minus' => 'Sisa minus',
        ];

        // Dua antrean kerja: aksi massal hanya perpindahan maju
        $antrean = [
            [
                'key' => 'outstanding',
                'judul' => 'Outstanding: belum diinput di SAKTI',
                'petunjuk' => 'Diurutkan dari yang paling lama. Tandai SAKTI setelah memo diinput di SAKTI dan tarikan terbaru sudah diupload.',
                'data' => $antreanOutstanding,
                'aksi' => 'sakti',
                'tombol' => 'Tandai SAKTI',
                'kosong' => 'Tidak ada memo outstanding.',
            ],
            [
                'key' => 'sakti',
                'judul' => 'SAKTI: menunggu SP2D',
                'petunjuk' => 'Memo yang sudah diinput di SAKTI dan belum dicairkan. Angkanya sudah masuk realisasi, jadi tidak lagi dihitung outstanding.',
                'data' => $antreanSakti,
                'aksi' => 'sp2d',
                'tombol' => 'Tandai SP2D',
                'kosong' => 'Tidak ada memo yang menunggu SP2D.',
            ],
        ];

        $th = 'px-3 py-2.5 text-xs font-medium text-gray-500 bg-gray-50 border-b border-gray-200';
    @endphp

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

        @if (session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- ============ Ringkasan ============ --}}
        <section aria-label="Ringkasan">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-px bg-gray-200 border border-gray-200 rounded-xl overflow-hidden">
                @foreach ([
                        ['Outstanding', $ringkasan['outstanding'], 'text-gray-900'],
                        ['SAKTI, menunggu SP2D', $ringkasan['sakti'], 'text-gray-900'],
                        ['SP2D bulan ini', $ringkasan['sp2d'], 'text-gray-900'],
                        ['Outstanding lebih dari 30 hari', $ringkasan['lama'], $ringkasan['lama']['jumlah'] > 0 ? 'text-red-700' : 'text-gray-900'],
                    ] as [$judul, $nilai, $warna])
                        <div class="bg-white px-4 py-4 sm:px-5">
                            <p class="text-xs text-gray-500">{{ $judul }}</p>
                            <p class="mt-1.5 text-2xl font-semibold tabular-nums {{ $warna }}">
                                {{ number_format($nilai['jumlah'], 0, ',', '.') }}
                                <span class="text-sm font-normal text-gray-400">memo</span>
                            </p>
                            <p class="mt-0.5 text-xs text-gray-500 tabular-nums">{{ $rp($nilai['total']) }}</p>
                        </div>
                @endforeach
            </div>
            <p class="mt-2 text-xs text-gray-500">
                Data realisasi mengacu pada tarikan SAKTI tanggal
                <span class="font-medium text-gray-700">{{ $tarikanTanggal ? $tarikanTanggal->translatedFormat('d F Y') : 'belum ada' }}</span>.
                Upload tarikan terbaru sebelum menandai memo SAKTI.
            </p>
        </section>

        {{-- ============ Kondisi per MAK ============ --}}
        <section class="bg-white rounded-xl border border-gray-200" aria-labelledby="judul-mak">
            <div class="px-5 pt-4 pb-3">
                <h3 id="judul-mak" class="text-base font-semibold text-gray-900">Kondisi per MAK</h3>
                <p class="mt-0.5 text-xs text-gray-500 max-w-3xl">
                    MAK yang pernah dipakai dalam memo (tidak termasuk memo batal). Sisa efektif adalah sisa dari tarikan SAKTI dikurangi memo outstanding, diurutkan dari yang paling tipis.
                </p>
            </div>

            <div class="flex flex-col gap-3 px-5 pb-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap gap-1.5" role="group" aria-label="Filter MAK">
                    @foreach ($filters as $key => $label)
                        <button type="button" data-filter="{{ $key }}" aria-pressed="{{ $key === 'semua' ? 'true' : 'false' }}"
                            class="js-mak-filter inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 border-gray-200 text-gray-600 hover:bg-gray-50 aria-pressed:bg-blue-900 aria-pressed:border-blue-900 aria-pressed:text-white">
                            {{ $label }}
                            <span class="tabular-nums opacity-70">{{ $hit[$key] }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                    @if ($belumDipakai > 0)
                        <label class="inline-flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                            <input type="checkbox" id="mon-mak-semua" class="rounded border-gray-300 text-blue-900 focus:ring-blue-600">
                            Tampilkan MAK yang belum dipakai ({{ $belumDipakai }})
                        </label>
                    @endif
                    <input type="search" id="mon-mak-cari" autocomplete="off" placeholder="Cari kode MAK atau uraian"
                        aria-label="Cari MAK"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full sm:w-64 focus:border-blue-700 focus:ring-1 focus:ring-blue-700 outline-none">
                </div>
            </div>

            <div class="max-h-[32rem] overflow-auto border-t border-gray-200">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th class="{{ $th }} pl-5 sticky top-0 z-10">MAK</th>
                            <th class="{{ $th }} text-right sticky top-0 z-10">Memo</th>
                            <th class="{{ $th }} text-right hidden lg:table-cell sticky top-0 z-10">Pagu</th>
                            <th class="{{ $th }} text-right hidden lg:table-cell sticky top-0 z-10">Realisasi</th>
                            <th class="{{ $th }} text-right sticky top-0 z-10">Outstanding</th>
                            <th class="{{ $th }} text-right sticky top-0 z-10">Sisa efektif</th>
                            <th class="{{ $th }} pr-5 w-40 hidden md:table-cell sticky top-0 z-10">Terpakai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($maks as $m)
                            @php
                                $persen = min(100, max(0, (float) ($m['persen'] ?? 0)));
                                $warnaBar = $m['kondisi'] === 'minus' ? 'bg-red-600' : ($m['kondisi'] === 'perhatian' ? 'bg-amber-500' : 'bg-blue-800');
                            @endphp
                            <tr class="js-mak-row {{ $m['dipakai'] ? '' : 'hidden' }}"
                                data-dipakai="{{ $m['dipakai'] ? 1 : 0 }}"
                                data-out="{{ $m['outstanding'] > 0 ? 1 : 0 }}"
                                data-sakti="{{ $m['menunggu_sp2d'] > 0 ? 1 : 0 }}"
                                data-minus="{{ $m['kondisi'] === 'minus' ? 1 : 0 }}"
                                data-search="{{ strtolower($m['mak'] . ' ' . $m['uraian']) }}">
                                <td class="pl-5 pr-3 py-3 min-w-0">
                                    <p class="font-mono text-xs font-semibold text-gray-900 break-all">{{ $m['mak'] }}</p>
                                    @if ($m['uraian'])
                                        <p class="mt-0.5 text-xs text-gray-500">{{ $m['uraian'] }}</p>
                                    @endif
                                    @if ($m['kondisi'] === 'minus')
                                        <span class="mt-1 inline-flex rounded border border-red-200 bg-red-50 px-1.5 py-0.5 text-[11px] font-medium text-red-700">Sisa minus</span>
                                    @elseif ($m['kondisi'] === 'perhatian')
                                        <span class="mt-1 inline-flex rounded border border-amber-200 bg-amber-50 px-1.5 py-0.5 text-[11px] font-medium text-amber-700">Hampir habis</span>
                                    @elseif ($m['kondisi'] === 'tanpa_master')
                                        <span class="mt-1 inline-flex rounded border border-gray-200 bg-gray-100 px-1.5 py-0.5 text-[11px] font-medium text-gray-600">Tidak ada di master aktif</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right whitespace-nowrap">
                                    <p class="text-xs font-medium tabular-nums text-gray-800">{{ $m['jumlah_memo'] }}</p>
                                    <p class="text-[11px] text-gray-400">
                                        {{ $m['memo_terakhir'] ? $m['memo_terakhir']->translatedFormat('d M Y') : '-' }}
                                    </p>
                                </td>
                                <td class="px-3 py-3 text-right tabular-nums text-gray-700 whitespace-nowrap hidden lg:table-cell">{{ $m['master'] ? $rp($m['pagu']) : '-' }}</td>
                                <td class="px-3 py-3 text-right tabular-nums text-gray-700 whitespace-nowrap hidden lg:table-cell">{{ $m['master'] ? $rp($m['realisasi']) : '-' }}</td>
                                <td class="px-3 py-3 text-right tabular-nums whitespace-nowrap {{ $m['outstanding'] > 0 ? 'text-amber-700 font-medium' : 'text-gray-400' }}">{{ $rp($m['outstanding']) }}</td>
                                <td class="px-3 py-3 text-right tabular-nums whitespace-nowrap font-semibold {{ $m['kondisi'] === 'minus' ? 'text-red-700' : 'text-gray-900' }}">{{ $m['master'] ? $rp($m['efektif']) : '-' }}</td>
                                <td class="pl-3 pr-5 py-3 hidden md:table-cell">
                                    @if ($m['persen'] !== null)
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 flex-1 rounded-full bg-gray-100 overflow-hidden" role="img"
                                                aria-label="Terpakai {{ $m['persen'] }} persen">
                                                <div class="h-full rounded-full {{ $warnaBar }}" style="width: {{ $persen }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-600 tabular-nums w-12 text-right">{{ number_format($m['persen'], 1, ',', '.') }}%</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500">
                                    Belum ada MAK. Upload tarikan SAKTI di halaman Anggaran atau buat memo terlebih dahulu.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="mon-mak-kosong" class="hidden">
                            <td colspan="7" class="px-5 py-8 text-center text-sm text-gray-500">Tidak ada MAK yang sesuai dengan filter.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ============ Antrean kerja ============ --}}
        @foreach ($antrean as $a)
            <section class="bg-white rounded-xl border border-gray-200">
                <form method="POST" action="{{ route('monitoring.status-massal') }}" class="js-antrean m-0">
                    @csrf
                    <input type="hidden" name="status" value="{{ $a['aksi'] }}">

                    <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <h3 class="text-base font-semibold text-gray-900">
                                {{ $a['judul'] }}
                                <span class="ml-1 text-sm font-normal text-gray-400">({{ $a['data']->count() }})</span>
                            </h3>
                            <p class="mt-0.5 text-xs text-gray-500 max-w-2xl">{{ $a['petunjuk'] }}</p>
                        </div>

                        @can('kelola-anggaran')
                            @if ($a['data']->isNotEmpty())
                                <div class="flex flex-wrap items-center gap-2">
                                    <label class="text-xs text-gray-500" for="tgl-{{ $a['key'] }}">Tanggal</label>
                                    <input type="date" id="tgl-{{ $a['key'] }}" name="tanggal" value="{{ now()->toDateString() }}"
                                        class="border border-gray-300 rounded-lg px-2.5 py-1.5 text-xs focus:border-blue-700 focus:ring-1 focus:ring-blue-700 outline-none">
                                    <button type="submit" disabled
                                        class="js-antrean-tombol inline-flex items-center gap-1.5 bg-blue-900 hover:bg-blue-800 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed text-white font-medium px-3.5 py-1.5 rounded-lg text-xs transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-1">
                                        {{ $a['tombol'] }}
                                        <span class="js-antrean-jumlah hidden rounded-full bg-white/20 px-1.5 tabular-nums"></span>
                                    </button>
                                </div>
                            @endif
                        @endcan
                    </div>

                    @if ($a['data']->isEmpty())
                        <p class="px-5 py-10 text-center text-sm text-gray-500 border-t border-gray-200">{{ $a['kosong'] }}</p>
                    @else
                        <div class="overflow-x-auto border-t border-gray-200">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left">
                                        @can('kelola-anggaran')
                                            <th class="{{ $th }} pl-5 pr-2 w-8">
                                                <input type="checkbox" class="js-antrean-semua rounded border-gray-300 text-blue-900 focus:ring-blue-600"
                                                    aria-label="Pilih semua di antrean ini">
                                            </th>
                                        @endcan
                                        <th class="{{ $th }}">Memo</th>
                                        <th class="{{ $th }}">Kegiatan</th>
                                        <th class="{{ $th }} hidden md:table-cell">MAK</th>
                                        <th class="{{ $th }} text-right">Nominal</th>
                                        <th class="{{ $th }} pr-5 text-right">Umur</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($a['data'] as $m)
                                        @php
                                            $umurClass = $a['key'] === 'outstanding'
                                                ? ($m['umur'] > 30 ? 'text-red-700 font-semibold' : ($m['umur'] > 14 ? 'text-amber-700 font-medium' : 'text-gray-500'))
                                                : ($m['umur'] > 30 ? 'text-amber-700 font-medium' : 'text-gray-500');
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            @can('kelola-anggaran')
                                                <td class="pl-5 pr-2 py-3">
                                                    <input type="checkbox" name="ids[]" value="{{ $m['id'] }}"
                                                        class="js-antrean-item rounded border-gray-300 text-blue-900 focus:ring-blue-600"
                                                        aria-label="Pilih memo {{ $m['nomor_memo'] }}">
                                                </td>
                                            @endcan
                                            <td class="px-3 py-3 whitespace-nowrap">
                                                <p class="text-xs font-semibold text-gray-900 tabular-nums">{{ $m['nomor_memo'] }}</p>
                                                <div class="mt-1 flex flex-wrap items-center gap-1">
                                                    @if ($m['label'])
                                                        <span class="inline-flex px-1.5 py-0.5 rounded border text-[11px] font-medium {{ $m['label'] === 'PNS' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-orange-50 text-orange-700 border-orange-200' }}">{{ $m['label'] }}</span>
                                                    @endif
                                                    @if ($m['lewat_tarikan'] ?? false)
                                                        <span class="inline-flex px-1.5 py-0.5 rounded border text-[11px] font-medium bg-violet-50 text-violet-700 border-violet-200"
                                                            title="Dibuat pada atau sebelum tanggal tarikan SAKTI terakhir, kemungkinan sudah diinput di SAKTI">
                                                            Cek SAKTI
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-3 py-3 min-w-0 max-w-xs">
                                                <p class="text-xs text-gray-800 break-words line-clamp-2">{{ $m['uraian'] }}</p>
                                                <p class="mt-0.5 text-[11px] text-gray-500">{{ $m['jenis_label'] }}, {{ $m['pic'] }}</p>
                                                <p class="mt-0.5 font-mono text-[11px] text-gray-400 break-all md:hidden">{{ $m['mak'] ?? '-' }}</p>
                                            </td>
                                            <td class="px-3 py-3 font-mono text-[11px] text-gray-600 break-all hidden md:table-cell">{{ $m['mak'] ?? '-' }}</td>
                                            <td class="px-3 py-3 text-right text-xs font-medium text-gray-800 tabular-nums whitespace-nowrap">{{ $rp($m['nominal']) }}</td>
                                            <td class="pl-3 pr-5 py-3 text-right text-xs tabular-nums whitespace-nowrap {{ $umurClass }}">{{ $m['umur'] }} hari</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </form>
            </section>
        @endforeach

        {{-- ============ Riwayat perubahan status ============ --}}
        <section class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4">
                <h3 class="text-base font-semibold text-gray-900">Riwayat perubahan status</h3>
                <p class="mt-0.5 text-xs text-gray-500">25 perubahan terakhir.</p>
            </div>

            @if ($riwayat->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-gray-500 border-t border-gray-200">Belum ada perubahan status.</p>
            @else
                <div class="overflow-x-auto border-t border-gray-200">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left">
                                <th class="{{ $th }} pl-5">Waktu</th>
                                <th class="{{ $th }}">Oleh</th>
                                <th class="{{ $th }}">Memo</th>
                                <th class="{{ $th }}">Perubahan</th>
                                <th class="{{ $th }} pr-5">Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($riwayat as $log)
                                <tr>
                                    <td class="pl-5 pr-3 py-3 text-xs text-gray-500 whitespace-nowrap tabular-nums">{{ $log->waktu->translatedFormat('d M Y, H:i') }}</td>
                                    <td class="px-3 py-3 text-xs text-gray-700 whitespace-nowrap">{{ $log->user_name ?? '-' }}</td>
                                    <td class="px-3 py-3 text-xs font-semibold text-gray-900 tabular-nums whitespace-nowrap">{{ $log->nomor_memo }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-0.5 rounded border text-[11px] font-medium {{ $statusBadge($log->dari) }}">{{ $statusLabel[$log->dari] ?? $log->dari }}</span>
                                        <span class="text-gray-400 text-xs mx-0.5" aria-hidden="true">&rarr;</span>
                                        <span class="inline-flex px-2 py-0.5 rounded border text-[11px] font-medium {{ $statusBadge($log->ke) }}">{{ $statusLabel[$log->ke] ?? $log->ke }}</span>
                                    </td>
                                    <td class="pl-3 pr-5 py-3 text-xs text-gray-500">{{ $log->alasan ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    <script>
        (function () {
            // ---- Filter tabel per MAK ----
            const cari = document.getElementById('mon-mak-cari');
            const semuaMak = document.getElementById('mon-mak-semua');
            const rows = Array.from(document.querySelectorAll('.js-mak-row'));
            const chips = Array.from(document.querySelectorAll('.js-mak-filter'));
            const kosong = document.getElementById('mon-mak-kosong');
            let aktif = 'semua';

            const kunci = { outstanding: 'out', sakti: 'sakti', minus: 'minus' };

            function filterMak() {
                const q = cari.value.trim().toLowerCase();
                const tampilSemua = q !== '' || (semuaMak && semuaMak.checked);
                let ada = 0;

                rows.forEach(function (tr) {
                    const cocokCari = q === '' || tr.dataset.search.includes(q);
                    const cocokPakai = tampilSemua || tr.dataset.dipakai === '1';
                    const cocokChip = aktif === 'semua' || tr.dataset[kunci[aktif]] === '1';
                    const cocok = cocokCari && cocokPakai && cocokChip;

                    tr.classList.toggle('hidden', !cocok);
                    if (cocok) ada++;
                });

                kosong.classList.toggle('hidden', ada > 0 || rows.length === 0);
            }

            chips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    aktif = chip.dataset.filter;
                    chips.forEach(function (c) {
                        c.setAttribute('aria-pressed', c === chip ? 'true' : 'false');
                    });
                    filterMak();
                });
            });

            cari.addEventListener('input', filterMak);
            if (semuaMak) semuaMak.addEventListener('change', filterMak);
            filterMak();

            // ---- Antrean: pilih semua + hitung yang dipilih ----
            document.querySelectorAll('.js-antrean').forEach(function (form) {
                const semua = form.querySelector('.js-antrean-semua');
                const items = Array.from(form.querySelectorAll('.js-antrean-item'));
                const tombol = form.querySelector('.js-antrean-tombol');
                const jumlah = form.querySelector('.js-antrean-jumlah');

                function perbarui() {
                    const n = items.filter(function (i) { return i.checked; }).length;

                    if (tombol) tombol.disabled = n === 0;
                    if (jumlah) {
                        jumlah.textContent = n;
                        jumlah.classList.toggle('hidden', n === 0);
                    }
                    if (semua) {
                        semua.checked = n > 0 && n === items.length;
                        semua.indeterminate = n > 0 && n < items.length;
                    }
                }

                if (semua) {
                    semua.addEventListener('change', function () {
                        items.forEach(function (i) { i.checked = semua.checked; });
                        perbarui();
                    });
                }

                items.forEach(function (i) { i.addEventListener('change', perbarui); });

                form.addEventListener('submit', function (e) {
                    const n = items.filter(function (i) { return i.checked; }).length;
                    if (n === 0) {
                        e.preventDefault();
                        return;
                    }
                    const aksi = form.querySelector('input[name="status"]').value.toUpperCase();
                    if (!window.confirm('Tandai ' + n + ' memo sebagai ' + aksi + '?')) {
                        e.preventDefault();
                    }
                });

                perbarui();
            });
        })();
    </script>
</x-app-layout>