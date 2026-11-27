<x-app-layout title="Disposisi">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Disposisi</h2>
    </x-slot>

    @php
        $hariC = \Illuminate\Support\Carbon::parse($hari);
        $isHariIni = $hariC->isToday();
        $sifatKelas = [
            'biasa' => 'kd-chip-gray',
            'segera' => 'kd-chip-amber',
            'sangat_segera' => 'kd-chip-red',
            'rahasia' => 'kd-chip-violet',
        ];
        $statusKelas = [
            'baru' => 'kd-st-baru',
            'dibaca' => 'kd-st-dibaca',
            'diteruskan' => 'kd-st-diteruskan',
            'selesai' => 'kd-st-selesai',
            'keluar' => 'kd-st-keluar',
        ];
        // Parameter yang ikut di setiap link (filter)
        $param = array_filter([
            'arah' => $f['arah'] !== 'semua' ? $f['arah'] : null,
            'status' => $f['status'] ?: null,
            'sifat' => $f['sifat'] ?: null,
            'q' => $f['q'] ?: null,
        ]);
        $jumlahFilter = count($param);
        $link = fn(array $tambah) => route('disposisi.index', array_filter($tambah + $param + ['tampil' => 'hari-ini']));
        $ringkasan = $ringkasan ?? ['baru' => 0];
    @endphp

    @include('disposisi._gaya')

    <div class="kd-wrap min-h-full">
        <div class="kd-container px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-5">

            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ session('error') }}</div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-3">
                @include('disposisi.kalender._tab', ['tampil' => 'hari-ini'])
                @if ($bolehKelola)
                    <a href="{{ route('disposisi.create') }}" class="kd-btn-primary w-full sm:w-auto">Input Disposisi</a>
                @endif
            </div>

            {{-- Filter --}}
            <form method="GET" action="{{ route('disposisi.index') }}" x-data="{ buka: {{ $jumlahFilter ? 'true' : 'false' }} }"
                class="kd-card p-3 sm:p-4" id="form-filter">
                <input type="hidden" name="tampil" value="hari-ini">
                <input type="hidden" name="hari" value="{{ $hari }}">

                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
                        </svg>
                        <input type="search" name="q" value="{{ $f['q'] }}" placeholder="Cari perihal, nomor surat, asal, tempat…"
                            class="kd-input" style="padding-left: 2.25rem;">
                    </div>
                    <button type="button" @click="buka = !buka" class="kd-btn-soft kd-hanya-hp" :aria-expanded="buka">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M6 12h12M10 20h4" />
                        </svg>
                        Filter{{ $jumlahFilter ? " ({$jumlahFilter})" : '' }}
                    </button>
                    <button class="kd-btn-primary kd-hanya-desktop">Terapkan</button>
                </div>

                <div class="kd-filter-grid" :class="buka ? '' : 'kd-tutup'">
                    <div>
                        <label class="kd-label" for="f-arah">Arah</label>
                        <select id="f-arah" name="arah" class="kd-input" onchange="this.form.submit()">
                            <option value="semua">Semua</option>
                            <option value="masuk" @selected($f['arah'] === 'masuk')>Diterima saya</option>
                            <option value="keluar" @selected($f['arah'] === 'keluar')>Dikirim saya</option>
                        </select>
                    </div>
                    <div>
                        <label class="kd-label" for="f-status">Status</label>
                        <select id="f-status" name="status" class="kd-input" onchange="this.form.submit()">
                            <option value="">Semua status</option>
                            <option value="perlu" @selected($f['status'] === 'perlu')>Perlu tindakan</option>
                            @foreach (\App\Models\Disposisi::STATUS as $k => $v)
                                <option value="{{ $k }}" @selected($f['status'] === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="kd-label" for="f-sifat">Sifat surat</label>
                        <select id="f-sifat" name="sifat" class="kd-input" onchange="this.form.submit()">
                            <option value="">Semua sifat</option>
                            @foreach (\App\Models\DisposisiSurat::SIFAT as $k => $v)
                                <option value="{{ $k }}" @selected($f['sifat'] === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button class="kd-btn-primary flex-1 kd-hanya-hp">Terapkan</button>
                        @if ($jumlahFilter)
                            <a href="{{ route('disposisi.index', ['tampil' => 'hari-ini', 'hari' => $hari]) }}"
                                class="kd-btn-ghost">Reset</a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="kd-card overflow-hidden">
                    {{-- Navigasi tanggal --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-4 sm:px-5 py-4 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <a href="{{ $link(['hari' => $hariC->copy()->subDay()->toDateString()]) }}"
                                class="kd-btn-ghost kd-ikon" aria-label="Hari sebelumnya">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                            </a>
                            <div class="min-w-0">
                                <p class="text-base font-semibold text-gray-900">{{ $hariC->translatedFormat('l, d F Y') }}</p>
                                <p class="text-xs text-gray-500">{{ $isHariIni ? 'Hari ini' : $hariC->diffForHumans(now()->startOfDay(), ['parts' => 1]) }}
                                    · {{ $daftarHari->count() }} disposisi</p>
                            </div>
                            <a href="{{ $link(['hari' => $hariC->copy()->addDay()->toDateString()]) }}"
                                class="kd-btn-ghost kd-ikon" aria-label="Hari berikutnya">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                        <div class="flex items-center gap-2">
                            @unless ($isHariIni)
                                <a href="{{ $link([]) }}" class="kd-btn-soft kd-kecil">Hari ini</a>
                            @endunless
                            <input type="date" value="{{ $hari }}" class="kd-input" style="width:auto"
                                onchange="if (this.value) location.href = @js($link(['hari' => '__H__'])).replace('__H__', this.value)">
                        </div>
                    </div>

                    @if ($daftarHari->isEmpty())
                        <div class="px-6 py-16 text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <p class="font-semibold text-gray-800">Tidak ada disposisi {{ $isHariIni ? 'hari ini' : 'di tanggal ini' }}</p>
                            <p class="text-sm text-gray-500">{{ $jumlahFilter ? 'Coba reset filter.' : 'Lihat tab Kalender untuk tanggal lain.' }}</p>
                        </div>
                    @else
                        {{-- Desktop: tabel --}}
                        <div class="kd-hanya-desktop overflow-x-auto">
                            <table class="kd-table w-full text-sm">
                                <thead>
                                    <tr>
                                        <th class="w-28">Waktu</th>
                                        <th>Surat</th>
                                        <th class="w-48">Dari / Kepada</th>
                                        <th class="w-56">Instruksi</th>
                                        <th class="w-36">Status</th>
                                        <th class="w-28 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($daftarHari as $r)
                                        @php $s = $r['surat']; @endphp
                                        <tr data-buka="{{ $r['surat_id'] }}" class="{{ $r['status'] === 'baru' ? 'kd-baru-row' : '' }}">
                                            <td>
                                                @if ($r['dijadwalkan'] && $s?->waktu_mulai)
                                                    <p class="font-semibold text-gray-900 tabular-nums whitespace-nowrap">{{ substr((string) $s->waktu_mulai, 0, 5) }}</p>
                                                    @if ($s->waktu_selesai)
                                                        <p class="text-[11px] text-gray-500 tabular-nums whitespace-nowrap">s.d. {{ substr((string) $s->waktu_selesai, 0, 5) }}</p>
                                                    @endif
                                                @elseif ($r['dijadwalkan'])
                                                    <p class="text-gray-500">Seharian</p>
                                                @else
                                                    <p class="text-xs text-blue-700 font-medium">Belum dibaca</p>
                                                    <p class="text-[11px] text-gray-400">{{ \Illuminate\Support\Carbon::parse($s?->tanggal_kegiatan ?? $r['batas'] ?? now())->translatedFormat('d M') }}</p>
                                                @endif
                                                @if ($s?->tempat)
                                                    <p class="text-[11px] text-gray-400 truncate" style="max-width:7rem" title="{{ $s->tempat }}">{{ $s->tempat }}</p>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    @if ($r['status'] === 'baru')
                                                        <span class="h-2 w-2 rounded-full bg-blue-600" title="Belum dibaca"></span>
                                                    @endif
                                                    <span class="kd-chip {{ $sifatKelas[$s?->sifat] ?? 'kd-chip-gray' }}">{{ $s?->sifat_label }}</span>
                                                    <span class="text-xs text-gray-400">{{ $s?->nomor_surat }}</span>
                                                </div>
                                                <p class="mt-1 font-medium text-gray-900 line-clamp-2">{{ $s?->perihal }}</p>
                                                <p class="text-xs text-gray-500">Dari {{ $s?->asal_surat }}</p>
                                            </td>
                                            <td>
                                                <p class="text-[11px] font-semibold uppercase tracking-wide {{ $r['arah'] === 'masuk' ? 'text-blue-600' : 'text-slate-500' }}">
                                                    {{ $r['arah'] === 'masuk' ? '↙ Diterima dari' : '↗ Dikirim ke' }}</p>
                                                <p class="text-gray-800">{{ $r['orang'] }}</p>
                                                <p class="text-xs text-gray-400">{{ $r['orang_unit'] }}</p>
                                            </td>
                                            <td>
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($r['instruksi'] as $ins)
                                                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[11px] text-slate-700">{{ $ins }}</span>
                                                    @endforeach
                                                </div>
                                                @if ($r['batas'])
                                                    <p class="mt-1 text-[11px] {{ $r['terlambat'] ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                                                        Batas {{ $r['batas']->translatedFormat('d M Y') }}{{ $r['terlambat'] ? ' · terlambat' : '' }}</p>
                                                @endif
                                            </td>
                                            <td><span class="kd-st {{ $statusKelas[$r['status']] ?? 'kd-st-keluar' }}">{{ $r['status_label'] }}</span></td>
                                            <td class="text-right">
                                                <button type="button" data-buka="{{ $r['surat_id'] }}" class="kd-btn-soft kd-kecil">
                                                    {{ $r['arah'] === 'masuk' && in_array($r['status'], ['baru', 'dibaca'], true) ? 'Tanggapi' : 'Buka' }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile: kartu --}}
                        <ul class="kd-hanya-hp divide-y divide-gray-100">
                            @foreach ($daftarHari as $r)
                                @php $s = $r['surat']; @endphp
                                <li>
                                    <button type="button" data-buka="{{ $r['surat_id'] }}"
                                        class="w-full text-left px-4 py-3.5 {{ $r['status'] === 'baru' ? 'kd-baru-row' : '' }}">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-center gap-1.5 text-xs">
                                                <span class="font-semibold text-gray-900 tabular-nums">
                                                    {{ $r['dijadwalkan'] ? ($s?->waktu_label ?? 'Seharian') : 'Belum dibaca' }}</span>
                                                <span class="kd-chip {{ $sifatKelas[$s?->sifat] ?? 'kd-chip-gray' }}">{{ $s?->sifat_label }}</span>
                                            </div>
                                            <span class="kd-st {{ $statusKelas[$r['status']] ?? 'kd-st-keluar' }}">{{ $r['status_label'] }}</span>
                                        </div>
                                        <p class="mt-1.5 font-medium text-gray-900 line-clamp-2">{{ $s?->perihal }}</p>
                                        <p class="mt-0.5 text-xs text-gray-500">
                                            {{ $r['arah'] === 'masuk' ? 'Dari' : 'Ke' }} {{ $r['orang'] }}
                                            @if ($s?->tempat) · {{ $s->tempat }} @endif
                                        </p>
                                        @if ($r['instruksi'])
                                            <p class="mt-1 text-[11px] text-gray-500">{{ implode(', ', $r['instruksi']) }}</p>
                                        @endif
                                        @if ($r['terlambat'])
                                            <p class="mt-1 text-[11px] font-semibold text-red-600">Lewat batas {{ $r['batas']->translatedFormat('d M') }}</p>
                                        @endif
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                @include('disposisi._google', ['google' => $google, 'ringkas' => true])

        </div>
    </div>

    @include('disposisi._popup', ['tanggal' => $hari, 'bukaSurat' => $bukaSurat, 'tampil' => 'hari-ini'])
</x-app-layout>
