<x-app-layout title="Peserta">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight break-words">
            Input Peserta — {{ $agenda->uraian_kegiatan }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Step indicator --}}
            <div class="flex items-center gap-3 px-1">
                <div class="flex items-center gap-2">
                    <span
                        class="h-6 w-6 rounded-full bg-green-100 text-green-600 text-xs font-bold flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    <span class="hidden sm:inline text-sm font-medium text-gray-500">Detail Agenda & Peserta</span>
                </div>
                <div class="flex-1 h-px bg-gray-200"></div>
                <div class="flex items-center gap-2">
                    <span
                        class="h-6 w-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">2</span>
                    <span class="text-sm font-semibold text-gray-700">Rincian Biaya</span>
                </div>
            </div>

            @php
                $komponenOptions = [
                    'tiket' => 'Tiket',
                    'dukungan_transportasi' => 'Dukungan Transportasi',
                    'transportasi_darat' => 'Transportasi Darat',
                    'transportasi_lokal' => 'Transportasi Lokal',
                    'peng_riil' => 'Peng. Riil',
                    'hotel' => 'Hotel',
                    'penginapan_30' => 'Penginapan 30%',
                    'lumpsum' => 'Uang Harian (Lumpsum)',
                    'representatif' => 'Representatif',
                ];

                $uhFieldMap = [
                    'uh_biasa' => ['hari' => 'hari_dinas_biasa', 'rate' => 'rate_dinas_biasa', 'label' => 'Biasa'],
                    'uh_biasa_60' => ['hari' => 'hari_biasa_60', 'rate' => 'rate_biasa_60', 'label' => 'Biasa 60%'],
                    'uh_fullday' => ['hari' => 'hari_fullday', 'rate' => 'rate_fullday', 'label' => 'Fullday'],
                    'uh_fullboard' => ['hari' => 'hari_fullboard', 'rate' => 'rate_fullboard', 'label' => 'FullBoard'],
                ];

                $simpleKomponen = collect($komponenOptions)->except('lumpsum')->all();

                $selectedKomponen = old('komponen_biaya', $agenda->komponen_biaya ?? []);
                $selectedUh = old('jenis_uang_harian', $agenda->jenis_uang_harian ?? []);

                $pengRiilRateNameById = $pengRiilRates->flatten()->pluck('tujuan', 'id');

                $uhRates = [
                    'uh_biasa' => (int) ($sbmRate->uh_biasa ?? 0),
                    'uh_biasa_60' => (int) ($sbmRate->uh_biasa_60 ?? 0),
                    'uh_fullday' => (int) ($sbmFlat->uh_fullday ?? 0),
                    'uh_fullboard' => (int) ($sbmFlat->uh_fullboard ?? 0),
                ];

                $cleanNum = function ($v) {
                    if ($v === null || $v === '') return null;
                    $intPart = explode('.', (string) $v)[0];
                    $digits = preg_replace('/\D/', '', $intPart);
                    return $digits === '' ? null : (int) $digits;
                };

                $agendaMulai = $agenda->tanggal_mulai->format('Y-m-d');
                $agendaSelesai = $agenda->tanggal_selesai->format('Y-m-d');

                $pegawaiList = $pegawaiList->sort(function ($a, $b) {
                    $urutanA = $a->urutan ?? PHP_INT_MAX;
                    $urutanB = $b->urutan ?? PHP_INT_MAX;

                    return $urutanA <=> $urutanB
                        ?: $b->golongan_rank <=> $a->golongan_rank
                        ?: $a->nama <=> $b->nama;
                })->values();

                $pnsList = $pegawaiList->filter(fn($p) => ($p->status_kepegawaian ?? 'PNS') === 'PNS')->values();
                $nonPnsList = $pegawaiList->filter(fn($p) => ($p->status_kepegawaian ?? 'PNS') === 'Non PNS')->values();
            @endphp

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl px-4 sm:px-5 py-3 text-xs text-red-700">
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach (collect($errors->all())->unique() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (!$sbmRate && in_array('lumpsum', $selectedKomponen))
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 sm:px-5 py-3 text-xs text-amber-800 mb-4">
                    Rate SBM untuk provinsi "<strong>{{ $agenda->tujuan }}</strong>" belum ada di master data.
                    Uang Harian sementara bernilai Rp 0 sampai datanya dilengkapi.
                </div>
            @endif
            @if ($pengRiilRates->isEmpty() && in_array('peng_riil', $selectedKomponen))
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 sm:px-5 py-3 text-xs text-amber-800 mb-4">
                    Master rate Peng. Riil (transportasi) belum di-seed. Opsi "SBM" di popup Peng. Riil belum ada
                    pilihannya — isi manual (at cost) dulu.
                </div>
            @endif

            <form action="{{ route('agendas.peserta.store', $agenda) }}" method="POST" id="peserta-form">
                @csrf

                <div class="mb-6">
                    @include('agendas.partials.komponen-biaya-selector', ['agenda' => $agenda])
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-xl px-4 sm:px-5 py-3 text-xs text-blue-800">
                    Rentang surat tugas:
                    <strong>{{ $agenda->tanggal_mulai->translatedFormat('d M Y') }}
                        – {{ $agenda->tanggal_selesai->translatedFormat('d M Y') }}</strong>.
                    Tanggal tiap peserta boleh lebih pendek dari rentang ini; kalau tidak diubah, peserta ikut rentang penuh.
                </div>

                @foreach ([
                        ['key' => 'pns', 'label' => 'Pegawai PNS', 'list' => $pnsList, 'accent' => 'blue'],
                        ['key' => 'nonpns', 'label' => 'Pegawai Non PNS', 'list' => $nonPnsList, 'accent' => 'amber'],
                    ] as $group)
                    <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden mb-6 mt-6">
                        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3 bg-{{ $group['accent'] }}-50/60">
                            <div class="flex items-center gap-2.5">
                                <span class="flex items-center justify-center h-7 w-7 rounded-full bg-{{ $group['accent'] }}-100 text-{{ $group['accent'] }}-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </span>
                                <div>
                                    <h3 class="font-semibold text-gray-800 leading-tight">{{ $group['label'] }}</h3>
                                    <span class="text-xs text-gray-400">{{ $group['list']->count() }} orang</span>
                                </div>
                            </div>
                            <span class="lg:hidden text-[11px] text-gray-400">Geser tabel ke samping →</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full table-auto text-sm max-lg:w-max max-lg:min-w-full">
                                <thead>
                                    <tr class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-500">
                                        <th class="px-3 py-2.5 text-left font-medium max-lg:sticky max-lg:left-0 max-lg:z-10 max-lg:w-[6.5rem] max-lg:min-w-[6.5rem] max-lg:max-w-[6.5rem] max-lg:bg-gray-50 max-lg:shadow-[1px_0_0_#e5e7eb]">Nama</th>
                                        <th class="px-2 py-2.5 text-center font-medium min-w-[14rem]" data-col="tanggal">Tanggal Dinas</th>
                                        @foreach ($simpleKomponen as $key => $label)
                                            <th class="px-2 py-2.5 text-center font-medium max-lg:min-w-[7.5rem] {{ in_array($key, $selectedKomponen) ? '' : 'hidden' }}"
                                                data-col="{{ $key }}">
                                                {{ $label }}
                                                @if ($key === 'peng_riil')
                                                    <div class="text-[9px] font-normal normal-case text-gray-400">SBM / Manual</div>
                                                @endif
                                            </th>
                                        @endforeach
                                        <th class="px-2 py-2.5 text-center font-medium max-lg:min-w-[11rem] {{ in_array('lumpsum', $selectedKomponen) ? '' : 'hidden' }}"
                                            data-col="lumpsum">Uang Harian (Lumpsum)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($group['list'] as $p)
                                        @php
                                            $pivot = $p->pivot;
                                            $nama = $p->nama_gelar ?? $p->nama;
                                            $lumpsumAwal = 0;
                                            foreach ($uhFieldMap as $f) {
                                                $lumpsumAwal += ($pivot->{$f['hari']} ?? 0) * ($pivot->{$f['rate']} ?? 0);
                                            }

                                            $tglMulaiPeserta = old(
                                                'tanggal_mulai.' . $p->id,
                                                ($pivot->tanggal_mulai ?? null)
                                                ? \Carbon\Carbon::parse($pivot->tanggal_mulai)->format('Y-m-d')
                                                : $agendaMulai
                                            );
                                            $tglSelesaiPeserta = old(
                                                'tanggal_selesai.' . $p->id,
                                                ($pivot->tanggal_selesai ?? null)
                                                ? \Carbon\Carbon::parse($pivot->tanggal_selesai)->format('Y-m-d')
                                                : $agendaSelesai
                                            );
                                        @endphp
                                        <tr class="peserta-row hover:bg-gray-50/60 transition" data-group="{{ $group['key'] }}">
                                            <td class="px-3 py-2 overflow-hidden max-lg:sticky max-lg:left-0 max-lg:z-10 max-lg:w-[6.5rem] max-lg:min-w-[6.5rem] max-lg:max-w-[6.5rem] max-lg:bg-white max-lg:shadow-[1px_0_0_#e5e7eb]">
                                                <span class="font-medium text-gray-800 text-sm truncate block max-lg:whitespace-normal max-lg:break-words max-lg:text-[11px] max-lg:leading-snug"
                                                    title="{{ $nama }}">{{ $nama }}</span>
                                            </td>

                                            <td class="px-2 py-2 min-w-[14rem]" data-col="tanggal">
                                                <div class="rounded-xl border border-gray-200 bg-white p-2 space-y-2 shadow-sm">
                                                    <div class="grid grid-cols-[1fr_auto_1fr] items-end gap-1.5">
                                                        <label class="block min-w-0">
                                                            <span class="block text-[9px] uppercase tracking-wide text-gray-400 mb-0.5">Mulai</span>
                                                            <input type="date" name="tanggal_mulai[{{ $p->id }}]"
                                                                class="tgl-mulai w-full min-w-0 bg-gray-50 border border-transparent rounded-md p-1.5 text-[11px] text-gray-700 transition-colors hover:border-gray-200 focus:outline-none focus:bg-white focus:border-blue-300 focus:ring-2 focus:ring-blue-300/35 [&::-webkit-calendar-picker-indicator]:opacity-45 [&::-webkit-calendar-picker-indicator]:cursor-pointer hover:[&::-webkit-calendar-picker-indicator]:opacity-100"
                                                                aria-label="Tanggal mulai dinas {{ $nama }}"
                                                                min="{{ $agendaMulai }}" max="{{ $agendaSelesai }}"
                                                                value="{{ $tglMulaiPeserta }}">
                                                        </label>
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-3.5 w-3.5 mb-2 text-gray-300 flex-shrink-0" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M5 12h14M13 6l6 6-6 6" />
                                                        </svg>
                                                        <label class="block min-w-0">
                                                            <span class="block text-[9px] uppercase tracking-wide text-gray-400 mb-0.5">Selesai</span>
                                                            <input type="date" name="tanggal_selesai[{{ $p->id }}]"
                                                                class="tgl-selesai w-full min-w-0 bg-gray-50 border border-transparent rounded-md p-1.5 text-[11px] text-gray-700 transition-colors hover:border-gray-200 focus:outline-none focus:bg-white focus:border-blue-300 focus:ring-2 focus:ring-blue-300/35 [&::-webkit-calendar-picker-indicator]:opacity-45 [&::-webkit-calendar-picker-indicator]:cursor-pointer hover:[&::-webkit-calendar-picker-indicator]:opacity-100"
                                                                aria-label="Tanggal selesai dinas {{ $nama }}"
                                                                min="{{ $tglMulaiPeserta }}" max="{{ $agendaSelesai }}"
                                                                value="{{ $tglSelesaiPeserta }}">
                                                        </label>
                                                    </div>

                                                    <div class="flex items-center justify-between gap-2">
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-{{ $group['accent'] }}-100 text-{{ $group['accent'] }}-600 px-2 py-0.5 text-[10px] font-semibold">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                                                            </svg>
                                                            <span class="row-lama">-</span> hari
                                                        </span>
                                                        <button type="button"
                                                            class="tgl-reset hidden text-[10px] text-gray-400 hover:text-{{ $group['accent'] }}-600 underline underline-offset-2">Semua
                                                            hari</button>
                                                    </div>

                                                    <div class="row-warn hidden rounded-md bg-amber-50 border border-amber-200 px-2 py-1 text-[10px] text-amber-700 text-center">
                                                        Total hari UH ≠ lama dinas
                                                    </div>
                                                </div>
                                            </td>

                                            @foreach ($simpleKomponen as $key => $label)
                                                @if ($key === 'peng_riil')
                                                    @php
                                                        $pengRiilDetailRaw = $pivot->peng_riil_detail ?? null;
                                                        $pengRiilEntries = [];
                                                        if ($pengRiilDetailRaw) {
                                                            $decoded = is_string($pengRiilDetailRaw)
                                                                ? json_decode($pengRiilDetailRaw, true)
                                                                : $pengRiilDetailRaw;
                                                            if (is_array($decoded)) {
                                                                $pengRiilEntries = $decoded;
                                                            }
                                                        }
                                                        if (empty($pengRiilEntries) && ($pivot->peng_riil ?? 0) > 0) {
                                                            $oldMode = $pivot->peng_riil_mode ?? 'manual';
                                                            $oldRateId = $pivot->peng_riil_rate_id ?? '';
                                                            $pengRiilEntries = [
                                                                [
                                                                    'mode' => $oldMode,
                                                                    'value' => (int) ($pivot->peng_riil ?? 0),
                                                                    'rate_id' => $oldRateId,
                                                                    'tujuan' => $pengRiilRateNameById[$oldRateId] ?? '',
                                                                    'keterangan' => '',
                                                                ]
                                                            ];
                                                        }
                                                        $pengRiilTotal = collect($pengRiilEntries)->sum(fn($e) => (int) ($e['value'] ?? 0));
                                                        $pengRiilCount = count($pengRiilEntries);
                                                    @endphp
                                                    <td class="px-2 py-2 max-lg:min-w-[7.5rem] {{ in_array('peng_riil', $selectedKomponen) ? '' : 'hidden' }}"
                                                        data-col="peng_riil">
                                                        <button type="button"
                                                            class="peng-riil-open-btn w-full border border-dashed border-gray-300 rounded-md px-2 py-1.5 text-[11px] text-gray-600 hover:border-{{ $group['accent'] }}-400 hover:text-{{ $group['accent'] }}-600 transition text-center"
                                                            data-pegawai-id="{{ $p->id }}" data-nama="{{ $nama }}"
                                                            data-detail='{{ json_encode($pengRiilEntries, JSON_UNESCAPED_UNICODE) }}'>
                                                            <span class="peng-riil-display font-semibold text-gray-700 block">
                                                                {{ $pengRiilTotal ? 'Rp ' . number_format($pengRiilTotal) : 'Isi Peng. Riil' }}
                                                            </span>
                                                            <span class="peng-riil-sub block text-[9px] text-gray-400">
                                                                @if ($pengRiilCount > 1)
                                                                    {{ $pengRiilCount }} item
                                                                @elseif ($pengRiilCount === 1)
                                                                    {{ $pengRiilEntries[0]['mode'] === 'sbm' ? 'SBM' : 'Manual' }}
                                                                @endif
                                                            </span>
                                                        </button>

                                                        <input type="hidden" class="col-peng_riil" name="peng_riil[{{ $p->id }}]"
                                                            data-group="{{ $group['key'] }}" value="{{ $pengRiilTotal }}">
                                                        <input type="hidden" class="peng-riil-detail-field"
                                                            name="peng_riil_detail[{{ $p->id }}]"
                                                            value='{{ json_encode($pengRiilEntries, JSON_UNESCAPED_UNICODE) }}'>
                                                    </td>
                                                @else
                                                    <td class="px-2 py-2 max-lg:min-w-[7.5rem] {{ in_array($key, $selectedKomponen) ? '' : 'hidden' }}"
                                                        data-col="{{ $key }}">
                                                        <input type="text" inputmode="numeric" name="{{ $key }}[{{ $p->id }}]"
                                                            placeholder="0"
                                                            class="rupiah-input col-{{ $key }} w-full border border-gray-200 rounded-md px-2 py-1.5 text-center text-xs focus:border-{{ $group['accent'] }}-400 focus:ring-1 focus:ring-{{ $group['accent'] }}-400 outline-none transition"
                                                            data-group="{{ $group['key'] }}"
                                                            value="{{ $cleanNum($pivot->{$key} ?? null) }}">
                                                    </td>
                                                @endif
                                            @endforeach

                                            <td class="px-2 py-2 max-lg:min-w-[11rem] {{ in_array('lumpsum', $selectedKomponen) ? '' : 'hidden' }}"
                                                data-col="lumpsum">
                                                <div class="space-y-1">
                                                    <div class="flex items-center justify-between gap-1 text-[9px]">
                                                        <span class="uh-mode-label text-gray-400">Otomatis dari tanggal</span>
                                                        <button type="button"
                                                            class="uh-auto-btn hidden text-{{ $group['accent'] }}-600 hover:underline">↺
                                                            Otomatis</button>
                                                    </div>
                                                    @foreach ($uhFieldMap as $uhKey => $f)
                                                        <div class="{{ in_array($uhKey, $selectedUh) ? '' : 'hidden' }}"
                                                            data-uh="{{ $uhKey }}">
                                                            <div class="text-[9px] text-gray-400 leading-tight">{{ $f['label'] }}
                                                            </div>
                                                            <div class="flex items-center gap-1">
                                                                <input type="text" inputmode="numeric"
                                                                    name="{{ $f['hari'] }}[{{ $p->id }}]" placeholder="hr"
                                                                    class="hari-input col-{{ $f['hari'] }} w-9 flex-shrink-0 border border-gray-200 rounded-md px-1 py-1.5 text-center text-[11px] focus:border-{{ $group['accent'] }}-400 focus:ring-1 focus:ring-{{ $group['accent'] }}-400 outline-none transition"
                                                                    data-group="{{ $group['key'] }}"
                                                                    value="{{ $pivot->{$f['hari']} ?? '' }}">
                                                                <span class="text-[10px] text-gray-300 flex-shrink-0">x</span>
                                                                <input type="text" inputmode="numeric" readonly
                                                                    name="{{ $f['rate'] }}[{{ $p->id }}]" placeholder="0"
                                                                    class="rupiah-input col-{{ $f['rate'] }} w-full border border-gray-200 bg-gray-50 rounded-md px-1.5 py-1.5 text-center text-[11px] text-gray-500 cursor-not-allowed outline-none"
                                                                    data-group="{{ $group['key'] }}"
                                                                    title="Dipatok dari SBM sesuai tujuan agenda"
                                                                    value="{{ $uhRates[$uhKey] }}">
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    <div class="text-[10px] text-gray-500 text-center pt-0.5 border-t border-gray-100">
                                                        Total: <span class="row-lumpsum-total font-semibold">Rp
                                                            {{ number_format($lumpsumAwal) }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="100%" class="px-4 py-6 text-center text-gray-400">
                                                Belum ada peserta di kategori ini. Kembali ke halaman "Buat Agenda" untuk menambahkan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if ($group['list']->isNotEmpty())
                                    <tfoot>
                                        <tr class="bg-gray-50 border-t border-gray-200 font-semibold text-gray-700 text-xs">
                                            <td class="px-3 py-2.5 max-lg:sticky max-lg:left-0 max-lg:z-10 max-lg:w-[6.5rem] max-lg:min-w-[6.5rem] max-lg:max-w-[6.5rem] max-lg:bg-gray-50 max-lg:shadow-[1px_0_0_#e5e7eb]">Subtotal</td>
                                            <td data-col="tanggal" class="min-w-[14rem]"></td>
                                            @foreach ($simpleKomponen as $key => $label)
                                                <td class="px-2 py-2.5 text-center total-{{ $key }} max-lg:min-w-[7.5rem] {{ in_array($key, $selectedKomponen) ? '' : 'hidden' }}"
                                                    data-group="{{ $group['key'] }}" data-col="{{ $key }}">Rp 0</td>
                                            @endforeach
                                            <td class="px-2 py-2.5 text-center total-lumpsum max-lg:min-w-[11rem] {{ in_array('lumpsum', $selectedKomponen) ? '' : 'hidden' }}"
                                                data-group="{{ $group['key'] }}" data-col="lumpsum">Rp 0</td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                @endforeach

                <div class="sticky bottom-4 flex justify-end">
                    <button type="submit"
                        class="w-full sm:w-auto justify-center inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-lg shadow-lg shadow-blue-600/25 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Peserta
                    </button>
                </div>

                {{-- Popup Modal Peng. Riil --}}
                <div id="peng-riil-modal"
                    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-5 space-y-4 max-h-[90vh] overflow-y-auto"
                        onclick="event.stopPropagation()">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800">Isi Peng. Riil</h3>
                            <button type="button" id="peng-riil-modal-close"
                                class="text-gray-400 hover:text-gray-600 text-xl leading-none px-2 -mr-2">&times;</button>
                        </div>
                        <p class="text-xs text-gray-500" id="peng-riil-modal-nama"></p>

                        <div id="peng-riil-modal-entries" class="space-y-3"></div>

                        <button type="button" id="peng-riil-modal-add"
                            class="w-full text-xs font-medium text-blue-600 hover:text-blue-700 border border-dashed border-blue-300 hover:border-blue-400 rounded-lg py-2.5 sm:py-2 transition">
                            + Tambah
                        </button>

                        <div class="text-xs text-gray-600 text-right pt-1 border-t border-gray-100">
                            Total: <span id="peng-riil-modal-total" class="font-semibold text-gray-800">Rp 0</span>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" id="peng-riil-modal-cancel"
                                class="px-4 py-2 text-sm rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Batal</button>
                            <button type="button" id="peng-riil-modal-save"
                                class="px-4 py-2 text-sm rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition">Simpan</button>
                        </div>
                    </div>

                    <template id="peng-riil-entry-template">
                        <div class="peng-riil-entry border border-gray-200 rounded-lg p-3 space-y-2.5 relative">
                            <button type="button"
                                class="peng-riil-entry-remove absolute top-1.5 right-1.5 text-gray-300 hover:text-red-500 text-lg sm:text-sm leading-none w-7 h-7 sm:w-5 sm:h-5 flex items-center justify-center">&times;</button>

                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs pr-7">
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" class="entry-mode-radio" value="manual" checked>
                                    Manual (At Cost)
                                </label>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" class="entry-mode-radio" value="sbm">
                                    Sesuai SBM
                                </label>
                            </div>

                            <div class="entry-manual-section">
                                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Nominal</label>
                                <input type="text" inputmode="numeric" placeholder="0"
                                    class="entry-manual-input w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                            </div>

                            <div class="entry-sbm-section hidden">
                                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Tujuan</label>
                                <select
                                    class="entry-sbm-select w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                                    <option value="">Pilih tujuan</option>
                                    @foreach ($pengRiilRates as $kategori => $rates)
                                        <optgroup label="{{ $kategori === 'jabodetabek' ? 'Transport Jakarta' : 'Transport Daerah' }}">
                                            @foreach ($rates as $r)
                                                <option value="{{ $r->id }}" data-rate="{{ (int) $r->rate_one_way }}"
                                                    data-kategori="{{ $kategori }}" data-tujuan="{{ $r->tujuan }}">
                                                    {{ $r->tujuan }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <p class="text-[11px] text-gray-500 mt-1.5">
                                    Rate PP (2x satu arah): <span class="entry-sbm-preview font-semibold text-gray-700">Rp 0</span>
                                </p>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Keterangan</label>
                                <input type="text" placeholder="mis. Transport Jakarta – Kab. Bogor PP"
                                    class="entry-keterangan-input w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                            </div>
                        </div>
                    </template>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.pesertaConfig = {
            simpleCols: @json(array_keys($simpleKomponen)),
            lumpsumFields: @json(array_values(array_map(
                fn($f) => ['hari' => $f['hari'], 'rate' => $f['rate']],
                $uhFieldMap
            ))),
            agendaMulai: @json($agendaMulai),
            agendaSelesai: @json($agendaSelesai)
        };
    </script>
    @vite(['resources/js/agendas/agenda-peserta.js'])
</x-app-layout>