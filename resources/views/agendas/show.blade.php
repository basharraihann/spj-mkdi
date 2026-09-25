<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $agenda->uraian_kegiatan }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Header info card --}}
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-6">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500 mb-5">
                    <span class="inline-flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ $agenda->tujuan }}
                    </span>
                    <span class="text-gray-300">•</span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ $agenda->tanggal_mulai->translatedFormat('d M Y') }} –
                        {{ $agenda->tanggal_selesai->translatedFormat('d M Y') }}
                    </span>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('agendas.edit', $agenda) }}"
                        class="inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-semibold border border-blue-500 text-blue-600 bg-white hover:bg-blue-50 transition">
                        Edit Agenda dan Peserta
                    </a>
                    <a href="{{ route('agendas.peserta', $agenda) }}"
                        class="inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-semibold border border-blue-500 text-blue-600 bg-white hover:bg-blue-50 transition">
                        Edit Biaya
                    </a>
                </div>
            </div>

            {{-- Kartu Dokumen Pendukung (langsung di sini, gak perlu ke halaman terpisah) --}}
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-6 space-y-5"
                x-data="{ openDokumen: {{ session('success') && str_contains(session('success'), 'okumen') ? 'true' : 'false' }} }">
                @php
                    $totalDokumenKategori = count($kategoriList);
                    $dokTerupload = collect($kategoriList)->keys()->filter(fn($key) => $agenda->dokumen($key))->count();
                    $dokPersen = $totalDokumenKategori > 0 ? round(($dokTerupload / $totalDokumenKategori) * 100) : 0;
                @endphp

                <button type="button" @click="openDokumen = !openDokumen"
                    class="w-full flex items-center justify-between gap-4 text-left">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Dokumen Pendukung</h3>
                        <p class="text-sm text-gray-400">Upload {{ $totalDokumenKategori }} dokumen pendukung agenda
                            ini.
                        </p>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $dokTerupload === $totalDokumenKategori ? 'bg-green-50 text-green-600' : 'bg-amber-50 text-amber-600' }}">
                            {{ $dokTerupload }}/{{ $totalDokumenKategori }} terupload
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transition-transform"
                            :class="{ 'rotate-180': openDokumen }" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </button>

                <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 rounded-full transition-all" style="width: {{ $dokPersen }}%"></div>
                </div>

                <div x-show="openDokumen" x-cloak x-transition.duration.200ms class="pt-1">

                    {{-- Form hapus dipisah per kategori (pakai atribut form="" biar gak nested <form> di dalam form
                        upload). Konfirmasi hapusnya dipicu dari tombol di bawah lewat confirmDelete() (SweetAlert2),
                        BUKAN pakai onsubmit/confirm() bawaan browser lagi. --}}
                        @foreach ($kategoriList as $key => $label)
                            @php $existing = $agenda->dokumen($key); @endphp
                            @if ($existing)
                                <form id="hapus-dok-{{ $key }}"
                                    action="{{ route('agendas.dokumen.destroy', [$agenda, $existing]) }}" method="POST"
                                    class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            @endif
                        @endforeach

                        {{-- Satu form untuk upload semua kategori sekaligus --}}
                        <form action="{{ route('agendas.dokumen.store-all', $agenda) }}" method="POST"
                            enctype="multipart/form-data" class="space-y-4">
                            @csrf

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach ($kategoriList as $key => $label)
                                    @php $existing = $agenda->dokumen($key); @endphp

                                    <div
                                        class="border border-gray-100 rounded-xl p-4 sm:p-5 {{ $existing ? 'bg-white' : 'bg-gray-50/60' }} transition">
                                        <div class="flex items-start sm:items-center justify-between gap-3 mb-3">
                                            <div class="flex items-center gap-3">
                                                <span
                                                    class="h-9 w-9 rounded-lg flex items-center justify-center flex-shrink-0 {{ $existing ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                                                    @if ($existing)
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                    @endif
                                                </span>
                                                <span class="font-semibold text-gray-800 text-sm">{{ $label }}</span>
                                            </div>
                                            <span
                                                class="flex-shrink-0 text-xs font-semibold {{ $existing ? 'text-green-600' : 'text-gray-400' }}">
                                                {{ $existing ? 'Sudah diupload' : 'Belum diupload' }}
                                            </span>
                                        </div>

                                        @if ($existing)
                                            <div
                                                class="flex items-center justify-between gap-3 text-sm bg-gray-50 border border-gray-100 rounded-lg px-3.5 py-2.5 mb-3">
                                                <a href="{{ asset('storage/' . $existing->file_path) }}" target="_blank"
                                                    class="flex items-center gap-2 text-blue-600 hover:text-blue-700 transition min-w-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                                    </svg>
                                                    <span class="truncate font-medium">{{ $existing->nama_file }}</span>
                                                </a>
                                                <button type="button"
                                                    onclick="confirmDelete('hapus-dok-{{ $key }}', 'file {{ $label }}')"
                                                    class="flex-shrink-0 inline-flex items-center gap-1 text-red-500 hover:text-red-600 text-xs font-semibold transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        @endif

                                        <input type="file" name="files[{{ $key }}]"
                                            class="w-full text-sm text-gray-500 border border-gray-200 rounded-lg
                                                                                                                           file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0
                                                                                                                           file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-600
                                                                                                                           hover:file:bg-gray-200 file:transition cursor-pointer
                                                                                                                           focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                                        <p class="text-xs text-gray-400 mt-1.5">
                                            {{ $existing ? 'Pilih file baru untuk mengganti.' : 'PDF, JPG, atau PNG.' }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>

                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2.5 rounded-lg text-sm shadow-sm transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                Simpan Semua Dokumen
                            </button>
                        </form>
                </div>
            </div>

            @php
                // Urutan sama kayak halaman Pegawai: prioritas urutan manual (drag & drop),
                // yang belum pernah diurutkan jatuh ke default (golongan tertinggi dulu, lalu nama).
                // Di-sort dulu sebelum di-filter per status, biar urutannya kebawa & konsisten.
                $pegawaiUrut = $agenda->pegawai->sort(function ($a, $b) {
                    $urutanA = $a->urutan ?? PHP_INT_MAX;
                    $urutanB = $b->urutan ?? PHP_INT_MAX;

                    return $urutanA <=> $urutanB
                        ?: $b->golongan_rank <=> $a->golongan_rank
                        ?: $a->nama <=> $b->nama;
                });

                $pnsPegawai = $pegawaiUrut->filter(fn($p) => ($p->status_kepegawaian ?? 'PNS') === 'PNS')->values();
                $nonPnsPegawai = $pegawaiUrut->filter(fn($p) => ($p->status_kepegawaian ?? 'PNS') === 'Non PNS')->values();

                $groups = [
                    [
                        'key' => 'pns',
                        'label' => 'Peserta PNS',
                        'list' => $pnsPegawai,
                        'nominatif_route' => 'pns',
                        'nominatif_label' => 'Nominatif PNS',
                        'memorandum_route' => 'pns',
                        'memorandum_label' => 'Memorandum PNS',
                        'dot' => 'bg-blue-500',
                    ],
                    [
                        'key' => 'nonpns',
                        'label' => 'Peserta Non PNS',
                        'list' => $nonPnsPegawai,
                        'nominatif_route' => 'non-pns',
                        'nominatif_label' => 'Nominatif Non PNS',
                        'memorandum_route' => 'non-pns',
                        'memorandum_label' => 'Memorandum Non PNS',
                        'dot' => 'bg-amber-500',
                    ],
                ];

                // Style pill dokumen: dua varian per status (belum terisi = outline, sudah terisi = solid tipis + centang)
                $pillBtn = 'inline-flex items-center px-3 py-1.5 rounded-md text-xs font-semibold border border-blue-500 text-blue-600 bg-white hover:bg-blue-50 transition';
                $pillBtnDone = 'inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-semibold border border-blue-500 text-blue-700 bg-blue-50 hover:bg-blue-100 transition';
                $pillBtnSolid = 'inline-flex items-center px-3 py-1.5 rounded-md text-xs font-semibold border border-emerald-500 text-emerald-600 bg-white hover:bg-emerald-50 transition';
                $pillBtnPurple = 'inline-flex items-center px-3 py-1.5 rounded-md text-xs font-semibold border border-purple-500 text-purple-600 bg-white hover:bg-purple-50 transition';
            @endphp

            @foreach ($groups as $group)
                <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">

                    {{-- Tabel peserta --}}
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-400">
                                <th class="text-left px-6 py-3.5 font-semibold">Nama Pegawai</th>
                                <th class="text-left px-6 py-3.5 font-semibold">Dokumen</th>
                                <th class="text-left px-6 py-3.5 font-semibold">Merge Dokumen</th>
                                <th class="text-center px-4 py-3.5 font-semibold w-28">Nominatif</th>
                                <th class="text-center px-4 py-3.5 font-semibold w-28">Memorandum</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($group['list'] as $p)
                                @php
                                    $pivot = $p->pivot;
                                    // Rincian Biaya dianggap terisi kalau salah satu komponen biayanya sudah ada nilainya
                                    $rincianBiayaTerisi = ($pivot->tiket ?? 0) > 0
                                        || ($pivot->lumpsum ?? 0) > 0
                                        || ($pivot->representatif ?? 0) > 0
                                        || ($pivot->hotel ?? 0) > 0;
                                    $pengRiilTerisi = ($pivot->peng_riil ?? 0) > 0 || !empty($pivot->peng_riil_detail);                                    // SPD butuh nomor surat tugas dari agenda
                                    $spdTerisi = !empty($agenda->nomor_st);
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-6 py-4 font-semibold text-gray-800 align-middle whitespace-nowrap">
                                        {{ $p->nama_gelar ?? $p->nama }}
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('agendas.spd', [$agenda, $p]) }}" target="_blank"
                                                class="{{ $spdTerisi ? $pillBtnDone : $pillBtn }}">
                                                @if ($spdTerisi)
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @endif
                                                SPD
                                            </a>
                                            <a href="{{ route('agendas.rincian-biaya', [$agenda, $p]) }}" target="_blank"
                                                class="{{ $rincianBiayaTerisi ? $pillBtnDone : $pillBtn }}">
                                                @if ($rincianBiayaTerisi)
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @endif
                                                Rincian Biaya
                                            </a>
                                            <a href="{{ route('agendas.pengeluaran-riil', [$agenda, $p]) }}" target="_blank"
                                                class="{{ $pengRiilTerisi ? $pillBtnDone : $pillBtn }}">
                                                @if ($pengRiilTerisi)
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @endif
                                                Pengeluaran Riil
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <a href="{{ route('agendas.merge-pdf', [$agenda, $p]) }}" target="_blank"
                                            class="{{ $pillBtnSolid }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            Merge Dokumen
                                        </a>
                                    </td>
                                    @if ($loop->first)
                                        <td class="px-4 py-4 align-middle text-center border-l border-gray-100 w-28"
                                            rowspan="{{ $group['list']->count() }}">
                                            <a href="{{ route('agendas.nominatif', [$agenda, $group['nominatif_route']]) }}"
                                                target="_blank" class="{{ $pillBtnPurple }} w-full justify-center">
                                                {{ $group['nominatif_label'] }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-4 align-middle text-center border-l border-gray-100 w-28"
                                            rowspan="{{ $group['list']->count() }}">
                                            <a href="{{ route('agendas.memorandum.pdf', [$agenda, $group['memorandum_route']]) }}"
                                                target="_blank" class="{{ $pillBtn }} w-full justify-center">
                                                {{ $group['memorandum_label'] }}
                                            </a>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada peserta di kategori
                                        ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach

        </div>
    </div>
</x-app-layout>