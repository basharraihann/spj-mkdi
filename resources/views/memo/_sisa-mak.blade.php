{{-- Kartu sisa anggaran MAK untuk memo (create & edit).
    Desain sama dengan kartu di agenda, tapi "kebutuhan" diambil live dari #nominal-input.

    PENTING: file ini TIDAK BOLEH @include partial form atau dirinya sendiri (rekursi).

    Sumber data:
    - <select id="mak-select"> : data-pagu, data-realisasi, data-sisa, data-outstanding,
                                 data-periode, data-per-tanggal, data-uraian-belanja
    - <input id="nominal-input"> : nominal yang diajukan
    - <span id="sisa-mak-koreksi" data-mak data-nominal> (opsional, edit saja):
      memo ini sudah masuk realisasi FA, jadi nominalnya dikembalikan ke sisa
      kalau MAK yang dipilih masih MAK aslinya.
--}}
<div id="sisa-mak-card" class="mt-4 rounded-xl border border-gray-200 bg-white overflow-hidden">

    <div id="sisa-mak-empty" class="px-4 py-3 text-xs text-gray-400">
        Pilih MAK dari daftar untuk melihat pagu dan sisa anggaran.
    </div>

    <div id="sisa-mak-body" class="hidden">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/70">
            <div id="sisa-mak-kode" class="font-mono text-[11px] font-semibold text-gray-800 break-all"></div>
            <div id="sisa-mak-uraian" class="text-xs text-gray-500 mt-0.5 leading-snug"></div>
            <div id="sisa-mak-periode" class="text-[11px] text-gray-400 mt-1"></div>
        </div>

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

        {{-- Perbandingan dengan nominal memo (muncul setelah nominal diisi) --}}
        <div id="sisa-mak-banding" class="hidden px-4 py-3 border-t border-gray-100">
            <div class="flex items-center justify-between gap-3">
                <span class="text-xs text-gray-500">Nominal memo ini</span>
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
        // Partial ini diletakkan DI ATAS kolom nominal, jadi tunggu halaman selesai dimuat.
        function init() {
            const card = document.getElementById('sisa-mak-card');
            const select = document.getElementById('mak-select');
            const nominal = document.getElementById('nominal-input');
            if (!card || !select || !nominal) return;

            const koreksiEl = document.getElementById('sisa-mak-koreksi');
            const koreksiMak = koreksiEl ? koreksiEl.dataset.mak : null;
            const koreksiNominal = koreksiEl ? (parseInt(koreksiEl.dataset.nominal || '0', 10) || 0) : 0;

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

                el('sisa-mak-empty').classList.toggle('hidden', !!adaData);
                el('sisa-mak-body').classList.toggle('hidden', !adaData);

                if (!adaData) {
                    el('sisa-mak-empty').textContent = (opt && opt.value === '__manual__')
                        ? 'MAK diisi manual, data pagu dan sisa anggaran tidak tersedia.'
                        : 'Pilih MAK dari daftar untuk melihat pagu dan sisa anggaran.';
                    return;
                }

                const kebutuhan = angka((nominal.value || '').replace(/\D/g, ''));

                // Koreksi edit: nominal memo ini sudah terhitung di realisasi FA
                const koreksi = (koreksiMak && opt.value === koreksiMak) ? koreksiNominal : 0;

                const pagu = angka(opt.dataset.pagu);
                const realisasi = angka(opt.dataset.realisasi) - koreksi;
                const sisa = angka(opt.dataset.sisa) + koreksi;
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
                efektifEl.classList.toggle('text-red-600', efektif <= 0 || !cukup);
                efektifEl.classList.toggle('text-emerald-600', efektif > 0 && cukup);

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
                        ? 'Sisa setelah memo ini ' + rp(selisih)
                        : 'Kurang ' + rp(Math.abs(selisih));
                    selisihEl.classList.toggle('text-emerald-600', cukup);
                    selisihEl.classList.toggle('text-red-600', !cukup);
                }
            }

            select.addEventListener('change', render);
            nominal.addEventListener('input', render);
            render();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
</script>