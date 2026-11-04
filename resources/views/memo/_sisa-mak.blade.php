{{-- Kartu sisa anggaran MAK + peringatan nominal. Dipakai di memo.create dan memo.edit.
     Syarat: ada <select id="mak-select"> (opsi memuat data-pagu, data-realisasi, data-sisa,
     data-outstanding, data-periode, data-per-tanggal) dan <input id="nominal-input">. --}}
<div id="sisa-mak-card" class="hidden mt-3 rounded-xl border border-gray-100 bg-gray-50/60 p-3.5">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
        <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Anggaran MAK ini</span>
        <span id="sisa-periode" class="text-[11px] text-gray-400"></span>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
        <div>
            <div class="text-[11px] text-gray-400">Pagu</div>
            <div id="sisa-pagu" class="font-medium text-gray-700 tabular-nums"></div>
        </div>
        <div>
            <div class="text-[11px] text-gray-400">Realisasi (FA)</div>
            <div id="sisa-realisasi" class="font-medium text-gray-700 tabular-nums"></div>
        </div>
        <div>
            <div class="text-[11px] text-gray-400">Sisa menurut FA</div>
            <div id="sisa-fa" class="font-medium text-gray-700 tabular-nums"></div>
        </div>
        <div>
            <div class="text-[11px] text-gray-400">Sudah diajukan di memo <span id="sisa-sejak" class="text-gray-300"></span></div>
            <div id="sisa-outstanding" class="font-medium text-gray-700 tabular-nums"></div>
        </div>
        <div class="rounded-lg bg-white border border-gray-200 px-2.5 py-1.5">
            <div class="text-[11px] text-gray-400">Sisa yang bisa dipakai</div>
            <div id="sisa-efektif" class="text-sm font-bold tabular-nums"></div>
        </div>
    </div>
</div>

<script>
    (function () {
        const sel = document.getElementById('mak-select');
        const nominal = document.getElementById('nominal-input');
        const card = document.getElementById('sisa-mak-card');
        if (!sel || !nominal || !card) return;

        const rp = n => (n < 0 ? '-Rp ' : 'Rp ') + Math.abs(n).toLocaleString('id-ID');
        const $ = id => document.getElementById(id);

        // Pesan di bawah kolom nominal
        const info = document.createElement('p');
        info.id = 'nominal-sisa-info';
        info.className = 'hidden text-xs mt-1.5';
        nominal.parentElement.insertAdjacentElement('afterend', info);

        function current() {
            const o = sel.selectedOptions[0];
            if (!o || sel.value === '' || sel.value === '__manual__' || o.dataset.sisa === undefined) return null;
            const c = {
                pagu: +o.dataset.pagu, real: +o.dataset.realisasi, sisa: +o.dataset.sisa,
                out: +(o.dataset.outstanding || 0), periode: o.dataset.periode || '', sejak: o.dataset.perTanggal || ''
            };
            c.efektif = c.sisa - c.out;
            return c;
        }

        function cek() {
            const c = current();
            const v = parseInt((nominal.value || '').replace(/\D/g, ''), 10) || 0;
            if (!c || v === 0) { info.classList.add('hidden'); return; }
            info.classList.remove('hidden', 'text-red-600', 'text-emerald-600');
            if (v > c.efektif) {
                info.classList.add('text-red-600');
                info.textContent = 'Nominal melebihi sisa MAK (' + rp(c.efektif) + '). Kurangi ' +
                    rp(v - Math.max(c.efektif, 0)) + ' atau pilih MAK lain.';
            } else {
                info.classList.add('text-emerald-600');
                info.textContent = 'Sisa MAK setelah memo ini: ' + rp(c.efektif - v) + '.';
            }
        }

        function render() {
            const c = current();
            if (!c) { card.classList.add('hidden'); cek(); return; }
            card.classList.remove('hidden');
            $('sisa-pagu').textContent = rp(c.pagu);
            $('sisa-realisasi').textContent = rp(c.real);
            $('sisa-fa').textContent = rp(c.sisa);
            $('sisa-outstanding').textContent = rp(c.out);
            $('sisa-sejak').textContent = c.sejak ? '(sejak ' + c.sejak + ')' : '';
            $('sisa-periode').textContent = c.periode ? 'FA periode ' + c.periode : '';
            const e = $('sisa-efektif');
            e.textContent = rp(c.efektif);
            e.className = 'text-sm font-bold tabular-nums ' +
                (c.efektif <= 0 ? 'text-red-600' : (c.pagu > 0 && c.efektif <= c.pagu * 0.1 ? 'text-amber-600' : 'text-emerald-600'));
            cek();
        }

        sel.addEventListener('change', render);
        nominal.addEventListener('input', cek);
        render();
    })();
</script>
