<x-app-layout title="Monitoring Memo">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitoring Memo & Anggaran') }}
        </h2>
    </x-slot>

    @php
        $rp = fn($n) => ($n < 0 ? '-Rp ' : 'Rp ') . number_format(abs((int) $n), 0, ',', '.');

        // Unit hanya ditampilkan untuk admin (user dan TU hanya melihat unitnya sendiri).
        // Ganti jadi `true` kalau mau ditampilkan untuk semua role.
        $lihatUnit = (bool) auth()->user()?->isAdmin();

        $statusLabel = ['outstanding' => 'Outstanding', 'sakti' => 'SAKTI', 'sp2d' => 'SP2D', 'batal' => 'Batal'];
        $statusBadge = fn($s) => match ($s) {
            'sakti' => 'bg-blue-50 text-blue-700 border-blue-200',
            'sp2d' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'batal' => 'bg-gray-100 text-gray-500 border-gray-200',
            default => 'bg-amber-50 text-amber-700 border-amber-200',
        };

        // Kondisi MAK: 'minus' | 'habis' | 'perhatian' | null
        $kondisiMak = function ($m) {
            if (!($m['master'] ?? false))
                return null;
            if ($m['kondisi'] === 'minus')
                return 'minus';
            if ($m['efektif'] <= 0)
                return 'habis';
            if ($m['kondisi'] === 'perhatian')
                return 'perhatian';
            return null;
        };
        $badgeKondisi = [
            'minus' => ['Minus', 'border-red-200 bg-red-50 text-red-700'],
            'habis' => ['Habis', 'border-gray-300 bg-gray-100 text-gray-700'],
            'perhatian' => ['Hampir habis', 'border-amber-200 bg-amber-50 text-amber-700'],
        ];
        $rankKondisi = ['minus' => 0, 'habis' => 1, 'perhatian' => 2];
        $makMap = $maks->keyBy('mak');

        // ---- Gabungkan tiga status. SP2D opsional: controller perlu mengirim $antreanSp2d ----
        $antreanSp2d = $antreanSp2d ?? collect();
        $rankStatus = ['outstanding' => 0, 'sakti' => 1, 'sp2d' => 2];

        $semuaMemo = collect();
        foreach ($antreanOutstanding as $m) {
            $semuaMemo->push($m + ['_status' => 'outstanding']);
        }
        foreach ($antreanSakti as $m) {
            $semuaMemo->push($m + ['_status' => 'sakti']);
        }
        foreach ($antreanSp2d as $m) {
            $semuaMemo->push($m + ['_status' => 'sp2d']);
        }

        // Opsi dropdown filter
        $unitOpsi = $semuaMemo->pluck('unit')->filter()->unique()->sort()->values();
        $adaTanpaUnit = $semuaMemo->contains(fn($m) => empty($m['unit']));
        $jenisOpsi = $semuaMemo->pluck('jenis_label')->filter()->unique()->sort()->values();

        // Kelompokkan per MAK; MAK yang genting di atas, lalu yang memonya paling lama
        $grup = $semuaMemo->groupBy(fn($m) => $m['mak'] ?? '')->map(function ($memos, $kode) use ($makMap, $kondisiMak, $rankStatus) {
            $mk = $kode !== '' ? $makMap->get($kode) : null;
            return [
                'kode' => $kode,
                'mak' => $mk,
                'kondisi' => $mk ? $kondisiMak($mk) : null,
                'memos' => $memos->sort(function ($a, $b) use ($rankStatus) {
                    return [$rankStatus[$a['_status']], -($a['umur'] ?? 0)] <=> [$rankStatus[$b['_status']], -($b['umur'] ?? 0)];
                })->values(),
                'umur_max' => (int) $memos->where('_status', '!=', 'sp2d')->max('umur'),
            ];
        })->sort(function ($a, $b) use ($rankKondisi) {
            return [$rankKondisi[$a['kondisi']] ?? 3, -$a['umur_max']] <=> [$rankKondisi[$b['kondisi']] ?? 3, -$b['umur_max']];
        })->values();

        $chips = [
            'semua' => ['Semua', $semuaMemo->count()],
            'outstanding' => ['Perlu diinput ke SAKTI', $antreanOutstanding->count()],
            'sakti' => ['Menunggu pencairan (SP2D)', $antreanSakti->count()],
            'sp2d' => ['Sudah cair (bulan ini)', $antreanSp2d->count()],
        ];

        // Sel "Sisa efektif MAK": angka + badge kondisi. Pagu/Realisasi/Outstanding ada di tooltip.
        $sisaCell = function ($mk, $kon) use ($rp, $badgeKondisi) {
            if (!($mk && $mk['master']))
                return new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-400">-</span>');
            $tip = 'Pagu ' . $rp($mk['pagu']) . ' | Realisasi ' . $rp($mk['realisasi']) . ' | Outstanding ' . $rp($mk['outstanding'])
                . ($mk['persen'] !== null ? ' | Terpakai ' . number_format($mk['persen'], 1, ',', '.') . '%' : '');
            $out = '<p class="text-xs font-semibold tabular-nums ' . ($kon === 'minus' ? 'text-red-700' : 'text-gray-900') . '" title="' . e($tip) . '">' . e($rp($mk['efektif'])) . '</p>';
            if ($kon)
                $out .= '<span class="mt-0.5 inline-flex rounded border px-1.5 py-0.5 text-[11px] font-medium ' . $badgeKondisi[$kon][1] . '">' . $badgeKondisi[$kon][0] . '</span>';
            return new \Illuminate\Support\HtmlString($out);
        };

        $th = 'px-3 py-2.5 text-xs font-medium text-gray-500 bg-gray-50 border-b border-gray-200';
    @endphp

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

        @if (session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm"
                role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- ============ Alur memo ============ --}}
        <section aria-labelledby="judul-alur">
            <h3 id="judul-alur" class="text-sm font-semibold text-gray-900">Alur memo</h3>
            <p class="mt-0.5 text-xs text-gray-500 max-w-3xl">
                Setiap memo melewati tiga langkah. Klik kartu untuk melihat memonya di tabel di bawah.
            </p>

            <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-[1fr_auto_1fr_auto_1fr] md:gap-2">
                <a href="#antrean" data-filter="outstanding"
                    class="js-step block rounded-xl border border-gray-200 bg-white px-4 py-4 transition hover:border-blue-300 hover:bg-blue-50/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600">
                    <p class="text-xs font-medium text-gray-500">
                        <span
                            class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-100 text-[11px] font-semibold text-amber-800">1</span>
                        Outstanding
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500">Belum diinput di SAKTI</p>
                    <p class="mt-2 text-2xl font-semibold tabular-nums text-gray-900">
                        {{ number_format($ringkasan['outstanding']['jumlah'], 0, ',', '.') }}
                        <span class="text-sm font-normal text-gray-400">memo</span>
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500 tabular-nums">{{ $rp($ringkasan['outstanding']['total']) }}
                    </p>
                    @if ($ringkasan['lama']['jumlah'] > 0)
                        <p class="mt-2 text-xs font-medium text-red-700">
                            {{ $ringkasan['lama']['jumlah'] }} memo lebih dari 30 hari
                            ({{ $rp($ringkasan['lama']['total']) }})
                        </p>
                    @endif
                </a>

                <div class="hidden items-center justify-center text-gray-300 md:flex" aria-hidden="true">&rarr;</div>

                <a href="#antrean" data-filter="sakti"
                    class="js-step block rounded-xl border border-gray-200 bg-white px-4 py-4 transition hover:border-blue-300 hover:bg-blue-50/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600">
                    <p class="text-xs font-medium text-gray-500">
                        <span
                            class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-100 text-[11px] font-semibold text-blue-800">2</span>
                        SAKTI
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500">Sudah diinput, uang belum cair</p>
                    <p class="mt-2 text-2xl font-semibold tabular-nums text-gray-900">
                        {{ number_format($ringkasan['sakti']['jumlah'], 0, ',', '.') }}
                        <span class="text-sm font-normal text-gray-400">memo</span>
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500 tabular-nums">{{ $rp($ringkasan['sakti']['total']) }}</p>
                </a>

                <div class="hidden items-center justify-center text-gray-300 md:flex" aria-hidden="true">&rarr;</div>

                <a href="#antrean" data-filter="sp2d"
                    class="js-step block rounded-xl border border-gray-200 bg-white px-4 py-4 transition hover:border-blue-300 hover:bg-blue-50/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600">
                    <p class="text-xs font-medium text-gray-500">
                        <span
                            class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-[11px] font-semibold text-emerald-800">3</span>
                        SP2D
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500">Uang sudah cair, bulan ini</p>
                    <p class="mt-2 text-2xl font-semibold tabular-nums text-gray-900">
                        {{ number_format($ringkasan['sp2d']['jumlah'], 0, ',', '.') }}
                        <span class="text-sm font-normal text-gray-400">memo</span>
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500 tabular-nums">{{ $rp($ringkasan['sp2d']['total']) }}</p>
                </a>
            </div>

            <p class="mt-2 text-xs text-gray-500">
                Memo yang dibatalkan tidak dihitung. Data realisasi mengacu pada tarikan SAKTI tanggal
                <span
                    class="font-medium text-gray-700">{{ $tarikanTanggal ? $tarikanTanggal->translatedFormat('d F Y') : 'belum ada' }}</span>.
                Upload tarikan terbaru sebelum menandai memo sudah di SAKTI.
            </p>
        </section>

        {{-- ============ Memo per MAK ============ --}}
        <section id="antrean" class="scroll-mt-4 bg-white rounded-xl border border-gray-200"
            aria-labelledby="judul-antrean">
            <form method="POST" action="{{ route('monitoring.status-massal') }}" id="form-antrean" class="m-0">
                @csrf
                <input type="hidden" name="status" id="antrean-status" value="">

                <div class="flex flex-col gap-3 px-5 pt-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h3 id="judul-antrean" class="text-base font-semibold text-gray-900">Memo per MAK</h3>
                        <p class="mt-0.5 text-xs text-gray-500 max-w-2xl">
                            Sisa efektif = sisa menurut tarikan SAKTI dikurangi memo outstanding (semua unit). MAK
                            dengan banyak memo bisa dibuka.
                        </p>
                    </div>

                    @can('kelola-anggaran')
                        @if ($antreanOutstanding->isNotEmpty() || $antreanSakti->isNotEmpty())
                            <div class="flex flex-col items-start gap-1.5 sm:items-end">
                                <div class="flex flex-wrap items-center gap-2">
                                    <label class="text-xs text-gray-500" for="antrean-tanggal"
                                        id="antrean-label-tanggal">Tanggal</label>
                                    <input type="date" id="antrean-tanggal" name="tanggal" value="{{ now()->toDateString() }}"
                                        class="border border-gray-300 rounded-lg px-2.5 py-1.5 text-xs focus:border-blue-700 focus:ring-1 focus:ring-blue-700 outline-none">
                                    <button type="submit" id="antrean-tombol" disabled
                                        class="inline-flex items-center gap-1.5 bg-blue-900 hover:bg-blue-800 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed text-white font-medium px-3.5 py-1.5 rounded-lg text-xs transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-1">
                                        <span id="antrean-teks">Pilih memo dulu</span>
                                        <span id="antrean-jumlah"
                                            class="hidden rounded-full bg-white/20 px-1.5 tabular-nums"></span>
                                    </button>
                                </div>
                                <p id="antrean-campur" class="hidden text-xs text-red-700" role="alert">
                                    Pilih memo dengan status yang sama.
                                </p>
                            </div>
                        @endif
                    @endcan
                </div>

                <div class="mt-3 flex flex-col gap-3 px-5 pb-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-wrap gap-1.5" role="group" aria-label="Filter status memo">
                        @foreach ($chips as $key => [$label, $n])
                            <button type="button" data-filter="{{ $key }}"
                                aria-pressed="{{ $key === 'semua' ? 'true' : 'false' }}"
                                class="js-filter inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 border-gray-200 text-gray-600 hover:bg-gray-50 aria-pressed:bg-blue-900 aria-pressed:border-blue-900 aria-pressed:text-white">
                                {{ $label }}
                                <span class="tabular-nums opacity-70">{{ $n }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                        @if ($lihatUnit)
                            <select id="filter-unit" aria-label="Filter unit"
                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-blue-700 focus:ring-1 focus:ring-blue-700 outline-none">
                                <option value="">Semua unit</option>
                                @foreach ($unitOpsi as $u)
                                    <option value="{{ $u }}">{{ $u }}</option>
                                @endforeach
                                @if ($adaTanpaUnit)
                                    <option value="__none">Tanpa unit</option>
                                @endif
                            </select>
                        @endif
                        <select id="filter-jenis" aria-label="Filter jenis memo"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-blue-700 focus:ring-1 focus:ring-blue-700 outline-none">
                            <option value="">Semua jenis</option>
                            @foreach ($jenisOpsi as $j)
                                <option value="{{ $j }}">{{ $j }}</option>
                            @endforeach
                        </select>
                        <input type="search" id="cari" autocomplete="off"
                            placeholder="Cari nomor memo, kegiatan, atau MAK" aria-label="Cari memo atau MAK"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full sm:w-64 focus:border-blue-700 focus:ring-1 focus:ring-blue-700 outline-none">
                    </div>
                </div>

                @if ($grup->isEmpty())
                    <p class="px-5 py-10 text-center text-sm text-gray-500 border-t border-gray-200">Belum ada memo.</p>
                @else
                    <div class="max-h-[40rem] overflow-auto border-t border-gray-200">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left">
                                    <th class="{{ $th }} pl-5 pr-2 w-8 sticky top-0 z-10">
                                        @can('kelola-anggaran')
                                            <input type="checkbox" id="antrean-semua"
                                                class="rounded border-gray-300 text-blue-900 focus:ring-blue-600"
                                                aria-label="Pilih semua memo yang tampil">
                                        @endcan
                                    </th>
                                    <th class="{{ $th }} sticky top-0 z-10">MAK</th>
                                    <th class="{{ $th }} sticky top-0 z-10">Memo</th>
                                    @if ($lihatUnit)
                                        <th class="{{ $th }} hidden lg:table-cell sticky top-0 z-10">Unit</th>
                                    @endif
                                    <th class="{{ $th }} hidden md:table-cell sticky top-0 z-10">Jenis</th>
                                    <th class="{{ $th }} sticky top-0 z-10">Status</th>
                                    <th class="{{ $th }} sticky top-0 z-10">Kegiatan</th>
                                    <th class="{{ $th }} text-right sticky top-0 z-10">Nominal</th>
                                    <th class="{{ $th }} text-right sticky top-0 z-10"
                                        title="Sisa menurut tarikan SAKTI dikurangi outstanding">Sisa efektif MAK</th>
                                    <th class="{{ $th }} pr-5 text-right sticky top-0 z-10">Umur</th>
                                </tr>
                            </thead>

                            @foreach ($grup as $g)
                                @php
                                    $mk = $g['mak'];
                                    $kon = $g['kondisi'];
                                    $multi = $g['memos']->count() > 1;
                                @endphp
                                <tbody class="js-grup border-b border-gray-200" data-multi="{{ $multi ? 1 : 0 }}" data-open="0">
                                    @if ($multi)
                                        {{-- Baris MAK (hanya untuk MAK dengan lebih dari satu memo) --}}
                                        <tr class="bg-gray-50/60">
                                            <td class="pl-5 pr-2 py-2.5 align-top">
                                                @can('kelola-anggaran')
                                                    <input type="checkbox"
                                                        class="js-grup-cek rounded border-gray-300 text-blue-900 focus:ring-blue-600"
                                                        aria-label="Pilih semua memo di MAK {{ $g['kode'] ?: 'tanpa MAK' }}">
                                                @endcan
                                            </td>
                                            <td class="px-3 py-2.5 align-top min-w-0">
                                                <p class="font-mono text-xs font-semibold text-gray-900 break-all">
                                                    {{ $g['kode'] ?: 'Tanpa MAK' }}</p>
                                                @if ($mk && !empty($mk['uraian']))
                                                    <p class="mt-0.5 text-xs text-gray-500 max-w-[16rem] truncate"
                                                        title="{{ $mk['uraian'] }}">{{ $mk['uraian'] }}</p>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2.5 align-top whitespace-nowrap">
                                                <p class="text-xs text-gray-700">
                                                    <span class="js-n font-semibold tabular-nums">{{ $g['memos']->count() }}</span>
                                                    memo
                                                </p>
                                                <button type="button" aria-expanded="false"
                                                    class="js-toggle mt-0.5 inline-flex items-center gap-1 text-xs font-medium text-blue-800 hover:text-blue-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 rounded">
                                                    <span class="js-toggle-ikon inline-block transition"
                                                        aria-hidden="true">&#9656;</span>
                                                    <span class="js-toggle-teks">Lihat memo</span>
                                                </button>
                                            </td>
                                            @if ($lihatUnit)
                                                <td class="px-3 py-2.5 hidden lg:table-cell"></td>
                                            @endif
                                            <td class="px-3 py-2.5 hidden md:table-cell"></td>
                                            <td class="px-3 py-2.5"></td>
                                            <td class="px-3 py-2.5"></td>
                                            <td class="px-3 py-2.5 align-top text-right whitespace-nowrap">
                                                <p class="js-sum text-xs font-semibold tabular-nums text-gray-900">
                                                    {{ $rp($g['memos']->sum('nominal')) }}</p>
                                            </td>
                                            <td class="px-3 py-2.5 align-top text-right whitespace-nowrap">
                                                {{ $sisaCell($mk, $kon) }}</td>
                                            <td class="pl-3 pr-5 py-2.5"></td>
                                        </tr>
                                    @endif

                                    {{-- Baris memo. MAK dengan satu memo: baris ini sekaligus baris MAK --}}
                                    @foreach ($g['memos'] as $m)
                                        @php
                                            $st = $m['_status'];
                                            $umur = $m['umur'] ?? null;
                                            $umurClass = $st === 'outstanding'
                                                ? (($umur ?? 0) > 30 ? 'text-red-700 font-semibold' : (($umur ?? 0) > 14 ? 'text-amber-700 font-medium' : 'text-gray-500'))
                                                : (($umur ?? 0) > 30 && $st === 'sakti' ? 'text-amber-700 font-medium' : 'text-gray-500');
                                            $umurKet = ['outstanding' => 'sejak dibuat', 'sakti' => 'sejak di SAKTI', 'sp2d' => 'sejak dibuat'][$st];
                                            $cari = strtolower(($m['nomor_memo'] ?? '') . ' ' . ($m['uraian'] ?? '') . ' ' . $g['kode'] . ' ' . ($m['pic'] ?? ''));
                                        @endphp
                                        <tr class="js-memo hover:bg-gray-50 {{ $multi ? 'hidden' : '' }}" data-status="{{ $st }}"
                                            data-nominal="{{ (int) $m['nominal'] }}" data-search="{{ $cari }}"
                                            data-unit="{{ $m['unit'] ?? '' }}" data-jenis="{{ $m['jenis_label'] ?? '' }}">
                                            <td class="pl-5 pr-2 py-2.5 align-top">
                                                @can('kelola-anggaran')
                                                    @if ($st !== 'sp2d')
                                                        <input type="checkbox" name="ids[]" value="{{ $m['id'] }}" data-status="{{ $st }}"
                                                            class="js-antrean-item rounded border-gray-300 text-blue-900 focus:ring-blue-600"
                                                            aria-label="Pilih memo {{ $m['nomor_memo'] }}">
                                                    @endif
                                                @endcan
                                            </td>
                                            <td class="px-3 py-2.5 align-top min-w-0">
                                                @unless ($multi)
                                                    <p class="font-mono text-xs font-semibold text-gray-900 break-all">
                                                        {{ $g['kode'] ?: 'Tanpa MAK' }}</p>
                                                    @if ($mk && !empty($mk['uraian']))
                                                        <p class="mt-0.5 text-xs text-gray-500 max-w-[16rem] truncate"
                                                            title="{{ $mk['uraian'] }}">{{ $mk['uraian'] }}</p>
                                                    @endif
                                                @endunless
                                            </td>
                                            <td class="px-3 py-2.5 align-top whitespace-nowrap">
                                                <p class="text-xs font-semibold text-gray-900 tabular-nums">{{ $m['nomor_memo'] }}
                                                </p>
                                                @if (!empty($m['label']) || ($m['lewat_tarikan'] ?? false))
                                                    <div class="mt-0.5 flex flex-wrap items-center gap-1">
                                                        @if (!empty($m['label']))
                                                            <span
                                                                class="inline-flex px-1.5 py-0.5 rounded border text-[11px] font-medium {{ $m['label'] === 'PNS' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-orange-50 text-orange-700 border-orange-200' }}">{{ $m['label'] }}</span>
                                                        @endif
                                                        @if ($m['lewat_tarikan'] ?? false)
                                                            <span
                                                                class="inline-flex px-1.5 py-0.5 rounded border text-[11px] font-medium bg-violet-50 text-violet-700 border-violet-200"
                                                                title="Dibuat pada atau sebelum tanggal tarikan SAKTI terakhir, kemungkinan sudah diinput di SAKTI">
                                                                Cek SAKTI
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            @if ($lihatUnit)
                                                <td class="px-3 py-2.5 align-top hidden lg:table-cell">
                                                    @if (!empty($m['unit']))
                                                        <p class="text-xs font-semibold text-gray-800 whitespace-nowrap">{{ $m['unit'] }}
                                                        </p>
                                                    @else
                                                        <span class="text-xs text-rose-500">Tanpa unit</span>
                                                    @endif
                                                </td>
                                            @endif
                                            <td class="px-3 py-2.5 align-top hidden md:table-cell whitespace-nowrap">
                                                <span
                                                    class="inline-flex px-2 py-0.5 rounded border text-[11px] font-medium {{ ($m['jenis'] ?? null) === 'perdin' ? 'bg-sky-50 text-sky-700 border-sky-200' : 'bg-gray-50 text-gray-700 border-gray-200' }}">{{ $m['jenis_label'] ?? '-' }}</span>
                                            </td>
                                            <td class="px-3 py-2.5 align-top whitespace-nowrap">
                                                <span
                                                    class="inline-flex px-2 py-0.5 rounded border text-[11px] font-medium {{ $statusBadge($st) }}">{{ $statusLabel[$st] }}</span>
                                            </td>
                                            <td class="px-3 py-2.5 align-top min-w-0 max-w-xs">
                                                <p class="text-xs text-gray-800 truncate" title="{{ $m['uraian'] ?? '' }}">
                                                    {{ $m['uraian'] ?? '-' }}</p>
                                                @if (!empty($m['pic']))
                                                    <p class="mt-0.5 text-[11px] text-gray-500 truncate">{{ $m['pic'] }}</p>
                                                @endif
                                            </td>
                                            <td
                                                class="px-3 py-2.5 align-top text-right text-xs font-medium text-gray-800 tabular-nums whitespace-nowrap">
                                                {{ $rp($m['nominal']) }}</td>
                                            <td class="px-3 py-2.5 align-top text-right whitespace-nowrap">
                                                @unless ($multi)
                                                    {{ $sisaCell($mk, $kon) }}
                                                @endunless
                                            </td>
                                            <td class="pl-3 pr-5 py-2.5 align-top text-right whitespace-nowrap">
                                                @if ($umur !== null)
                                                    <p class="text-xs tabular-nums {{ $umurClass }}">{{ $umur }} hari</p>
                                                    <p class="text-[11px] text-gray-400">{{ $umurKet }}</p>
                                                @else
                                                    <span class="text-xs text-gray-400">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            @endforeach

                            <tbody id="antrean-kosong" class="hidden">
                                <tr>
                                    <td colspan="{{ $lihatUnit ? 10 : 9 }}"
                                        class="px-5 py-10 text-center text-sm text-gray-500">Tidak ada memo yang sesuai
                                        dengan filter.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            </form>
        </section>

        {{-- ============ Riwayat perubahan status ============ --}}
        <section class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4">
                <h3 class="text-base font-semibold text-gray-900">Riwayat perubahan status</h3>
                <p class="mt-0.5 text-xs text-gray-500">25 perubahan terakhir.</p>
            </div>

            @if ($riwayat->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-gray-500 border-t border-gray-200">Belum ada perubahan status.
                </p>
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
                                    <td class="pl-5 pr-3 py-3 text-xs text-gray-500 whitespace-nowrap tabular-nums">
                                        {{ $log->waktu->translatedFormat('d M Y, H:i') }}</td>
                                    <td class="px-3 py-3 text-xs text-gray-700 whitespace-nowrap">{{ $log->user_name ?? '-' }}
                                    </td>
                                    <td class="px-3 py-3 text-xs font-semibold text-gray-900 tabular-nums whitespace-nowrap">
                                        {{ $log->nomor_memo }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-2 py-0.5 rounded border text-[11px] font-medium {{ $statusBadge($log->dari) }}">{{ $statusLabel[$log->dari] ?? $log->dari }}</span>
                                        <span class="text-gray-400 text-xs mx-0.5" aria-hidden="true">&rarr;</span>
                                        <span
                                            class="inline-flex px-2 py-0.5 rounded border text-[11px] font-medium {{ $statusBadge($log->ke) }}">{{ $statusLabel[$log->ke] ?? $log->ke }}</span>
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
            const form = document.getElementById('form-antrean');
            if (!form) return;

            const chips = Array.from(document.querySelectorAll('.js-filter'));
            const grups = Array.from(form.querySelectorAll('.js-grup'));
            const items = Array.from(form.querySelectorAll('.js-antrean-item'));
            const cari = document.getElementById('cari');
            const fUnit = document.getElementById('filter-unit');
            const fJenis = document.getElementById('filter-jenis');
            const kosong = document.getElementById('antrean-kosong');

            const semua = document.getElementById('antrean-semua');
            const tombol = document.getElementById('antrean-tombol');
            const teks = document.getElementById('antrean-teks');
            const jumlah = document.getElementById('antrean-jumlah');
            const campur = document.getElementById('antrean-campur');
            const inputStatus = document.getElementById('antrean-status');
            const labelTanggal = document.getElementById('antrean-label-tanggal');

            const aksi = {
                outstanding: { status: 'sakti', teks: 'Tandai sudah di SAKTI', tanggal: 'Tanggal diinput' },
                sakti: { status: 'sp2d', teks: 'Tandai sudah cair (SP2D)', tanggal: 'Tanggal cair' }
            };

            let filter = 'semua';

            const rp = function (n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); };

            // Item yang cocok dengan filter dan pencarian (walau barisnya sedang tertutup)
            function cocokItem(i) {
                return i.closest('tr').dataset.cocok === '1';
            }

            function terapkan() {
                const q = cari.value.trim().toLowerCase();
                let grupTampil = 0;

                grups.forEach(function (g) {
                    const memos = Array.from(g.querySelectorAll('.js-memo'));
                    const buka = g.dataset.open === '1';
                    let n = 0, sum = 0;

                    memos.forEach(function (tr) {
                        const vUnit = fUnit ? fUnit.value : '';
                        const cocokUnit = vUnit === ''
                            || (vUnit === '__none' ? tr.dataset.unit === '' : tr.dataset.unit === vUnit);
                        const cocokJenis = !fJenis || fJenis.value === '' || tr.dataset.jenis === fJenis.value;
                        const cocok = (filter === 'semua' || tr.dataset.status === filter)
                            && cocokUnit && cocokJenis
                            && (q === '' || tr.dataset.search.indexOf(q) !== -1);
                        tr.dataset.cocok = cocok ? '1' : '0';
                        if (cocok) { n++; sum += Number(tr.dataset.nominal); }
                    });

                    // Buka tutup hanya berlaku kalau yang cocok lebih dari satu memo
                    const lipat = g.dataset.multi === '1' && n > 1;

                    memos.forEach(function (tr) {
                        const tampil = tr.dataset.cocok === '1' && (!lipat || buka);
                        tr.classList.toggle('hidden', !tampil);
                        const cb = tr.querySelector('.js-antrean-item');
                        if (cb && tr.dataset.cocok !== '1') cb.checked = false;
                    });

                    g.classList.toggle('hidden', n === 0);
                    if (n > 0) grupTampil++;

                    const elN = g.querySelector('.js-n');
                    const elSum = g.querySelector('.js-sum');
                    if (elN) elN.textContent = n;
                    if (elSum) elSum.textContent = rp(sum);

                    const t = g.querySelector('.js-toggle');
                    if (t) t.classList.toggle('hidden', !lipat);
                });

                if (kosong) kosong.classList.toggle('hidden', grupTampil > 0);
                perbarui();
            }

            function perbarui() {
                const dipilih = items.filter(function (i) { return i.checked; });
                const jenis = Array.from(new Set(dipilih.map(function (i) { return i.dataset.status; })));
                const n = dipilih.length;
                const campurAda = jenis.length > 1;

                if (campur) campur.classList.toggle('hidden', !campurAda);

                if (tombol) {
                    if (n === 0) {
                        tombol.disabled = true;
                        teks.textContent = 'Pilih memo dulu';
                        inputStatus.value = '';
                        labelTanggal.textContent = 'Tanggal';
                    } else if (campurAda) {
                        tombol.disabled = true;
                        teks.textContent = 'Status campur';
                        inputStatus.value = '';
                        labelTanggal.textContent = 'Tanggal';
                    } else {
                        const a = aksi[jenis[0]];
                        tombol.disabled = false;
                        teks.textContent = a.teks;
                        inputStatus.value = a.status;
                        labelTanggal.textContent = a.tanggal;
                    }
                    jumlah.textContent = n;
                    jumlah.classList.toggle('hidden', n === 0);
                }

                // Kotak centang per MAK
                grups.forEach(function (g) {
                    const cek = g.querySelector('.js-grup-cek');
                    if (!cek) return;
                    const gi = Array.from(g.querySelectorAll('.js-antrean-item')).filter(cocokItem);
                    const c = gi.filter(function (i) { return i.checked; }).length;
                    cek.disabled = gi.length === 0;
                    cek.checked = gi.length > 0 && c === gi.length;
                    cek.indeterminate = c > 0 && c < gi.length;
                });

                // Kotak centang di header tabel
                if (semua) {
                    const gi = items.filter(cocokItem);
                    const c = gi.filter(function (i) { return i.checked; }).length;
                    semua.checked = gi.length > 0 && c === gi.length;
                    semua.indeterminate = c > 0 && c < gi.length;
                }
            }

            // Chip filter dan kartu alur
            function pilihFilter(key) {
                filter = key;
                chips.forEach(function (c) {
                    c.setAttribute('aria-pressed', c.dataset.filter === key ? 'true' : 'false');
                });
                terapkan();
            }

            chips.forEach(function (c) {
                c.addEventListener('click', function () { pilihFilter(c.dataset.filter); });
            });
            document.querySelectorAll('.js-step').forEach(function (s) {
                s.addEventListener('click', function () { pilihFilter(s.dataset.filter); });
            });

            cari.addEventListener('input', terapkan);
            if (fUnit) fUnit.addEventListener('change', terapkan);
            if (fJenis) fJenis.addEventListener('change', terapkan);

            grups.forEach(function (g) {
                // Buka tutup memo per MAK
                const t = g.querySelector('.js-toggle');
                if (t) {
                    t.addEventListener('click', function () {
                        const buka = g.dataset.open !== '1';
                        g.dataset.open = buka ? '1' : '0';
                        t.setAttribute('aria-expanded', buka ? 'true' : 'false');
                        t.querySelector('.js-toggle-teks').textContent = buka ? 'Tutup' : 'Lihat memo';
                        t.querySelector('.js-toggle-ikon').style.transform = buka ? 'rotate(90deg)' : '';
                        terapkan();
                    });
                }

                // Centang semua memo di satu MAK
                const cek = g.querySelector('.js-grup-cek');
                if (cek) {
                    cek.addEventListener('change', function () {
                        g.querySelectorAll('.js-antrean-item').forEach(function (i) {
                            if (cocokItem(i)) i.checked = cek.checked;
                        });
                        perbarui();
                    });
                }
            });

            if (semua) {
                semua.addEventListener('change', function () {
                    items.forEach(function (i) { if (cocokItem(i)) i.checked = semua.checked; });
                    perbarui();
                });
            }

            items.forEach(function (i) { i.addEventListener('change', perbarui); });

            form.addEventListener('submit', function (e) {
                const dipilih = items.filter(function (i) { return i.checked; });
                const jenis = Array.from(new Set(dipilih.map(function (i) { return i.dataset.status; })));
                if (dipilih.length === 0 || jenis.length !== 1) {
                    e.preventDefault();
                    return;
                }
                const nama = aksi[jenis[0]].status.toUpperCase();
                if (!window.confirm('Tandai ' + dipilih.length + ' memo sebagai ' + nama + '?')) {
                    e.preventDefault();
                }
            });

            terapkan();
        })();
    </script>
</x-app-layout>