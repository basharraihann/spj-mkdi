<x-app-layout title="Disposisi">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Disposisi</h2>
    </x-slot>

    @php
        $badgeSifat = [
            'biasa' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'segera' => 'bg-amber-50 text-amber-800 ring-amber-200',
            'sangat_segera' => 'bg-red-50 text-red-700 ring-red-200',
            'rahasia' => 'bg-purple-50 text-purple-700 ring-purple-200',
        ];
        $labelSifat = [
            'biasa' => 'Biasa',
            'segera' => 'Segera',
            'sangat_segera' => 'Sangat segera',
            'rahasia' => 'Rahasia',
        ];
        $labelStatus = [
            'proses' => 'Proses',
            'terlambat' => 'Terlambat',
            'selesai' => 'Selesai',
        ];

        // Filter aktif (dibaca langsung dari request supaya view mandiri)
        $q = (string) request('q', '');
        $filter = (string) request('status', '');
        $fSifat = (string) request('sifat', '');
        $fUnit = (string) request('unit', '');
        $fDari = (string) request('dari', '');
        $fSampai = (string) request('sampai', '');
        $fUrut = (string) request('urut', 'terbaru');
        $adaFilter = collect(['q', 'status', 'sifat', 'unit', 'dari', 'sampai'])->contains(fn($k) => request()->filled($k));
        $adaFilterLanjut = collect(['status', 'sifat', 'unit', 'dari', 'sampai'])->contains(fn($k) => request()->filled($k)) || $fUrut !== 'terbaru';
        $resetUrl = ($adaFilter || $fUrut !== 'terbaru') ? route('disposisi.index') : null;

        $tgl = fn($d) => $d ? \Illuminate\Support\Carbon::parse($d)->translatedFormat('d M Y') : '–';

        // Ringkasan posisi, progres, dan batas waktu per surat
        $info = function ($s) {
            $posisi = $s->disposisi->filter(fn($d) => in_array($d->status, \App\Models\Disposisi::TERBUKA, true));
            $pekerja = $s->disposisi->where('status', '!=', 'diteruskan');
            $total = $pekerja->count();
            $selesai = $pekerja->where('status', 'selesai')->count();

            return [
                'posisi' => $posisi,
                'total' => $total,
                'selesai' => $selesai,
                'persen' => $total > 0 ? (int) round($selesai / $total * 100) : 0,
                'batas' => $posisi->pluck('batas_waktu')->filter()->sort()->first(),
                'terlambat' => $posisi->contains(fn($d) => $d->terlambat),
                'selesaiSurat' => $s->status === 'selesai',
            ];
        };

        // Lampiran surat: link eksternal (link_berkas) atau file upload (file_path), sama seperti halaman detail
        $berkas = function ($s) {
            if ($s->link_berkas) {
                $host = parse_url($s->link_berkas, PHP_URL_HOST) ?: 'Tautan';
                return ['url' => $s->link_berkas, 'nama' => $host, 'ext' => 'LINK'];
            }
            if ($s->file_path) {
                $nama = $s->file_name ?: basename($s->file_path);
                return [
                    'url' => route('disposisi.berkas', $s),
                    'nama' => $nama,
                    'ext' => strtoupper(pathinfo($nama, PATHINFO_EXTENSION)) ?: 'FILE',
                ];
            }
            return null;
        };

        // Tempat kegiatan dan link meeting.
        // Link diambil dari kolom link_meeting jika ada; jika tidak, dari kolom tempat yang berisi URL.
        $lokasi = function ($s) {
            $tempat = trim((string) ($s->tempat ?? ''));
            $link = '';
            foreach (['link_meeting', 'link_zoom', 'link_online', 'meeting_link', 'url_meeting', 'link_rapat', 'tautan'] as $kolom) {
                if (! empty($s->{$kolom})) {
                    $link = trim((string) $s->{$kolom});
                    break;
                }
            }
            if ($link === '') {
                // Cadangan: kolom lain mana pun yang isinya URL, selain kolom lampiran
                foreach ($s->getAttributes() as $kolom => $nilai) {
                    if (in_array($kolom, ['link_berkas', 'file_path', 'file_name'], true)) {
                        continue;
                    }
                    if (is_string($nilai) && preg_match('~^\s*https?://~i', $nilai)) {
                        $link = trim($nilai);
                        break;
                    }
                }
            }
            if ($link === '' && \Illuminate\Support\Str::startsWith($tempat, ['http://', 'https://'])) {
                $link = $tempat;
                $tempat = '';
            }
            if ($link === '') {
                // tempat berisi teks dan URL, mis. "Online https://zoom.us/j/123"
                if (preg_match('~https?://\S+~i', $tempat, $m)) {
                    $link = $m[0];
                    $tempat = trim(str_replace($m[0], '', $tempat));
                }
            }
            return [
                'tempat' => $tempat,
                'link' => $link,
                'online' => $link !== '' || strcasecmp($tempat, 'online') === 0,
                'linkAman' => $link !== '' && \Illuminate\Support\Str::startsWith($link, ['http://', 'https://']),
                'host' => $link !== '' ? (parse_url($link, PHP_URL_HOST) ?: $link) : '',
            ];
        };

        // Pola garis miring untuk kartu surat yang sudah selesai
        $polaSelesai = 'background-image: repeating-linear-gradient(135deg, transparent 0 12px, rgba(148,163,184,.14) 12px 24px);';
    @endphp

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
        @endif

        {{-- Tab + pencarian + filter + daftar dalam satu kartu --}}
        <x-disposisi.kartu-atas tampil="daftar"
            :q="$q" placeholder="Cari nomor surat, perihal, asal surat, nomor agenda, nama penerima…"
            :lanjut="$adaFilterLanjut" :reset-url="$resetUrl" :boleh-catat="$bolehCatat">

            <x-disposisi.pilih name="status" label="Status">
                <option value="">Semua status</option>
                @foreach ($labelStatus as $k => $v)
                    <option value="{{ $k }}" @selected($filter === $k)>{{ $v }}</option>
                @endforeach
            </x-disposisi.pilih>

            <x-disposisi.pilih name="sifat" label="Sifat surat">
                <option value="">Semua sifat</option>
                @foreach ($labelSifat as $k => $v)
                    <option value="{{ $k }}" @selected($fSifat === $k)>{{ $v }}</option>
                @endforeach
            </x-disposisi.pilih>

            <x-disposisi.pilih name="unit" label="Posisi di unit">
                <option value="">Semua unit</option>
                @foreach ($daftarUnit ?? [] as $u)
                    <option value="{{ $u->id }}" @selected($fUnit === (string) $u->id)>{{ $u->nama }}</option>
                @endforeach
            </x-disposisi.pilih>

            <x-disposisi.kolom name="dari" label="Diterima dari" type="date" :auto="true"
                :value="$fDari" :max="$fSampai ?: null" />

            <x-disposisi.kolom name="sampai" label="Diterima sampai" type="date" :auto="true"
                :value="$fSampai" :min="$fDari ?: null" />

            <x-disposisi.pilih name="urut" label="Urutkan">
                <option value="terbaru" @selected($fUrut === 'terbaru')>Terbaru diterima</option>
                <option value="terlama" @selected($fUrut === 'terlama')>Terlama diterima</option>
                <option value="batas" @selected($fUrut === 'batas')>Batas waktu terdekat</option>
                <option value="jadwal" @selected($fUrut === 'jadwal')>Jadwal kegiatan terdekat</option>
            </x-disposisi.pilih>

            {{-- Daftar surat, menyatu di bawah filter --}}
            <x-slot name="bawah">
                @if ($surat->isEmpty())
                    <div class="px-6 py-14 text-center">
                        <div class="mx-auto mb-3 grid h-11 w-11 place-items-center rounded-2xl bg-slate-100 text-slate-500">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 3h7l5 5v11a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-900">Belum ada disposisi</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $adaFilter ? 'Coba ubah atau reset pencarian.' : 'Klik "Input disposisi" untuk mencatat surat masuk.' }}</p>
                    </div>
                @else
                    <ul class="space-y-2.5 p-3 sm:p-4">
                        @foreach ($surat as $s)
                            @php
                                $i = $info($s);
                                $posisi = $i['posisi'];
                                $selesaiSurat = $i['selesaiSurat'];
                                $file = $berkas($s);
                                $lok = $lokasi($s);
// Eselon II yang meneruskan tapi ikut serta (kolom `ikut`)
$ikut = $s->disposisi
    ->where('ikut', true)
    ->reject(fn($d) => $posisi->contains('kepada_user_id', $d->kepada_user_id))
    ->unique('kepada_user_id')
    ->values();
$tampilan = $posisi->concat($ikut)->values();
                                

                                // Blok tanggal: tanggal kegiatan, atau tanggal diterima jika tidak ada kegiatan
                                $adaKegiatan = (bool) $s->tanggal_kegiatan;
                                $tglBlok = $adaKegiatan ? $s->tanggal_kegiatan : $s->tanggal_terima;
                                $blok = $tglBlok ? \Illuminate\Support\Carbon::parse($tglBlok) : null;
                                $hariIni = $blok?->isToday() ?? false; // warna oranye hanya untuk hari ini

                                // Waktu disposisi terakhir dibuat pada surat ini
                                $terakhir = $s->disposisi->sortByDesc('created_at')->first()?->created_at;
                            @endphp
                            <li class="group relative flex flex-col gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-slate-300 hover:shadow-sm md:flex-row md:items-center md:gap-0 {{ $selesaiSurat ? 'bg-slate-50' : 'bg-white' }}"
                                @if ($selesaiSurat) style="{{ $polaSelesai }}" @endif>

                                {{-- Blok tanggal --}}
                                <div class="flex flex-none items-baseline gap-2 md:w-16 md:flex-col md:items-center md:gap-0 md:text-center" title="{{ $adaKegiatan ? 'Tanggal kegiatan' : 'Tanggal diterima' }}">
                                    <span class="text-xs font-medium {{ $hariIni ? 'text-orange-600' : 'text-slate-500' }}">{{ $blok?->translatedFormat('D') ?? '–' }}</span>
                                    <span class="text-2xl font-semibold leading-none md:mt-0.5 {{ $hariIni ? 'text-orange-600' : 'text-slate-700' }}">{{ $blok?->format('d') ?? '–' }}</span>
                                    <span class="text-[11px] text-slate-400 md:mt-0.5">{{ $blok?->translatedFormat('M Y') }}</span>
                                    @unless ($adaKegiatan)
                                        <span class="text-[10px] uppercase tracking-wide text-slate-300">Diterima</span>
                                    @endunless
                                </div>

                                <div class="hidden h-14 w-px flex-none bg-slate-200 md:mx-4 md:block"></div>

                                {{-- Jam, dari siapa, tempat --}}
                                <div class="space-y-1.5 text-xs text-slate-600 md:w-48 md:flex-none">
                                    <p class="flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5 flex-none text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @if ($adaKegiatan)
                                            <span class="min-w-0 truncate">{{ $s->waktu_label }}</span>
                                        @else
                                            <span class="text-slate-400">Tanpa jadwal</span>
                                        @endif
                                    </p>

                                    <p class="flex items-center gap-1.5" title="Dari: {{ $s->asal_surat }}">
                                        <svg class="h-3.5 w-3.5 flex-none text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                        <span class="min-w-0 truncate">{{ $s->asal_surat }}</span>
                                    </p>

                                    @if ($lok['tempat'] !== '' || $lok['online'])
                                        @php
                                            $labelTempat = $lok['tempat'] !== '' ? $lok['tempat'] : 'Online';
                                            $ikonVideo = $lok['online'] && strcasecmp($labelTempat, 'online') === 0;
                                        @endphp
                                        <p class="flex items-center gap-1.5">
                                            @if ($ikonVideo)
                                                <svg class="h-3.5 w-3.5 flex-none text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.55-2.28A1 1 0 0121 8.62v6.76a1 1 0 01-1.45.9L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                            @else
                                                <svg class="h-3.5 w-3.5 flex-none text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.66 16.66L13.41 20.9a2 2 0 01-2.82 0l-4.25-4.24a8 8 0 1111.32 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            @endif
                                            <span class="min-w-0 truncate" title="{{ $labelTempat }}">{{ $labelTempat }}</span>
                                        </p>

                                        {{-- Link zoom / meeting, tepat di bawah lokasi --}}
                                        @if ($lok['link'] !== '')
                                            <p class="flex items-center gap-1.5">
                                                <svg class="h-3.5 w-3.5 flex-none text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.83 10.17a4 4 0 010 5.66l-3 3a4 4 0 01-5.66-5.66l1.5-1.5m6.66-1.34a4 4 0 010-5.66l3-3a4 4 0 115.66 5.66l-1.5 1.5" /></svg>
                                                @if ($lok['linkAman'])
                                                    <a href="{{ $lok['link'] }}" target="_blank" rel="noopener noreferrer" title="{{ $lok['link'] }}"
                                                        class="relative z-10 min-w-0 truncate font-medium text-blue-600 hover:underline">{{ $lok['host'] }}</a>
                                                @else
                                                    <span class="min-w-0 truncate text-slate-500">{{ $lok['link'] }}</span>
                                                @endif
                                            </p>
                                        @endif
                                    @endif
                                </div>

                                {{-- Perihal dan penerima --}}
                                <div class="min-w-0 flex-1 md:px-4">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="inline-flex rounded-full px-2 py-px text-[11px] font-medium ring-1 ring-inset {{ $badgeSifat[$s->sifat] ?? $badgeSifat['biasa'] }}">{{ $s->sifat_label }}</span>
                                        <span class="text-[11px] text-slate-400">{{ $s->nomor_surat }}@if ($s->nomor_agenda) · Agenda {{ $s->nomor_agenda }}@endif</span>
                                    </div>
                                    <p class="mt-1 line-clamp-2 text-sm font-medium text-slate-900">{{ $s->perihal }}</p>

                                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5">
    @if ($tampilan->isNotEmpty())
        <div class="flex min-w-0 items-center gap-2">
            <div class="flex flex-none -space-x-1.5">
                @foreach ($tampilan->take(4) as $d)
                    <x-avatar :user="$d->kepada" class="h-6 w-6 text-[9px] ring-2 ring-white" />
                @endforeach
                @if ($tampilan->count() > 4)
                    <span class="grid h-6 w-6 place-items-center rounded-full bg-slate-100 text-[9px] font-medium text-slate-600 ring-2 ring-white">+{{ $tampilan->count() - 4 }}</span>
                @endif
            </div>
            <p class="min-w-0 truncate text-xs text-slate-500">
                {{ $posisi->pluck('kepada.name')->filter()->take(2)->implode(', ') }}@if ($posisi->count() > 2) +{{ $posisi->count() - 2 }} @endif
                @foreach ($ikut as $d)
                    <span class="ml-1 inline-flex rounded-full bg-blue-50 px-1.5 py-px text-[10px] font-medium text-blue-700 ring-1 ring-inset ring-blue-200">{{ $d->kepada?->name }} ikut</span>
                @endforeach
            </p>
        </div>
    @else
        <p class="text-xs text-slate-400">{{ $selesaiSurat ? 'Tidak ada yang menunggu tindakan' : 'Belum ada penerima aktif' }}</p>
    @endif
</div>
                                </div>

                                {{-- Lampiran surat + waktu, tepat sebelum status --}}
                                <div class="space-y-1.5 md:w-56 md:flex-none md:px-4">
                                    @if ($file)
                                        <a href="{{ $file['url'] }}" target="_blank" rel="noopener noreferrer" title="Buka lampiran: {{ $file['nama'] }}"
                                            class="relative z-10 inline-flex max-w-full items-center gap-1.5 rounded-md border border-slate-200 bg-white px-2 py-0.5 text-[11px] text-slate-600 transition hover:border-blue-300 hover:text-blue-700">
                                            <svg class="h-3 w-3 flex-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.17 7l-6.59 6.59a2 2 0 102.83 2.83l6.58-6.59a4 4 0 10-5.66-5.66L5.76 10.76a6 6 0 108.48 8.48L20 13.5" /></svg>
                                            <span class="flex-none font-semibold">{{ $file['ext'] }}</span>
                                            <span class="min-w-0 truncate">{{ $file['nama'] }}</span>
                                        </a>
                                    @endif

                                    @if ($terakhir)
                                        <p class="flex items-center gap-1.5 text-[11px] text-slate-500" title="Waktu disposisi terakhir dibuat">
                                            <svg class="h-3 w-3 flex-none text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <span class="min-w-0 truncate">Didisposisi {{ $terakhir->translatedFormat('d M Y, H:i') }}</span>
                                        </p>
                                    @endif
                                </div>

                                {{-- Status, batas waktu, progres, aksi --}}
                                <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-2.5 md:w-56 md:flex-none md:border-0 md:pl-4 md:pt-0">
                                    <div class="min-w-0 flex-1 space-y-1">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-px text-[11px] font-medium ring-1 ring-inset {{ $selesaiSurat ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-blue-50 text-blue-700 ring-blue-200' }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $selesaiSurat ? 'bg-emerald-500' : 'bg-blue-500' }}"></span>
                                            {{ $selesaiSurat ? 'Selesai' : 'Proses' }}
                                        </span>

                                        @if ($i['batas'] && ! $selesaiSurat)
                                            <p class="text-[11px] {{ $i['terlambat'] ? 'font-semibold text-red-600' : 'text-slate-500' }}">
                                                Batas {{ $tgl($i['batas']) }}{{ $i['terlambat'] ? ' · Terlambat' : '' }}
                                            </p>
                                        @endif

                                        @if ($i['total'] > 0)
                                            <div class="flex items-center gap-2">
                                                <div class="h-1 w-14 overflow-hidden rounded-full bg-slate-200/70" role="progressbar" aria-valuenow="{{ $i['persen'] }}" aria-valuemin="0" aria-valuemax="100">
                                                    <div class="h-full rounded-full {{ $i['persen'] === 100 ? 'bg-emerald-500' : 'bg-blue-500' }}" style="width: {{ $i['persen'] }}%"></div>
                                                </div>
                                                <span class="text-[11px] text-slate-500">{{ $i['selesai'] }}/{{ $i['total'] }}</span>
                                            </div>
                                        @else
                                            <p class="text-[11px] text-slate-400">Belum didisposisikan</p>
                                        @endif
                                    </div>

                                    <span class="inline-flex flex-none items-center justify-center whitespace-nowrap rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-medium text-slate-700 transition group-hover:bg-slate-50">Detail</span>
                                </div>

                                {{-- Seluruh kartu bisa diklik; tautan lampiran dan meeting berada di atasnya (z-10) --}}
                                <a href="{{ route('disposisi.show', $s) }}" class="absolute inset-0 rounded-xl focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100" aria-label="Detail disposisi {{ $s->perihal }}"></a>
                            </li>
                        @endforeach
                    </ul>

                    @if ($surat->hasPages())
                        <div class="border-t border-slate-100 px-6 py-4">{{ $surat->links() }}</div>
                    @endif
                @endif
            </x-slot>
        </x-disposisi.kartu-atas>
    </div>
</x-app-layout>