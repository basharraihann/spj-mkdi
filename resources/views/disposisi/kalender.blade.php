<x-app-layout title="Disposisi">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Disposisi</h2>
    </x-slot>

    @php
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
    @endphp

    @include('disposisi._gaya')

    <style>
        /* ====== Layout: sidebar kecil (kiri) + kalender besar (kanan) ====== */
        .kal-layout { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem; }
        @media (min-width: 1024px) {
            .kal-layout { grid-template-columns: 290px minmax(0, 1fr); align-items: start; }
            .kal-side { position: sticky; top: 1rem; }
        }
        /* Halaman dilebarkan supaya kalender lebih lega */
        .kd-container { max-width: 1760px; }

        /* ====== Sidebar: Rapat Hari Ini ====== */
        .rp-list { display: flex; flex-direction: column; gap: .6rem; }
        .rp-item {
            width: 100%; text-align: left; border-radius: .75rem; border: 1px solid #e8eef7; background: #fff;
            padding: .65rem .75rem .65rem .85rem; border-left-width: 4px;
            transition: background .15s, border-color .15s;
        }
        .rp-item:hover { background: #f8fbff; border-color: #bfdbfe; }
        .rp-baru { border-left-color: #2563eb; }
        .rp-dibaca { border-left-color: #60a5fa; }
        .rp-diteruskan { border-left-color: #a5b4fc; }
        .rp-selesai { border-left-color: #4ade80; }
        .rp-keluar { border-left-color: #94a3b8; }
        .rp-jam { font-size: .72rem; font-weight: 700; color: #1d4ed8; font-variant-numeric: tabular-nums; }
        .rp-judul {
            margin-top: .15rem; font-size: .8rem; font-weight: 600; color: #0f172a; line-height: 1.25;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .rp-tempat {
            margin-top: .1rem; font-size: .68rem; color: #94a3b8;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        /* ====== Avatar (dipakai di sidebar & di event kalender) ====== */
        .av {
            width: 22px; height: 22px; border-radius: 999px; border: 2px solid #fff; margin-left: -7px;
            object-fit: cover; background: #e2e8f0; color: #334155; font-size: .6rem; font-weight: 700;
            display: inline-flex; align-items: center; justify-content: center; flex: none; line-height: 1;
        }
        .av:first-child { margin-left: 0; }
        .av-n { background: #0f172a; color: #fff; }
        .av-row { display: flex; align-items: center; margin-top: .3rem; }

        /* ====== FullCalendar: dasar ====== */
        #kal {
            --fc-border-color: #edf1f7;
            --fc-today-bg-color: #f5f9ff;
            --fc-page-bg-color: #fff;
            --fc-neutral-bg-color: #f8fafc;
            --fc-now-indicator-color: #2563eb;
            font-size: 13px;
        }
        #kal .fc-scrollgrid { border-radius: .75rem; overflow: hidden; }
        #kal .fc-event { cursor: pointer; }
        #kal a.fc-daygrid-day-number,
        #kal a.fc-col-header-cell-cushion { text-decoration: none; }

        /* Header kolom */
        #kal .fc-col-header-cell-cushion { padding: .8rem .25rem; font-weight: 600; color: #0f172a; font-size: .85rem; }
        #kal .fc-col-header-cell.fc-day-today { box-shadow: inset 0 -3px 0 #2563eb; }
        #kal .fc-col-header-cell.fc-day-today .fc-col-header-cell-cushion { color: #2563eb; }

        /* Tampilan Bulan */
        #kal .fc-daygrid-day-frame { min-height: 150px; }
        #kal .fc-daygrid-day-top { justify-content: center; }
        #kal .fc-daygrid-day-number {
            font-size: 1.25rem; font-weight: 600; color: #1e293b; padding: .55rem 0 .2rem;
            font-variant-numeric: tabular-nums;
        }
        #kal .fc-day-other .fc-daygrid-day-number { color: #cbd5e1; }
        #kal .fc-day-today .fc-daygrid-day-number { color: #2563eb; }
        #kal .fc-daygrid-day.fc-day-today { background: #f5f9ff; box-shadow: inset 0 3px 0 #2563eb; }
        #kal .fc-daygrid-event {
            margin: 3px 6px 0; border-radius: 8px; border: 0; padding: 4px 7px;
            font-size: .72rem; font-weight: 500; justify-content: flex-start; white-space: normal;
        }
        #kal .fc-daygrid-dot-event .fc-daygrid-event-dot { display: none; }
        #kal .fc-daygrid-more-link { font-size: .72rem; color: #2563eb; font-weight: 600; margin: 3px 8px; }

        /* Tampilan Hari / Minggu */
        #kal .fc-timegrid-event { border-radius: 8px; border: 0; padding: 3px 5px; }
        #kal .fc-timegrid-slot { height: 2.6rem; }
        #kal .fc-timegrid-slot-label-cushion,
        #kal .fc-timegrid-axis-cushion { font-size: .7rem; color: #94a3b8; }

        /* Tampilan Daftar */
        #kal .fc-list-day-cushion { background: #f5f9ff; }
        #kal .fc-list-event:hover td { background: #f8fbff; }

        /* Tampilan Tahun */
        #kal .fc-multimonth-title { font-size: .9rem; color: #1e3a8a; }
        #kal .fc-multimonth .fc-daygrid-day-number { font-size: .72rem; padding: .2rem 0; }
        #kal .fc-multimonth .fc-daygrid-day-frame { min-height: 0; }
        #kal .fc-multimonth .fc-daygrid-day.fc-day-today { box-shadow: none; }

        /* ====== Isi event (judul + avatar) ====== */
        #kal .ev-in { width: 100%; min-width: 0; overflow: hidden; }
        #kal .ev-t { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.3; }
        #kal .ev-t b { font-variant-numeric: tabular-nums; margin-right: .25rem; }

        /* ====== Warna event per status (biru) ====== */
        #kal .ev-baru { background: #2563eb !important; color: #fff !important; }
        #kal .ev-dibaca { background: #60a5fa !important; color: #fff !important; }
        #kal .ev-diteruskan { background: #c7d2fe !important; color: #312e81 !important; }
        #kal .ev-selesai { background: #bbf7d0 !important; color: #14532d !important; }
        #kal .ev-keluar { background: #fff !important; color: #1e40af !important; box-shadow: inset 0 0 0 1px #93c5fd; }
        #kal .ev .fc-event-main,
        #kal .ev .fc-event-time,
        #kal .ev .fc-event-title { color: inherit !important; }

        /* Warna titik pada tampilan Daftar */
        #kal .fc-list-event.ev { background: transparent !important; color: #0f172a !important; box-shadow: none; }
        #kal .fc-list-event.ev-baru .fc-list-event-dot { border-color: #2563eb; }
        #kal .fc-list-event.ev-dibaca .fc-list-event-dot { border-color: #60a5fa; }
        #kal .fc-list-event.ev-diteruskan .fc-list-event-dot { border-color: #818cf8; }
        #kal .fc-list-event.ev-selesai .fc-list-event-dot { border-color: #22c55e; }
        #kal .fc-list-event.ev-keluar .fc-list-event-dot { border-color: #94a3b8; }

        /* ====== Mobile ====== */
        @media (max-width: 767px) {
            #kal .fc-daygrid-day-frame { min-height: 72px; }
            #kal .fc-daygrid-day-number { font-size: .9rem; padding: .35rem 0 .1rem; }
            #kal .fc-daygrid-event { margin: 1px 2px 0; padding: 2px 4px; font-size: .62rem; }
            #kal .fc-col-header-cell-cushion { font-size: .68rem; padding: .55rem 0; }
            #kal .av { width: 16px; height: 16px; font-size: .5rem; margin-left: -5px; }
        }

        /* ====== Popup ====== */
        #modal-dispo[hidden] { display: none; }
    </style>

    <div class="kd-wrap min-h-full">
        <div class="kd-container space-y-5 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">

            {{-- Notifikasi --}}
            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ session('error') }}</div>
            @endif

            {{-- ================= Tab + pencarian + filter dalam satu kartu ================= --}}
            <x-disposisi.kartu-atas tampil="kalender" form-id="form-filter"
                :q="$f['q']" placeholder="Cari perihal, nomor surat, asal, tempat…"
                :lanjut="$jumlahLanjutan > 0" :reset-url="$resetUrl" :boleh-catat="$bolehCatat">

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
                        {{-- ================= KALENDER ================= --}}
                        <div class="kal-layout">

                            {{-- Kiri: Rapat Hari Ini (atas ke bawah) --}}
                            <aside class="kal-side">
                                <div class="kd-card p-4">
                                    <div class="mb-3 flex items-start justify-between gap-2">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800">Rapat Hari Ini</p>
                                            <p class="text-[11px] text-gray-400">{{ now()->translatedFormat('l, d F Y') }}</p>
                                        </div>
                                        <span class="kd-chip kd-chip-gray">{{ $rapatHariIni->count() }}</span>
                                    </div>

                                    @if ($rapatHariIni->isEmpty())
                                        <div class="rounded-xl bg-slate-50 px-3 py-6 text-center text-xs text-gray-400">
                                            Tidak ada rapat hari ini.
                                        </div>
                                    @else
                                        <ol class="rp-list">
                                            @foreach ($rapatHariIni as $r)
                                                @php
                                                    $s = $r['surat'];
                                                    $orangRapat = $r['orang_users'] ?? [];
                                                @endphp
                                                <li>
                                                    <button type="button" data-buka="{{ $r['surat_id'] }}" class="rp-item rp-{{ $r['status'] }}">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <span class="rp-jam">
                                                                @if ($s?->waktu_mulai)
                                                                    {{ substr((string) $s->waktu_mulai, 0, 5) }}{{ $s->waktu_selesai ? ' – ' . substr((string) $s->waktu_selesai, 0, 5) : '' }}
                                                                @else
                                                                    Seharian
                                                                @endif
                                                            </span>
                                                            <span class="kd-st {{ $statusKelas[$r['status']] ?? 'kd-st-keluar' }}">{{ $r['status_label'] }}</span>
                                                        </div>

                                                        <p class="rp-judul">{{ $s?->perihal }}</p>

                                                        @if ($s?->tempat)
                                                            <p class="rp-tempat">{{ $s->tempat }}</p>
                                                        @endif

                                                        @if (count($orangRapat))
                                                            <div class="av-row">
                                                                @foreach (array_slice($orangRapat, 0, 5) as $o)
                                                                    @if (!empty($o['foto']))
                                                                        <img class="av" src="{{ $o['foto'] }}" alt="{{ $o['nama'] }}" title="{{ $o['nama'] }}">
                                                                    @else
                                                                        <span class="av" title="{{ $o['nama'] }}">{{ mb_strtoupper(mb_substr(trim($o['nama']), 0, 1)) }}</span>
                                                                    @endif
                                                                @endforeach
                                                                @if (count($orangRapat) > 5)
                                                                    <span class="av av-n">+{{ count($orangRapat) - 5 }}</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </button>
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
                                        <button type="button" id="k-prev" class="kd-btn-ghost kd-ikon" aria-label="Sebelumnya">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                        </button>
                                        <h2 id="k-judul" class="text-lg font-bold capitalize text-gray-900" style="min-width: 9rem">&nbsp;</h2>
                                        <button type="button" id="k-next" class="kd-btn-ghost kd-ikon" aria-label="Berikutnya">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                        </button>
                                        <button type="button" id="k-today" class="kd-btn-soft kd-kecil">Hari ini</button>
                                    </div>

                                    <div class="kd-seg" id="k-view">
                                        <button type="button" data-v="timeGridDay">Hari</button>
                                        <button type="button" data-v="timeGridWeek">Minggu</button>
                                        <button type="button" data-v="dayGridMonth">Bulan</button>
                                        <button type="button" data-v="multiMonthYear" class="kd-hanya-desktop">Tahun</button>
                                        <button type="button" data-v="listWeek">Daftar</button>
                                    </div>
                                </div>

                                <div id="kal"></div>
                                <p id="kal-gagal" class="mt-3 hidden text-sm text-gray-500">
                                    Kalender gagal dimuat (koneksi ke CDN). Buka tab "Hari Ini" untuk melihat disposisi dalam bentuk tabel.
                                </p>
                            </div>

                        </div>
                    </div>
                </x-slot>
            </x-disposisi.kartu-atas>

        </div>
    </div>

    {{-- ================= Popup detail + feedback ================= --}}
    <div id="modal-dispo" hidden class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4" style="background: rgb(15 23 42 / .45)">
        <div class="kd-modal-box w-full overflow-y-auto rounded-t-3xl bg-white shadow-2xl sm:max-w-2xl sm:rounded-2xl">

            {{-- Kepala --}}
            <div class="sticky top-0 z-10 flex items-start justify-between gap-3 border-b border-gray-100 bg-white/95 px-5 py-4 backdrop-blur">
                <div class="min-w-0">
                    <div id="m-chip" class="mb-1 flex flex-wrap gap-1.5"></div>
                    <h3 id="m-perihal" class="text-base font-semibold text-gray-900"></h3>
                    <p id="m-nomor" class="text-xs text-gray-500"></p>
                </div>
                <button type="button" data-tutup class="kd-btn-ghost kd-ikon" aria-label="Tutup">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            {{-- Isi (diisi lewat JS) --}}
            <div id="m-isi" class="space-y-4 px-5 py-4 text-sm">
                <p class="text-gray-400">Memuat…</p>
            </div>

            {{-- Form feedback --}}
            <form id="m-form" method="POST" class="space-y-2 border-t border-gray-100 bg-slate-50/60 px-5 py-4">
                @csrf
                <input type="hidden" name="_dari" value="kalender">
                <input type="hidden" name="_tanggal" id="m-tanggal" value="{{ $tanggal }}">

                <label for="m-feedback" class="kd-label">Tulis feedback</label>
                <textarea id="m-feedback" name="isi" rows="2" required maxlength="2000" class="kd-input" style="background: #fff"
                    placeholder="mis. Siap hadir / sudah ditindaklanjuti / diwakilkan oleh …"></textarea>
                @error('isi')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror

                <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
                    <label id="m-selesai-wrap" class="hidden items-center gap-2 text-xs text-gray-600">
                        <input type="checkbox" name="tandai_selesai" value="1" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        Sekalian tandai disposisi saya selesai
                    </label>
                    <div class="flex gap-2 sm:ml-auto">
                        <a id="m-detail" href="#" class="kd-btn-ghost flex-1 border border-gray-200 bg-white sm:flex-none">Detail lengkap</a>
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

                // Kepala popup
                $('m-chip').innerHTML =
                    `<span class="kd-chip kd-chip-gray">${esc(s.sifat)}</span>` +
                    `<span class="kd-st ${selesai ? 'kd-st-selesai' : 'kd-st-baru'}">${selesai ? 'Surat selesai' : 'Proses'}</span>`;
                $('m-perihal').textContent = s.perihal;
                $('m-nomor').textContent = s.nomor_surat + ' · dari ' + s.asal_surat;

                // Form feedback
                $('m-form').action = s.url_feedback;
                $('m-detail').href = s.url_detail;
                $('m-selesai-wrap').classList.toggle('hidden', !s.bisa_selesai);
                $('m-selesai-wrap').classList.toggle('flex', !!s.bisa_selesai);

                // Jadwal kegiatan
                const jadwal = s.tanggal_kegiatan ? `
                    <div class="flex items-start gap-3 rounded-xl bg-blue-50 px-4 py-3 text-blue-900">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <div>
                            <b>${esc(s.tanggal_kegiatan)}</b>${s.waktu ? ' · ' + esc(s.waktu) : ''}
                            ${s.tempat ? `<div class="text-xs text-blue-700">${esc(s.tempat)}</div>` : ''}
                        </div>
                    </div>` : '';

                // Berkas surat
                const berkas = s.url_berkas ? `
                    <a href="${esc(s.url_berkas)}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:underline">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        Lihat file surat
                    </a>` : '';

                // Alur disposisi
                const alur = s.disposisi.map((d) => `
                    <li class="rounded-xl border ${d.saya ? 'border-blue-300 bg-blue-50/50' : 'border-gray-200'} px-3.5 py-2.5">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <span class="text-gray-600">
                                ${esc(d.dari)} <span class="text-gray-400">→</span>
                                <b class="text-gray-900">${esc(d.kepada)}</b>
                                ${d.saya ? '<span class="ml-1 rounded bg-blue-600 px-1.5 py-0.5 text-[10px] font-semibold text-white">Anda</span>' : ''}
                            </span>
                            <span class="kd-st ${stKelas[d.status] || 'kd-st-keluar'}">${esc(d.status_label)}</span>
                        </div>
                        ${d.instruksi.length ? `
                            <div class="mt-1.5 flex flex-wrap gap-1">
                                ${d.instruksi.map((i) => `<span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[11px] text-slate-700">${esc(i)}</span>`).join('')}
                            </div>` : ''}
                        ${d.catatan ? `<p class="mt-1.5 whitespace-pre-line text-gray-700">${esc(d.catatan)}</p>` : ''}
                        ${d.batas ? `<p class="mt-1 text-[11px] ${d.terlambat ? 'font-semibold text-red-600' : 'text-gray-500'}">Batas ${esc(d.batas)}${d.terlambat ? ' · terlambat' : ''}</p>` : ''}
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
                    </li>`).join('') : '<li class="text-gray-400">Belum ada feedback.</li>';

                $('m-isi').innerHTML = `${jadwal}${berkas}
                    <div><p class="kd-label">Alur disposisi</p><ul class="space-y-2">${alur}</ul></div>
                    <div><p class="kd-label">Feedback</p><ul class="space-y-2.5">${fb}</ul></div>`;
            }

            // Tombol pembuka popup (sidebar "Rapat Hari Ini")
            document.querySelectorAll('[data-buka]').forEach((el) => el.addEventListener('click', (e) => {
                e.stopPropagation();
                buka(el.dataset.buka);
            }));
            if (BUKA) buka(BUKA);

            // ---------------- Kalender ----------------
            if (!$('kal')) return;
            if (typeof FullCalendar === 'undefined') {
                $('kal-gagal').classList.remove('hidden');
                return;
            }

            const mobile = window.matchMedia('(max-width: 767px)').matches;
            const maxAvatar = mobile ? 3 : 5;

            // Avatar satu orang: foto kalau ada, kalau tidak huruf depan nama
            const avatar = (o) => o.foto
                ? `<img class="av" src="${esc(o.foto)}" alt="${esc(o.nama)}" title="${esc(o.nama)}">`
                : `<span class="av" title="${esc(o.nama)}">${inisial(o.nama)}</span>`;

            const cal = new FullCalendar.Calendar($('kal'), {
                locale: 'id',
                initialView: VIEW || (mobile ? 'listWeek' : 'dayGridMonth'),
                initialDate: TANGGAL,
                headerToolbar: false,
                height: 'auto',
                dayMaxEvents: mobile ? 2 : 3,
                nowIndicator: true,
                navLinks: true, // klik angka tanggal -> buka tampilan Hari
                slotMinTime: '06:00:00',
                slotMaxTime: '20:00:00',
                allDayText: 'Seharian',
                noEventsText: 'Tidak ada disposisi',
                eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                multiMonthMaxColumns: 3,
                events: {
                    url: URL_EVENTS,
                    failure: () => $('kal-gagal').classList.remove('hidden'),
                },

                // Isi event: judul + deretan avatar orang yang didispo / mengirim
                eventContent: (arg) => {
                    const tipe = arg.view.type;
                    if (tipe === 'multiMonthYear' || tipe === 'listWeek') return true; // pakai tampilan bawaan

                    const orang = arg.event.extendedProps.orang || [];
                    const avatarHtml = orang.slice(0, maxAvatar).map(avatar).join('');
                    const sisa = orang.length - maxAvatar;
                    const lebih = sisa > 0 ? `<span class="av av-n">+${sisa}</span>` : '';
                    const jam = arg.timeText ? `<b>${esc(arg.timeText)}</b>` : '';

                    return {
                        html: `<div class="ev-in">
                            <div class="ev-t">${jam}${esc(arg.event.title)}</div>
                            ${orang.length ? `<div class="av-row">${avatarHtml}${lebih}</div>` : ''}
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
                    $('k-judul').textContent = info.view.title;
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