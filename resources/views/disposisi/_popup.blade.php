{{-- Popup detail + feedback disposisi. Variabel: $tanggal, $bukaSurat, $tampil --}}
<style>#modal-dispo[hidden] { display: none; }</style>

    {{-- ================= Popup detail + feedback ================= --}}
    <div id="modal-dispo" hidden class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" style="background: rgb(15 23 42 / .45)">
        <div class="kd-modal-box w-full sm:max-w-2xl overflow-y-auto rounded-t-3xl sm:rounded-2xl bg-white shadow-2xl">
            <div class="sticky top-0 z-10 flex items-start justify-between gap-3 border-b border-gray-100 bg-white/95 backdrop-blur px-5 py-4">
                <div class="min-w-0">
                    <div id="m-chip" class="mb-1 flex flex-wrap gap-1.5"></div>
                    <h3 id="m-perihal" class="text-base font-semibold text-gray-900"></h3>
                    <p id="m-nomor" class="text-xs text-gray-500"></p>
                </div>
                <button type="button" data-tutup class="kd-btn-ghost kd-ikon" aria-label="Tutup">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div id="m-isi" class="px-5 py-4 space-y-4 text-sm">
                <p class="text-gray-400">Memuat…</p>
            </div>
            <form id="m-form" method="POST" class="border-t border-gray-100 bg-slate-50/60 px-5 py-4 space-y-2">
                @csrf
                <input type="hidden" name="_dari" value="kalender">
                <input type="hidden" name="_tampil" value="{{ $tampil ?? 'hari-ini' }}">
                <input type="hidden" name="_tanggal" id="m-tanggal" value="{{ $tanggal }}">
                <label for="m-feedback" class="kd-label">Tulis feedback</label>
                <textarea id="m-feedback" name="isi" rows="2" required maxlength="2000" class="kd-input" style="background:#fff"
                    placeholder="mis. Siap hadir / sudah ditindaklanjuti / diwakilkan oleh …"></textarea>
                @error('isi')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <label id="m-selesai-wrap" class="hidden items-center gap-2 text-xs text-gray-600">
                        <input type="checkbox" name="tandai_selesai" value="1" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        Sekalian tandai disposisi saya selesai
                    </label>
                    <div class="flex gap-2 sm:ml-auto">
                        <a id="m-detail" href="#" class="kd-btn-ghost flex-1 sm:flex-none border border-gray-200 bg-white">Detail lengkap</a>
                        <button class="kd-btn-primary flex-1 sm:flex-none">Kirim Feedback</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<script>
        (function () {
            const URL_DETAIL = @json(route('kalender.detail', ['surat' => '__ID__']));
            const BUKA = @json($bukaSurat);

            const $ = (id) => document.getElementById(id);
            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const ymd = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            const stKelas = { baru: 'kd-st-baru', dibaca: 'kd-st-dibaca', diteruskan: 'kd-st-diteruskan', selesai: 'kd-st-selesai' };

            // ---------------- Popup ----------------
            const modal = $('modal-dispo');
            const tutup = () => { modal.hidden = true; document.body.style.overflow = ''; };
            modal.addEventListener('click', (e) => { if (e.target === modal || e.target.closest('[data-tutup]')) tutup(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.hidden) tutup(); });

            async function buka(id) {
                modal.hidden = false; document.body.style.overflow = 'hidden';
                $('m-chip').innerHTML = ''; $('m-nomor').textContent = ''; $('m-perihal').textContent = 'Memuat…';
                $('m-isi').innerHTML = '<p class="text-gray-400">Memuat…</p>';
                try {
                    const r = await fetch(URL_DETAIL.replace('__ID__', id), { headers: { Accept: 'application/json' } });
                    if (!r.ok) throw new Error(r.status);
                    tampil(await r.json());
                } catch (e) {
                    $('m-perihal').textContent = 'Gagal memuat';
                    $('m-isi').innerHTML = '<p class="text-red-600">Detail disposisi tidak bisa dibuka.</p>';
                }
            }

            function tampil(s) {
                $('m-chip').innerHTML = `<span class="kd-chip kd-chip-gray">${esc(s.sifat)}</span>` +
                    `<span class="kd-st ${s.status === 'selesai' ? 'kd-st-selesai' : 'kd-st-baru'}">${s.status === 'selesai' ? 'Surat selesai' : 'Proses'}</span>`;
                $('m-perihal').textContent = s.perihal;
                $('m-nomor').textContent = s.nomor_surat + ' · dari ' + s.asal_surat;
                $('m-form').action = s.url_feedback;
                $('m-detail').href = s.url_detail;
                $('m-selesai-wrap').classList.toggle('hidden', !s.bisa_selesai);
                $('m-selesai-wrap').classList.toggle('flex', !!s.bisa_selesai);

                const jadwal = s.tanggal_kegiatan ? `
                    <div class="flex items-start gap-3 rounded-xl bg-blue-50 px-4 py-3 text-blue-900">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <div><b>${esc(s.tanggal_kegiatan)}</b>${s.waktu ? ' · ' + esc(s.waktu) : ''}${s.tempat ? `<div class="text-xs text-blue-700">${esc(s.tempat)}</div>` : ''}</div>
                    </div>` : '';
                const berkas = s.url_berkas ? `<a href="${esc(s.url_berkas)}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:underline">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>Lihat file surat</a>` : '';

                const alur = s.disposisi.map((d) => `
                    <li class="rounded-xl border ${d.saya ? 'border-blue-300 bg-blue-50/50' : 'border-gray-200'} px-3.5 py-2.5">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <span class="text-gray-600">${esc(d.dari)} <span class="text-gray-400">→</span>
                                <b class="text-gray-900">${esc(d.kepada)}</b>${d.saya ? ' <span class="ml-1 rounded bg-blue-600 px-1.5 py-0.5 text-[10px] font-semibold text-white">Anda</span>' : ''}</span>
                            <span class="kd-st ${stKelas[d.status] || 'kd-st-keluar'}">${esc(d.status_label)}</span>
                        </div>
                        ${d.instruksi.length ? `<div class="mt-1.5 flex flex-wrap gap-1">${d.instruksi.map((i) => `<span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[11px] text-slate-700">${esc(i)}</span>`).join('')}</div>` : ''}
                        ${d.catatan ? `<p class="mt-1.5 text-gray-700 whitespace-pre-line">${esc(d.catatan)}</p>` : ''}
                        ${d.batas ? `<p class="mt-1 text-[11px] ${d.terlambat ? 'text-red-600 font-semibold' : 'text-gray-500'}">Batas ${esc(d.batas)}${d.terlambat ? ' · terlambat' : ''}</p>` : ''}
                    </li>`).join('');

                const fb = s.feedback.length ? s.feedback.map((f) => `
                    <li class="flex gap-2.5 ${f.saya ? 'flex-row-reverse text-right' : ''}">
                        ${f.foto
                            ? `<img src="${esc(f.foto)}" alt="${esc(f.nama)}" title="${esc(f.nama)}" class="h-8 w-8 shrink-0 rounded-full object-cover">`
                            : `<span title="${esc(f.nama)}" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${f.saya ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700'} text-[11px] font-bold">${esc((f.nama || '?').trim().charAt(0).toUpperCase())}</span>`}
                        <div class="max-w-[85%] rounded-2xl ${f.saya ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-800'} px-3.5 py-2">
                            <p class="text-[11px] ${f.saya ? 'text-blue-100' : 'text-slate-500'}">${esc(f.nama)} · ${esc(f.waktu)}</p>
                            <p class="whitespace-pre-line text-left">${esc(f.isi)}</p>
                        </div>
                    </li>`).join('') : '<li class="text-gray-400">Belum ada feedback.</li>';

                $('m-isi').innerHTML = `${jadwal}${berkas}
                    <div><p class="kd-label">Alur disposisi</p><ul class="space-y-2">${alur}</ul></div>
                    <div><p class="kd-label">Feedback</p><ul class="space-y-2.5">${fb}</ul></div>`;
            }

            document.querySelectorAll('[data-buka]').forEach((el) => el.addEventListener('click', (e) => {
                e.stopPropagation();
                buka(el.dataset.buka);
            }));
            if (BUKA) buka(BUKA);
    })();
</script>
