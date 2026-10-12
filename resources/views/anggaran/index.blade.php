<x-app-layout title="Anggaran">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitor Anggaran') }}
        </h2>
    </x-slot>

    <!-- CDN Chart.js untuk Grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">

            <!-- Header Section & Action Buttons -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Realisasi Anggaran per Akun</h2>
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

                    <a href="{{ route('anggaran.export') }}"
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

            <!-- Section Grafik -->
            <div id="section-chart" class="mb-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Serapan Anggaran Donut Chart -->
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

                <!-- Pagu dan Realisasi Horizontal Bar Chart -->
                <div class="lg:col-span-2 bg-gray-50/60 border border-gray-100 rounded-xl p-4 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pagu dan Realisasi</h3>
                        <span class="text-xs text-gray-400">Item terbesar di tampilan ini</span>
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
                <!-- Baris 1: Filter Hierarki Berantai (5 Column Grid) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    @foreach (['keg' => 'Kegiatan', 'ro' => 'RO / SubOutput', 'komp' => 'Komponen', 'sub' => 'SubKomponen', 'akun' => 'Akun'] as $k =>$label)
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 font-medium">{{ $label }}</label>
                            <select id="f-{{ $k }}"
                                class="w-full rounded-xl border border-gray-200 py-2 px-3 text-sm text-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none bg-white"></select>
                        </div>
                    @endforeach
                </div>

                <!-- Baris 2: Controls & Search (3 Column Grid) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Level Tampilan -->
                    <div>
                        <label class="block text-xs text-gray-500 mb-1 font-medium">Level Tampilan</label>
                        <select id="f-level"
                            class="w-full rounded-xl border border-gray-200 py-2 px-3 text-sm text-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none bg-white">
                            <option value="item">Item Detail</option>
                            <option value="akun">Akun</option>
                            <option value="sub">SubKomponen</option>
                            <option value="komp">Komponen</option>
                        </select>
                    </div>

                    <!-- Cari Item -->
                    <div>
                        <label class="block text-xs text-gray-500 mb-1 font-medium">Cari item</label>
                        <input type="text" id="ang-q" placeholder="mis. konsumsi, perjalanan..." autocomplete="off"
                            class="w-full rounded-xl border border-gray-200 py-2 px-3 text-sm text-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none bg-white">
                    </div>

                    <!-- Baris per Halaman -->
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

                <!-- Card Summary -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 pt-2">
                    <div class="bg-gray-50 rounded-xl p-3"><p class="text-xs text-gray-500">Pagu</p><p id="s-pagu" class="font-semibold text-gray-900">-</p></div>
                    <div class="bg-gray-50 rounded-xl p-3"><p class="text-xs text-gray-500">Realisasi</p><p id="s-real" class="font-semibold text-gray-900">-</p></div>
                    <div class="bg-gray-50 rounded-xl p-3"><p class="text-xs text-gray-500">Sisa</p><p id="s-sisa" class="font-semibold text-gray-900">-</p></div>
                    <div class="bg-gray-50 rounded-xl p-3"><p class="text-xs text-gray-500">% Realisasi</p><p id="s-pct" class="font-semibold text-gray-900">-</p></div>
                </div>

                <!-- Tabel Data -->
                <div class="rounded-xl border border-gray-100 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500">
                            <tr>
                                <th class="text-left px-4 py-2.5 font-medium">Jalur</th>
                                <th class="text-left px-4 py-2.5 font-medium">Akun</th>
                                <th class="text-left px-4 py-2.5 font-medium">Item / Uraian</th>
                                <th class="text-right px-4 py-2.5 font-medium">Pagu</th>
                                <th class="text-right px-4 py-2.5 font-medium">Realisasi</th>
                                <th class="text-right px-4 py-2.5 font-medium">Sisa</th>
                                <th class="text-right px-4 py-2.5 font-medium">%</th>
                            </tr>
                        </thead>
                        <tbody id="ang-tb" class="divide-y divide-gray-50"></tbody>
                    </table>
                </div>

                <!-- Footer Counter & Pagination -->
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

            function fillSel() {
                CH.forEach((k, i) => {
                    const seen = new Set();
                    let html = '<option value="">Semua</option>';
                    items.forEach((it) => {
                        const kode = it[k + '_kode'];
                        if (!match(it, i) || seen.has(kode)) return;
                        seen.add(kode);
                        html += `<option value="${esc(kode)}">${esc(kode + ' – ' + (it[k + '_uraian'] || ''))}</option>`;
                    });
                    const sel = $('f-' + k);
                    sel.innerHTML = html;
                    sel.value = F[k];
                });
            }

            function updateCharts(P, R, aggregatedList) {
                const sisa = Math.max(0, P - R);

                $('lbl-chart-real').textContent = 'Rp ' + rp(R);$('lbl-chart-sisa').textContent = 'Rp ' + rp(sisa);

                // 1. Donut Chart
                const ctxDonut = $('chart-donut').getContext('2d');
                if (donutChartInstance) donutChartInstance.destroy();

                donutChartInstance = new Chart(ctxDonut, {
                    type: 'doughnut',
                    data: {
                        labels: ['Realisasi', 'Sisa'],
                        datasets: [{
                            data: [R, sisa],
                            backgroundColor: ['#2563eb', '#e5e7eb'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: Rp ${rp(ctx.raw)}` } }
                        }
                    }
                });

                // 2. Bar Chart (Top 7 Items)
                const topItems = [...aggregatedList].sort((a, b) => b.pagu - a.pagu).slice(0, 7);
                const labels = topItems.map((x) => x.item_kode || x.akun_kode || x.sub_kode || 'Item');
                const paguData = topItems.map((x) => x.pagu);
                const realData = topItems.map((x) => x.realisasi);

                const ctxBar = $('chart-bar').getContext('2d');
                if (barChartInstance) barChartInstance.destroy();

                barChartInstance = new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            { label: 'Pagu', data: paguData, backgroundColor: '#bfdbfe', borderRadius: 4 },
                            { label: 'Realisasi', data: realData, backgroundColor: '#2563eb', borderRadius: 4 }
                        ]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'top', align: 'end' } },
                        scales: {
                            x: {
                                ticks: {
                                    callback: (v) => v >= 1e9 ? (v / 1e9).toFixed(1) + ' M' : (v >= 1e6 ? (v / 1e6).toFixed(0) + ' jt' : v)
                                }
                            }
                        }
                    }
                });
            }

            function render() {
                const q = $('ang-q').value.trim().toLowerCase();
                const level = $('f-level').value;
                const limitVal = $('f-limit').value;
                const limit = limitVal === 'all' ? Infinity : parseInt(limitVal, 10);

                let filtered = items.filter((it) => match(it, 5) &&
                    (!q || (it.item_uraian + ' ' + it.akun_uraian + ' ' + it.sub_uraian).toLowerCase().includes(q)));

                // Grouping berdasarkan Level Tampilan
                let aggregatedRows = [];
                if (level === 'item') {
                    aggregatedRows = filtered;
                } else {
                    const groups = {};
                    filtered.forEach((it) => {
                        let key = '';
                        let u = '';
                        if (level === 'akun') { key = it.akun_kode; u = it.akun_uraian; }
                        else if (level === 'sub') { key = it.sub_kode; u = it.sub_uraian; }
                        else if (level === 'komp') { key = it.komp_kode; u = it.komp_uraian; }

                        if (!groups[key]) {
                            groups[key] = { ...it, item_kode: key, item_uraian: u, pagu: 0, realisasi: 0 };
                        }
                        groups[key].pagu += it.pagu;
                        groups[key].realisasi += it.realisasi;
                    });
                    aggregatedRows = Object.values(groups);
                }

                // Kalkulasi total
                let P = 0, R = 0;
                filtered.forEach((x) => { P += x.pagu; R += x.realisasi; });
                $('s-pagu').textContent = 'Rp ' + rp(P);$('s-real').textContent = 'Rp ' + rp(R);
                $('s-sisa').textContent = 'Rp ' + rp(P - R);$('s-pct').textContent = P ? (R / P * 100).toFixed(1) + '%' : '-';

                // Update Grafik
                updateCharts(P, R, aggregatedRows);

                // Logika Pagination
                const totalRows = aggregatedRows.length;
                const totalPages = limit === Infinity ? 1 : Math.ceil(totalRows / limit) || 1;
                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const startIdx = (currentPage - 1) * limit;
                const endIdx = limit === Infinity ? totalRows : startIdx + limit;
                const pageRows = aggregatedRows.slice(startIdx, endIdx);

                // Render Baris Tabel
                $('ang-tb').innerHTML = pageRows.map((x) => {
                    const pill = (t) => `<span class="inline-block font-mono text-[11px] text-gray-600 bg-gray-50 border border-gray-100 rounded px-1.5 py-0.5 mr-1">${esc(t)}</span>`;
                    return `<tr class="hover:bg-gray-50/60">
                        <td class="px-4 py-2.5 whitespace-nowrap">${pill(x.keg_kode)}${pill(x.ro_kode)}${pill(x.komp_kode)}${pill(x.sub_kode)}</td>
                        <td class="px-4 py-2.5"><span class="font-mono text-xs text-gray-700">${esc(x.akun_kode)}</span><div class="text-xs text-gray-400">${esc(x.akun_uraian)}</div></td>
                        <td class="px-4 py-2.5 text-gray-600">${esc(x.item_kode ? x.item_kode + '. ' : '')}${esc(x.item_uraian)}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">${rp(x.pagu)}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">${rp(x.realisasi)}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">${rp(x.pagu - x.realisasi)}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">${x.pagu ? (x.realisasi / x.pagu * 100).toFixed(1) : '0.0'}%</td>
                    </tr>`;
                }).join('');

                // Status Pagination UI
                $('ang-cnt').textContent = `Menampilkan ${totalRows === 0 ? 0 : startIdx + 1} - ${Math.min(endIdx, totalRows)} dari ${totalRows} baris`;
                $('page-info').textContent = `Halaman ${currentPage} dari ${totalPages}`;
                $('btn-prev').disabled = currentPage <= 1;
                $('btn-next').disabled = currentPage >= totalPages;
            }

            // Event Handlers
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
            $('f-limit').addEventListener('change', () => { currentPage = 1; render(); });$('ang-q').addEventListener('input', () => { currentPage = 1; render(); });

            $('btn-prev').addEventListener('click', () => { if (currentPage > 1) { currentPage--; render(); } });$('btn-next').addEventListener('click', () => { currentPage++; render(); });

            // Fetch Initial Data
            fetch(@json(route('anggaran.data')), { headers: { Accept: 'application/json' } })
                .then((r) => r.json())
                .then((d) => {
                    if (!d.upload) {
                        $('ang-meta').textContent = 'Belum ada data.';$('ang-empty').classList.remove('hidden');
                        return;
                    }
                    items = d.items;
                    $('ang-meta').textContent = [d.upload.satker, d.upload.periode, 'diperbarui ' + d.upload.at].filter(Boolean).join(' · ');$('ang-body').classList.remove('hidden');
                    fillSel();
                    render();
                })
                .catch(() => { $('ang-meta').textContent = 'Gagal memuat data.'; });
        })();
    </script>
</x-app-layout>