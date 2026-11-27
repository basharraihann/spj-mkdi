<x-app-layout title="Disposisi">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Disposisi</h2>
    </x-slot>

    @php
        // Kelas badge status (warna badge ada di disposisi/_gaya)
        $statusKelas = [
            'baru' => 'kd-st-baru',
            'dibaca' => 'kd-st-dibaca',
            'diteruskan' => 'kd-st-diteruskan',
            'selesai' => 'kd-st-selesai',
            'keluar' => 'kd-st-keluar',
        ];

        // Filter yang ikut ke sumber event kalender
        $param = array_filter([
            'arah' => $f['arah'] !== 'semua' ? $f['arah'] : null,
            'status' => $f['status'] ?: null,
            'sifat' => $f['sifat'] ?: null,
            'q' => $f['q'] ?: null,
        ]);
        $jumlahFilter = count($param);
        $jumlahLanjutan = count(array_intersect_key($param, array_flip(['arah', 'status', 'sifat'])));

        $resetUrl = $jumlahFilter
            ? route('disposisi.index', array_filter(['tampil' => 'kalender', 'tanggal' => $tanggal, 'view' => $view]))
            : null;

        // Controller sudah mengirim satu baris per surat, lengkap dengan 'kelas' warna
        $rapatTampil = $rapatHariIni;
    @endphp

    @include('disposisi._gaya')
    @include('disposisi._gaya-kalender')

    <div class="kd-wrap min-h-full">
        <div class="kd-container space-y-5 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">

            {{-- Notifikasi --}}
            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                    role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            {{-- ================= Tab + pencarian + filter dalam satu kartu ================= --}}
            <x-disposisi.kartu-atas tampil="kalender" form-id="form-filter" :q="$f['q']"
                placeholder="Cari perihal, nomor surat, asal, tempat…" :lanjut="$jumlahLanjutan > 0"
                :reset-url="$resetUrl" :boleh-catat="$bolehCatat">

                <x-slot name="hidden">
                    <input type="hidden" name="tampil" value="kalender">
                    <input type="hidden" name="tanggal" id="f-tanggal" value="{{ $tanggal }}">
                    <input type="hidden" name="view" id="f-view" value="{{ $view }}">
                </x-slot>

                <x-disposisi.pilih name="arah" label="Arah">
                    <option value="semua">Semua</option>
                    <option value="masuk" @selected($f['arah'] === 'masuk')>Diterima saya</option>
                    <option value="keluar" @selected($f['arah'] === 'keluar')>Dikirim saya</option>
                </x-disposisi.pilih>

                <x-disposisi.pilih name="status" label="Status">
                    <option value="">Semua status</option>
                    <option value="perlu" @selected($f['status'] === 'perlu')>Perlu tindakan</option>
                    @foreach (\App\Models\Disposisi::STATUS as $k => $v)
                        <option value="{{ $k }}" @selected($f['status'] === $k)>{{ $v }}</option>
                    @endforeach
                </x-disposisi.pilih>

                <x-disposisi.pilih name="sifat" label="Sifat surat">
                    <option value="">Semua sifat</option>
                    @foreach (\App\Models\DisposisiSurat::SIFAT as $k => $v)
                        <option value="{{ $k }}" @selected($f['sifat'] === $k)>{{ $v }}</option>
                    @endforeach
                </x-disposisi.pilih>

                <x-slot name="bawah">
                    <div class="p-3 sm:p-4">
                        <div class="kal-layout">

                            {{-- Kiri: Jadwal Hari Ini --}}
                            <aside class="kal-side">
                                <div class="kd-card p-4">
                                    <div class="mb-3 flex items-start justify-between gap-2">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800">Jadwal Hari Ini</p>
                                            <p class="text-[11px] text-gray-400">
                                                {{ now()->translatedFormat('l, d F Y') }}
                                            </p>
                                        </div>
                                        <span class="kd-chip kd-chip-gray">{{ $rapatTampil->count() }}</span>
                                    </div>

                                    @if ($rapatTampil->isEmpty())
                                        <div class="rounded-xl bg-slate-50 px-3 py-6 text-center text-xs text-gray-400">
                                            Tidak ada jadwal hari ini.
                                        </div>
                                    @else
                                        <ol class="rp-list">
                                            @foreach ($rapatTampil as $r)
                                                @php
                                                    $s = $r['surat'];

                                                    // Orang sudah lengkap dari controller (termasuk yang "ikut")
                                                    $orangRapat = collect($r['orang_users'] ?? []);

                                                    // Link zoom: kolom link_daring, atau URL yang terselip di kolom tempat
                                                    $tempat = trim((string) $s?->tempat);
                                                    $linkRapat = $s?->link_daring ?: null;
                                                    if (!$linkRapat && preg_match('~https?://\S+~i', $tempat, $m)) {
                                                        $linkRapat = $m[0];
                                                        $tempat = trim(str_replace($m[0], '', $tempat));
                                                    }
                                                    $hostRapat = $linkRapat ? (parse_url($linkRapat, PHP_URL_HOST) ?: $linkRapat) : null;
                                                @endphp
                                                <li>
                                                    <div role="button" tabindex="0" data-buka="{{ $r['surat_id'] }}"
                                                        class="rp-item w-{{ $r['kelas'] }}">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <span class="rp-jam">
                                                                @if ($s?->waktu_mulai)
                                                                    {{ substr((string) $s->waktu_mulai, 0, 5) }}{{ $s->waktu_selesai ? ' – ' . substr((string) $s->waktu_selesai, 0, 5) : '' }}
                                                                @else
                                                                    Seharian
                                                                @endif
                                                            </span>
                                                            <span
                                                                class="kd-st {{ $statusKelas[$r['status']] ?? 'kd-st-keluar' }}">{{ $r['status_label'] }}</span>
                                                        </div>

                                                        <p class="rp-judul">{{ $s?->perihal }}</p>

                                                        @if ($s?->asal_surat)
                                                            <p class="rp-meta" title="Dari: {{ $s->asal_surat }}">Dari:
                                                                {{ $s->asal_surat }}</p>
                                                        @endif

                                                        @if ($tempat !== '')
                                                            <p class="rp-tempat">{{ $tempat }}</p>
                                                        @endif

                                                        @if ($linkRapat)
                                                            <a href="{{ $linkRapat }}" target="_blank" rel="noopener noreferrer"
                                                                onclick="event.stopPropagation()" title="{{ $linkRapat }}"
                                                                class="rp-link">{{ $hostRapat }}</a>
                                                        @endif

                                                        @if ($orangRapat->isNotEmpty())
                                                            <div class="av-row">
                                                                @foreach ($orangRapat->take(5) as $o)
                                                                    @php $judulAv = $o['nama'] . (!empty($o['ikut']) ? ' (ikut)' : ''); @endphp
                                                                    @if (!empty($o['foto']))
                                                                        <img class="av" src="{{ $o['foto'] }}" alt="{{ $o['nama'] }}"
                                                                            title="{{ $judulAv }}">
                                                                    @else
                                                                        <span class="av"
                                                                            title="{{ $judulAv }}">{{ mb_strtoupper(mb_substr(trim($o['nama']), 0, 1)) }}</span>
                                                                    @endif
                                                                @endforeach
                                                                @if ($orangRapat->count() > 5)
                                                                    <span class="av av-n">+{{ $orangRapat->count() - 5 }}</span>
                                                                @endif
                                                            </div>
                                                            <p class="rp-nama">
                                                                {{ $orangRapat->pluck('nama')->take(3)->implode(', ') }}@if ($orangRapat->count() > 3)
                                                                +{{ $orangRapat->count() - 3 }}@endif
                                                            </p>
                                                        @endif
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ol>
                                    @endif
                                </div>
                            </aside>

                            {{-- Kanan: kalender utama --}}
                            <div class="min-w-0">
                                <div class="mb-4 flex flex-col justify-between gap-3 md:flex-row md:items-center">
                                    <div class="flex items-center gap-2">
                                        <button type="button" id="k-today" class="kb">Hari ini</button>
                                        <button type="button" id="k-prev" class="kb" style="width:2.25rem;padding:0"
                                            aria-label="Sebelumnya">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <button type="button" id="k-next" class="kb" style="width:2.25rem;padding:0"
                                            aria-label="Berikutnya">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div class="ml-2 leading-tight" style="min-width:7rem">
                                            <p id="k-bulan" class="text-base font-bold capitalize text-gray-900">&nbsp;
                                            </p>
                                            <p id="k-tahun" class="text-xs text-gray-400">&nbsp;</p>
                                        </div>
                                    </div>

                                    <div class="kd-seg" id="k-view">
                                        <button type="button" data-v="timeGridDay">Hari</button>
                                        <button type="button" data-v="timeGridWeek">Minggu</button>
                                        <button type="button" data-v="dayGridMonth">Bulan</button>
                                        <button type="button" data-v="multiMonthYear"
                                            class="kd-hanya-desktop">Tahun</button>
                                        <button type="button" data-v="listWeek">Daftar</button>
                                    </div>
                                </div>

                                <div id="kal"></div>
                                <p id="kal-gagal" class="mt-3 hidden text-sm text-gray-500">
                                    Kalender gagal dimuat (koneksi ke CDN). Muat ulang halaman atau periksa koneksi internet.
                                </p>
                            </div>

                        </div>
                    </div>
                </x-slot>
            </x-disposisi.kartu-atas>

        </div>
    </div>

    {{-- ================= Popup detail + feedback ================= --}}
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
            <div id="m-isi" class="pp-body text-sm">
                <p class="text-gray-400">Memuat…</p>
            </div>

            {{-- Form feedback --}}
            <form id="m-form" method="POST" class="pp-foot space-y-2">
                @csrf
                <input type="hidden" name="_dari" value="kalender">
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

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/id.global.min.js"></script>
    <script>
        (function () {
            // ---------------- Konfigurasi dari server ----------------
            const URL_EVENTS = @json(route('kalender.events', $param));
            const URL_DETAIL = @json(route('kalender.detail', ['surat' => '__ID__']));
            const TANGGAL = @json($tanggal);
            const VIEW = @json($view);
            const BUKA = @json($bukaSurat);

            // ---------------- Helper ----------------
            const $ = (id) => document.getElementById(id);
            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
            }[c]));
            const ymd = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            const inisial = (nama) => esc((nama || '?').trim().charAt(0).toUpperCase());
            const stKelas = { baru: 'kd-st-baru', dibaca: 'kd-st-dibaca', diteruskan: 'kd-st-diteruskan', selesai: 'kd-st-selesai' };

            // ---------------- Popup ----------------
            const modal = $('modal-dispo');

            const tutup = () => {
                modal.hidden = true;
                document.body.style.overflow = '';
            };

            modal.addEventListener('click', (e) => {
                if (e.target === modal || e.target.closest('[data-tutup]')) tutup();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.hidden) tutup();
            });

            async function buka(id) {
                modal.hidden = false;
                document.body.style.overflow = 'hidden';
                $('m-chip').innerHTML = '';
                $('m-nomor').textContent = '';
                $('m-perihal').textContent = 'Memuat…';
                $('m-isi').innerHTML = '<p class="text-gray-400">Memuat…</p>';

                try {
                    const r = await fetch(URL_DETAIL.replace('__ID__', id), { headers: { Accept: 'application/json' } });
                    if (!r.ok) throw new Error(r.status);
                    renderDetail(await r.json());
                } catch (e) {
                    $('m-perihal').textContent = 'Gagal memuat';
                    $('m-isi').innerHTML = '<p class="text-red-600">Detail disposisi tidak bisa dibuka.</p>';
                }
            }

            function renderDetail(s) {
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

                // Kepala popup
                $('m-chip').innerHTML =
                    `<span class="kd-chip kd-chip-gray">${esc(s.sifat)}</span>` +
                    `<span class="kd-st ${selesai ? 'kd-st-selesai' : 'kd-st-baru'}">${selesai ? 'Surat selesai' : 'Proses'}</span>`;
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

                // Alur disposisi (timeline)
                const alur = s.disposisi.map((d) => `
                    <li>
                        <div class="pp-card ${d.saya ? 'saya' : ''}">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <span class="pp-flow">
                                    ${esc(d.dari)} <span style="color:#94a3b8">→</span>
                                    <b>${esc(d.kepada)}</b>
                                    ${d.saya ? '<span class="pp-ikut anda">Anda</span>' : ''}
                                    ${d.ikut ? '<span class="pp-ikut">ikut</span>' : ''}
                                </span>
                                <span class="kd-st ${stKelas[d.status] || 'kd-st-keluar'}">${esc(d.status_label)}</span>
                            </div>
                            ${d.instruksi.length ? `<div class="mt-1.5 flex flex-wrap gap-1">${d.instruksi.map((i) => `<span class="pp-tag">${esc(i)}</span>`).join('')}</div>` : ''}
                            ${d.catatan ? `<p class="pp-note">${esc(d.catatan)}</p>` : ''}
                            ${d.batas ? `<p class="pp-due ${d.terlambat ? 'telat' : ''}">Batas ${esc(d.batas)}${d.terlambat ? ' · terlambat' : ''}</p>` : ''}
                        </div>
                    </li>`).join('');

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

                $('m-isi').innerHTML = `${infoHtml}${zoom}${berkas}
                    <div><p class="pp-sec">Alur disposisi <i>${s.disposisi.length}</i></p><ul class="pp-tl">${alur}</ul></div>
                    <div><p class="pp-sec">Feedback <i>${s.feedback.length}</i></p><ul class="space-y-2.5">${fb}</ul></div>`;
            }

            // Tombol pembuka popup (sidebar jadwal hari ini)
            document.querySelectorAll('[data-buka]').forEach((el) => {
                el.addEventListener('click', (e) => {
                    e.stopPropagation();
                    buka(el.dataset.buka);
                });
                el.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' && e.target === el) buka(el.dataset.buka);
                });
            });
            if (BUKA) buka(BUKA);

            // ---------------- Kalender ----------------
            if (!$('kal')) return;
            if (typeof FullCalendar === 'undefined') {
                $('kal-gagal').classList.remove('hidden');
                return;
            }

            const mobile = window.matchMedia('(max-width: 767px)').matches;

            // Avatar satu orang: foto kalau ada, kalau tidak huruf depan nama
            const avatar = (o) => o.foto
                ? `<img class="av" src="${esc(o.foto)}" alt="${esc(o.nama)}" title="${esc(o.nama)}${o.ikut ? ' (ikut)' : ''}">`
                : `<span class="av" title="${esc(o.nama)}${o.ikut ? ' (ikut)' : ''}">${inisial(o.nama)}</span>`;

            const IKON_VIDEO = '<svg class="ev-ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.55-2.28A1 1 0 0121 8.62v6.76a1 1 0 01-1.45.9L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>';
            const IKON_GEDUNG = '<svg class="ev-ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>';

            const cal = new FullCalendar.Calendar($('kal'), {
                locale: 'id',
                initialView: VIEW || (mobile ? 'listWeek' : 'timeGridWeek'),
                initialDate: TANGGAL,
                headerToolbar: false,
                height: 'auto',
                dayMaxEvents: mobile ? 2 : 3,
                nowIndicator: true,
                navLinks: true, // klik angka tanggal -> buka tampilan Hari
                slotMinTime: '06:00:00',
                slotMaxTime: '20:00:00',
                slotDuration: '01:00:00',
                slotLabelInterval: '01:00',
                slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                slotEventOverlap: false,      // event bentrok jadi berdampingan
                eventMinHeight: 40,
                displayEventEnd: true,        // jam tampil "08:30 - 12:00"
                eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                allDayText: 'Seharian',
                noEventsText: 'Tidak ada disposisi',
                multiMonthMaxColumns: 3,
                // Controller sudah mengirim satu event per surat, jadi tidak perlu disaring lagi
                events: {
                    url: URL_EVENTS,
                    failure: () => $('kal-gagal').classList.remove('hidden'),
                },

                // Header hari: nama kecil + angka besar
                dayHeaderContent: (arg) => {
                    const t = arg.view.type;
                    if (t !== 'timeGridWeek' && t !== 'timeGridDay') return arg.text;
                    const hari = arg.date.toLocaleDateString('id-ID', { weekday: 'short' });
                    return { html: `<div class="dh"><span class="dh-n">${esc(hari)}</span><span class="dh-d">${arg.date.getDate()}</span></div>` };
                },

                // Isi event: ikon + avatar di atas, judul, jam di bawah
                eventContent: (arg) => {
                    const tipe = arg.view.type;
                    if (tipe === 'multiMonthYear' || tipe === 'listWeek') return true; // pakai tampilan bawaan

                    const p = arg.event.extendedProps;
                    const orang = p.orang || [];
                    const online = /online|zoom|meet|teams|https?:\/\//i.test(p.tempat || '');

                    const maks = mobile ? 2 : 3;
                    const sisa = orang.length - maks;
                    const avatarHtml = orang.length
                        ? `<div class="av-row">${orang.slice(0, maks).map(avatar).join('')}${sisa > 0 ? `<span class="av av-n">${sisa}+</span>` : ''}</div>`
                        : '';

                    return {
                        html: `<div class="ev-card">
                            <div class="ev-top">${online ? IKON_VIDEO : IKON_GEDUNG}${avatarHtml}</div>
                            <div class="ev-ttl">${esc(arg.event.title)}</div>
                            <div class="ev-jam">${arg.event.allDay ? 'Seharian' : esc(arg.timeText)}</div>
                        </div>`,
                    };
                },

                eventDidMount: (info) => {
                    const p = info.event.extendedProps;
                    info.el.title = info.event.title + '\n' + (p.sub || '') + (p.tempat ? '\n' + p.tempat : '');
                },

                eventClick: (info) => {
                    info.jsEvent.preventDefault();
                    buka(info.event.extendedProps.surat_id);
                },

                datesSet: (info) => {
                    const awal = ymd(cal.getDate());
                    const mulai = info.view.currentStart;
                    const tahunSaja = info.view.type === 'multiMonthYear';
                    $('k-bulan').textContent = tahunSaja ? mulai.getFullYear() : mulai.toLocaleDateString('id-ID', { month: 'long' });
                    $('k-tahun').textContent = tahunSaja ? '' : mulai.getFullYear();
                    document.querySelectorAll('#k-view button').forEach((b) => {
                        b.classList.toggle('aktif', b.dataset.v === info.view.type);
                    });
                    $('f-tanggal').value = awal;
                    $('f-view').value = info.view.type;
                    $('m-tanggal').value = awal;
                },
            });

            // Navigasi & pilihan tampilan
            $('k-prev').onclick = () => cal.prev();
            $('k-next').onclick = () => cal.next();
            $('k-today').onclick = () => cal.today();
            document.querySelectorAll('#k-view button').forEach((b) => {
                b.onclick = () => cal.changeView(b.dataset.v);
            });

            cal.render();
        })();
    </script>
</x-app-layout>