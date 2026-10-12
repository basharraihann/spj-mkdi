<x-app-layout title="Anggaran">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitor Anggaran') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Realisasi Anggaran per Akun</h2>
                    <p id="ang-meta" class="text-sm text-gray-500">Memuat data...</p>
                </div>
                @if ($canUpload)
                    <a href="{{ route('anggaran.upload') }}"
                        class="inline-flex justify-center items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 shrink-0 transition">
                        Upload / Perbarui Data
                    </a>
                @endif
            </div>

            <p id="ang-empty" class="hidden py-10 text-center text-gray-400 text-sm">
                Belum ada data anggaran. @if ($canUpload) Silakan upload file dari SAKTI. @endif
            </p>

            <div id="ang-body" class="hidden">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-5">
                    @foreach (['keg' => 'Kegiatan', 'ro' => 'RO / SubOutput', 'komp' => 'Komponen', 'sub' => 'SubKomponen', 'akun' => 'Akun'] as $k => $label)
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ $label }}</label>
                            <select id="f-{{ $k }}"
                                class="w-full rounded-xl border border-gray-200 py-2 px-3 text-sm text-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"></select>
                        </div>
                    @endforeach
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Cari item</label>
                        <input type="text" id="ang-q" placeholder="mis. konsumsi, perjalanan..." autocomplete="off"
                            class="w-full rounded-xl border border-gray-200 py-2 px-3 text-sm text-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
                    <div class="bg-gray-50 rounded-xl p-3"><p class="text-xs text-gray-500">Pagu</p><p id="s-pagu" class="font-semibold text-gray-900">-</p></div>
                    <div class="bg-gray-50 rounded-xl p-3"><p class="text-xs text-gray-500">Realisasi</p><p id="s-real" class="font-semibold text-gray-900">-</p></div>
                    <div class="bg-gray-50 rounded-xl p-3"><p class="text-xs text-gray-500">Sisa</p><p id="s-sisa" class="font-semibold text-gray-900">-</p></div>
                    <div class="bg-gray-50 rounded-xl p-3"><p class="text-xs text-gray-500">% Realisasi</p><p id="s-pct" class="font-semibold text-gray-900">-</p></div>
                </div>

                <div class="rounded-xl border border-gray-100 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500">
                            <tr>
                                <th class="text-left px-4 py-2.5 font-medium">Jalur</th>
                                <th class="text-left px-4 py-2.5 font-medium">Akun</th>
                                <th class="text-left px-4 py-2.5 font-medium">Item</th>
                                <th class="text-right px-4 py-2.5 font-medium">Pagu</th>
                                <th class="text-right px-4 py-2.5 font-medium">Realisasi</th>
                                <th class="text-right px-4 py-2.5 font-medium">Sisa</th>
                                <th class="text-right px-4 py-2.5 font-medium">%</th>
                            </tr>
                        </thead>
                        <tbody id="ang-tb" class="divide-y divide-gray-50"></tbody>
                    </table>
                </div>
                <p id="ang-cnt" class="text-xs text-gray-400 mt-3"></p>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const CH = ['keg', 'ro', 'komp', 'sub', 'akun'];
            const LIMIT = 500;
            const $ = (id) => document.getElementById(id);
            const rp = (n) => new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const F = { keg: '', ro: '', komp: '', sub: '', akun: '' };
            let items = [];

            // Filter berantai: pilihan di level bawah hanya muncul kalau cocok dengan level atasnya.
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

            function render() {
                const q = $('ang-q').value.trim().toLowerCase();
                const rows = items.filter((it) => match(it, 5) &&
                    (!q || (it.item_uraian + ' ' + it.akun_uraian).toLowerCase().includes(q)));

                let P = 0, R = 0;
                rows.forEach((x) => { P += x.pagu; R += x.realisasi; });
                $('s-pagu').textContent = 'Rp ' + rp(P);
                $('s-real').textContent = 'Rp ' + rp(R);
                $('s-sisa').textContent = 'Rp ' + rp(P - R);
                $('s-pct').textContent = P ? (R / P * 100).toFixed(1) + '%' : '-';

                $('ang-tb').innerHTML = rows.slice(0, LIMIT).map((x) => {
                    const pill = (t) => `<span class="inline-block font-mono text-[11px] text-gray-600 bg-gray-50 border border-gray-100 rounded px-1.5 py-0.5 mr-1">${esc(t)}</span>`;
                    return `<tr class="hover:bg-gray-50/60">
                        <td class="px-4 py-2.5 whitespace-nowrap">${pill(x.keg_kode)}${pill(x.ro_kode)}${pill(x.komp_kode)}${pill(x.sub_kode)}</td>
                        <td class="px-4 py-2.5"><span class="font-mono text-xs text-gray-700">${esc(x.akun_kode)}</span><div class="text-xs text-gray-400">${esc(x.akun_uraian)}</div></td>
                        <td class="px-4 py-2.5 text-gray-600">${esc(x.item_kode)}. ${esc(x.item_uraian)}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">${rp(x.pagu)}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">${rp(x.realisasi)}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">${rp(x.pagu - x.realisasi)}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">${x.pagu ? (x.realisasi / x.pagu * 100).toFixed(1) : '0.0'}%</td>
                    </tr>`;
                }).join('');

                $('ang-cnt').textContent = `Menampilkan ${Math.min(LIMIT, rows.length)} dari ${rows.length} item` +
                    (rows.length > LIMIT ? ' (persempit filter untuk melihat sisanya)' : '');
            }

            CH.forEach((k, i) => {
                $('f-' + k).addEventListener('change', function () {
                    F[k] = this.value;
                    for (let j = i + 1; j < CH.length; j++) F[CH[j]] = '';
                    fillSel();
                    render();
                });
            });
            $('ang-q').addEventListener('input', render);

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
