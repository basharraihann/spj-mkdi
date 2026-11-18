<x-app-layout title="Monitoring Memo">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitoring Memo & Anggaran') }}
        </h2>
    </x-slot>

    @php
        $rp = fn($n) => ($n < 0 ? '-Rp ' : 'Rp ') . number_format(abs((int) $n), 0, ',', '.');

        // Kelas dasar header kolom tabel
        $th = 'px-2 py-2.5 text-[10px] font-semibold uppercase tracking-wide text-gray-500 bg-gray-50 border-b border-gray-200';

        $statusLabel = ['outstanding' => 'Outstanding', 'sakti' => 'SAKTI', 'sp2d' => 'SP2D', 'batal' => 'Batal'];
        $statusBadge = fn($s) => match ($s) {
            'sakti' => 'bg-blue-50 text-blue-700 border-blue-200',
            'sp2d' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'batal' => 'bg-gray-100 text-gray-500 border-gray-200',
            default => 'bg-amber-50 text-amber-700 border-amber-200',
        };
        $statusDot = fn($s) => match ($s) {
            'sakti' => 'bg-blue-500',
            'sp2d' => 'bg-emerald-500',
            'batal' => 'bg-gray-400',
            default => 'bg-amber-500',
        };

        $bisaKelola = auth()->user()?->can('kelola-anggaran');

        // Tahap alur memo (dari tabel memo_progres)
        $tahapLabel = [
            'draft' => 'Draft, belum dikirim',
            'dikirim' => 'Menunggu K2',
            'lolos_spk' => 'Lolos K2, menunggu input SAKTI',
            'sakti' => 'Menunggu K3',
            'otp_ppk' => 'OTP PPK',
            'ppspm' => 'PPSPM, menunggu pencairan',
            'dikembalikan' => 'Dikembalikan ke unit',
        ];
        $tahapDot = fn($t) => match ($t) {
            'dikirim' => 'bg-blue-500',
            'lolos_spk' => 'bg-indigo-500',
            'sakti' => 'bg-sky-500',
            'otp_ppk' => 'bg-violet-500',
            'ppspm' => 'bg-emerald-500',
            'dikembalikan' => 'bg-red-500',
            default => 'bg-gray-400',
        };

        $antreanSp2d = $antreanSp2d ?? collect();
        $unitRingkasan = $unitRingkasan ?? collect();
        $rankStatus = ['outstanding' => 0, 'sakti' => 1, 'sp2d' => 2];

        // Riwayat lengkap per memo datang dari controller ($logPerMemo), jangan dibuat ulang di sini.
        $logPerMemo = $logPerMemo ?? collect();

        // ---- Tabel anggaran per MAK: hanya MAK yang dipakai memo, disusun berjenjang dari kode MAK ----
        // Kode MAK dipecah per titik. 7 bagian = Program.Kegiatan.KRO.RO.Komponen.Sub.Akun,
        // 6 bagian = tanpa awalan program. Selain itu ditampilkan sebagai satu baris apa adanya.
        $mkNama = ['prog' => 'Program', 'keg' => 'Kegiatan', 'ro' => 'Sub Output', 'komp' => 'Komponen', 'sub' => 'Sub Komponen', 'akun' => 'Akun'];
        // Nama program, sama dengan halaman Anggaran
        $mkNamaProgram = [
            'CL' => 'Program Koordinasi Pelaksanaan Kebijakan',
            'WA' => 'Program Dukungan Manajemen',
        ];
        // Kegiatan -> Program. Kode MAK 6 bagian tidak memuat awalan program, jadi dicari dari sini.
        // Controller boleh mengirim $kegProgram (mis. ['7458' => 'CL']); isi di bawah jadi cadangan.
        $mkKegProgram = ($kegProgram ?? []) + ['7458' => 'CL', '7459' => 'CL'];
        $mkUraianLevel = $uraianLevel ?? [];
        $mkFlat = [];
        $mkAnak = ['' => []];

        foreach (collect($maks ?? [])->filter(fn($x) => !empty($x['dipakai']) && (int) ($x['outstanding'] ?? 0) > 0) as $x) {
            $kodeMak = trim((string) $x['mak']);
            $p = explode('.', $kodeMak);
            $progMak = trim((string) ($x['prog_kode'] ?? $x['program'] ?? '')) ?: ($mkKegProgram[$p[0]] ?? null);
            // Kunci jalur untuk uraian baris induk (sama dengan petaAnggaran() di controller)
            $kunciUr = [];
            if (count($p) >= 6) {
                $o7 = count($p) === 7 ? 1 : 0;
                $kk = $p[$o7];
                $kr = $kk . '|' . $p[$o7 + 1] . '.' . $p[$o7 + 2];
                $kc = $p[$o7 + 3];
                $kc = (ctype_digit($kc) && strlen($kc) < 3) ? str_pad($kc, 3, '0', STR_PAD_LEFT) : $kc;
                $kc = $kr . '|' . $kc;
                $kunciUr = ['keg' => $kk, 'ro' => $kr, 'komp' => $kc, 'sub' => $kc . '|' . $p[$o7 + 4]];
            }
            $segs = match (count($p)) {
                7 => [['prog', $p[0]], ['keg', $p[0] . '.' . $p[1]], ['ro', $p[2] . '.' . $p[3]], ['komp', $p[4]], ['sub', $p[5]], ['akun', $p[6]]],
                6 => $progMak
                    ? [['prog', $progMak], ['keg', $progMak . '.' . $p[0]], ['ro', $p[1] . '.' . $p[2]], ['komp', $p[3]], ['sub', $p[4]], ['akun', $p[5]]]
                    : [['keg', $p[0]], ['ro', $p[1] . '.' . $p[2]], ['komp', $p[3]], ['sub', $p[4]], ['akun', $p[5]]],
                default => [['akun', $kodeMak]],
            };

            // Unit = pemilik anggaran (terkunci per MAK), bukan unit pemakai memo
            $unitPemilik = $x['unit_pemilik'] ?? $x['unit_anggaran'] ?? $x['unit'] ?? null;
            $unitMak = ($unitPemilik && $unitPemilik !== '__none') ? [(string) $unitPemilik] : [];

            $induk = '';
            $jalur = '';
            foreach ($segs as $d => [$lv, $kode]) {
                $jalur .= '|' . $kode;
                $id = 'mk-' . substr(md5($jalur), 0, 10);
                if (!isset($mkFlat[$id])) {
                    $mkFlat[$id] = [
                        'id' => $id,
                        'parent' => $induk,
                        'depth' => $d,
                        'lv' => $lv,
                        'kode' => $kode,
                        'uraian' => $lv === 'prog' ? ($mkNamaProgram[$kode] ?? 'Program ' . $kode) : ($mkUraianLevel[$lv][$kunciUr[$lv] ?? ''] ?? null),
                        'kondisi' => null,
                        'pagu' => 0,
                        'real' => 0,
                        'lalu' => null,
                        'ini' => null,
                        'out' => 0,
                        'units' => [],
                        'kids' => false,
                    ];
                    $mkAnak[$induk][] = $id;
                    $mkAnak[$id] = [];
                    if ($induk !== '') {
                        $mkFlat[$induk]['kids'] = true;
                    }
                }
                $mkFlat[$id]['pagu'] += (int) ($x['pagu'] ?? 0);
                $mkFlat[$id]['real'] += (int) ($x['realisasi'] ?? 0);
                if (($x['real_lalu'] ?? null) !== null) {
                    $mkFlat[$id]['lalu'] = ($mkFlat[$id]['lalu'] ?? 0) + (int) $x['real_lalu'];
                }
                if (($x['real_ini'] ?? null) !== null) {
                    $mkFlat[$id]['ini'] = ($mkFlat[$id]['ini'] ?? 0) + (int) $x['real_ini'];
                }
                $mkFlat[$id]['out'] += (int) ($x['outstanding'] ?? 0);
                foreach ($unitMak as $u) {
                    $mkFlat[$id]['units'][$u] = true;
                }
                $induk = $id;
            }
            // $induk sekarang = baris akun (daun)
            $mkFlat[$induk]['uraian'] = $x['uraian'] ?? null;
            $mkFlat[$induk]['kondisi'] = $x['kondisi'] ?? null;

            // Item anggaran di bawah akun (level terdalam). Tidak ikut dijumlah ke induk: angka induk
            // sudah dari akun, jadi menjumlah item lagi akan dobel.
            foreach (($x['items'] ?? []) as $ix => $it) {
                $idItem = $induk . '-i' . $ix;
                $mkFlat[$idItem] = [
                    'id' => $idItem,
                    'parent' => $induk,
                    'depth' => count($segs),
                    'lv' => 'item',
                    'kode' => $it['kode'] !== '' ? $it['kode'] : '—',
                    'uraian' => $it['uraian'] ?? null,
                    'kondisi' => null,
                    'pagu' => (int) $it['pagu'],
                    'real' => (int) $it['real'],
                    'lalu' => $it['lalu'],
                    'ini' => $it['ini'],
                    'out' => null,
                    'units' => $mkFlat[$induk]['units'],
                    'kids' => false,
                ];
                $mkAnak[$induk][] = $idItem;
                $mkAnak[$idItem] = [];
                $mkFlat[$induk]['kids'] = true;
            }
        }

        foreach ($mkAnak as $k => $ids) {
            usort($ids, fn($a, $b) => strnatcmp($mkFlat[$a]['kode'], $mkFlat[$b]['kode']));
            $mkAnak[$k] = $ids;
        }

        $mkBaris = [];
        $mkJalan = function ($induk) use (&$mkJalan, &$mkBaris, $mkAnak, $mkFlat) {
            foreach ($mkAnak[$induk] as $id) {
                $mkBaris[] = $mkFlat[$id];
                $mkJalan($id);
            }
        };
        $mkJalan('');

        $mkJumlahAkun = collect($mkBaris)->where('lv', 'akun')->count();
        $mkAngka = fn($n) => number_format((int) $n, 0, ',', '.');

        // Opsi filter: hanya level dan unit yang benar-benar ada di data
        $mkUrutLv = ['prog', 'keg', 'ro', 'komp', 'sub', 'akun'];
        $mkLevelAda = collect($mkUrutLv)->filter(fn($l) => collect($mkBaris)->contains('lv', $l))->values();
        $mkUnitOpsi = collect($mkBaris)->flatMap(fn($r) => array_keys($r['units']))->unique()->sort()->values();

        // Gabungkan semua memo, lalu kelompokkan per unit untuk panel "Show"
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
        $memoPerUnit = $semuaMemo
            ->groupBy(fn($m) => $m['unit'] ?: '__none')
            ->map(fn($g) => $g->sort(fn($a, $b) => [$rankStatus[$a['_status']], -($a['umur'] ?? 0)] <=> [$rankStatus[$b['_status']], -($b['umur'] ?? 0)])->values());
    @endphp

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-[96rem] mx-auto space-y-5">

        @if ($errors->any())
            <div class="rounded-xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- ============ Anggaran per MAK (hanya MAK yang dipakai memo) ============ --}}
        <section aria-labelledby="judul-mak" class="bg-white shadow-sm rounded-2xl border border-gray-100">
            <div class="flex items-start justify-between gap-4 flex-wrap px-4 pt-4 sm:px-6 sm:pt-6 pb-4">
                <div class="min-w-0">
                    <h3 id="judul-mak" class="text-lg font-bold text-gray-800">Anggaran per MAK</h3>
                    <p class="mt-0.5 text-sm text-gray-500 max-w-3xl">
                        Hanya MAK yang masih punya outstanding. Unit = pemilik
                        anggaran.
                    </p>
                </div>
                <span
                    class="text-xs font-semibold text-gray-500 bg-gray-50 border border-gray-100 rounded-full px-3 py-1.5">
                    Data SAKTI:
                    {{ $tarikanTanggal ? $tarikanTanggal->translatedFormat('d M Y') : 'belum ada' }}
                </span>
            </div>

            @if (count($mkBaris) === 0)
                <div class="border-t border-gray-200 py-12 text-center text-sm text-slate-400">Belum ada memo yang memakai
                    MAK.</div>
            @else
                <div class="border-t border-gray-200 rounded-b-2xl" style="overflow:clip">
                    <div
                        class="flex flex-col xl:flex-row xl:items-center justify-between gap-3 px-4 sm:px-6 py-3 border-b border-slate-100">
                        <div class="text-xs text-slate-500">
                            Klik baris untuk menandai. Klik panah untuk membuka atau menutup cabang.
                            <span class="font-medium text-slate-700">{{ $mkJumlahAkun }} MAK masih outstanding.</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <button type="button" id="mk-expand"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm hover:bg-slate-50 transition">
                                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                </svg>
                                Buka semua
                            </button>
                            <button type="button" id="mk-collapse"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm hover:bg-slate-50 transition">
                                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5 5.25 5.25" />
                                </svg>
                                Tutup semua
                            </button>
                            <label class="inline-flex items-center gap-2 text-xs text-slate-500">
                                Level
                                <select id="mk-level"
                                    class="rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs font-medium text-slate-700 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    <option value="">Semua (sampai item)</option>
                                    <option value="prog">Program</option>
                                    <option value="keg">Kegiatan</option>
                                    <option value="ro">RO / Sub Output</option>
                                    <option value="komp">Komponen</option>
                                    <option value="sub">Sub Komponen</option>
                                    <option value="akun">Akun</option>
                                    <option value="item">Item</option>
                                </select>
                            </label>
                            <label class="inline-flex items-center gap-2 text-xs text-slate-500">
                                Unit
                                <select id="mk-unit"
                                    class="rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs font-medium text-slate-700 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    <option value="">Semua unit</option>
                                    @foreach ($mkUnitOpsi as $un)
                                        <option value="{{ $un }}">{{ $un }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </div>

                    <div class="mk-scroll">
                        <table class="w-full table-fixed text-[11px] leading-tight">
                            <colgroup>
                                <col style="width:13%">
                                <col style="width:20%">
                                <col style="width:9%">
                                <col style="width:10%">
                                <col style="width:9%">
                                <col style="width:8%">
                                <col style="width:9%">
                                <col style="width:5%">
                                <col style="width:9%">
                                <col style="width:8%">
                            </colgroup>
                            <thead style="background:#2563eb" class="text-[11px] text-white sticky top-0 z-10">
                                <tr>
                                    <th class="text-left px-2 py-2 font-semibold">Kode</th>
                                    <th class="text-left px-2 py-2 font-semibold">Uraian</th>
                                    <th class="text-left px-2 py-2 font-semibold">Unit</th>
                                    <th class="text-right px-2 py-2 font-semibold">Pagu Revisi</th>
                                    <th class="text-right px-2 py-2 font-semibold">Real. Lalu</th>
                                    <th class="text-right px-2 py-2 font-semibold">Real. Ini</th>
                                    <th class="text-right px-2 py-2 font-semibold">Real. s.d.</th>
                                    <th class="text-right px-2 py-2 font-semibold">%</th>
                                    <th class="text-right px-2 py-2 font-semibold">Sisa</th>
                                    <th class="text-right px-2 py-2 font-semibold">Outstanding</th>
                                </tr>
                            </thead>
                            <tbody id="mk-tb">
                                @foreach ($mkBaris as $r)
                                    @php
                                        $daun = $r['lv'] === 'akun';
                                        $tebal = $r['depth'] <= 1 ? 'font-semibold' : '';
                                        $bgBaris = $r['depth'] === 0 ? 'bg-slate-100' : ($r['depth'] === 1 ? 'bg-slate-50' : '');
                                        $persenMak = $r['pagu'] > 0 ? $r['real'] / $r['pagu'] * 100 : 0;
                                        $sisaMak = $r['pagu'] - $r['real'];
                                        $unitBaris = array_keys($r['units']);
                                        $numCls = 'px-2 py-1.5 text-right whitespace-nowrap tabular-nums align-middle';
                                    @endphp
                                    <tr class="mk-row cursor-pointer {{ $bgBaris }}" data-id="{{ $r['id'] }}"
                                        data-parent="{{ $r['parent'] }}" data-kids="{{ $r['kids'] ? 1 : 0 }}"
                                        data-lv="{{ $r['lv'] }}" data-units="{{ implode('|', $unitBaris) }}">
                                        <td class="py-1.5 pr-1 align-middle">
                                            <div class="flex flex-wrap items-center"
                                                style="padding-left:{{ 4 + min($r['depth'], 7) * 10 }}px">
                                                @if ($r['kids'])
                                                    <button type="button" data-act="toggle" aria-label="Buka/tutup"
                                                        aria-expanded="true"
                                                        class="mk-toggle shrink-0 w-4 h-4 mr-1 rounded flex items-center justify-center text-slate-500 hover:bg-slate-200 transition">
                                                        <svg class="mk-chev is-open w-3 h-3" xmlns="http://www.w3.org/2000/svg"
                                                            fill="none" viewBox="0 0 24 24" stroke-width="2.6"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                                        </svg>
                                                    </button>
                                                @else
                                                    <span class="shrink-0 w-4 h-4 mr-1"></span>
                                                @endif
                                                <span class="tabular-nums text-slate-800 {{ $tebal }}">{{ $r['kode'] }}</span>
                                                @if ($daun && ($r['kondisi'] ?? null) === 'minus')
                                                    <span
                                                        class="ml-1 rounded px-1.5 py-0.5 text-[10px] font-medium bg-red-100 text-red-700">Minus</span>
                                                @elseif ($daun && ($r['kondisi'] ?? null) === 'perhatian')
                                                    <span
                                                        class="ml-1 rounded px-1.5 py-0.5 text-[10px] font-medium bg-amber-100 text-amber-700">Hampir
                                                        habis</span>
                                                @elseif ($daun && ($r['kondisi'] ?? null) === 'tanpa_master')
                                                    <span
                                                        class="ml-1 rounded px-1.5 py-0.5 text-[10px] font-medium bg-slate-200 text-slate-600">Tanpa
                                                        master</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-1.5 px-2 align-middle">
                                            @if ($daun)
                                                @if (($r['kondisi'] ?? null) === 'tanpa_master')
                                                    <span class="leading-snug break-words text-amber-700">MAK belum ada di master
                                                        SAKTI</span>
                                                @else
                                                    <span
                                                        class="leading-snug break-words text-slate-800">{{ $r['uraian'] ?: '-' }}</span>
                                                @endif
                                            @else
                                                <span
                                                    class="leading-snug break-words {{ $r['lv'] === 'item' ? 'text-slate-800' : 'text-slate-500' }} {{ $tebal }}">{{ $r['uraian'] ?: ($r['lv'] === 'item' ? '-' : ($mkNama[$r['lv']] ?? '')) }}</span>
                                            @endif
                                        </td>
                                        <td class="py-1.5 px-2 align-middle break-words">
                                            @if (count($unitBaris) === 1)
                                                <span class="text-blue-600">{{ $unitBaris[0] }}</span>
                                            @elseif (count($unitBaris) > 1)
                                                <span class="text-slate-400"
                                                    title="{{ implode(', ', $unitBaris) }}">{{ count($unitBaris) }} unit</span>
                                            @else
                                                <span class="text-slate-300">–</span>
                                            @endif
                                        </td>
                                        <td class="{{ $numCls }} {{ $tebal }}">{{ $mkAngka($r['pagu']) }}</td>
                                        <td class="{{ $numCls }} {{ $tebal }}">
                                            {!! $r['lalu'] === null ? '<span class="text-slate-300">–</span>' : $mkAngka($r['lalu']) !!}
                                        </td>
                                        <td class="{{ $numCls }} {{ $tebal }}">
                                            {!! $r['ini'] === null ? '<span class="text-slate-300">–</span>' : $mkAngka($r['ini']) !!}
                                        </td>
                                        <td class="{{ $numCls }} {{ $tebal }}">{{ $mkAngka($r['real']) }}</td>
                                        <td class="{{ $numCls }}">{{ number_format($persenMak, 2, ',', '.') }}%</td>
                                        <td class="{{ $numCls }} {{ $tebal }} {{ $sisaMak < 0 ? 'text-red-600' : '' }}">
                                            {{ $mkAngka($sisaMak) }}
                                        </td>
                                        <td
                                            class="{{ $numCls }} {{ $tebal }} {{ ($r['out'] ?? 0) > 0 ? 'text-amber-700' : '' }}">
                                            {!! $r['out'] === null ? '<span class="text-slate-300">–</span>' : $mkAngka($r['out']) !!}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </section>

        {{-- ============ Progres per unit ============ --}}
        @if ($unitRingkasan->isNotEmpty())
            <section aria-labelledby="judul-unit" class="bg-white shadow-sm rounded-2xl border border-gray-100">
                <div class="px-4 pt-4 sm:px-6 sm:pt-6 pb-4">
                    <h3 id="judul-unit" class="text-lg font-bold text-gray-800">Progres per unit</h3>
                    <p class="mt-0.5 text-sm text-gray-500 max-w-3xl">
                        Klik Show untuk daftar memo unit. SP2D bulan ini saja. Kolom Perlu tindakan:
                        <span class="font-semibold text-red-600">merah</span> = pengajuan menunggu keputusan,
                        <span class="font-semibold text-blue-600">biru</span> = memo perlu ditindaklanjuti.
                    </p>
                    {{-- Legenda tahap memo --}}
                    <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-gray-500">
                        <span class="inline-flex items-center gap-1.5"><span
                                class="h-2 w-2 rounded-full bg-amber-500"></span><b
                                class="font-semibold text-gray-700">Outstanding</b> belum diinput di SAKTI</span>
                        <span class="inline-flex items-center gap-1.5"><span
                                class="h-2 w-2 rounded-full bg-blue-500"></span><b
                                class="font-semibold text-gray-700">SAKTI</b> sudah diinput, uang belum cair</span>
                        <span class="inline-flex items-center gap-1.5"><span
                                class="h-2 w-2 rounded-full bg-emerald-500"></span><b
                                class="font-semibold text-gray-700">SP2D</b> uang sudah cair</span>
                    </div>
                </div>

                <div class="border-t border-gray-200 rounded-b-2xl overflow-hidden">
                    {{-- Header kolom --}}
                    <div
                        class="pu-grid pu-head px-4 sm:px-6 py-2.5 bg-gray-50 border-b border-gray-100 text-[10px] font-semibold uppercase tracking-wide text-gray-500">
                        <span>Unit</span>
                        <span>Memo</span>
                        <span>Status memo</span>
                        <span>Perlu tindakan</span>
                        <span class="text-right">Nominal Outstanding</span>
                        <span class="text-right">Detail</span>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach ($unitRingkasan as $u)
                            @php
                                $daftar = $memoPerUnit->get($u['unit'], collect());
                                $jmlPengajuan = $daftar->filter(fn($m) => !empty($m['pengajuan']))->count();
                                $jmlTindakan = $daftar->filter(
                                    fn($m) => in_array($m['tahap'] ?? 'draft', ['dikirim', 'lolos_spk', 'sakti', 'otp_ppk', 'ppspm'], true)
                                        && ($m['_status'] ?? '') !== 'sp2d'
                                )->count();
                                $jo = (int) $u['outstanding_jumlah'];
                                $js = (int) $u['sakti_jumlah'];
                                $jp = (int) $u['sp2d_jumlah'];
                            @endphp
                            <div>
                                <div class="pu-grid px-4 sm:px-6 py-3.5 hover:bg-gray-50 transition">
                                    {{-- Unit --}}
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 break-words">{{ $u['label'] }}</p>
                                        @if ($u['lama'] > 0)
                                            <p class="mt-0.5 text-[11px] font-medium text-red-600">{{ $u['lama'] }} memo &gt; 30
                                                hari</p>
                                        @endif
                                    </div>

                                    {{-- Total memo --}}
                                    <div class="text-base font-semibold tabular-nums text-gray-900">
                                        {{ $u['jumlah'] }}
                                        <span class="ml-1 text-xs font-normal text-gray-400 md:hidden">memo</span>
                                    </div>

                                    {{-- Status: hanya yang ada isinya --}}
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-700 tabular-nums"
                                        role="img" aria-label="{{ $jo }} outstanding, {{ $js }} SAKTI, {{ $jp }} SP2D">
                                        @if ($jo > 0)
                                            <span class="inline-flex items-center gap-1.5"><span
                                                    class="h-2 w-2 rounded-full bg-amber-500"></span>{{ $jo }}
                                                Outstanding</span>
                                        @endif
                                        @if ($js > 0)
                                            <span class="inline-flex items-center gap-1.5"><span
                                                    class="h-2 w-2 rounded-full bg-blue-500"></span>{{ $js }}
                                                SAKTI</span>
                                        @endif
                                        @if ($jp > 0)
                                            <span class="inline-flex items-center gap-1.5"><span
                                                    class="h-2 w-2 rounded-full bg-emerald-500"></span>{{ $jp }}
                                                SP2D</span>
                                        @endif
                                        @if ($jo + $js + $jp === 0)
                                            <span class="text-gray-300">–</span>
                                        @endif
                                    </div>

                                    {{-- Perlu tindakan --}}
                                    <div class="text-xs font-semibold tabular-nums">
                                        @if ($jmlPengajuan > 0)
                                            <span class="text-red-600">{{ $jmlPengajuan }} pengajuan</span>
                                        @elseif ($jmlTindakan > 0)
                                            <span class="text-blue-600">{{ $jmlTindakan }} perlu tindakan</span>
                                        @else
                                            <span class="font-normal text-gray-300">–</span>
                                        @endif
                                    </div>

                                    {{-- Nominal outstanding --}}
                                    <div
                                        class="md:text-right text-xs font-semibold tabular-nums whitespace-nowrap {{ $u['outstanding_total'] > 0 ? 'text-gray-900' : 'text-gray-300' }}">
                                        {{ $rp($u['outstanding_total']) }}
                                    </div>

                                    {{-- Detail --}}
                                    <div class="md:text-right">
                                        <button type="button" aria-expanded="false" data-target="unit-detail-{{ $loop->index }}"
                                            class="js-unit-toggle rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-[11px] font-semibold text-gray-700 hover:bg-gray-100 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                                            <span class="js-label">Show</span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Detail memo unit (tersembunyi sampai Show diklik) --}}
                                <div id="unit-detail-{{ $loop->index }}"
                                    class="hidden border-t border-gray-200 bg-gray-50 px-4 py-4 sm:px-6">
                                    @if ($daftar->isEmpty())
                                        <p class="text-xs text-gray-500">Belum ada memo.</p>
                                    @else
                                        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                                            <table class="w-full table-fixed text-sm">
                                                <colgroup>
                                                    <col style="width:14%"> {{-- Memo --}}
                                                    <col style="width:11%"> {{-- Status --}}
                                                    <col style="width:12%"> {{-- Jenis --}}
                                                    <col style="width:16%"> {{-- Kegiatan --}}
                                                    <col style="width:12%"> {{-- MAK --}}
                                                    <col style="width:10%"> {{-- Nominal --}}
                                                    <col style="width:17%"> {{-- Progres --}}
                                                    <col style="width:8%"> {{-- Log --}}
                                                </colgroup>
                                                <thead>
                                                    <tr class="text-left">
                                                        <th class="{{ $th }} pl-4">Memo</th>
                                                        <th class="{{ $th }}">Status</th>
                                                        <th class="{{ $th }}">Jenis</th>
                                                        <th class="{{ $th }}">Kegiatan</th>
                                                        <th class="{{ $th }}">MAK</th>
                                                        <th class="{{ $th }} text-right">Nominal</th>
                                                        <th class="{{ $th }}">Progres</th>
                                                        <th class="{{ $th }} pr-4 text-right">Log</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @foreach ($daftar as $m)
                                                        @php
                                                            $st = $m['_status'];
                                                            $tahap = $m['tahap'] ?? 'draft';
                                                            $pengajuan = $m['pengajuan'] ?? null;
                                                            $logs = $logPerMemo->get($m['nomor_memo'], collect());
                                                            $logId = 'log-' . $loop->parent->index . '-' . $loop->index;
                                                        @endphp
                                                        <tr class="hover:bg-blue-50/30 transition">
                                                            <td class="pl-4 pr-2 py-2.5 align-top">
                                                                <p class="text-xs font-semibold text-gray-900 tabular-nums break-all">
                                                                    {{ $m['nomor_memo'] }}
                                                                </p>
                                                                @if (!empty($m['label']))
                                                                    <span
                                                                        class="mt-1 inline-flex px-2 py-0.5 rounded-full border text-[10px] font-semibold {{ $m['label'] === 'PNS' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-orange-50 text-orange-700 border-orange-200' }}">{{ $m['label'] }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="px-2 py-2.5 align-top">
                                                                <span
                                                                    class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md border text-[11px] font-semibold {{ $statusBadge($st) }}">
                                                                    <span class="h-1.5 w-1.5 rounded-full {{ $statusDot($st) }}"></span>
                                                                    {{ $statusLabel[$st] }}
                                                                </span>
                                                            </td>
                                                            <td class="px-2 py-2.5 align-top">
                                                                <span
                                                                    class="inline-flex whitespace-nowrap px-2 py-0.5 rounded-full border text-[10px] font-semibold {{ ($m['jenis'] ?? null) === 'perdin' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-gray-50 text-gray-700 border-gray-200' }}">{{ $m['jenis_label'] ?? '-' }}</span>
                                                            </td>

                                                            {{-- Kegiatan: sempit, bisa dibuka selengkapnya --}}
                                                            <td class="px-2 py-2.5 align-top">
                                                                <p class="js-kegiatan text-xs text-gray-800 truncate"
                                                                    title="{{ $m['uraian'] ?? '' }}">
                                                                    {{ $m['uraian'] ?? '-' }}
                                                                </p>
                                                                <button type="button"
                                                                    class="js-kegiatan-toggle mt-0.5 hidden text-[11px] font-semibold text-blue-700 hover:text-blue-900 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 rounded">
                                                                    Selengkapnya
                                                                </button>
                                                                @if (!empty($m['pic']))
                                                                    <p class="mt-0.5 text-[11px] text-gray-500">
                                                                        {{ $m['pic'] }}
                                                                    </p>
                                                                @endif
                                                            </td>

                                                            {{-- MAK: boleh turun baris, tidak melebarkan tabel --}}
                                                            <td class="px-2 py-2.5 align-top">
                                                                <p class="font-mono text-[11px] text-gray-700 break-all">
                                                                    {{ $m['mak'] ?: '-' }}
                                                                </p>
                                                            </td>
                                                            <td
                                                                class="px-2 py-2.5 align-top text-right text-xs font-semibold text-gray-800 tabular-nums">
                                                                {{ $rp($m['nominal']) }}
                                                            </td>

                                                            {{-- Progres + aksi --}}
                                                            <td class="px-2 py-2.5 align-top">
                                                                <p
                                                                    class="inline-flex items-start gap-1.5 text-[11px] font-medium leading-snug {{ $tahap === 'dikembalikan' ? 'text-red-600' : 'text-gray-600' }}">
                                                                    <span
                                                                        class="mt-[5px] h-1.5 w-1.5 shrink-0 rounded-full {{ $tahapDot($tahap) }}"></span>
                                                                    <span>{{ $tahapLabel[$tahap] ?? $tahap }}</span>
                                                                </p>

                                                                {{-- Pengajuan pengembalian yang menunggu keputusan --}}
                                                                @if ($pengajuan)
                                                                    <div
                                                                        class="mt-2 rounded-md border border-red-200 bg-red-50 px-2.5 py-1.5 text-[11px] leading-snug text-red-700 break-words">
                                                                        <span class="font-semibold">Pengajuan pengembalian menunggu
                                                                            keputusan:</span>
                                                                        {{ $pengajuan['alasan'] }}
                                                                    </div>
                                                                @endif

                                                                @if ($bisaKelola)
                                                                    {{-- Keputusan pengajuan --}}
                                                                    @if ($pengajuan)
                                                                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                                                            <x-aksi-button varian="hijau" :url="route('memo.pengajuan.putusan', $pengajuan['id'])" :fields="['setuju' => 1]"
                                                                                :konfirmasi="'Setujui pengajuan? Memo akan dikembalikan ke unit kerja' . (in_array($tahap, ['sakti', 'otp_ppk'], true) ? ' dan SAKTI dilepas' : '') . '.'">
                                                                                Setujui pengajuan
                                                                            </x-aksi-button>
                                                                            <x-aksi-button varian="merah" :url="route('memo.pengajuan.putusan', $pengajuan['id'])" :fields="['setuju' => 0]" prompt="catatan"
                                                                                :wajib="true" prompt-label="Catatan penolakan (wajib diisi):">
                                                                                Tolak pengajuan
                                                                            </x-aksi-button>
                                                                        </div>
                                                                    @endif

                                                                    {{-- Aksi tahap (tetap tampil, juga saat ada pengajuan) --}}
                                                                    <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                                                        @if ($tahap === 'dikirim' || $tahap === 'sakti')
                                                                            @php $gerbang = $tahap === 'dikirim' ? 'K2' : 'K3'; @endphp
                                                                            <x-aksi-button varian="hijau" :url="route('memo.verifikasi', $m['id'])" :fields="['gerbang' => $gerbang, 'lolos' => 1]"
                                                                                :konfirmasi="'Verifikasi ' . $gerbang . ': lengkap dan sesuai?'">
                                                                                {{ $gerbang }} lolos
                                                                            </x-aksi-button>
                                                                            <x-aksi-button varian="merah" :url="route('memo.verifikasi', $m['id'])" :fields="['gerbang' => $gerbang, 'lolos' => 0]"
                                                                                prompt="alasan" :wajib="true" :prompt-label="'Alasan tidak lolos ' . $gerbang . ' (wajib diisi). Memo akan dikembalikan ke unit kerja:'">
                                                                                {{ $gerbang }} tidak lolos
                                                                            </x-aksi-button>
                                                                        @endif

                                                                        @if ($tahap === 'lolos_spk')
                                                                            <x-aksi-button varian="biru" :url="route('memo.input-sakti', $m['id'])" :konfirmasi="'Catat memo ' . $m['nomor_memo'] . ' sudah diinput di SAKTI?'">
                                                                                Input SAKTI
                                                                            </x-aksi-button>
                                                                        @endif

                                                                        @if ($tahap === 'otp_ppk')
                                                                            <x-aksi-button varian="biru" :url="route('memo.selesai-otp', $m['id'])"
                                                                                konfirmasi="OTP PPK selesai? Setelah ini memo tidak bisa dikembalikan lagi.">
                                                                                Selesai OTP
                                                                            </x-aksi-button>
                                                                        @endif

                                                                        @if ($tahap === 'ppspm' && $st === 'sakti')
                                                                            <form method="POST" action="{{ route('monitoring.status-massal') }}"
                                                                                class="js-aksi-form m-0 flex flex-wrap items-center gap-1.5"
                                                                                data-konfirmasi="Tandai memo {{ $m['nomor_memo'] }} sudah cair (SP2D)?">
                                                                                @csrf
                                                                                <input type="hidden" name="status" value="sp2d">
                                                                                <input type="hidden" name="ids[]" value="{{ $m['id'] }}">
                                                                                <input type="date" name="tanggal"
                                                                                    value="{{ now()->toDateString() }}" title="Tanggal cair"
                                                                                    aria-label="Tanggal cair"
                                                                                    class="w-full min-w-0 border border-gray-200 rounded-md px-1.5 py-1 text-[11px] text-gray-600 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none">
                                                                                <button type="submit"
                                                                                    class="btn-utama inline-flex items-center rounded-md px-2.5 py-1 text-[11px] font-semibold text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-300">
                                                                                    Tandai cair (SP2D)
                                                                                </button>
                                                                            </form>
                                                                        @endif
                                                                    </div>
                                                                @endif

                                                                @if ($st === 'sp2d')
                                                                    <p class="mt-2 text-[11px] font-semibold text-emerald-700">
                                                                        Selesai
                                                                        @if (!empty($m['tgl_sp2d']))
                                                                            <span class="font-normal text-gray-500 tabular-nums">&middot;
                                                                                {{ $m['tgl_sp2d']->translatedFormat('d M Y') }}</span>
                                                                        @endif
                                                                    </p>
                                                                @endif
                                                            </td>

                                                            {{-- Log --}}
                                                            <td class="pl-2 pr-4 py-2.5 align-top text-right">
                                                                <button type="button" data-target="{{ $logId }}" aria-expanded="false"
                                                                    @disabled($logs->isEmpty())
                                                                    class="js-log-toggle rounded-md border border-gray-200 bg-white px-2 py-1 text-[11px] font-semibold text-gray-600 hover:bg-gray-50 cursor-pointer disabled:opacity-40 disabled:cursor-default focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                                                                    Log <span
                                                                        class="text-gray-400 tabular-nums">({{ $logs->count() }})</span>
                                                                </button>
                                                            </td>
                                                        </tr>

                                                        {{-- Riwayat memo (tersembunyi sampai Log diklik) --}}
                                                        <tr id="{{ $logId }}" class="hidden">
                                                            <td colspan="8" class="bg-white px-4 py-4 sm:px-6">
                                                                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                                                                    <p
                                                                        class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                                                                        Riwayat</p>
                                                                    <ul class="mt-3 space-y-3">
                                                                        @foreach ($logs as $log)
                                                                            <li class="flex items-start gap-3">
                                                                                <span
                                                                                    class="mt-2 h-2 w-2 shrink-0 rounded-full bg-gray-300"></span>
                                                                                <div class="min-w-0">
                                                                                    <p class="text-sm font-medium text-gray-800">
                                                                                        {{ $log['judul'] }}
                                                                                    </p>
                                                                                    @if (!empty($log['catatan']))
                                                                                        <p class="mt-0.5 text-xs text-gray-600 break-words">
                                                                                            {{ $log['catatan'] }}
                                                                                        </p>
                                                                                    @endif
                                                                                    <p class="mt-0.5 text-xs text-gray-500 tabular-nums">
                                                                                        {{ $log['waktu']->translatedFormat('d M Y, H:i') }}
                                                                                        @if (!empty($log['oleh']))
                                                                                            &middot; {{ $log['oleh'] }}
                                                                                        @endif
                                                                                    </p>
                                                                                </div>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

    </div>

    <script>
        (function () {
            // Show / Hide daftar memo per unit
            document.querySelectorAll('.js-unit-toggle').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const panel = document.getElementById(btn.dataset.target);
                    if (!panel) return;
                    const terbuka = panel.classList.toggle('hidden') === false;
                    btn.setAttribute('aria-expanded', terbuka ? 'true' : 'false');
                    const label = btn.querySelector('.js-label');
                    if (label) label.textContent = terbuka ? 'Hide' : 'Show';

                    // Tombol "Selengkapnya" hanya perlu kalau teksnya benar-benar terpotong.
                    // Diukur saat panel terbuka, karena saat tersembunyi lebarnya 0.
                    if (terbuka) {
                        panel.querySelectorAll('.js-kegiatan').forEach(function (p) {
                            const t = p.parentElement.querySelector('.js-kegiatan-toggle');
                            if (!t) return;
                            const terpotong = p.classList.contains('truncate') && p.scrollWidth > p.clientWidth;
                            const sedangBuka = !p.classList.contains('truncate');
                            t.classList.toggle('hidden', !(terpotong || sedangBuka));
                        });
                    }
                });
            });

            // Kegiatan: Selengkapnya / Tutup
            document.querySelectorAll('.js-kegiatan-toggle').forEach(function (t) {
                t.addEventListener('click', function () {
                    const p = t.parentElement.querySelector('.js-kegiatan');
                    const ringkas = p.classList.toggle('truncate');
                    p.classList.toggle('whitespace-normal', !ringkas);
                    p.classList.toggle('break-words', !ringkas);
                    t.textContent = ringkas ? 'Selengkapnya' : 'Tutup';
                });
            });

            // Log: buka / tutup riwayat memo
            document.querySelectorAll('.js-log-toggle').forEach(function (b) {
                b.addEventListener('click', function () {
                    const row = document.getElementById(b.dataset.target);
                    if (!row) return;
                    const terbuka = row.classList.toggle('hidden') === false;
                    b.setAttribute('aria-expanded', terbuka ? 'true' : 'false');
                    b.classList.toggle('bg-gray-100', terbuka);
                });
            });

            // Tabel MAK: buka/tutup cabang, tandai baris, filter level dan unit
            (function () {
                const tb = document.getElementById('mk-tb');
                if (!tb) return;
                const rows = Array.from(tb.querySelectorAll('tr[data-id]'));
                const byId = new Map(rows.map(function (r) { return [r.dataset.id, r]; }));
                const urutLv = ['prog', 'keg', 'ro', 'komp', 'sub', 'akun'];
                const selLv = document.getElementById('mk-level');
                const selUnit = document.getElementById('mk-unit');
                // Semua cabang terbuka di awal (jumlah MAK yang dipakai biasanya kecil)
                const open = new Set(rows.filter(function (r) { return r.dataset.kids === '1'; }).map(function (r) { return r.dataset.id; }));
                let selected = null;

                // Baris induk menampung unit semua turunannya, jadi cukup cek baris itu sendiri
                function cocokUnit(r) {
                    const u = selUnit ? selUnit.value : '';
                    if (!u) return true;
                    return (r.dataset.units || '').split('|').indexOf(u) !== -1;
                }

                function tampak(r) {
                    if (!cocokUnit(r)) return false;
                    let p = r.dataset.parent;
                    while (p) {
                        if (!open.has(p)) return false;
                        const pr = byId.get(p);
                        p = pr ? pr.dataset.parent : '';
                    }
                    return true;
                }

                function terapkan() {
                    rows.forEach(function (r) {
                        r.classList.toggle('hidden', !tampak(r));
                        const btn = r.querySelector('.mk-toggle');
                        if (btn) {
                            const o = open.has(r.dataset.id);
                            btn.setAttribute('aria-expanded', o ? 'true' : 'false');
                            btn.querySelector('.mk-chev').classList.toggle('is-open', o);
                        }
                        r.classList.toggle('is-selected', r.dataset.id === selected);
                    });
                }

                tb.addEventListener('click', function (e) {
                    const tr = e.target.closest('tr[data-id]');
                    if (!tr) return;
                    if (e.target.closest('[data-act="toggle"]')) {
                        const id = tr.dataset.id;
                        open.has(id) ? open.delete(id) : open.add(id);
                    } else {
                        selected = selected === tr.dataset.id ? null : tr.dataset.id;
                    }
                    terapkan();
                });

                document.getElementById('mk-expand').addEventListener('click', function () {
                    rows.forEach(function (r) { if (r.dataset.kids === '1') open.add(r.dataset.id); });
                    if (selLv) selLv.value = '';
                    terapkan();
                });
                document.getElementById('mk-collapse').addEventListener('click', function () {
                    open.clear();
                    terapkan();
                });

                // Level: tampilkan sampai level terpilih, level di bawahnya ditutup
                if (selLv) selLv.addEventListener('change', function () {
                    const batas = urutLv.indexOf(selLv.value); // -1 = semua
                    open.clear();
                    rows.forEach(function (r) {
                        if (r.dataset.kids === '1' && (batas < 0 || urutLv.indexOf(r.dataset.lv) < batas)) {
                            open.add(r.dataset.id);
                        }
                    });
                    terapkan();
                });

                if (selUnit) selUnit.addEventListener('change', terapkan);

                terapkan();
            })();

            // Form "Tandai cair": konfirmasi sebelum kirim
            document.querySelectorAll('.js-aksi-form').forEach(function (f) {
                f.addEventListener('submit', function (e) {
                    if (!window.confirm(f.dataset.konfirmasi || 'Ubah status memo ini?')) {
                        e.preventDefault();
                    }
                });
            });
        })();
    </script>

    <style>
        /* Tombol utama: gradasi biru yang sama dengan "Kirim ke Keuangan" di halaman memo */
        .btn-utama {
            background-image: linear-gradient(to bottom, #2563eb, #1d4ed8);
            background-color: #1d4ed8;
            border: 1px solid #1e40af;
            box-shadow: 0 1px 2px rgba(30, 64, 175, .35), inset 0 1px 0 rgba(255, 255, 255, .15);
            transition: background .15s, box-shadow .15s, transform .05s;
            cursor: pointer;
        }

        .btn-utama:hover {
            background-image: linear-gradient(to bottom, #1d4ed8, #1e40af);
            box-shadow: 0 2px 6px rgba(30, 64, 175, .4), inset 0 1px 0 rgba(255, 255, 255, .15);
        }

        .btn-utama:active {
            transform: translateY(1px);
        }

        /* ===== Progres per unit: baris unit dengan 6 kolom (di layar kecil menumpuk) ===== */
        .pu-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: .5rem;
        }

        @media (min-width: 768px) {
            .pu-grid {
                grid-template-columns: minmax(0, 2fr) 4rem minmax(0, 2.5fr) minmax(0, 1.6fr) minmax(0, 1.5fr) 5rem;
                gap: 1rem;
                align-items: center;
            }
        }

        .pu-head {
            display: none;
        }

        @media (min-width: 768px) {
            .pu-head {
                display: grid;
            }
        }

        /* ===== Tabel anggaran per MAK (tampilan sama dengan halaman Anggaran) ===== */
        .mk-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 0 0 1px rgb(226 232 240 / .8), 0 1px 2px rgb(15 23 42 / .04);
        }

        .mk-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .mk-scroll table {
            min-width: 900px;
            border-collapse: collapse;
        }

        @media (min-width: 1024px) {
            .mk-scroll {
                overflow-x: visible;
            }

            .mk-scroll table {
                min-width: 0;
            }
        }

        .mk-scroll td,
        .mk-scroll th {
            border: 1px solid #e2e8f0;
        }

        .mk-scroll th {
            border-color: rgb(255 255 255 / .25);
        }

        .mk-scroll tr> :first-child {
            border-left: 0;
        }

        .mk-scroll tr> :last-child {
            border-right: 0;
        }

        .mk-scroll thead tr>* {
            border-top: 0;
        }

        .mk-scroll tbody tr:last-child>* {
            border-bottom: 0;
        }

        .mk-row {
            transition: background-color .12s ease;
        }

        .mk-row:hover {
            background-color: rgb(241 245 249 / .9);
        }

        .mk-row.is-selected {
            background-color: #eff6ff !important;
            box-shadow: inset 0 0 0 1px #93c5fd;
        }

        .mk-chev {
            transition: transform .15s ease;
        }

        .mk-chev.is-open {
            transform: rotate(90deg);
        }

        @media (prefers-reduced-motion: reduce) {

            .mk-row,
            .mk-chev {
                transition: none;
            }
        }
    </style>
</x-app-layout>