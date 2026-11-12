{{--
    Kartu sisa anggaran MAK.
    Di-include dari partial form MAK lewat:
        @includeWhen(isset($totalBiaya), 'agendas.partials.sisa-mak-card')

    PENTING: file ini TIDAK BOLEH @include partial form MAK atau dirinya sendiri,
    kalau tidak akan rekursi tanpa akhir dan memory habis.

    Sumber data: atribut data-* pada <select id="mak-select"> (pagu, realisasi,
    sisa, outstanding, periode, per-tanggal). Kartu diperbarui lewat JS tiap kali
    MAK dipilih.
--}}
@php
    $kebutuhanCard = (int) ($kebutuhan ?? ($totalBiaya ?? 0));
    $fmtRpCard = fn($n) => ($n < 0 ? '-Rp ' : 'Rp ') . number_format(abs((int) $n), 0, ',', '.');
@endphp

<div id="sisa-mak-card" class="mt-4 rounded-xl border border-gray-200 bg-white overflow-hidden"
    data-kebutuhan="{{ $kebutuhanCard }}">

    {{-- Kondisi awal / MAK manual: tidak ada data anggaran --}}
    <div id="sisa-mak-empty" class="px-4 py-3 text-xs text-gray-400">
        Pilih MAK dari daftar untuk melihat pagu dan sisa anggaran.
    </div>

    <div id="sisa-mak-body" class="hidden">
        {{-- Header: MAK terpilih + periode data --}}
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/70">
            <div id="sisa-mak-kode" class="font-mono text-[11px] font-semibold text-gray-800 break-all"></div>
            <div id="sisa-mak-uraian" class="text-xs text-gray-500 mt-0.5 leading-snug"></div>
            <div id="sisa-mak-periode" class="text-[11px] text-gray-400 mt-1"></div>
        </div>

        {{-- Rincian angka --}}
        <dl class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-y sm:divide-y-0 divide-gray-100">
            <div class="px-4 py-3">
                <dt class="text-[11px] text-gray-400">Pagu</dt>
                <dd id="sisa-mak-pagu" class="mt-1 text-sm font-semibold text-gray-800 tabular-nums"></dd>
            </div>
            <div class="px-4 py-3">
                <dt class="text-[11px] text-gray-400">Realisasi</dt>
                <dd id="sisa-mak-realisasi" class="mt-1 text-sm font-semibold text-gray-800 tabular-nums"></dd>
            </div>
            <div class="px-4 py-3">
                <dt class="text-[11px] text-gray-400">Outstanding</dt>
                <dd id="sisa-mak-outstanding" class="mt-1 text-sm font-semibold text-amber-600 tabular-nums"></dd>
            </div>
            <div class="px-4 py-3">
                <dt class="text-[11px] text-gray-400">Sisa efektif</dt>
                <dd id="sisa-mak-efektif" class="mt-1 text-sm font-semibold tabular-nums"></dd>
            </div>
        </dl>

        {{-- Perbandingan dengan total biaya agenda --}}
        <div id="sisa-mak-banding" class="px-4 py-3 border-t border-gray-100">
            <div class="flex items-center justify-between gap-3">
                <span class="text-xs text-gray-500">Total biaya agenda</span>
                <span id="sisa-mak-kebutuhan" class="text-sm font-semibold text-gray-800 tabular-nums"></span>
            </div>

            <div class="mt-2 h-2 rounded-full bg-gray-100 overflow-hidden">
                <div id="sisa-mak-bar" class="h-full rounded-full transition-all" style="width: 0%"></div>
            </div>

            <div class="mt-2 flex items-center justify-between gap-3">
                <span id="sisa-mak-status"
                    class="rounded-full border px-2 py-0.5 text-[11px] font-semibold whitespace-nowrap"></span>
                <span id="sisa-mak-selisih" class="text-xs tabular-nums"></span>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const card = document.getElementById('sisa-mak-card');
        const select = document.getElementById('mak-select');
        if (!card || !select) return;

        const kebutuhan = parseInt(card.dataset.kebutuhan || '0', 10) || 0;
        const el = (id) => document.getElementById(id);

        const rp = (n) => (n < 0 ? '-Rp ' : 'Rp ') + Math.abs(Math.round(n)).toLocaleString('id-ID');
        const angka = (v) => parseInt(v || '0', 10) || 0;

        const kelas = {
            ok: ['bg-emerald-50', 'border-emerald-100', 'text-emerald-600'],
            bad: ['bg-red-50', 'border-red-100', 'text-red-600'],
        };

        function setStatus(cukup) {
            const badge = el('sisa-mak-status');
            badge.classList.remove(...kelas.ok, ...kelas.bad);
            badge.classList.add(...(cukup ? kelas.ok : kelas.bad));
            badge.textContent = cukup ? 'Anggaran cukup' : 'Anggaran tidak cukup';
        }

        function render() {
            const opt = select.selectedOptions[0];
            const adaData = opt && opt.value && opt.value !== '__manual__' && opt.dataset.pagu !== undefined;

            el('sisa-mak-empty').classList.toggle('hidden', adaData);
            el('sisa-mak-body').classList.toggle('hidden', !adaData);

            if (!adaData) {
                el('sisa-mak-empty').textContent = (opt && opt.value === '__manual__')
                    ? 'MAK diisi manual, data pagu dan sisa anggaran tidak tersedia.'
                    : 'Pilih MAK dari daftar untuk melihat pagu dan sisa anggaran.';
                return;
            }

            const pagu = angka(opt.dataset.pagu);
            const realisasi = angka(opt.dataset.realisasi);
            const sisa = angka(opt.dataset.sisa);
            const outstanding = angka(opt.dataset.outstanding);
            const efektif = sisa - outstanding;
            const selisih = efektif - kebutuhan;
            const cukup = kebutuhan <= 0 || efektif >= kebutuhan;

            el('sisa-mak-kode').textContent = opt.value;
            el('sisa-mak-uraian').textContent = opt.dataset.uraianBelanja || '';

            const periode = [];
            if (opt.dataset.periode) periode.push('Periode ' + opt.dataset.periode);
            if (opt.dataset.perTanggal) periode.push('per ' + opt.dataset.perTanggal);
            el('sisa-mak-periode').textContent = periode.join(', ');

            el('sisa-mak-pagu').textContent = rp(pagu);
            el('sisa-mak-realisasi').textContent = rp(realisasi);
            el('sisa-mak-outstanding').textContent = rp(outstanding);

            const efektifEl = el('sisa-mak-efektif');
            efektifEl.textContent = rp(efektif);
            efektifEl.classList.toggle('text-red-600', !cukup);
            efektifEl.classList.toggle('text-emerald-600', cukup);

            // Bagian perbandingan hanya relevan kalau total biaya sudah ada
            el('sisa-mak-banding').classList.toggle('hidden', kebutuhan <= 0);
            if (kebutuhan > 0) {
                el('sisa-mak-kebutuhan').textContent = rp(kebutuhan);

                const persen = efektif <= 0 ? 100 : Math.min(100, (kebutuhan / efektif) * 100);
                const bar = el('sisa-mak-bar');
                bar.style.width = persen.toFixed(1) + '%';
                bar.classList.toggle('bg-emerald-500', cukup);
                bar.classList.toggle('bg-red-500', !cukup);

                setStatus(cukup);

                const selisihEl = el('sisa-mak-selisih');
                selisihEl.textContent = cukup
                    ? 'Sisa setelah agenda ini ' + rp(selisih)
                    : 'Kurang ' + rp(Math.abs(selisih));
                selisihEl.classList.toggle('text-emerald-600', cukup);
                selisihEl.classList.toggle('text-red-600', !cukup);
            }
        }

        select.addEventListener('change', render);
        render(); // isi saat halaman dibuka (mode edit, MAK sudah terpilih)
    })();
</script>