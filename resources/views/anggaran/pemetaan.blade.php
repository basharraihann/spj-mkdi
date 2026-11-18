<x-app-layout title="Pemetaan Unit">

    <style>
        /* Kartu: gaya sama dengan halaman Monitor Anggaran */
        .card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 0 0 1px rgb(226 232 240 / .8), 0 1px 2px rgb(15 23 42 / .04);
        }

        /* Kartu unit (collapsible) */
        details.unit-card > summary { list-style: none; cursor: pointer; }
        details.unit-card > summary::-webkit-details-marker { display: none; }
        details.unit-card[open] .chev { transform: rotate(90deg); }
        .chev { transition: transform .15s ease; }
        .rule-row:hover { background: rgb(248 250 252); }
        .chip-on { background: #2563eb !important; color: #fff !important; box-shadow: none !important; }
    </style>

    @php
        $inp = 'w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white';
        $btn = 'inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition shadow-sm';
        $btnLite = 'inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 transition';
        $card = 'card';
        $iconCircle = 'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100';
        $docIcon = '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>';

        /*
         * Semua aturan ditampilkan, dikelompokkan PER UNIT:
         *   - Unit yang induknya D1-D4 (Asdep, Sesdep, Deputi) -> masuk blok Deputi masing-masing
         *   - Unit lain (Biro, Inspektorat, dst.)               -> blok "Biro & Unit Lainnya"
         * Agar semua aturan tampil, $rules sebaiknya berisi seluruh data (->get(), bukan ->paginate()).
         */
        $items = method_exists($rules, 'getCollection') ? $rules->getCollection() : collect($rules);
        $isPaged = method_exists($rules, 'hasPages') && $rules->hasPages();

        $kegDeputi = [1 => 'CL.7458', 2 => 'CL.7459', 3 => 'CL.7460', 4 => 'CL.7461'];

        $levelOf = function ($r) {
            if (filled($r->sub_kode)) return 'sub';
            if (filled($r->komp_kode)) return 'komp';
            if (filled($r->keg_kode)) return 'kegro';
            return 'ro';
        };
        $levelMeta = [
            'sub'   => ['SubKomponen', 'bg-violet-50 text-violet-700 ring-violet-100'],
            'komp'  => ['Komponen', 'bg-amber-50 text-amber-700 ring-amber-100'],
            'kegro' => ['Kegiatan + RO', 'bg-blue-50 text-blue-700 ring-blue-100'],
            'ro'    => ['RO', 'bg-slate-100 text-slate-600 ring-slate-200'],
        ];

        // Urutan unit di dalam Deputi: Asdep 1..5, Sesdep, lalu Deputi sendiri
        $unitRank = function ($u) {
            if (preg_match('/^Asdep (\d)/', $u, $m)) return (int) $m[1];
            if (str_starts_with($u, 'Sesdep')) return 6;
            if (preg_match('/^D[1-4]$/', $u)) return 7;
            return 9;
        };

        $byUnit = $items->groupBy(fn($r) => $r->unit ?: 'Tanpa unit');

        $deputiBlocks = [];
        $lain = [];
        foreach ($byUnit as $unit => $grp) {
            if (preg_match('/^D([1-4])$/', (string) $grp->first()->induk, $m)) {
                $deputiBlocks[(int) $m[1]][$unit] = $grp;
            } else {
                $lain[$unit] = $grp;
            }
        }
        ksort($deputiBlocks);
        foreach ($deputiBlocks as $n => $units) {
            uksort($units, fn($a, $b) => [$unitRank($a), $a] <=> [$unitRank($b), $b]);
            $deputiBlocks[$n] = $units;
        }
        ksort($lain, SORT_NATURAL | SORT_FLAG_CASE);

        $sections = [];
        foreach ($deputiBlocks as $n => $units) {
            $sections[] = [
                'id' => 'd' . $n,
                'title' => 'Deputi ' . $n,
                'hint' => 'Kegiatan ' . ($kegDeputi[$n] ?? '') . ' · Asdep, Sesdep, dan Deputi',
                'badge' => 'D' . $n,
                'units' => $units,
            ];
        }
        if (!empty($lain)) {
            $sections[] = [
                'id' => 'lain',
                'title' => 'Biro & Unit Lainnya',
                'hint' => 'Biro, Inspektorat, dan unit yang tidak berada di bawah Deputi',
                'badge' => 'B',
                'units' => $lain,
            ];
        }

        // Kode anggaran nyata per unit (dari upload aktif, sudah di-resolve). Dikirim controller sebagai $coverage.
        $hasCov = isset($coverage);
        $cov = $hasCov
            ? collect($coverage)->groupBy(fn($c) => $c->unit ?: 'Tanpa unit')
            : collect();

        $totalRules = $items->count();
        $totalUnits = $byUnit->count();
        $totalRo = $items->pluck('ro_kode')->unique()->count();
        $totalDetail = $items->filter(fn($r) => filled($r->sub_kode) || filled($r->komp_kode))->count();
    @endphp

    <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- ===== Judul (pita putih di bawah navbar) ===== --}}
        <div class="-mx-4 sm:-mx-6 lg:-mx-8 -mt-8 border-b border-slate-200 bg-white px-4 py-5 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-slate-800">Pemetaan Unit Anggaran</h2>
                    <p class="mt-1 max-w-3xl text-sm text-slate-500">Aturan paling spesifik menang: SubKomponen, lalu
                        Komponen, lalu Kegiatan + RO, lalu RO saja.
                        Perubahan langsung berlaku ke semua data, tanpa upload ulang.</p>
                </div>
                <a href="{{ route('anggaran.index') }}"
                    class="{{ $btnLite }} !px-4 !py-2.5 !text-sm shrink-0 gap-2 shadow-sm">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Monitor Anggaran
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach ($errors->all() as $e)
                <div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <datalist id="dl-unit">
            @foreach ($units as $u)
            <option value="{{ $u }}">@endforeach
        </datalist>

        <datalist id="dl-induk">
            @foreach ($induks as $u)
            <option value="{{ $u }}">@endforeach
        </datalist>

        {{-- ===== Belum dipetakan ===== --}}
        @if ($unmapped->isNotEmpty())
            <div class="card overflow-hidden" style="box-shadow:0 0 0 1px rgb(253 230 138), 0 1px 2px rgb(15 23 42 / .04)">
                <div class="px-5 py-3 bg-amber-50 border-b border-amber-200 text-sm text-amber-900">
                    <b>{{ $unmapped->count() }} baris belum dipetakan</b> di upload aktif ({{ $upload->periode ?? '' }}).
                    <span class="text-xs text-amber-700">RO yang dipecah per komponen/subkomponen tampil per kode, dan
                        aturannya disimpan per kode itu.</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-xs text-white" style="background:#2563eb">
                            <tr>
                                <th class="text-left px-4 py-2.5 font-semibold">Kegiatan</th>
                                <th class="text-left px-3 py-2.5 font-semibold">RO</th>
                                <th class="text-left px-3 py-2.5 font-semibold">Komponen / Sub</th>
                                <th class="text-left px-3 py-2.5 font-semibold">Uraian</th>
                                <th class="text-right px-3 py-2.5 font-semibold">Pagu</th>
                                <th class="text-left px-4 py-2.5 font-semibold">Pilih unit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($unmapped as $r)
                                <tr>
                                    <td class="px-4 py-2 font-mono text-xs">{{ $r->keg_kode }}</td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $r->ro_kode }}</td>
                                    <td class="px-3 py-2 font-mono text-xs">
                                        @if ($r->level === 'sub'){{ $r->sub_kode }}
                                        @elseif ($r->level === 'komp'){{ $r->komp_kode }}
                                        @else–@endif
                                    </td>
                                    <td class="px-3 py-2">{{ $r->ro_uraian }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums">{{ number_format($r->pagu, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <form method="POST" action="{{ route('anggaran.pemetaan.store') }}"
                                            class="flex flex-wrap items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="ro_kode" value="{{ $r->ro_kode }}">
                                            @if ($r->level === 'sub')
                                                <input type="hidden" name="sub_kode" value="{{ $r->sub_kode }}">
                                            @elseif ($r->level === 'komp')
                                                <input type="hidden" name="komp_kode" value="{{ $r->komp_kode }}">
                                            @endif
                                            <input name="unit" list="dl-unit" required placeholder="Unit kerja"
                                                class="{{ $inp }} !w-44">
                                            <input name="induk" list="dl-induk" placeholder="Induk (otomatis)"
                                                class="{{ $inp }} !w-32">
                                            @unless ($r->level)
                                                <label class="flex items-center gap-1 text-xs text-slate-500">
                                                    <input type="checkbox" name="keg_kode" value="{{ $r->keg_kode }}"
                                                        class="rounded border-slate-300"> hanya {{ $r->keg_kode }}
                                                </label>
                                            @endunless
                                            <button class="{{ $btn }}">Simpan</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ===== Tambah aturan ===== --}}
        <div class="{{ $card }} p-5 sm:p-6">
            <div class="flex items-start gap-3">
                <span class="{{ $iconCircle }}">{!! $docIcon !!}</span>
                <div>
                    <h3 class="text-base font-semibold text-slate-800">Pemetaan RO ke Unit</h3>
                    <p class="text-sm text-slate-500">Tentukan unit kerja untuk setiap kode anggaran.</p>
                </div>
            </div>

            @if ($unmapped->isEmpty() && $upload)
                <div class="mt-4 flex items-center gap-2.5 rounded-xl bg-blue-50 px-4 py-2.5 text-sm text-blue-700">
                    <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                    Semua kode di upload aktif sudah terpetakan.
                </div>
            @endif

            <div class="mt-5 border-t border-slate-100 pt-5">
                <h3 class="text-sm font-semibold text-slate-800 mb-1">Tambah / ubah aturan</h3>
                <p class="text-xs text-slate-500 mb-4">Kosongkan kolom yang tidak perlu. Kalau kuncinya sudah ada,
                    unitnya diperbarui. Contoh: RO <span class="font-mono">EBA.962</span> + SubKomponen <span
                        class="font-mono">962.0P</span>. Kegiatan otomatis dikosongkan bila Komponen/SubKomponen diisi.
                    Induk dikosongkan = otomatis dari akhiran nama unit (D1-D4), atau unit itu sendiri.</p>
                <form method="POST" action="{{ route('anggaran.pemetaan.store') }}"
                    class="grid grid-cols-2 lg:grid-cols-7 gap-3 items-end">
                    @csrf
                    <div><label class="block text-xs text-slate-500 mb-1 font-medium">Kegiatan</label><input
                            name="keg_kode" placeholder="CL.7458" class="{{ $inp }}"></div>
                    <div><label class="block text-xs text-slate-500 mb-1 font-medium">RO <span
                                class="text-red-500">*</span></label><input name="ro_kode" required
                            placeholder="ABR.001" class="{{ $inp }}"></div>
                    <div><label class="block text-xs text-slate-500 mb-1 font-medium">Komponen</label><input
                            name="komp_kode" placeholder="001" class="{{ $inp }}"></div>
                    <div><label class="block text-xs text-slate-500 mb-1 font-medium">SubKomponen</label><input
                            name="sub_kode" placeholder="962.0A" class="{{ $inp }}"></div>
                    <div><label class="block text-xs text-slate-500 mb-1 font-medium">Unit kerja <span
                                class="text-red-500">*</span></label><input name="unit" list="dl-unit" required
                            placeholder="Asdep 1 D1 / BIRO UHM" class="{{ $inp }}"></div>
                    <div><label class="block text-xs text-slate-500 mb-1 font-medium">Induk</label><input name="induk"
                            list="dl-induk" placeholder="D1 (otomatis)" class="{{ $inp }}"></div>
                    <div><button class="{{ $btn }} w-full">Simpan</button></div>
                </form>
            </div>
        </div>

        {{-- ===== Ringkasan ===== --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([
                ['Total aturan', $totalRules, 'Seluruh aturan pemetaan', 'text-blue-600 bg-blue-50 ring-blue-100'],
                ['Unit kerja', $totalUnits, 'Asdep, Sesdep, Deputi, dan Biro', 'text-emerald-600 bg-emerald-50 ring-emerald-100'],
                ['RO terpetakan', $totalRo, 'Kode RO berbeda', 'text-indigo-600 bg-indigo-50 ring-indigo-100'],
                ['Aturan rinci', $totalDetail, 'Per Komponen / SubKomponen', 'text-violet-600 bg-violet-50 ring-violet-100'],
            ] as [$lbl, $num, $sub, $tone])
                <div class="card p-4 sm:p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">{{ $lbl }}</span>
                        <span class="h-2 w-2 rounded-full ring-4 {{ $tone }}"></span>
                    </div>
                    <div class="mt-2 text-3xl font-semibold tracking-tight text-slate-800 tabular-nums">
                        {{ number_format($num, 0, ',', '.') }}</div>
                    <div class="mt-0.5 text-xs text-slate-400">{{ $sub }}</div>
                </div>
            @endforeach
        </div>

        {{-- ===== Daftar aturan: semua, per unit ===== --}}
        <div class="{{ $card }} overflow-hidden">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 px-5 sm:px-6 py-4">
                <div class="flex items-start gap-3">
                    <span class="{{ $iconCircle }}">{!! $docIcon !!}</span>
                    <div>
                        <h3 class="text-base font-semibold text-slate-800">Aturan pemetaan
                            (<span id="pm-count">{{ $totalRules }}</span>)</h3>
                        <p class="text-sm text-slate-500">Kode anggaran (Program, RO, Komponen, SubKomponen) di setiap unit kerja.</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input id="pm-search" type="search" placeholder="Cari kode / unit" autocomplete="off"
                            class="{{ $inp }} !w-64 !pl-9">
                    </div>
                    <button type="button" id="pm-open" class="{{ $btnLite }} !px-3.5 !py-2.5 !text-sm">Buka semua</button>
                    <button type="button" id="pm-close" class="{{ $btnLite }} !px-3.5 !py-2.5 !text-sm">Tutup
                        semua</button>
                </div>
            </div>

            @if ($isPaged)
                <div class="mx-5 sm:mx-6 mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-800">
                    Daftar ini masih terpaginasi, jadi hanya sebagian aturan yang tampil. Ganti
                    <span class="font-mono">paginate()</span> menjadi <span class="font-mono">get()</span> di controller
                    agar semua aturan muncul.
                </div>
            @endif

            {{-- Filter cepat per kelompok --}}
            @if ($items->isNotEmpty())
                <div class="px-5 sm:px-6 pb-4 flex flex-wrap items-center gap-2" id="pm-chips">
                    <button type="button" data-sec="all"
                        class="pm-chip chip-on inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium bg-slate-100 text-slate-600">
                        Semua <span class="tabular-nums opacity-70">{{ $totalRules }}</span>
                    </button>
                    @foreach ($sections as $sec)
                        <button type="button" data-sec="{{ $sec['id'] }}"
                            class="pm-chip inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium bg-slate-100 text-slate-600 hover:bg-slate-200">
                            {{ $sec['title'] }}
                            <span class="tabular-nums opacity-70">{{ collect($sec['units'])->sum(fn($g) => $g->count()) }}</span>
                        </button>
                    @endforeach
                </div>
            @endif

            <div class="border-t border-slate-100 bg-slate-50/60 px-4 sm:px-6 py-5 space-y-8">

                @if ($items->isEmpty())
                    <div class="py-10 text-center text-sm text-slate-400">Belum ada aturan pemetaan.</div>
                @endif

                @foreach ($sections as $sec)
                    <section class="pm-section" data-sec="{{ $sec['id'] }}">

                        {{-- Judul blok: Deputi / Biro --}}
                        <div class="mb-3 flex items-center gap-3">
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-sm font-semibold text-white shadow-sm">{{ $sec['badge'] }}</span>
                            <div>
                                <h4 class="text-sm font-semibold text-slate-800">{{ $sec['title'] }}</h4>
                                <p class="text-xs text-slate-500">{{ $sec['hint'] }}</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @foreach ($sec['units'] as $unitName => $grp)
                                @php
                                    $cnt = $grp->countBy($levelOf);
                                    $indukLabel = $grp->first()->induk;
                                    $rows = $grp->sortBy(fn($x) => $x->keg_kode . '|' . $x->ro_kode . '|' . $x->komp_kode . '|' . $x->sub_kode);
                                @endphp

                                <details open class="unit-card card overflow-hidden"
                                    id="unit-{{ \Illuminate\Support\Str::slug($unitName) }}">
                                    <summary
                                        class="flex flex-wrap items-center gap-3 px-4 py-3 bg-white hover:bg-slate-50">
                                        <svg class="chev h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                        <span class="text-sm font-semibold text-slate-800">
                                            {{ preg_match('/^D([1-4])$/', $unitName, $dm) ? 'Deputi ' . $dm[1] . ' (langsung)' : $unitName }}
                                        </span>
                                        @if (filled($indukLabel) && $indukLabel !== $unitName)
                                            <span
                                                class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">Induk
                                                {{ $indukLabel }}</span>
                                        @endif

                                        <span class="ml-auto flex flex-wrap items-center gap-1.5">
                                            @foreach ($levelMeta as $lv => [$lvName, $lvCls])
                                                @if (($cnt[$lv] ?? 0) > 0)
                                                    <span
                                                        class="hidden sm:inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 {{ $lvCls }}">{{ $lvName }}
                                                        {{ $cnt[$lv] }}</span>
                                                @endif
                                            @endforeach
                                            <span
                                                class="pm-unit-count rounded-full bg-blue-600 px-2.5 py-0.5 text-[11px] font-semibold text-white tabular-nums">{{ $hasCov ? ($cov[$unitName] ?? collect())->unique(fn($c) => $c->keg_kode . '|' . $c->ro_kode . '|' . $c->komp_kode . '|' . $c->sub_kode)->count() : $grp->count() }}</span>
                                        <span class="text-[11px] text-slate-400">{{ $hasCov ? 'kode' : 'aturan' }}</span>
                                        </span>
                                    </summary>

                                    @php
                                        $covRows = ($cov[$unitName] ?? collect())
                                            ->unique(fn($c) => $c->keg_kode . '|' . $c->ro_kode . '|' . $c->komp_kode . '|' . $c->sub_kode)
                                            ->sortBy(fn($c) => $c->keg_kode . '|' . $c->ro_kode . '|' . ($c->sub_kode ?: $c->komp_kode))
                                            ->values();
                                        $pk = $pr = $pc = null;
                                    @endphp

                                    {{-- Kode anggaran di unit ini: Program, RO, Komponen, SubKomponen --}}
                                    <div class="overflow-x-auto border-t border-slate-100">
                                        <table class="w-full text-sm">
                                            <thead class="text-[11px] uppercase tracking-wide text-slate-500 bg-slate-50">
                                                <tr>
                                                    <th class="text-left px-4 py-2 font-semibold w-1/4">Program</th>
                                                    <th class="text-left px-3 py-2 font-semibold w-1/4">RO</th>
                                                    <th class="text-left px-3 py-2 font-semibold w-1/4">Komponen</th>
                                                    <th class="text-left px-3 py-2 font-semibold w-1/4">SubKomponen</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($covRows as $c)
                                                    @php
                                                        $kk = (string) $c->keg_kode;
                                                        $rr = (string) $c->ro_kode;
                                                        $cc = (string) $c->komp_kode;
                                                        $sameK = $kk === $pk;
                                                        $sameR = $sameK && $rr === $pr;
                                                        $sameC = $sameR && $cc === $pc;
                                                        $pk = $kk; $pr = $rr; $pc = $cc;
                                                        $covHay = strtolower(implode(' ', [$kk, $rr, $cc, $c->sub_kode, $unitName]));
                                                    @endphp
                                                    <tr class="pm-cov border-t border-slate-100 rule-row" data-text="{{ $covHay }}">
                                                        <td class="px-4 py-1.5 font-mono text-xs {{ $sameK ? 'text-slate-300' : 'text-slate-700' }}">{{ $kk !== '' ? $kk : '–' }}</td>
                                                        <td class="px-3 py-1.5 font-mono text-xs {{ $sameR ? 'text-slate-300' : 'font-medium text-slate-800' }}">{{ $rr }}</td>
                                                        <td class="px-3 py-1.5 font-mono text-xs {{ $sameC ? 'text-slate-300' : 'text-slate-700' }}">{{ $cc !== '' ? $cc : '–' }}</td>
                                                        <td class="px-3 py-1.5 font-mono text-xs text-slate-700">{{ $c->sub_kode ?: '–' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="px-4 py-3 text-xs text-slate-400">
                                                            {{ $hasCov ? 'Belum ada kode anggaran di upload aktif untuk unit ini.' : 'Daftar kode belum dikirim controller (variabel $coverage).' }}
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- Aturan penyusunnya (untuk ubah / hapus) --}}
                                    <details class="border-t border-slate-100 bg-slate-50/60">
                                        <summary class="flex cursor-pointer items-center gap-2 px-4 py-2 text-xs font-medium text-slate-500 hover:text-slate-700">
                                            <svg class="chev h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                            </svg>
                                            Aturan pemetaan unit ini ({{ $grp->count() }}) · ubah / hapus
                                        </summary>
                                        <div class="overflow-x-auto border-t border-slate-100 bg-white">
                                            <table class="w-full text-sm">
                                                <thead class="text-[11px] uppercase tracking-wide text-slate-500 bg-slate-50">
                                                    <tr>
                                                        <th class="text-left px-4 py-2 font-semibold">Kegiatan</th>
                                                        <th class="text-left px-3 py-2 font-semibold">RO</th>
                                                        <th class="text-left px-3 py-2 font-semibold">Komponen</th>
                                                        <th class="text-left px-3 py-2 font-semibold">SubKomponen</th>
                                                        <th class="text-left px-3 py-2 font-semibold">Jenis aturan</th>
                                                        <th class="px-4 py-2 text-right font-semibold">Aksi</th>
                                                    </tr>
                                                </thead>

                                                @foreach ($rows as $r)
                                                    @php
                                                        $lv = $levelOf($r);
                                                        [$lvName, $lvCls] = $levelMeta[$lv];
                                                        $hay = strtolower(implode(' ', [$r->keg_kode, $r->ro_kode, $r->komp_kode, $r->sub_kode, $r->unit, $r->induk]));
                                                    @endphp
                                                    <tbody class="pm-rule border-t border-slate-100" data-text="{{ $hay }}">
                                                        <tr class="rule-row">
                                                            <td class="px-4 py-2 font-mono text-xs text-slate-600">
                                                                @if (filled($r->keg_kode)){{ $r->keg_kode }}@else<span
                                                                    class="font-sans text-slate-400">Semua kegiatan</span>@endif
                                                            </td>
                                                            <td class="px-3 py-2">
                                                                <span
                                                                    class="rounded-md bg-slate-100 px-1.5 py-0.5 font-mono text-xs font-medium text-slate-700">{{ $r->ro_kode }}</span>
                                                            </td>
                                                            <td class="px-3 py-2 font-mono text-xs text-slate-600">
                                                                {{ $r->komp_kode ?: '–' }}</td>
                                                            <td class="px-3 py-2 font-mono text-xs text-slate-600">
                                                                {{ $r->sub_kode ?: '–' }}</td>
                                                            <td class="px-3 py-2">
                                                                <span
                                                                    class="rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 {{ $lvCls }}">{{ $lvName }}</span>
                                                            </td>
                                                            <td class="px-4 py-2 text-right whitespace-nowrap">
                                                                <button type="button"
                                                                    class="pm-edit text-xs font-medium text-blue-600 hover:underline">Ubah</button>
                                                                <form method="POST"
                                                                    action="{{ route('anggaran.pemetaan.destroy', $r) }}"
                                                                    class="inline"
                                                                    onsubmit="return confirm('Hapus aturan {{ $r->ro_kode }} {{ $r->sub_kode ?: $r->komp_kode }}?')">
                                                                    @csrf @method('DELETE')
                                                                    <button
                                                                        class="ml-3 text-xs font-medium text-red-600 hover:underline">Hapus</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                        <tr class="pm-edit-row hidden bg-blue-50/50">
                                                            <td colspan="6" class="px-4 py-3">
                                                                <form method="POST"
                                                                    action="{{ route('anggaran.pemetaan.update', $r) }}"
                                                                    class="flex flex-wrap items-end gap-3">
                                                                    @csrf @method('PUT')
                                                                    <div>
                                                                        <label
                                                                            class="block text-[11px] font-medium text-slate-500 mb-1">Unit
                                                                            kerja</label>
                                                                        <input name="unit" value="{{ $r->unit }}" list="dl-unit"
                                                                            required class="{{ $inp }} !w-60">
                                                                    </div>
                                                                    <div>
                                                                        <label
                                                                            class="block text-[11px] font-medium text-slate-500 mb-1">Induk</label>
                                                                        <input name="induk" value="{{ $r->induk }}"
                                                                            list="dl-induk" placeholder="–"
                                                                            class="{{ $inp }} !w-32">
                                                                    </div>
                                                                    <button class="{{ $btn }} !py-2.5">Simpan</button>
                                                                    <button type="button"
                                                                        class="pm-edit {{ $btnLite }} !py-2.5">Batal</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                @endforeach
                                            </table>
                                        </div>
                                    </details>
                                </details>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <div id="pm-empty" class="hidden py-10 text-center text-sm text-slate-400">Tidak ada aturan yang cocok.
                </div>
            </div>

            @if ($isPaged)
                <div class="px-5 py-3 border-t border-slate-100">{{ $rules->links() }}</div>
            @endif
        </div>
    </div>

    <script>
        (function () {
            var secFilter = 'all';
            var search = document.getElementById('pm-search');
            var chips = document.querySelectorAll('.pm-chip');
            var sections = document.querySelectorAll('.pm-section');
            var cards = document.querySelectorAll('details.unit-card');
            var counter = document.getElementById('pm-count');
            var empty = document.getElementById('pm-empty');

            function apply() {
                var q = (search.value || '').trim().toLowerCase();
                var total = 0;

                sections.forEach(function (sec) {
                    var secOk = secFilter === 'all' || sec.dataset.sec === secFilter;
                    var secVisible = 0;

                    sec.querySelectorAll('details.unit-card').forEach(function (card) {
                        var shownRules = 0, shownCov = 0;
                        card.querySelectorAll('tbody.pm-rule').forEach(function (tb) {
                            var ok = secOk && (q === '' || tb.dataset.text.indexOf(q) !== -1);
                            tb.style.display = ok ? '' : 'none';
                            if (ok) shownRules++;
                        });
                        var covRows = card.querySelectorAll('tr.pm-cov');
                        covRows.forEach(function (tr) {
                            var ok = secOk && (q === '' || tr.dataset.text.indexOf(q) !== -1);
                            tr.style.display = ok ? '' : 'none';
                            if (ok) shownCov++;
                        });
                        var cnt = card.querySelector('.pm-unit-count');
                        if (cnt) cnt.textContent = covRows.length ? shownCov : shownRules;
                        var vis = shownRules + shownCov;
                        card.style.display = (vis > 0 || (q === '' && secOk)) ? '' : 'none';
                        if (q !== '' && vis > 0) card.open = true;
                        secVisible += shownRules;
                    });

                    sec.style.display = secVisible > 0 ? '' : 'none';
                    total += secVisible;
                });

                if (counter) counter.textContent = total;
                if (empty) empty.classList.toggle('hidden', total > 0 || sections.length === 0);
            }

            chips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    secFilter = chip.dataset.sec;
                    chips.forEach(function (c) { c.classList.remove('chip-on'); });
                    chip.classList.add('chip-on');
                    apply();
                });
            });

            if (search) search.addEventListener('input', apply);

            var openAll = document.getElementById('pm-open');
            var closeAll = document.getElementById('pm-close');
            if (openAll) openAll.addEventListener('click', function () { cards.forEach(function (c) { c.open = true; }); });
            if (closeAll) closeAll.addEventListener('click', function () { cards.forEach(function (c) { c.open = false; }); });

            // Tombol Ubah / Batal: tampilkan atau sembunyikan baris edit milik aturan itu
            document.querySelectorAll('tbody.pm-rule').forEach(function (tb) {
                var editRow = tb.querySelector('.pm-edit-row');
                tb.querySelectorAll('.pm-edit').forEach(function (btn) {
                    btn.addEventListener('click', function () { editRow.classList.toggle('hidden'); });
                });
            });
        })();
    </script>
</x-app-layout>