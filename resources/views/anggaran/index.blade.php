<x-app-layout title="Anggaran">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitor Anggaran') }}
        </h2>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Laporan Ketersediaan Dana Detail</h2>
                    <p id="ang-meta" class="text-sm text-gray-500">Memuat data...</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button id="btn-toggle-chart" type="button" onclick="toggleChart()"
                        class="inline-flex justify-center items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm">
                        <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        <span id="lbl-toggle-chart">Sembunyikan Grafik</span>
                    </button>

                    <a id="btn-export" href="{{ route('anggaran.export') }}"
                        class="inline-flex justify-center items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm">
                        <svg class="w-4 h-4 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Export Excel
                    </a>

                    @if ($canUpload)
                        <a href="{{ route('anggaran.upload') }}"
                            class="inline-flex justify-center items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 shrink-0 transition shadow-sm">
                            <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            Upload / Perbarui Data
                        </a>
                    @endif
                </div>
            </div>

            <div id="section-chart" class="mb-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="bg-gray-50/60 border border-gray-100 rounded-xl p-4 flex flex-col justify-between">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Serapan Anggaran</h3>
                    <div class="relative flex items-center justify-center my-auto py-2" style="height: 170px;">
                        <canvas id="chart-donut"></canvas>
                    </div>
                    <div class="flex justify-between items-center text-xs text-gray-600 pt-2 border-t border-gray-200/60">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-indigo-600 inline-block"></span>Realisasi: <b id="lbl-chart-real">Rp 0</b></span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-gray-300 inline-block"></span>Sisa: <b id="lbl-chart-sisa">Rp 0</b></span>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-gray-50/60 border border-gray-100 rounded-xl p-4 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pagu dan Realisasi</h3>
                        <span class="text-xs text-gray-400">7 pagu terbesar di tampilan ini</span>
                    </div>
                    <div class="relative w-full" style="height: 180px;">
                        <canvas id="chart-bar"></canvas>
                    </div>
                </div>
            </div>

            <p id="ang-empty" class="hidden py-10 text-center text-gray-400 text-sm">
                Belum ada data anggaran. @if ($canUpload) Silakan upload file dari SAKTI. @endif
            </p>

            <div id="ang-body" class="hidden space-y-4 mb-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach (['keg' => 'Kegiatan', 'ro' => 'RO / SubOutput', 'komp' => 'Komponen', 'sub' => 'SubKomponen', 'akun' => 'Akun'] as $k =>$label)
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 font-medium">{{ $label }}</label>
                            <select id="f-{{ $k }}"
                                class="w-full truncate rounded-xl border border-gray-200 py-2 px-3 text-sm text-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none bg-white"></select>
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1 font-medium">Level Tampilan</label>
                        <select id="f-level"
                            class="w-full rounded-xl border border-gray-200 py-2 px-3 text-sm text-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none bg-white">
                            <option value="all">Semua level (hierarki)</option>
                            <option value="prog">Program</option>
                            <option value="keg">Kegiatan</option>
                            <option value="ro">RO / Sub Output</option>
                            <option value="komp">Komponen</option>
                            <option value="sub">Sub Komponen</option>
                            <option value="akun">Akun</option>
                            <option value="item">Item</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1 font-medium">Cari kode / uraian</label>
                        <input type="text" id="ang-q" placeholder="mis. CL.7458, ABB.001, 051 atau konsumsi" autocomplete="off"
                            class="w-full rounded-xl border border-gray-200 py-2 px-3 text-sm text-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none bg-white">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1 font-medium">Baris per Halaman</label>
                        <select id="f-limit"
                            class="w-full rounded-xl border border-gray-200 py-2 px-3 text-sm text-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none bg-white">
                            <option value="10">10 Baris</option>
                            <option value="25">25 Baris</option>
                            <option value="50">50 Baris</option>
                            <option value="100">100 Baris</option>
                            <option value="500" selected>500 Baris</option>
                            <option value="all">Semua Data</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 pt-2">
                    <div class="bg-gray-50 rounded-xl p-3"><p class="text-xs text-gray-500">Pagu Revisi</p><p id="s-pagu" class="font-semibold text-gray-900">-</p></div>
                    <div class="bg-gray-50 rounded-xl p-3"><p class="text-xs text-gray-500">Realisasi s.d. Periode</p><p id="s-real" class="font-semibold text-gray-900">-</p></div>
                    <div class="bg-gray-50 rounded-xl p-3"><p class="text-xs text-gray-500">Sisa Anggaran</p><p id="s-sisa" class="font-semibold text-gray-900">-</p></div>
                    <div class="bg-gray-50 rounded-xl p-3"><p class="text-xs text-gray-500">% Realisasi</p><p id="s-pct" class="font-semibold text-gray-900">-</p></div>
                </div>

                <div class="rounded-xl border border-gray-100 overflow-x-auto">
                    <table class="min-w-[1120px] w-full text-sm">
                        <thead class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="text-left px-4 py-2.5 font-semibold align-middle" rowspan="2">Kode &amp; Uraian</th>
                                <th class="text-right px-3 py-2.5 font-semibold align-middle" rowspan="2">Pagu Revisi</th>
                                <th class="text-right px-3 py-2.5 font-semibold align-middle" rowspan="2">Lock Pagu</th>
                                <th class="text-center px-3 py-2 font-semibold border-b border-gray-200" colspan="3">Realisasi TA</th>
                                <th class="text-center px-3 py-2.5 font-semibold align-middle" rowspan="2">%</th>
                                <th class="text-right px-4 py-2.5 font-semibold align-middle" rowspan="2">Sisa Anggaran</th>
                            </tr>
                            <tr>
                                <th class="text-right px-3 py-2 font-semibold">Periode Lalu</th>
                                <th class="text-right px-3 py-2 font-semibold">Periode Ini</th>
                                <th class="text-right px-3 py-2 font-semibold">s.d. Periode</th>
                            </tr>
                        </thead>
                        <tbody id="ang-tb" class="divide-y divide-gray-50"></tbody>
                    </table>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4">
                    <p id="ang-cnt" class="text-xs text-gray-500"></p>
                    <div class="flex items-center gap-2">
                        <button id="btn-prev" class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50 disabled:opacity-40 transition">Sebelumnya</button>
                        <span id="page-info" class="text-xs text-gray-600 font-medium">Halaman 1</span>
                        <button id="btn-next" class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50 disabled:opacity-40 transition">Selanjutnya</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let donutChartInstance = null;
        let barChartInstance = null;

        function toggleChart() {
            const sec = document.getElementById('section-chart');
            const lbl = document.getElementById('lbl-toggle-chart');
            if (sec.classList.contains('hidden')) {
                sec.classList.remove('hidden');
                lbl.textContent = 'Sembunyikan Grafik';
            } else {
                sec.classList.add('hidden');
                lbl.textContent = 'Tampilkan Grafik';
            }
        }

        (function () {
            const CH = ['keg', 'ro', 'komp', 'sub', 'akun'];
            const $ = (id) => document.getElementById(id);
            const rp = (n) => new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const F = { keg: '', ro: '', komp: '', sub: '', akun: '' };

            let items = [];
            let currentPage = 1;

            const match = (it, upTo) => CH.slice(0, upTo).every((k) => !F[k] || it[k + '_kode'] === F[k]);

            const LEVELS = [
                { key: 'prog', label: 'Program',      kode: 'prog_kode', uraian: 'prog_uraian' },
                { key: 'keg',  label: 'Kegiatan',     kode: 'keg_kode',  uraian: 'keg_uraian' },
                { key: 'ro',   label: 'Sub Output',   kode: 'ro_kode',   uraian: 'ro_uraian',   color: '#CC00CC' },
                { key: 'komp', label: 'Komponen',     kode: 'komp_kode', uraian: 'komp_uraian', color: '#CC9900' },
                { key: 'sub',  label: 'Sub Komponen', kode: 'sub_kode',  uraian: 'sub_uraian' },
                { key: 'akun', label: 'Akun',         kode: 'akun_kode', uraian: 'akun_uraian' },
            ];
            const ITEM = { key: 'item', label: 'Item' };

            function lvKode(it, lv) {
                if (lv.key !== 'prog') return String(it[lv.kode] ?? '').trim();
                const p = String(it.prog_kode ?? '').trim();
                if (p) return p;
                const k = String(it.keg_kode ?? '').trim();
                return k.includes('.') ? k.split('.')[0] : 'PROG';
            }

            const NAMA_PROGRAM = {
                CL: 'Program Koordinasi Pelaksanaan Kebijakan',
                WA: 'Program Dukungan Manajemen',
            };
            function lvUraian(it, lv) {
                if (lv.key !== 'prog') return it[lv.uraian] || '';
                const u = String(it.prog_uraian ?? '').trim();
                if (u) return u;
                const k = lvKode(it, lv);
                return NAMA_PROGRAM[k] || (k === 'PROG' ? 'Program Utama' : 'Program ' + k);
            }

            const padKode = (key, k) => (key === 'komp' && /^\d+$/.test(k) && k.length < 3) ? k.padStart(3, '0') : k;
            const numOrNull = (v) => (v === undefined || v === null || v === '') ? null : Number(v);

            function vals(it) {
                const lalu = numOrNull(it.realisasi_lalu), ini = numOrNull(it.realisasi_ini);
                let sd = numOrNull(it.realisasi);
                if (sd === null) sd = (lalu || 0) + (ini || 0);
                return { pagu: Number(it.pagu) || 0, lock: Number(it.lock_pagu) || 0, lalu, ini, sd };
            }
            function addV(to, v) {
                to.pagu += v.pagu; to.lock += v.lock; to.sd += v.sd;
                if (v.lalu !== null) to.lalu = (to.lalu || 0) + v.lalu;
                if (v.ini !== null) to.ini = (to.ini || 0) + v.ini;
            }
            const zero = () => ({ pagu: 0, lock: 0, lalu: null, ini: null, sd: 0 });

            function buildRows(list) {
                const chain = LEVELS;
                const root = { ch: new Map(), leaf: [] };
                list.forEach((it) => {
                    const v = vals(it);
                    let node = root;
                    chain.forEach((lv, depth) => {
                        const raw = lvKode(it, lv);
                        let c = node.ch.get(raw);
                        if (!c) {
                            c = { lv, depth, kode: padKode(lv.key, raw), uraian: lvUraian(it, lv), v: zero(), ch: new Map(), leaf: [] };
                            node.ch.set(raw, c);
                        }
                        addV(c.v, v);
                        node = c;
                    });
                    node.leaf.push({ lv: ITEM, depth: chain.length, kode: String(it.item_kode ?? '').trim(), uraian: it.item_uraian || '', v });
                });
                const out = [];
                (function walk(n) { n.ch.forEach((c) => { out.push(c); walk(c); }); n.leaf.forEach((l) => out.push(l)); })(root);
                return out;
            }

            function matchQ(it, q) {
                if (!q) return true;
                const hay = [];
                LEVELS.forEach((lv) => {
                    const k = lvKode(it, lv);
                    hay.push(k, padKode(lv.key, k), lvUraian(it, lv));
                });
                hay.push(it.item_kode || '', it.item_uraian || '');
                const h = hay.join('\n').toLowerCase();
                return q.toLowerCase().split(/[,;\n]+/).map((t) => t.trim()).filter(Boolean).some((t) => h.includes(t));
            }

            function fillSel() {
                CH.forEach((k, i) => {
                    const seen = new Set();
                    let html = '<option value="">Semua</option>';
                    items.forEach((it) => {
                        const kode = it[k + '_kode'];
                        if (!match(it, i) || seen.has(kode)) return;
                        seen.add(kode);
                        html += `<option value="${esc(kode)}">${esc(padKode(k, String(kode ?? '')) + ' – ' + (it[k + '_uraian'] || ''))}</option>`;
                    });
                    const sel = $('f-' + k);
                    sel.innerHTML = html;
                    sel.value = F[k];
                });
            }

            function updateExportLink() {
                const p = new URLSearchParams();
                CH.forEach((k) => { if (F[k]) p.set(k, F[k]); });
                const q = $('ang-q').value.trim();
                if (q) p.set('q', q);
                if ($('f-level').value !== 'all') p.set('level', $('f-level').value);
                const base = @json(route('anggaran.export'));
                $('btn-export').href = base + (p.toString() ? '?' + p.toString() : '');
            }

            function updateCharts(P, R, rows) {
                const sisa = Math.max(0, P - R);

                $('lbl-chart-real').textContent = 'Rp ' + rp(R);
                $('lbl-chart-sisa').textContent = 'Rp ' + rp(sisa);

                const ctxDonut = $('chart-donut').getContext('2d');
                if (donutChartInstance) donutChartInstance.destroy();
                donutChartInstance = new Chart(ctxDonut, {
                    type: 'doughnut',
                    data: { labels: ['Realisasi', 'Sisa'], datasets: [{ data: [R, sisa], backgroundColor: ['#2563eb', '#e5e7eb'], borderWidth: 0 }] },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '72%',
                        plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: Rp ${rp(ctx.raw)}` } } }
                    }
                });

                const pool = rows.filter((r) => $('f-level').value === 'all' ? r.lv.key === 'item' : true);
                const top = [...pool].sort((a, b) => b.v.pagu - a.v.pagu).slice(0, 7);
                const ctxBar = $('chart-bar').getContext('2d');
                if (barChartInstance) barChartInstance.destroy();
                barChartInstance = new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: top.map((x) => x.kode || '-'),
                        datasets: [
                            { label: 'Pagu', data: top.map((x) => x.v.pagu), backgroundColor: '#bfdbfe', borderRadius: 4 },
                            { label: 'Realisasi', data: top.map((x) => x.v.sd), backgroundColor: '#2563eb', borderRadius: 4 }
                        ]
                    },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'top', align: 'end' } },
                        scales: { x: { ticks: { callback: (v) => v >= 1e9 ? (v / 1e9).toFixed(1) + ' M' : (v >= 1e6 ? (v / 1e6).toFixed(0) + ' jt' : v) } } }
                    }
                });
            }

            function render() {
                const q = $('ang-q').value.trim();
                const level = $('f-level').value;
                const limitVal = $('f-limit').value;
                const limit = limitVal === 'all' ? Infinity : parseInt(limitVal, 10);

                const filtered = items.filter((it) => match(it, 5) && matchQ(it, q));
                let rows = buildRows(filtered);
                if (level !== 'all') rows = rows.filter((r) => r.lv.key === level);

                let P = 0, R = 0;
                filtered.forEach((x) => { const v = vals(x); P += v.pagu; R += v.sd; });
                $('s-pagu').textContent = 'Rp ' + rp(P);
                $('s-real').textContent = 'Rp ' + rp(R);
                $('s-sisa').textContent = 'Rp ' + rp(P - R);
                $('s-pct').textContent = P ? (R / P * 100).toFixed(2).replace('.', ',') + ' %' : '-';

                updateCharts(P, R, rows);
                updateExportLink();

                const totalRows = rows.length;
                const totalPages = limit === Infinity ? 1 : Math.ceil(totalRows / limit) || 1;
                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;
                const startIdx = (currentPage - 1) * limit;
                const endIdx = limit === Infinity ? totalRows : startIdx + limit;
                const pageRows = rows.slice(startIdx, endIdx);

                const dash = '<span class="text-gray-300">–</span>';
                $('ang-tb').innerHTML = pageRows.map((r) => {
                    const v = r.v;
                    const pct = v.pagu > 0 ? v.sd / v.pagu : 0;
                    const col = r.lv.color ? `style="color:${r.lv.color}"` : '';
                    const bold = r.depth <= 2 ? 'font-semibold' : '';
                    const bar = pct >= 0.9 ? 'bg-emerald-500' : (pct >= 0.5 ? 'bg-indigo-500' : 'bg-amber-500');
                    const pad = 16 + Math.min(r.depth, 7) * 16;
                    return `<tr class="hover:bg-gray-50/60">
                        <td class="py-2 pr-4 align-top" style="padding-left:${pad}px">
                            <div class="flex items-start gap-2 ${r.lv.color ? '' : 'text-gray-800'}" ${col}>
                                <span class="mt-0.5 shrink-0 font-mono text-[11px] bg-gray-50 border border-gray-100 rounded px-1.5 py-0.5">${esc(r.kode || '—')}</span>
                                <div class="min-w-0">
                                    <div class="${bold} leading-snug">${esc(r.uraian)}</div>
                                    <div class="text-[10px] uppercase tracking-wider text-gray-400">${esc(r.lv.label)}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2 text-right whitespace-nowrap tabular-nums align-top">${rp(v.pagu)}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap tabular-nums align-top">${rp(v.lock)}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap tabular-nums align-top">${v.lalu === null ? dash : rp(v.lalu)}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap tabular-nums align-top">${v.ini === null ? dash : rp(v.ini)}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap tabular-nums align-top">${rp(v.sd)}</td>
                        <td class="px-3 py-2 align-top" style="min-width:104px">
                            <div class="text-center text-xs font-semibold tabular-nums">${(pct * 100).toFixed(2).replace('.', ',')} %</div>
                            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-gray-200"><div class="h-full rounded-full ${bar}" style="width:${Math.min(100, pct * 100)}%"></div></div>
                        </td>
                        <td class="px-4 py-2 text-right whitespace-nowrap tabular-nums align-top">${rp(v.pagu - v.sd)}</td>
                    </tr>`;
                }).join('');

                $('ang-cnt').textContent = `Menampilkan ${totalRows === 0 ? 0 : startIdx + 1} - ${Math.min(endIdx, totalRows)} dari ${totalRows} baris`;
                $('page-info').textContent = `Halaman ${currentPage} dari ${totalPages}`;
                $('btn-prev').disabled = currentPage <= 1;
                $('btn-next').disabled = currentPage >= totalPages;
            }

            CH.forEach((k, i) => {
                $('f-' + k).addEventListener('change', function () {
                    F[k] = this.value;
                    for (let j = i + 1; j < CH.length; j++) F[CH[j]] = '';
                    currentPage = 1;
                    fillSel();
                    render();
                });
            });
            $('f-level').addEventListener('change', () => { currentPage = 1; render(); });
            $('f-limit').addEventListener('change', () => { currentPage = 1; render(); });
            $('ang-q').addEventListener('input', () => { currentPage = 1; render(); });
            $('btn-prev').addEventListener('click', () => { if (currentPage > 1) { currentPage--; render(); } });
            $('btn-next').addEventListener('click', () => { currentPage++; render(); });

            // Ambil data
            fetch(@json(route('anggaran.data')), { headers: { Accept: 'application/json' } })
                .then((r) => r.json())
                .then((d) => {
                    if (!d.upload) {
                        $('ang-meta').textContent = 'Belum ada data.';
                        $('ang-empty').classList.remove('hidden');
                        return;
                    }
                    items = d.items;
                    $('ang-meta').textContent = [d.upload.satker, d.upload.periode, 'diperbarui ' + d.upload.at].filter(Boolean).join(' · ');
                    $('ang-body').classList.remove('hidden');
                    fillSel();
                    render();
                })
                .catch(() => { $('ang-meta').textContent = 'Gagal memuat data.'; });
        })();
    </script>
</x-app-layout>