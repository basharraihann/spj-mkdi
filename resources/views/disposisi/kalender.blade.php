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
    <style>
    #k-view button.aktif {
        background: #ea580c !important;
        color: #fff !important;
        border-color: #ea580c !important;
        box-shadow: none !important;
    }
</style>

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

    {{-- Popup detail + feedback (dipakai bersama tab Daftar) --}}
    @include('disposisi._popup', ['dari' => 'kalender', 'tanggal' => $tanggal, 'bukaSurat' => $bukaSurat])

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/id.global.min.js"></script>
    <script>
        (function () {
            // ---------------- Konfigurasi dari server ----------------
            const URL_EVENTS = @json(route('kalender.events', $param));
            const TANGGAL = @json($tanggal);
            const VIEW = @json($view);

            // ---------------- Helper ----------------
            const $ = (id) => document.getElementById(id);
            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
            }[c]));
            const ymd = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            const inisial = (nama) => esc((nama || '?').trim().charAt(0).toUpperCase());

            // Popup ada di disposisi/_popup (window.dispoPopup)

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
                    window.dispoPopup.buka(info.event.extendedProps.surat_id);
                },

                eventMouseEnter: (info) => window.dispoPopup.intip(info.event.extendedProps.surat_id),

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