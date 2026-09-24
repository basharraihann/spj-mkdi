<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Welcome card --}}
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">
                            Halo, {{ auth()->user()->name ?? 'Pengguna' }} 👋
                        </h3>
                        <p class="text-sm text-gray-400 mt-1">
                            Selamat datang kembali. Berikut ringkasan aktivitas perjalanan dinas Anda.
                        </p>
                    </div>

                    <a href="{{ route('agendas.create') }}"
                        class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2.5 rounded-lg text-sm shadow-sm transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Agenda Baru
                    </a>
                </div>
            </div>

            {{-- Ringkasan statistik --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-5 flex items-center gap-4">
                    <div
                        class="h-11 w-11 shrink-0 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Agenda Bulan Ini</p>
                        <p class="text-xl font-bold text-gray-800">{{ $agendaBulanIni }}</p>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-5 flex items-center gap-4">
                    <div
                        class="h-11 w-11 shrink-0 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Agenda Tahun {{ now()->year }}</p>
                        <p class="text-xl font-bold text-gray-800">{{ $agendaTahunIni }}</p>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-5 flex items-center gap-4">
                    <div
                        class="h-11 w-11 shrink-0 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Dana Diajukan (LS) Bulan Ini</p>
                        <p class="text-xl font-bold text-gray-800">Rp
                            {{ number_format($totalDanaBulanIni, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Chart pengeluaran & kalender agenda --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 bg-white shadow-sm rounded-2xl border border-gray-100 p-5">
                    <h4 class="font-semibold text-gray-800 mb-1">Tren Pengeluaran Perjalanan Dinas</h4>
                    <p class="text-sm text-gray-400 mb-4">Total belanja per bulan, tahun {{ now()->year }}.</p>
                    <canvas id="chartPengeluaran" height="110"></canvas>
                </div>

                {{-- Kalender (dirender oleh JS dari data JSON) --}}
                <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-5">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <div class="flex items-center gap-1 min-w-0">
                            <select id="kal-bulan" aria-label="Pilih bulan"
                                class="border-0 bg-transparent py-0 pl-0 pr-6 text-sm font-semibold text-gray-800 focus:ring-0 cursor-pointer"></select>
                            <select id="kal-tahun" aria-label="Pilih tahun"
                                class="border-0 bg-transparent py-0 pl-0 pr-6 text-sm font-semibold text-gray-800 focus:ring-0 cursor-pointer"></select>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button" id="kal-hariini"
                                class="hidden text-xs text-blue-600 hover:bg-blue-50 px-2 py-1 rounded-lg transition">Hari
                                ini</button>
                            <button type="button" id="kal-prev" aria-label="Bulan sebelumnya"
                                class="h-7 w-7 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition">‹</button>
                            <button type="button" id="kal-next" aria-label="Bulan berikutnya"
                                class="h-7 w-7 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition">›</button>
                        </div>
                    </div>
                    <p class="text-sm text-gray-400 mb-4">Tanggal bertanda ada agenda SPJ.</p>

                    <div class="grid grid-cols-7 gap-1 text-center text-xs text-gray-400 mb-1.5">
                        <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span
                            class="text-red-400">Min</span>
                    </div>

                    <div id="kal-grid" class="grid grid-cols-7 gap-1 transition-opacity"></div>

                    <p id="kal-error" class="hidden mt-3 text-xs text-red-500">Gagal memuat kalender. Coba lagi.</p>

                    <div id="kalender-detail" class="hidden mt-4 pt-4 border-t border-gray-100 space-y-2.5"></div>
                </div>
            </div>

            {{-- Chart.js untuk grafik pengeluaran --}}
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // ==================== Chart ====================
                    const ctx = document.getElementById('chartPengeluaran');
                    if (ctx) {
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: @json($chartLabels),
                                datasets: [{
                                    label: 'Total Belanja (Rp)',
                                    data: @json($chartData),
                                    backgroundColor: '#5b42f3',
                                    borderRadius: 6,
                                    maxBarThickness: 36,
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: function (context) {
                                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function (value) {
                                                if (value >= 1000000) {
                                                    return 'Rp ' + (value / 1000000) + 'jt';
                                                }
                                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // ==================== Kalender (tanpa refresh) ====================
                    const KALENDER_URL = @json(route('dashboard.kalender'));
                    const NAMA_BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                    const grid = document.getElementById('kal-grid');
                    const detailBox = document.getElementById('kalender-detail');
                    const selBulan = document.getElementById('kal-bulan');
                    const selTahun = document.getElementById('kal-tahun');
                    const btnPrev = document.getElementById('kal-prev');
                    const btnNext = document.getElementById('kal-next');
                    const btnHariIni = document.getElementById('kal-hariini');
                    const errBox = document.getElementById('kal-error');

                    let kal = @json($kalender);
                    let requestId = 0;

                    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
                        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
                    }[c]));

                    const icon = d => `<svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 mt-px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="${d}"/></svg>`;
                    const ICON_LOKASI = icon('M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z');
                    const ICON_PEGAWAI = icon('M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z');
                    const ICON_TANGGAL = icon('M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z');

                    function renderControls() {
                        selBulan.innerHTML = NAMA_BULAN.map((n, i) =>
                            `<option value="${i + 1}" ${i + 1 === kal.bulan ? 'selected' : ''}>${n}</option>`).join('');

                        let opsiTahun = '';
                        for (let y = kal.tahun - 5; y <= kal.tahun + 5; y++) {
                            opsiTahun += `<option value="${y}" ${y === kal.tahun ? 'selected' : ''}>${y}</option>`;
                        }
                        selTahun.innerHTML = opsiTahun;

                        btnHariIni.classList.toggle('hidden', kal.isBulanIni);
                    }

                    function renderGrid() {
                        let html = '';
                        for (let i = 0; i < kal.offset; i++) html += '<div></div>';

                        for (let tgl = 1; tgl <= kal.jumlahHari; tgl++) {
                            const list = kal.marks[tgl] || [];
                            const ada = list.length > 0;
                            const isHariIni = tgl === kal.hariIni;
                            const kolom = (kal.offset + tgl - 1) % 7; // 5 = Sabtu, 6 = Minggu

                            let warna;
                            if (isHariIni) warna = 'ring-2 ring-blue-400 font-bold text-blue-700';
                            else if (kolom === 6) warna = 'text-red-400';
                            else if (kolom === 5) warna = 'text-gray-400';
                            else warna = 'text-gray-600';

                            const bg = ada ? 'bg-blue-50 hover:bg-blue-100 cursor-pointer font-semibold' : '';
                            const dots = ada
                                ? `<span class="absolute bottom-1 flex gap-0.5">${list.slice(0, 3).map(() => '<span class="h-1 w-1 rounded-full bg-blue-500"></span>').join('')}</span>`
                                : '';

                            html += `<button type="button" ${ada ? `data-tgl="${tgl}"` : ''}
                                class="kalender-hari relative aspect-square flex items-center justify-center rounded-lg text-xs transition ${warna} ${bg}">
                                ${tgl}${dots}</button>`;
                        }
                        grid.innerHTML = html;

                        // reset panel detail tiap ganti bulan
                        detailBox.classList.add('hidden');
                        detailBox.innerHTML = '';

                        // kalau hari ini punya agenda, langsung tampilkan detailnya
                        if (kal.hariIni) {
                            grid.querySelector(`[data-tgl="${kal.hariIni}"]`)?.click();
                        }
                    }

                    function render() {
                        renderControls();
                        renderGrid();
                    }

                    function showDetail(tgl) {
                        const list = kal.marks[tgl] || [];

                        grid.querySelectorAll('.kalender-hari').forEach(b =>
                            b.classList.remove('outline', 'outline-2', 'outline-blue-500'));
                        grid.querySelector(`[data-tgl="${tgl}"]`)?.classList.add('outline', 'outline-2', 'outline-blue-500');

                        detailBox.classList.remove('hidden');
                        detailBox.innerHTML = list.map(a => {
                            const pegawai = (a.pegawai || []).map(esc).join(', ') || '-';
                            return `<a href="/agendas/${a.id}" class="block text-xs hover:bg-gray-50 -mx-2 px-2 py-2 rounded-lg transition">
                                <span class="block font-semibold text-gray-800">${esc(a.uraian)}</span>
                                <span class="flex gap-1.5 text-blue-500 mt-1">${ICON_LOKASI}<span>${esc(a.lokasi)}</span></span>
                                <span class="flex gap-1.5 text-blue-500">${ICON_PEGAWAI}<span>${pegawai}</span></span>
                                <span class="flex gap-1.5 text-blue-500">${ICON_TANGGAL}<span>${esc(a.periode)}</span></span>
                            </a>`;
                        }).join('');
                    }

                    async function muatBulan(ym) {
                        const id = ++requestId; // abaikan respons lama kalau user klik cepat
                        errBox.classList.add('hidden');
                        grid.classList.add('opacity-50');

                        try {
                            const res = await fetch(`${KALENDER_URL}?bulan=${encodeURIComponent(ym)}`, {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            if (!res.ok) throw new Error('HTTP ' + res.status);
                            const data = await res.json();
                            if (id !== requestId) return;
                            kal = data;
                            render();
                        } catch (e) {
                            if (id === requestId) errBox.classList.remove('hidden');
                            console.error('Kalender gagal dimuat:', e);
                        } finally {
                            if (id === requestId) grid.classList.remove('opacity-50');
                        }
                    }

                    const pad = n => String(n).padStart(2, '0');

                    grid.addEventListener('click', e => {
                        const btn = e.target.closest('[data-tgl]');
                        if (btn) showDetail(btn.dataset.tgl);
                    });
                    btnPrev.addEventListener('click', () => muatBulan(kal.prev));
                    btnNext.addEventListener('click', () => muatBulan(kal.next));
                    btnHariIni.addEventListener('click', () => muatBulan(''));
                    selBulan.addEventListener('change', () => muatBulan(`${selTahun.value}-${pad(selBulan.value)}`));
                    selTahun.addEventListener('change', () => muatBulan(`${selTahun.value}-${pad(selBulan.value)}`));

                    render();
                });
            </script>
        </div>
    </div>
</x-app-layout>