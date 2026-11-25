<x-app-layout title="Disposisi">

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Disposisi
        </h2>
    </x-slot>
    @include('disposisi._gaya')

    @php
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
        ];
        $tabs = [
            'surat' => ['Semua Disposisi', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            'masuk' => ['Masuk', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            'terkirim' => ['Terkirim', 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8'],
        ];
        $tgl = fn($d) => $d ? \Illuminate\Support\Carbon::parse($d)->translatedFormat('d M Y') : '–';
        $adaFilter = $q !== '' || $filter !== '';
    @endphp

    <div class="kd-wrap min-h-full">
        <div class="kd-container px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-5">

            {{-- Judul + tab --}}
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <p class="text-base font-medium text-gray-700">Pencatatan &amp; pemantauan surat masuk</p>
                    <p class="text-xs text-gray-400 mt-0.5">Catat surat masuk, kirim disposisi, dan pantau posisi surat</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <nav class="kd-tabs w-full sm:w-auto overflow-x-auto">
                        @foreach ($tabs as $k => [$label, $ikon])
                            <a href="{{ route('disposisi.index', ['tab' => $k]) }}" class="kd-tab flex-1 justify-center {{ $tab === $k ? 'aktif' : '' }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}" />
                                </svg>
                                @if ($k === 'surat')
                                    <span class="kd-hanya-desktop">{{ $label }}</span><span class="kd-hanya-hp">Semua</span>
                                @else
                                    {{ $label }}
                                @endif
                                @if ($k === 'masuk' && $ringkasan['baru'] > 0)
                                    <span class="kd-dot">{{ $ringkasan['baru'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </nav>
                    <a href="{{ route('kalender.index', ['tab' => 'kalender']) }}" class="kd-btn-outline flex-1 sm:flex-none">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Kalender
                    </a>
                    @if ($bolehCatat)
                        <a href="{{ route('disposisi.create') }}" class="kd-btn-primary flex-1 sm:flex-none">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Input Disposisi
                        </a>
                    @endif
                </div>
            </div>

            {{-- Ringkasan disposisi saya --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach ([
                    ['Belum dibaca', $ringkasan['baru'], '#2563eb', 'baru'],
                    ['Perlu tindakan', $ringkasan['perlu'], '#0369a1', 'perlu'],
                    ['Lewat batas waktu', $ringkasan['terlambat'], '#dc2626', 'perlu'],
                    ['Selesai', $ringkasan['selesai'], '#15803d', 'selesai'],
                ] as [$label, $angka, $warna, $fs])
                    <a href="{{ route('disposisi.index', ['tab' => 'masuk', 'status' => $fs]) }}" class="kd-card kd-stat hover:border-blue-200 transition">
                        <p>{{ $label }}</p>
                        <p style="color: {{ $warna }}">{{ $angka }}</p>
                    </a>
                @endforeach
            </div>

            <div class="kd-card overflow-hidden">
                {{-- Filter --}}
                <form method="GET" class="flex flex-col sm:flex-row gap-2 p-3 sm:p-4 border-b border-gray-100">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="relative flex-1">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
                        </svg>
                        <input type="search" name="q" value="{{ $q }}" placeholder="Cari nomor surat, perihal, asal surat, nomor agenda…"
                            class="kd-input" style="padding-left: 2.25rem;">
                    </div>
                    <select name="status" class="kd-input kd-auto-sm" style="min-width: 11rem" onchange="this.form.submit()">
                        <option value="">Semua status</option>
                        @if ($tab === 'surat')
                            <option value="proses" @selected($filter === 'proses')>Proses</option>
                            <option value="selesai" @selected($filter === 'selesai')>Selesai</option>
                        @else
                            <option value="perlu" @selected($filter === 'perlu')>Perlu tindakan</option>
                            @foreach (\App\Models\Disposisi::STATUS as $k => $v)
                                <option value="{{ $k }}" @selected($filter === $k)>{{ $v }}</option>
                            @endforeach
                        @endif
                    </select>
                    <div class="flex gap-2">
                        <button class="kd-btn-primary flex-1">Cari</button>
                        @if ($adaFilter)
                            <a href="{{ route('disposisi.index', ['tab' => $tab]) }}" class="kd-btn-ghost">Reset</a>
                        @endif
                    </div>
                </form>

                @if ($tab === 'surat')
                    @if ($surat->isEmpty())
                        <div class="px-6 py-16 text-center">
                            <p class="font-semibold text-gray-800">Belum ada disposisi</p>
                            <p class="text-sm text-gray-500">{{ $adaFilter ? 'Coba reset pencarian.' : 'Klik "Input Disposisi" untuk mencatat surat masuk.' }}</p>
                        </div>
                    @else
                        {{-- Desktop --}}
                        <div class="kd-hanya-desktop overflow-x-auto">
                            <table class="kd-table w-full text-sm">
                                <thead>
                                    <tr>
                                        <th>Surat</th>
                                        <th class="w-32">Diterima</th>
                                        <th class="w-36">Jadwal</th>
                                        <th class="w-56">Posisi sekarang</th>
                                        <th class="w-28">Status</th>
                                        <th class="w-20"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($surat as $s)
                                        @php $posisi = $s->disposisi->filter(fn($d) => in_array($d->status, \App\Models\Disposisi::TERBUKA, true)); @endphp
                                        <tr onclick="location.href='{{ route('disposisi.show', $s) }}'">
                                            <td>
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    <span class="kd-chip {{ $sifatKelas[$s->sifat] ?? 'kd-chip-gray' }}">{{ $s->sifat_label }}</span>
                                                    <span class="text-xs text-gray-400">{{ $s->nomor_surat }}</span>
                                                </div>
                                                <p class="mt-1 font-medium text-gray-900 line-clamp-2">{{ $s->perihal }}</p>
                                                <p class="text-xs text-gray-500">Dari {{ $s->asal_surat }}@if ($s->nomor_agenda) · Agenda {{ $s->nomor_agenda }}@endif</p>
                                            </td>
                                            <td class="whitespace-nowrap text-gray-600">{{ $tgl($s->tanggal_terima) }}</td>
                                            <td class="text-gray-600">
                                                @if ($s->tanggal_kegiatan)
                                                    <p class="whitespace-nowrap">{{ $tgl($s->tanggal_kegiatan) }}</p>
                                                    <p class="text-[11px] text-gray-400">{{ $s->waktu_label }}</p>
                                                @else
                                                    <span class="text-gray-300">–</span>
                                                @endif
                                            </td>
                                            <td>
                                                @forelse ($posisi->take(3) as $d)
                                                    <p class="text-xs text-gray-800">{{ $d->kepada?->name ?? '-' }}
                                                        <span class="text-gray-400">· {{ $d->kepada?->unit?->nama }}</span></p>
                                                @empty
                                                    <span class="text-xs text-gray-400">–</span>
                                                @endforelse
                                                @if ($posisi->count() > 3)
                                                    <p class="text-xs text-gray-400">+{{ $posisi->count() - 3 }} lainnya</p>
                                                @endif
                                            </td>
                                            <td><span class="kd-st {{ $s->status === 'selesai' ? 'kd-st-selesai' : 'kd-st-baru' }}">{{ $s->status === 'selesai' ? 'Selesai' : 'Proses' }}</span></td>
                                            <td class="text-right"><a href="{{ route('disposisi.show', $s) }}" class="kd-btn-soft kd-kecil">Detail</a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- HP --}}
                        <ul class="kd-hanya-hp divide-y divide-gray-100">
                            @foreach ($surat as $s)
                                @php $posisi = $s->disposisi->filter(fn($d) => in_array($d->status, \App\Models\Disposisi::TERBUKA, true)); @endphp
                                <li>
                                    <a href="{{ route('disposisi.show', $s) }}" class="block px-4 py-3.5">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <span class="kd-chip {{ $sifatKelas[$s->sifat] ?? 'kd-chip-gray' }}">{{ $s->sifat_label }}</span>
                                                <span class="text-[11px] text-gray-400">{{ $tgl($s->tanggal_terima) }}</span>
                                            </div>
                                            <span class="kd-st {{ $s->status === 'selesai' ? 'kd-st-selesai' : 'kd-st-baru' }}">{{ $s->status === 'selesai' ? 'Selesai' : 'Proses' }}</span>
                                        </div>
                                        <p class="mt-1.5 font-medium text-gray-900 line-clamp-2">{{ $s->perihal }}</p>
                                        <p class="text-xs text-gray-500">{{ $s->nomor_surat }} · {{ $s->asal_surat }}</p>
                                        @if ($posisi->isNotEmpty())
                                            <p class="mt-1 text-[11px] text-blue-700">Di: {{ $posisi->pluck('kepada.name')->filter()->take(2)->implode(', ') }}{{ $posisi->count() > 2 ? ' +' . ($posisi->count() - 2) : '' }}</p>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <div class="px-4 py-4">{{ $surat->links() }}</div>
                    @endif
                @else
                    @if ($disposisi->isEmpty())
                        <div class="px-6 py-16 text-center">
                            <p class="font-semibold text-gray-800">{{ $tab === 'masuk' ? 'Belum ada disposisi untuk Anda' : 'Belum ada disposisi yang Anda kirim' }}</p>
                            @if ($adaFilter)
                                <p class="text-sm text-gray-500">Coba reset pencarian.</p>
                            @endif
                        </div>
                    @else
                        {{-- Desktop --}}
                        <div class="kd-hanya-desktop overflow-x-auto">
                            <table class="kd-table w-full text-sm">
                                <thead>
                                    <tr>
                                        <th>Surat</th>
                                        <th class="w-48">{{ $tab === 'masuk' ? 'Dari' : 'Kepada' }}</th>
                                        <th class="w-56">Instruksi</th>
                                        <th class="w-32">Batas waktu</th>
                                        <th class="w-32">Status</th>
                                        <th class="w-28"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($disposisi as $d)
                                        @php
                                            $orang = $tab === 'masuk' ? $d->dari : $d->kepada;
                                            $baru = $tab === 'masuk' && $d->status === 'baru';
                                        @endphp
                                        <tr class="{{ $baru ? 'kd-baru-row' : '' }}" onclick="location.href='{{ route('disposisi.show', $d->surat_id) }}'">
                                            <td>
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    @if ($baru)
                                                        <span class="h-2 w-2 rounded-full bg-blue-600" title="Belum dibaca"></span>
                                                    @endif
                                                    @if ($d->surat)
                                                        <span class="kd-chip {{ $sifatKelas[$d->surat->sifat] ?? 'kd-chip-gray' }}">{{ $d->surat->sifat_label }}</span>
                                                    @endif
                                                    <span class="text-xs text-gray-400">{{ $d->surat?->nomor_surat }}</span>
                                                </div>
                                                <p class="mt-1 line-clamp-2 {{ $baru ? 'font-semibold text-gray-900' : 'font-medium text-gray-800' }}">{{ $d->surat?->perihal }}</p>
                                                <p class="text-xs text-gray-500">Dari {{ $d->surat?->asal_surat }} · {{ $d->created_at?->translatedFormat('d M Y H:i') }}</p>
                                            </td>
                                            <td>
                                                <p class="text-gray-800">{{ $orang?->name ?? '-' }}</p>
                                                <p class="text-xs text-gray-400">{{ $orang?->unit?->nama }}</p>
                                            </td>
                                            <td>
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($d->instruksi_label as $ins)
                                                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[11px] text-slate-700">{{ $ins }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap {{ $d->terlambat ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                                {{ $tgl($d->batas_waktu) }}
                                                @if ($d->terlambat)
                                                    <p class="text-[11px]">Terlambat</p>
                                                @endif
                                            </td>
                                            <td><span class="kd-st {{ $statusKelas[$d->status] ?? 'kd-st-keluar' }}">{{ $d->status_label }}</span></td>
                                            <td class="text-right">
                                                <a href="{{ route('disposisi.show', $d->surat_id) }}" class="kd-btn-soft kd-kecil whitespace-nowrap">
                                                    {{ $tab === 'masuk' && in_array($d->status, \App\Models\Disposisi::TERBUKA, true) ? 'Tindak lanjuti' : 'Detail' }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- HP --}}
                        <ul class="kd-hanya-hp divide-y divide-gray-100">
                            @foreach ($disposisi as $d)
                                @php
                                    $orang = $tab === 'masuk' ? $d->dari : $d->kepada;
                                    $baru = $tab === 'masuk' && $d->status === 'baru';
                                @endphp
                                <li>
                                    <a href="{{ route('disposisi.show', $d->surat_id) }}" class="block px-4 py-3.5 {{ $baru ? 'kd-baru-row' : '' }}">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                @if ($d->surat)
                                                    <span class="kd-chip {{ $sifatKelas[$d->surat->sifat] ?? 'kd-chip-gray' }}">{{ $d->surat->sifat_label }}</span>
                                                @endif
                                                <span class="text-[11px] text-gray-400">{{ $d->created_at?->translatedFormat('d M') }}</span>
                                            </div>
                                            <span class="kd-st {{ $statusKelas[$d->status] ?? 'kd-st-keluar' }}">{{ $d->status_label }}</span>
                                        </div>
                                        <p class="mt-1.5 line-clamp-2 {{ $baru ? 'font-semibold text-gray-900' : 'font-medium text-gray-800' }}">{{ $d->surat?->perihal }}</p>
                                        <p class="text-xs text-gray-500">{{ $tab === 'masuk' ? 'Dari' : 'Ke' }} {{ $orang?->name ?? '-' }}
                                            @if ($d->batas_waktu)
                                                · <span class="{{ $d->terlambat ? 'text-red-600 font-semibold' : '' }}">batas {{ $d->batas_waktu->translatedFormat('d M') }}</span>
                                            @endif
                                        </p>
                                        @if ($d->instruksi_label)
                                            <p class="mt-1 text-[11px] text-gray-500">{{ implode(', ', $d->instruksi_label) }}</p>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <div class="px-4 py-4">{{ $disposisi->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
