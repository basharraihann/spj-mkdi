{{--
Popup detail disposisi + form feedback. Dipakai bersama oleh tab Kalender, Daftar, dan Jadwal Pegawai.

Cara pakai:
@include('disposisi._popup', ['dari' => 'kalender', 'tanggal' => $tanggal, 'bukaSurat' => $bukaSurat])
@include('disposisi._popup', ['dari' => 'daftar'])
@include('disposisi._popup', ['dari' => 'jadwal'])

Cara membuka popup:
- elemen apa pun dengan atribut data-buka="ID_SURAT"
- atau lewat JS: window.dispoPopup.buka(idSurat)
- atau lewat URL: ?buka=ID_SURAT (dipakai oleh link notifikasi)

Supaya terasa instan, data surat diambil lebih dulu (prefetch) saat kursor/jari mendekati
elemen data-buka dan saat halaman idle. Prefetch memakai ?intip=1 sehingga TIDAK menandai
disposisi sebagai dibaca; penandaan baru terjadi saat popup benar-benar dibuka.

Membutuhkan CSS dari disposisi/_gaya (kelas pp-*, kd-*) untuk bagian info/zoom/berkas.
--}}
@php
    $dari = $dari ?? 'kalender';
    $tanggal = $tanggal ?? '';
    $bukaAwal = (int) ($bukaSurat ?? request('buka', 0));
@endphp

<div id="modal-dispo" hidden class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4"
    style="background: rgb(15 23 42 / .5)">
    <div class="pp-box w-full rounded-t-3xl bg-white shadow-2xl sm:max-w-2xl sm:rounded-2xl">
        <div class="pp-accent"></div>

        {{-- Kepala --}}
        <div class="pp-head">
            <div class="min-w-0">
                <div id="m-chip" class="mb-2 flex flex-wrap gap-1.5"></div>
                <h3 id="m-perihal" class="pp-title"></h3>
                <p id="m-nomor" class="pp-sub"></p>
            </div>
            <button type="button" data-tutup class="kd-btn-ghost kd-ikon" aria-label="Tutup">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Isi (diisi lewat JS) --}}
        <div id="m-isi" class="pp-body text-sm"></div>

        {{-- Form feedback --}}
        <form id="m-form" method="POST" class="pp-foot space-y-2">
            @csrf
            <input type="hidden" name="_dari" value="{{ $dari }}">
            <input type="hidden" name="_tanggal" id="m-tanggal" value="{{ $tanggal }}">

            <label for="m-feedback" class="kd-label">Tulis feedback</label>
            <textarea id="m-feedback" name="isi" rows="2" required maxlength="2000" class="kd-input"
                style="background: #fff"
                placeholder="mis. Siap hadir / sudah ditindaklanjuti / diwakilkan oleh …"></textarea>
            @error('isi')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror

            <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
                <label id="m-selesai-wrap" class="hidden items-center gap-2 text-xs text-gray-600">
                    <input type="checkbox" name="tandai_selesai" value="1"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Sekalian tandai disposisi saya selesai
                </label>
                <div class="flex gap-2 sm:ml-auto">
                    <a id="m-detail" href="#"
                        class="kd-btn-ghost flex-1 border border-gray-200 bg-white sm:flex-none">Detail lengkap</a>
                    <button class="kd-btn-primary flex-1 sm:flex-none">Kirim Feedback</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const URL_DETAIL = @json(route('kalender.detail', ['surat' => '__ID__']));
        const BUKA = @json($bukaAwal);

        const $ = (id) => document.getElementById(id);
        const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));
        const inisial = (nama) => esc((nama || '?').trim().charAt(0).toUpperCase());

        // Warna status & sifat: sama dengan halaman detail (show.blade) dan alur (_node.blade)
        const WARNA = {
            baru: ['bg-red-500', 'bg-red-50 text-red-700 border-red-200'],
            dibaca: ['bg-amber-500', 'bg-amber-50 text-amber-700 border-amber-200'],
            diteruskan: ['bg-blue-500', 'bg-blue-50 text-blue-700 border-blue-200'],
            selesai: ['bg-emerald-500', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        };
        const WARNA_LAIN = ['bg-gray-400', 'bg-gray-50 text-gray-600 border-gray-200'];
        const SIFAT = {
            biasa: 'bg-slate-100 text-slate-600 ring-slate-200',
            segera: 'bg-amber-50 text-amber-800 ring-amber-200',
            sangat_segera: 'bg-red-50 text-red-700 ring-red-200',
            rahasia: 'bg-purple-50 text-purple-700 ring-purple-200',
        };

        const modal = $('modal-dispo');
        const cache = new Map();     // id -> data detail
        const inflight = new Map();  // id -> Promise prefetch yang sedang jalan
        let aktif = null;            // id surat yang sedang tampil di popup

        const tutup = () => {
            modal.hidden = true;
            aktif = null;
            document.body.style.overflow = '';

            // Buang ?buka= dari URL supaya refresh tidak membuka popup lagi
            try {
                const u = new URL(location.href);
                if (u.searchParams.has('buka')) {
                    u.searchParams.delete('buka');
                    history.replaceState(null, '', u);
                }
            } catch (e) { }
        };

        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.closest('[data-tutup]')) tutup();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.hidden) tutup();
        });

        // ---------------- Pengambilan data ----------------

        function ambil(id, intip) {
            const url = URL_DETAIL.replace('__ID__', id) + (intip ? '?intip=1' : '');
            return fetch(url, { headers: { Accept: 'application/json' } }).then((r) => {
                if (!r.ok) throw new Error(r.status);
                return r.json();
            });
        }

        /** Ambil data di latar belakang (tidak menandai dibaca). */
        function intip(id) {
            id = String(id);
            if (!id || cache.has(id) || inflight.has(id)) return;
            const p = ambil(id, true)
                .then((d) => { if (!cache.has(id)) cache.set(id, d); })
                .catch(() => { })
                .finally(() => inflight.delete(id));
            inflight.set(id, p);
        }

        // ---------------- Membuka popup ----------------

        const kerangka = () => `
            <div class="animate-pulse space-y-3">
                <div class="h-14 rounded-xl bg-slate-100"></div>
                <div class="h-4 w-1/3 rounded bg-slate-100"></div>
                <div class="h-20 rounded-xl bg-slate-100"></div>
                <div class="h-4 w-1/4 rounded bg-slate-100"></div>
                <div class="h-12 rounded-xl bg-slate-100"></div>
            </div>`;

        async function buka(id) {
            id = String(id);
            aktif = id;
            modal.hidden = false;
            document.body.style.overflow = 'hidden';

            const awal = cache.get(id);
            if (awal) {
                // Sudah ada di cache: tampil seketika, tanpa loading
                renderDetail(awal);
            } else {
                $('m-chip').innerHTML = '';
                $('m-nomor').textContent = '';
                $('m-perihal').innerHTML = '<span class="block h-5 w-2/3 animate-pulse rounded bg-slate-100"></span>';
                $('m-isi').innerHTML = kerangka();

                // Kalau prefetch-nya sedang jalan, tunggu itu dulu (lebih cepat daripada mulai dari nol)
                if (inflight.has(id)) {
                    await inflight.get(id);
                    if (aktif === id && cache.has(id)) renderDetail(cache.get(id));
                }
            }

            // Ambil data terbaru. Request ini yang menandai disposisi sebagai dibaca.
            try {
                const segar = await ambil(id, false);
                if (aktif !== id) return;
                const sebelum = cache.get(id);
                cache.set(id, segar);
                if (!sebelum || JSON.stringify(sebelum) !== JSON.stringify(segar)) {
                    renderDetail(segar);
                }
            } catch (e) {
                if (aktif === id && !cache.has(id)) {
                    $('m-perihal').textContent = 'Gagal memuat';
                    $('m-isi').innerHTML = '<p class="text-red-600">Detail disposisi tidak bisa dibuka.</p>';
                }
            }
        }

        // ---------------- Tampilan ----------------

        function renderDetail(s) {
            const isi = $('m-isi');
            const gulir = isi.scrollTop;
            const selesai = s.status === 'selesai';
            const ico = (d) => `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="${d}"/></svg>`;
            const IC = {
                tgl: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                pin: 'M17.66 16.66L13.41 20.9a2 2 0 01-2.82 0l-4.25-4.24a8 8 0 1111.32 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z',
                org: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                vid: 'M15 10l4.55-2.28A1 1 0 0121 8.62v6.76a1 1 0 01-1.45.9L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
            };

            // Link rapat online: dari link_daring atau URL yang terselip di tempat
            let tempat = String(s.tempat || '').trim();
            let link = s.link_daring || '';
            if (!link) {
                const m = tempat.match(/https?:\/\/\S+/i);
                if (m) { link = m[0]; tempat = tempat.replace(m[0], '').trim(); }
            }
            link = /^https?:\/\//i.test(link) ? link : '';
            let host = '';
            try { host = link ? new URL(link).hostname : ''; } catch (e) { host = link; }
            if (!tempat && link) tempat = 'Online';

            // Kepala popup: badge sifat + status, sama seperti halaman detail
            $('m-chip').innerHTML =
                `<span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset ${SIFAT[s.sifat_key] || SIFAT.biasa}">${esc(s.sifat)}</span>` +
                `<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset ${selesai ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-blue-50 text-blue-700 ring-blue-200'}">` +
                `<span class="h-1.5 w-1.5 rounded-full ${selesai ? 'bg-emerald-500' : 'bg-blue-500'}"></span>${selesai ? 'Selesai' : 'Proses'}</span>`;
            $('m-perihal').textContent = s.perihal;
            $('m-nomor').textContent = s.nomor_surat;

            // Form feedback
            $('m-form').action = s.url_feedback;
            $('m-detail').href = s.url_detail;
            $('m-selesai-wrap').classList.toggle('hidden', !s.bisa_selesai);
            $('m-selesai-wrap').classList.toggle('flex', !!s.bisa_selesai);

            // Kartu info: jadwal, tempat, asal surat
            const info = [];
            if (s.tanggal_kegiatan) {
                info.push(`<div class="pp-info"><span class="pp-ic">${ico(IC.tgl)}</span><div><p class="pp-lbl">Jadwal</p><p class="pp-val">${esc(s.tanggal_kegiatan)}${s.waktu ? `<small>${esc(s.waktu)}</small>` : ''}</p></div></div>`);
            }
            if (tempat) {
                info.push(`<div class="pp-info"><span class="pp-ic">${ico(link && /^online$/i.test(tempat) ? IC.vid : IC.pin)}</span><div><p class="pp-lbl">Tempat</p><p class="pp-val">${esc(tempat)}</p></div></div>`);
            }
            if (s.asal_surat) {
                info.push(`<div class="pp-info ${info.length % 2 === 0 ? 'full' : ''}"><span class="pp-ic">${ico(IC.org)}</span><div><p class="pp-lbl">Asal surat</p><p class="pp-val">${esc(s.asal_surat)}</p></div></div>`);
            }
            const infoHtml = info.length ? `<div class="pp-grid">${info.join('')}</div>` : '';

            // Tombol gabung rapat online
            const zoom = link ? `
                <a href="${esc(link)}" target="_blank" rel="noopener noreferrer" class="pp-zoom">
                    <div style="display:flex;align-items:center;gap:.7rem;min-width:0">
                        <span class="pp-ic">${ico(IC.vid)}</span>
                        <div class="t"><b>Gabung rapat online</b><span class="h">${esc(host)}</span></div>
                    </div>
                    <span class="go">Buka ↗</span>
                </a>` : '';

            // Berkas surat
            const berkas = s.url_berkas ? `
                <a href="${esc(s.url_berkas)}" target="_blank" rel="noopener noreferrer" class="pp-file">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    Lihat file surat
                </a>` : '';

            // Alur disposisi: kartu dan warna status sama dengan _node.blade
            const alur = s.disposisi.map((d) => {
                const w = WARNA[d.status] || WARNA_LAIN;
                return `
                <li class="relative pl-6">
                    <span class="absolute left-0 top-4 h-3 w-3 rounded-full ring-4 ring-white ${w[0]}"></span>
                    <div class="rounded-xl border ${d.saya ? 'border-blue-300 bg-blue-50/30' : 'border-gray-200 bg-white'} p-3.5">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="text-sm">
                                <span class="text-gray-500">${esc(d.dari || '-')}</span>
                                <span class="mx-1 text-gray-400">→</span>
                                <span class="font-semibold text-gray-900">${esc(d.kepada || '-')}</span>
                                ${d.saya ? '<span class="ml-1 rounded bg-blue-600 px-1.5 py-0.5 text-[10px] font-semibold text-white">Anda</span>' : ''}
                                ${d.ikut ? '<span class="ml-1 rounded bg-blue-50 px-1.5 py-0.5 text-[10px] font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">Ikut</span>' : ''}
                                ${d.kepada_unit ? `<div class="text-xs text-gray-400">${esc(d.kepada_unit)}</div>` : ''}
                            </div>
                            <span class="whitespace-nowrap rounded-full border px-2 py-0.5 text-[11px] font-semibold ${w[1]}">${esc(d.status_label)}</span>
                        </div>
                        ${d.instruksi.length ? `<div class="mt-2 flex flex-wrap gap-1">${d.instruksi.map((i) => `<span class="rounded-md bg-gray-100 px-1.5 py-0.5 text-[11px] text-gray-700">${esc(i)}</span>`).join('')}</div>` : ''}
                        ${d.catatan ? `<p class="mt-2 whitespace-pre-line text-sm text-gray-700">${esc(d.catatan)}</p>` : ''}
                        ${d.batas ? `<p class="mt-2 text-[11px] ${d.terlambat ? 'font-semibold text-red-600' : 'text-gray-500'}">Batas ${esc(d.batas)}${d.terlambat ? ' (terlambat)' : ''}</p>` : ''}
                    </div>
                </li>`;
            }).join('');

            // Feedback
            const fb = s.feedback.length ? s.feedback.map((f) => `
                <li class="flex gap-2.5 ${f.saya ? 'flex-row-reverse text-right' : ''}">
                    ${f.foto
                    ? `<img src="${esc(f.foto)}" alt="${esc(f.nama)}" title="${esc(f.nama)}" class="h-8 w-8 shrink-0 rounded-full object-cover">`
                    : `<span title="${esc(f.nama)}" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${f.saya ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700'} text-[11px] font-bold">${inisial(f.nama)}</span>`}
                    <div class="max-w-[85%] rounded-2xl ${f.saya ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-800'} px-3.5 py-2">
                        <p class="text-[11px] ${f.saya ? 'text-blue-100' : 'text-slate-500'}">${esc(f.nama)} · ${esc(f.waktu)}</p>
                        <p class="whitespace-pre-line text-left">${esc(f.isi)}</p>
                    </div>
                </li>`).join('') : '<li class="rounded-xl bg-slate-50 px-3 py-4 text-center text-xs text-gray-400">Belum ada feedback.</li>';

            isi.innerHTML = `${infoHtml}${zoom}${berkas}
                <div><p class="pp-sec">Alur disposisi <i>${s.disposisi.length}</i></p><ul class="space-y-3">${alur}</ul></div>
                <div><p class="pp-sec">Feedback <i>${s.feedback.length}</i></p><ul class="space-y-2.5">${fb}</ul></div>`;
            isi.scrollTop = gulir;
        }

        // ---------------- Pemicu ----------------

        const pasang = (el) => {
            el.addEventListener('click', (e) => {
                // Kalau elemennya link (mis. kartu di Jadwal Pegawai), jangan pindah halaman
                if (e.ctrlKey || e.metaKey || e.shiftKey) return;
                e.preventDefault();
                e.stopPropagation();
                buka(el.dataset.buka);
            });
            el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && e.target === el) buka(el.dataset.buka);
            });
            // Ambil data lebih dulu begitu kursor / jari mendekat
            ['mouseenter', 'focus', 'touchstart'].forEach((ev) => {
                el.addEventListener(ev, () => intip(el.dataset.buka), { passive: true, once: true });
            });
        };
        const semua = Array.from(document.querySelectorAll('[data-buka]'));
        semua.forEach(pasang);

        // Saat halaman idle, ambil beberapa surat pertama supaya klik pertama pun instan
        const idle = window.requestIdleCallback || ((fn) => setTimeout(fn, 800));
        idle(() => {
            const ids = [...new Set(semua.map((el) => el.dataset.buka))].slice(0, 6);
            ids.reduce((rantai, id) => rantai.then(() => { intip(id); return inflight.get(String(id)); }), Promise.resolve());
        });

        // Bisa dipanggil dari script lain di halaman (mis. event kalender)
        window.dispoPopup = { buka, tutup, intip };

        // Dibuka otomatis lewat ?buka=ID (link notifikasi)
        if (BUKA) buka(BUKA);
    })();
</script>