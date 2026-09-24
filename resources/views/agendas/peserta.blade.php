<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Input Peserta — {{ $agenda->uraian_kegiatan }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Step indicator --}}
            <div class="flex items-center gap-3 px-1">
                <div class="flex items-center gap-2">
                    <span
                        class="h-6 w-6 rounded-full bg-green-100 text-green-600 text-xs font-bold flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    <span class="text-sm font-medium text-gray-500">Detail Agenda & Peserta</span>
                </div>
                <div class="flex-1 h-px bg-gray-200"></div>
                <div class="flex items-center gap-2">
                    <span
                        class="h-6 w-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">2</span>
                    <span class="text-sm font-semibold text-gray-700">Rincian Biaya</span>
                </div>
            </div>

            {{-- Banner info --}}
            <div
                class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-xl px-5 py-4 flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-blue-900">
                    Pilih komponen biaya untuk agenda ini di bawah, lalu isi rincian biaya untuk tiap peserta yang
                    sudah dipilih di langkah sebelumnya.
                </p>
            </div>

            @php
                // ================== Semua komponen biaya & jenis UH yang mungkin ada ==================
                // Pemilihan komponen sekarang dilakukan di halaman ini (chip di atas tabel),
                // bukan lagi di step 1. Karena itu SEMUA kolom di-render sekali di HTML,
                // lalu JS yang munculkan/sembunyikan sesuai chip yang dicentang — supaya
                // nominal yang udah diisi user gak hilang walau pilihan komponen diubah-ubah.
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
                    'belanja_bahan' => 'Belanja Bahan',
                    'honor_narsum' => 'Honor Narsum',
                ];

                $uhFieldMap = [
                    'uh_biasa' => ['hari' => 'hari_dinas_biasa', 'rate' => 'rate_dinas_biasa', 'label' => 'Biasa'],
                    'uh_biasa_60' => ['hari' => 'hari_biasa_60', 'rate' => 'rate_biasa_60', 'label' => 'Biasa 60%'],
                    'uh_fullday' => ['hari' => 'hari_fullday', 'rate' => 'rate_fullday', 'label' => 'Fullday'],
                    'uh_fullboard' => ['hari' => 'hari_fullboard', 'rate' => 'rate_fullboard', 'label' => 'FullBoard'],
                ];

                // Kolom "simple" = semua komponen selain lumpsum, satu input per kolom.
                // Selalu semua opsi (bukan cuma yang aktif) — visibilitas kolom diatur JS.
                $simpleKomponen = collect($komponenOptions)->except('lumpsum')->all();

                // Dipakai buat centang chip awal (kalau agenda ini sudah pernah disimpan
                // komponennya sebelumnya, misal balik lagi ke halaman ini) & buat nyembunyiin
                // kolom yang belum dicentang saat halaman pertama kali dimuat.
                $selectedKomponen = old('komponen_biaya', $agenda->komponen_biaya ?? []);
                $selectedUh = old('jenis_uang_harian', $agenda->jenis_uang_harian ?? []);

                // Lookup cepat id -> nama tujuan buat nampilin label di popup Peng.
                // Riil (data lengkapnya ada di $pengRiilRates yg dikirim controller,
                // dikelompokkan per kategori buat <optgroup>).
                $pengRiilRateNameById = $pengRiilRates->flatten()->pluck('tujuan', 'id');

                // Rate UH dipatok dari SBM sesuai provinsi tujuan agenda ($sbmRate/$sbmFlat
                // dikirim dari AgendaController@pesertaForm). Kalau provinsinya belum ada di
                // master sbm_rates, rate-nya 0 dulu — tetap dikunci (readonly), bukan dibuka
                // supaya user ngetik manual.
                $uhRates = [
                    'uh_biasa' => (int) ($sbmRate->uh_biasa ?? 0),
                    'uh_biasa_60' => (int) ($sbmRate->uh_biasa_60 ?? 0),
                    'uh_fullday' => (int) ($sbmFlat->uh_fullday ?? 0),
                    'uh_fullboard' => (int) ($sbmFlat->uh_fullboard ?? 0),
                ];

                // helper: ambil nilai bersih sebagai integer, buang semua karakter selain digit
                // (aman utk "500000", "500000.00", atau data lama yg kadung tersimpan "500.000")
                $cleanNum = function ($v) {
                    if ($v === null || $v === '') {
                        return null;
                    }
                    $intPart = explode('.', (string) $v)[0];
                    $digits = preg_replace('/\D/', '', $intPart);
                    return $digits === '' ? null : (int) $digits;
                };

                // Urutan sama kayak halaman Pegawai: prioritas urutan manual (drag & drop),
                // yang belum pernah diurutkan jatuh ke default (golongan tertinggi dulu, lalu nama).
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

            @if (!$sbmRate && in_array('lumpsum', $selectedKomponen))
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 text-xs text-amber-800 mb-4">
                    Rate SBM untuk provinsi "<strong>{{ $agenda->tujuan }}</strong>" belum ada di master data.
                    Uang Harian sementara bernilai Rp 0 sampai datanya dilengkapi.
                </div>
            @endif
            @if ($pengRiilRates->isEmpty() && in_array('peng_riil', $selectedKomponen))
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 text-xs text-amber-800 mb-4">
                    Master rate Peng. Riil (transportasi) belum di-seed. Opsi "SBM" di popup Peng. Riil belum ada
                    pilihannya — isi manual (at cost) dulu.
                </div>
            @endif

            <form action="{{ route('agendas.peserta.store', $agenda) }}" method="POST" id="peserta-form">
                @csrf

                {{-- Komponen biaya untuk agenda ini — kolom di tabel bawah menyesuaikan
                secara langsung (tanpa reload) sesuai chip yang dicentang di sini --}}
                <div class="mb-6">
                    @include('agendas.partials.komponen-biaya-selector', ['agenda' => $agenda])
                </div>

                @foreach ([
                        ['key' => 'pns', 'label' => 'Pegawai PNS', 'list' => $pnsList, 'accent' => 'blue'],
                        ['key' => 'nonpns', 'label' => 'Pegawai Non PNS', 'list' => $nonPnsList, 'accent' => 'amber'],
                    ] as $group)
                    <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden mb-6">
                        {{-- Header grup --}}
                        <div
                            class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3 bg-{{ $group['accent'] }}-50/60">
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="flex items-center justify-center h-7 w-7 rounded-full bg-{{ $group['accent'] }}-100 text-{{ $group['accent'] }}-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </span>
                                <div>
                                    <h3 class="font-semibold text-gray-800 leading-tight">{{ $group['label'] }}</h3>
                                    <span class="text-xs text-gray-400">{{ $group['list']->count() }} orang</span>
                                </div>
                            </div>
                        </div>

                        <table class="w-full table-auto text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-500">
                                    <th class="px-3 py-2.5 text-left font-medium">Nama</th>
                                    @foreach ($simpleKomponen as $key => $label)
                                        <th class="px-2 py-2.5 text-center font-medium {{ in_array($key, $selectedKomponen) ? '' : 'hidden' }}"
                                            data-col="{{ $key }}">
                                            {{ $label }}
                                            @if ($key === 'peng_riil')
                                                <div class="text-[9px] font-normal normal-case text-gray-400">SBM / Manual</div>
                                            @endif
                                        </th>
                                    @endforeach
                                    <th class="px-2 py-2.5 text-center font-medium {{ in_array('lumpsum', $selectedKomponen) ? '' : 'hidden' }}"
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
                                    @endphp
                                    <tr class="peserta-row hover:bg-gray-50/60 transition" data-group="{{ $group['key'] }}">
                                        <td class="px-3 py-2 overflow-hidden">
                                            <span class="font-medium text-gray-800 text-sm truncate block"
                                                title="{{ $nama }}">{{ $nama }}</span>
                                        </td>

                                        @foreach ($simpleKomponen as $key => $label)
                                            @if ($key === 'peng_riil')
                                                @php
                                                    // ==== Peng. Riil sekarang mendukung multi-entry ====
                                                    // Disimpan sebagai JSON array di kolom pivot `peng_riil_detail`,
                                                    // tiap entry: {mode, value, rate_id, tujuan, keterangan}.
                                                    // NB: kolom `peng_riil_detail` (TEXT/JSON, nullable) perlu
                                                    // ditambahkan ke tabel pivot via migration kalau belum ada,
                                                    // dan controller perlu decode/encode field ini saat simpan & load.
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
                                                    // Fallback: migrasikan data lama (single mode/value) jadi 1 entry,
                                                    // supaya data yang sudah kesimpan sebelum fitur ini gak hilang.
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
                                                <td class="px-2 py-2 {{ in_array('peng_riil', $selectedKomponen) ? '' : 'hidden' }}"
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
                                                <td class="px-2 py-2 {{ in_array($key, $selectedKomponen) ? '' : 'hidden' }}"
                                                    data-col="{{ $key }}">
                                                    <input type="text" inputmode="numeric" name="{{ $key }}[{{ $p->id }}]"
                                                        placeholder="0"
                                                        class="rupiah-input col-{{ $key }} w-full border border-gray-200 rounded-md px-2 py-1.5 text-center text-xs focus:border-{{ $group['accent'] }}-400 focus:ring-1 focus:ring-{{ $group['accent'] }}-400 outline-none transition"
                                                        data-group="{{ $group['key'] }}"
                                                        value="{{ $cleanNum($pivot->{$key} ?? null) }}">
                                                </td>
                                            @endif
                                        @endforeach

                                        <td class="px-2 py-2 {{ in_array('lumpsum', $selectedKomponen) ? '' : 'hidden' }}"
                                            data-col="lumpsum">
                                            <div class="space-y-1">
                                                @foreach ($uhFieldMap as $uhKey => $f)
                                                    <div class="{{ in_array($uhKey, $selectedUh) ? '' : 'hidden' }}"
                                                        data-uh="{{ $uhKey }}">
                                                        <div class="text-[9px] text-gray-400 leading-tight">{{ $f['label'] }}</div>
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
                                                <div
                                                    class="text-[10px] text-gray-500 text-center pt-0.5 border-t border-gray-100">
                                                    Total: <span class="row-lumpsum-total font-semibold">Rp
                                                        {{ number_format($lumpsumAwal) }}</span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%" class="px-4 py-6 text-center text-gray-400">
                                            Belum ada peserta di kategori ini. Kembali ke halaman "Buat Agenda" untuk
                                            menambahkan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($group['list']->isNotEmpty())
                                <tfoot>
                                    <tr class="bg-gray-50 border-t border-gray-200 font-semibold text-gray-700 text-xs">
                                        <td class="px-3 py-2.5">Subtotal</td>
                                        @foreach ($simpleKomponen as $key => $label)
                                            <td class="px-2 py-2.5 text-center total-{{ $key }} {{ in_array($key, $selectedKomponen) ? '' : 'hidden' }}"
                                                data-group="{{ $group['key'] }}" data-col="{{ $key }}">Rp 0</td>
                                        @endforeach
                                        <td class="px-2 py-2.5 text-center total-lumpsum {{ in_array('lumpsum', $selectedKomponen) ? '' : 'hidden' }}"
                                            data-group="{{ $group['key'] }}" data-col="lumpsum">Rp 0</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                @endforeach

                <div class="sticky bottom-4 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-lg shadow-lg shadow-blue-600/25 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Peserta
                    </button>
                </div>

                {{-- Popup Peng. Riil — satu modal dipakai bareng buat semua baris peserta.
                Mendukung banyak entry sekaligus (tombol "+ Tambah"), tiap entry bisa
                Manual atau Sesuai SBM sendiri-sendiri. Isinya ditulis balik ke hidden
                input baris yang lagi diedit pas "Simpan". --}}
                <div id="peng-riil-modal"
                    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-5 space-y-4 max-h-[90vh] overflow-y-auto"
                        onclick="event.stopPropagation()">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800">Isi Peng. Riil</h3>
                            <button type="button" id="peng-riil-modal-close"
                                class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                        </div>
                        <p class="text-xs text-gray-500" id="peng-riil-modal-nama"></p>

                        <div id="peng-riil-modal-entries" class="space-y-3"></div>

                        <button type="button" id="peng-riil-modal-add"
                            class="w-full text-xs font-medium text-blue-600 hover:text-blue-700 border border-dashed border-blue-300 hover:border-blue-400 rounded-lg py-2 transition">
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

                    {{-- Template satu baris entry Peng. Riil, di-clone tiap klik "+ Tambah" --}}
                    <template id="peng-riil-entry-template">
                        <div class="peng-riil-entry border border-gray-200 rounded-lg p-3 space-y-2.5 relative">
                            <button type="button"
                                class="peng-riil-entry-remove absolute top-2 right-2 text-gray-300 hover:text-red-500 text-sm leading-none w-5 h-5 flex items-center justify-center">&times;</button>

                            <div class="flex gap-4 text-xs">
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
                                        <optgroup
                                            label="{{ $kategori === 'jabodetabek' ? 'Transport Jakarta' : 'Transport Daerah' }}">
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
                                    Rate PP (2x satu arah): <span
                                        class="entry-sbm-preview font-semibold text-gray-700">Rp 0</span>
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
        // Daftar SEMUA kolom "simple" & pasangan hari/rate lumpsum yang mungkin ada.
        // Totalnya tetap dihitung walau kolomnya lagi disembunyikan (biar nominal yang
        // sudah diisi user gak hilang saat chip komponen dicentang/dilepas bolak-balik).
        const SIMPLE_COLS = @json(array_keys($simpleKomponen));
        const LUMPSUM_FIELDS = @json(array_values(array_map(
            fn($f) => ['hari' => $f['hari'], 'rate' => $f['rate']],
            $uhFieldMap
        )));
        const LUMPSUM_RATE_COLS = new Set(LUMPSUM_FIELDS.map(f => 'col-' + f.rate));

        // ==== Toggle kolom tabel real-time sesuai chip "Komponen Biaya" & "Jenis Uang Harian" ====
        // (chip-nya di-render sama partial agendas/partials/komponen-biaya-selector)
        document.querySelectorAll('input[name="komponen_biaya[]"]').forEach(function (cb) {
            function syncKomponenCol() {
                document.querySelectorAll('[data-col="' + cb.value + '"]').forEach(function (el) {
                    el.classList.toggle('hidden', !cb.checked);
                });
            }
            cb.addEventListener('change', syncKomponenCol);
            syncKomponenCol(); // set kondisi awal (misal form reload setelah validasi gagal)
        });

        document.querySelectorAll('input[name="jenis_uang_harian[]"]').forEach(function (cb) {
            function syncUhPair() {
                document.querySelectorAll('[data-uh="' + cb.value + '"]').forEach(function (el) {
                    el.classList.toggle('hidden', !cb.checked);
                });
            }
            cb.addEventListener('change', syncUhPair);
            syncUhPair();
        });

        // Ambil digit murni saja dari sebuah string, buang titik/koma/karakter lain
        function onlyDigits(value) {
            return String(value ?? '').replace(/\D/g, '');
        }

        // Format angka jadi "500.000"
        function formatRupiah(rawValue) {
            const digits = onlyDigits(rawValue);
            if (!digits) return '';
            // buang angka nol di depan (misal "0500000" -> "500000"), tapi biarkan "0" tunggal
            const trimmed = digits.replace(/^0+(?=\d)/, '');
            return trimmed.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Hitung total lumpsum satu baris dari semua pasangan hari/rate yang aktif
        function rowLumpsumValue(row) {
            let total = 0;
            LUMPSUM_FIELDS.forEach(function (f) {
                const hari = parseInt(onlyDigits(row.querySelector('.col-' + f.hari)?.value) || '0', 10);
                const rate = parseInt(onlyDigits(row.querySelector('.col-' + f.rate)?.value) || '0', 10);
                total += hari * rate;
            });
            return total;
        }

        function updateRowLumpsumDisplay(row) {
            const display = row.querySelector('.row-lumpsum-total');
            if (display) {
                display.textContent = 'Rp ' + (formatRupiah(String(rowLumpsumValue(row))) || '0');
            }
        }

        function recalcTotals(group) {
            SIMPLE_COLS.forEach(function (col) {
                let sum = 0;
                document.querySelectorAll('input.col-' + col + '[data-group="' + group + '"]').forEach(function (input) {
                    sum += parseInt(onlyDigits(input.value) || '0', 10);
                });
                const target = document.querySelector('.total-' + col + '[data-group="' + group + '"]');
                if (target) {
                    target.textContent = sum ? ('Rp ' + formatRupiah(String(sum))) : 'Rp 0';
                }
            });

            // Lumpsum dihitung dari total tiap baris (hari x rate), bukan dijumlah langsung dari satu input
            let lumpsumSum = 0;
            document.querySelectorAll('tr.peserta-row[data-group="' + group + '"]').forEach(function (row) {
                lumpsumSum += rowLumpsumValue(row);
            });
            const lumpsumTarget = document.querySelector('.total-lumpsum[data-group="' + group + '"]');
            if (lumpsumTarget) {
                lumpsumTarget.textContent = lumpsumSum ? ('Rp ' + formatRupiah(String(lumpsumSum))) : 'Rp 0';
            }
        }

        // ==== Popup Peng. Riil: satu modal dipakai bareng buat semua baris peserta, ====
        // ==== mendukung banyak entry (tombol "+ Tambah") dengan keterangan otomatis ====
        (function () {
            const modal = document.getElementById('peng-riil-modal');
            if (!modal) return;

            const entriesContainer = document.getElementById('peng-riil-modal-entries');
            const template = document.getElementById('peng-riil-entry-template');
            const addBtn = document.getElementById('peng-riil-modal-add');
            const totalDisplay = document.getElementById('peng-riil-modal-total');
            const namaLabel = document.getElementById('peng-riil-modal-nama');
            let activeBtn = null;
            let entryCounter = 0;

            function entryValue(entryEl) {
                const isSbm = entryEl.querySelector('.entry-mode-radio[value="sbm"]').checked;
                if (isSbm) {
                    const opt = entryEl.querySelector('.entry-sbm-select').selectedOptions[0];
                    return opt && opt.value ? parseInt(opt.dataset.rate || '0', 10) * 2 : 0;
                }
                return parseInt(onlyDigits(entryEl.querySelector('.entry-manual-input').value) || '0', 10);
            }

            function updateModalTotal() {
                let total = 0;
                entriesContainer.querySelectorAll('.peng-riil-entry').forEach(function (el) {
                    total += entryValue(el);
                });
                totalDisplay.textContent = 'Rp ' + (formatRupiah(String(total)) || '0');
            }

            function syncEntryMode(entryEl) {
                const isSbm = entryEl.querySelector('.entry-mode-radio[value="sbm"]').checked;
                entryEl.querySelector('.entry-manual-section').classList.toggle('hidden', isSbm);
                entryEl.querySelector('.entry-sbm-section').classList.toggle('hidden', !isSbm);
            }

            // Bikin keterangan otomatis: "Transport Jakarta – (tujuan) PP" untuk
            // kategori Jabodetabek, atau "Transport Daerah (tujuan) PP" untuk provinsi.
            function updateEntryKeterangan(entryEl) {
                const select = entryEl.querySelector('.entry-sbm-select');
                const preview = entryEl.querySelector('.entry-sbm-preview');
                const keteranganInput = entryEl.querySelector('.entry-keterangan-input');
                const opt = select.selectedOptions[0];

                if (!opt || !opt.value) {
                    preview.textContent = 'Rp 0';
                    return;
                }

                const rate = parseInt(opt.dataset.rate || '0', 10) * 2;
                preview.textContent = 'Rp ' + (formatRupiah(String(rate)) || '0');

                const tujuan = opt.dataset.tujuan || opt.textContent.trim();
                keteranganInput.value = opt.dataset.kategori === 'jabodetabek'
                    ? 'Transport Jakarta – ' + tujuan + ' PP'
                    : 'Transport Daerah (' + tujuan + ') PP';
            }

            function wireEntry(entryEl) {
                entryCounter++;
                // radio per-entry perlu name unik biar gak "berbagi" pilihan antar entry
                const radioName = 'peng-riil-entry-mode-' + entryCounter;
                entryEl.querySelectorAll('.entry-mode-radio').forEach(function (r) { r.name = radioName; });

                entryEl.querySelectorAll('.entry-mode-radio').forEach(function (r) {
                    r.addEventListener('change', function () {
                        syncEntryMode(entryEl);
                        updateModalTotal();
                    });
                });

                const manualInput = entryEl.querySelector('.entry-manual-input');
                manualInput.addEventListener('input', function () {
                    manualInput.value = onlyDigits(manualInput.value);
                    updateModalTotal();
                });
                manualInput.addEventListener('blur', function () {
                    manualInput.value = formatRupiah(manualInput.value);
                });

                const sbmSelect = entryEl.querySelector('.entry-sbm-select');
                sbmSelect.addEventListener('change', function () {
                    updateEntryKeterangan(entryEl);
                    updateModalTotal();
                });

                entryEl.querySelector('.peng-riil-entry-remove').addEventListener('click', function () {
                    if (entriesContainer.querySelectorAll('.peng-riil-entry').length <= 1) {
                        // selalu sisakan minimal 1 baris — reset jadi kosong aja daripada dihapus total
                        entryEl.remove();
                        addEntry();
                        updateModalTotal();
                        return;
                    }
                    entryEl.remove();
                    updateModalTotal();
                });
            }

            function addEntry(data) {
                const frag = template.content.cloneNode(true);
                const entryEl = frag.querySelector('.peng-riil-entry');
                entriesContainer.appendChild(frag);
                wireEntry(entryEl);

                if (data) {
                    const isSbm = data.mode === 'sbm';
                    entryEl.querySelector('.entry-mode-radio[value="' + (isSbm ? 'sbm' : 'manual') + '"]').checked = true;
                    syncEntryMode(entryEl);

                    if (isSbm) {
                        entryEl.querySelector('.entry-sbm-select').value = data.rate_id || '';
                        updateEntryKeterangan(entryEl);
                    } else {
                        entryEl.querySelector('.entry-manual-input').value = formatRupiah(String(data.value || ''));
                    }
                    if (data.keterangan) {
                        entryEl.querySelector('.entry-keterangan-input').value = data.keterangan;
                    }
                }

                return entryEl;
            }

            function openModal(btn) {
                activeBtn = btn;
                namaLabel.textContent = btn.dataset.nama || '';
                entriesContainer.innerHTML = '';

                let detail = [];
                try {
                    detail = JSON.parse(btn.dataset.detail || '[]');
                } catch (e) {
                    detail = [];
                }

                if (Array.isArray(detail) && detail.length) {
                    detail.forEach(function (d) { addEntry(d); });
                } else {
                    addEntry();
                }

                updateModalTotal();
                modal.classList.remove('hidden');
            }

            function closeModal() {
                modal.classList.add('hidden');
                activeBtn = null;
            }

            addBtn.addEventListener('click', function () {
                addEntry();
                updateModalTotal();
            });

            document.querySelectorAll('.peng-riil-open-btn').forEach(function (btn) {
                btn.addEventListener('click', function () { openModal(btn); });
            });

            document.getElementById('peng-riil-modal-close').addEventListener('click', closeModal);
            document.getElementById('peng-riil-modal-cancel').addEventListener('click', closeModal);
            modal.addEventListener('click', closeModal); // klik area gelap di luar kartu

            document.getElementById('peng-riil-modal-save').addEventListener('click', function () {
                if (!activeBtn) return;

                const entries = [];
                entriesContainer.querySelectorAll('.peng-riil-entry').forEach(function (entryEl) {
                    const isSbm = entryEl.querySelector('.entry-mode-radio[value="sbm"]').checked;
                    const keterangan = entryEl.querySelector('.entry-keterangan-input').value.trim();

                    if (isSbm) {
                        const opt = entryEl.querySelector('.entry-sbm-select').selectedOptions[0];
                        if (!opt || !opt.value) return; // skip entry SBM yang belum pilih tujuan
                        entries.push({
                            mode: 'sbm',
                            value: parseInt(opt.dataset.rate || '0', 10) * 2,
                            rate_id: opt.value,
                            tujuan: opt.dataset.tujuan || opt.textContent.trim(),
                            keterangan: keterangan,
                        });
                    } else {
                        const value = parseInt(onlyDigits(entryEl.querySelector('.entry-manual-input').value) || '0', 10);
                        if (!value && !keterangan) return; // skip entry manual yang kosong total
                        entries.push({
                            mode: 'manual',
                            value: value,
                            rate_id: '',
                            tujuan: '',
                            keterangan: keterangan,
                        });
                    }
                });

                const pegawaiId = activeBtn.dataset.pegawaiId;
                const valueInput = document.querySelector('input.col-peng_riil[name="peng_riil[' + pegawaiId + ']"]');
                const detailField = document.querySelector('.peng-riil-detail-field[name="peng_riil_detail[' + pegawaiId + ']"]');
                const displayEl = activeBtn.querySelector('.peng-riil-display');
                const subEl = activeBtn.querySelector('.peng-riil-sub');

                const total = entries.reduce(function (sum, e) { return sum + (e.value || 0); }, 0);
                const entriesJson = JSON.stringify(entries);

                activeBtn.dataset.detail = entriesJson;
                valueInput.value = total;
                detailField.value = entriesJson;

                displayEl.textContent = total ? 'Rp ' + formatRupiah(String(total)) : 'Isi Peng. Riil';
                subEl.textContent = entries.length > 1
                    ? entries.length + ' item'
                    : (entries.length === 1 ? (entries[0].mode === 'sbm' ? 'SBM' : 'Manual') : '');

                recalcTotals(valueInput.dataset.group);
                closeModal();
            });
        })();

        document.querySelectorAll('.rupiah-input').forEach(function (input) {
            // saat halaman dimuat: tampilkan dengan format titik
            input.value = formatRupiah(input.value);

            // saat field difokus: tampilkan angka polos, biar gampang diedit
            input.addEventListener('focus', function () {
                input.value = onlyDigits(input.value);
                requestAnimationFrame(function () {
                    input.setSelectionRange(input.value.length, input.value.length);
                });
            });

            // saat mengetik: hanya izinkan digit, tanpa reformat live (hindari bug kursor)
            input.addEventListener('input', function () {
                input.value = onlyDigits(input.value);
                const row = input.closest('tr.peserta-row');
                if (row) {
                    const isLumpsumRate = Array.from(input.classList).some(c => LUMPSUM_RATE_COLS.has(c));
                    if (isLumpsumRate) updateRowLumpsumDisplay(row);
                }
                recalcTotals(input.dataset.group);
            });

            // saat pindah field: format ulang jadi "500.000"
            input.addEventListener('blur', function () {
                input.value = formatRupiah(input.value);
            });
        });

        // Input jumlah hari (semua jenis UH aktif): digit saja, maksimal 2 digit, tanpa format ribuan
        document.querySelectorAll('.hari-input').forEach(function (input) {
            input.value = onlyDigits(input.value);

            input.addEventListener('input', function () {
                input.value = onlyDigits(input.value).slice(0, 2);
                const row = input.closest('tr.peserta-row');
                updateRowLumpsumDisplay(row);
                recalcTotals(input.dataset.group);
            });
        });

        // hitung total lumpsum tiap baris & subtotal awal saat halaman dimuat
        document.querySelectorAll('tr.peserta-row').forEach(updateRowLumpsumDisplay);
        ['pns', 'nonpns'].forEach(recalcTotals);

        // sebelum submit, pastikan value yang dikirim adalah angka polos
        document.getElementById('peserta-form').addEventListener('submit', function () {
            document.querySelectorAll('.rupiah-input, .hari-input').forEach(function (input) {
                input.value = onlyDigits(input.value);
            });
        });
    </script>
</x-app-layout>