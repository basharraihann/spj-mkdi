<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Agenda</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Breadcrumb / context bar --}}
            <div class="flex items-center gap-2 mb-5 px-1 text-sm">
                <a href="{{ route('agendas.index') }}" class="text-gray-400 hover:text-gray-600 transition">Agenda</a>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-300" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-gray-700 font-semibold truncate max-w-xs">{{ $agenda->uraian_kegiatan }}</span>
            </div>

            @if ($errors->any())
                @php
                    // Label field dalam Bahasa Indonesia, biar error jelas nunjuk ke field
                    // mana — dipakai bareng sama nama field asli kalau labelnya belum ke-daftar.
                    $fieldLabels = [
                        'nomor_st' => 'Nomor Surat Tugas',
                        'nomor_st_karo' => 'Nomor ST Kepala Biro',
                        'uraian_kegiatan' => 'Uraian Kegiatan',
                        'tujuan' => 'Tujuan (Provinsi)',
                        'kota_tujuan' => 'Kab/Kota',
                        'alat_angkut' => 'Alat Angkut',
                        'tanggal_mulai' => 'Tanggal Mulai',
                        'tanggal_selesai' => 'Tanggal Selesai',
                        'ppk_id' => 'Pejabat Pembuat Komitmen (PPK)',
                        'bendahara_id' => 'Bendahara',
                        'penanggung_jawab_id' => 'Penanggung Jawab Kegiatan',
                        'pic_id' => 'PIC',
                        'pegawai_id' => 'Peserta',
                        'mak' => 'MAK',
                        'uraian_giat' => 'Uraian Giat',
                        'uraian_komponen' => 'Uraian Komponen',
                        'uraian_akun_ap' => 'Uraian Akun (AP)',
                        'uraian_belanja' => 'Uraian Belanja',
                        'petugas_verifikasi_id' => 'Petugas Verifikasi',
                        'klasifikasi' => 'Klasifikasi',
                        'nilai_persen' => 'Nilai %',
                        'pagu' => 'Pagu',
                        'pengajuan_nominal' => 'Pengajuan',
                        'nomor_memo_pns' => 'Nomor Memo (PNS)',
                        'nomor_memo_non_pns' => 'Nomor Memo (Non PNS)',
                    ];
                @endphp
                <div class="flex gap-2.5 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <div>
                        <p class="font-semibold mb-1">Ada {{ $errors->count() }} field yang perlu dicek:</p>
                        <ul class="space-y-0.5 list-disc list-inside">
                            @foreach ($errors->getMessages() as $field => $messages)
                                @php
                                    // pegawai_id.* / komponen_biaya.* dll -> ambil nama field induknya aja
                                    $baseField = explode('.', $field)[0];
                                    $label = $fieldLabels[$baseField] ?? $baseField;
                                @endphp
                                <li>
                                    <strong>{{ $label }}</strong>
                                    — {{ str_contains($messages[0], 'validation.') ? 'wajib diisi / formatnya belum sesuai' : $messages[0] }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('agendas.update', $agenda) }}" method="POST" id="agenda-form">
                @csrf
                @method('PUT')

                @php
                    // Peserta yang sudah dipilih sebelumnya, buat pre-check checkbox (kalau
                    // form ini di-reload setelah validasi gagal, old() menang).
                    $oldPesertaIds = old('pegawai_id', $selectedPesertaIds->all());

// Replika logika pengurutan dari PegawaiController::index, sama persis dengan                    // create.blade.php), supaya urutan peserta di sini konsisten dengan halaman
                    // Buat Agenda & Data Pegawai: dikelompokkan per unit kerja (alfabet, unit
                    // kosong ditaruh paling bawah), lalu di dalam tiap unit: PNS diurutkan
                    // urutan→golongan_rank, Non PNS diurutkan urutan→nama.
                    $sortGolongan = function ($a, $b) {
                        $urutanA = $a->urutan ?? PHP_INT_MAX;
                        $urutanB = $b->urutan ?? PHP_INT_MAX;

                        return $urutanA <=> $urutanB ?: $b->golongan_rank <=> $a->golongan_rank;
                    };

                    $sortNama = function ($a, $b) {
                        $urutanA = $a->urutan ?? PHP_INT_MAX;
                        $urutanB = $b->urutan ?? PHP_INT_MAX;

                        return $urutanA <=> $urutanB ?: $a->nama <=> $b->nama;
                    };

                    $unitUrutan = $pegawaiList
                        ->map(fn($p) => $p->unit_kerja ?: 'Belum Diisi Unit')
                        ->unique()
                        ->sort(fn($a, $b) => ($a === 'Belum Diisi Unit' ? 'zzz_' . $a : $a) <=> ($b === 'Belum Diisi Unit' ? 'zzz_' . $b : $b))
                        ->values();

                    $pesertaPnsList = collect();
                    $pesertaNonPnsList = collect();

                    foreach ($unitUrutan as $unit) {
                        $itemsUnit = $pegawaiList->filter(fn($p) => ($p->unit_kerja ?: 'Belum Diisi Unit') === $unit);

                        $pesertaPnsList = $pesertaPnsList->concat(
                            $itemsUnit->filter(fn($p) => ($p->status_kepegawaian ?? 'PNS') === 'PNS')->sort($sortGolongan)->values()
                        );
                        $pesertaNonPnsList = $pesertaNonPnsList->concat(
                            $itemsUnit->filter(fn($p) => ($p->status_kepegawaian ?? 'PNS') === 'Non PNS')->sort($sortNama)->values()
                        );
                    }

                    $pesertaPnsList = $pesertaPnsList->values();
                    $pesertaNonPnsList = $pesertaNonPnsList->values();

                    // role_penandatangan itu kolom teks bebas (bisa lebih dari satu role,
                    // misal "PPK, Petugas Verifikasi"), jadi dicocokkan pakai substring —
                    // aman karena keempat label tidak ada yang beririsan satu sama lain.
                    $ppkList = $pegawaiList
                        ->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'PPK'))
                        ->values();
                    $bendaharaList = $pegawaiList
                        ->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'Bendahara'))
                        ->values();
                    $pjList = $pegawaiList
                        ->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'Penanggung Jawab Kegiatan'))
                        ->values();

                    $unitKerjaList = $pegawaiList
                        ->pluck('unit_kerja')
                        ->filter()
                        ->unique()
                        ->sort()
                        ->values();
                @endphp

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:items-stretch">

                    {{-- KOLOM KIRI: pilih peserta (sticky, tinggi menyesuaikan kolom kanan) --}}
                    <div class="lg:col-span-2 lg:sticky lg:top-6 lg:h-full">
                        <div
                            class="lg:h-full flex flex-col bg-white shadow-sm rounded-2xl border border-gray-100 p-6 sm:p-7 space-y-4">

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-3-3.87" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-800">Peserta</h3>
                                    </div>
                                </div>
                                <span id="peserta-count"
                                    class="text-xs font-semibold text-blue-600 bg-blue-50 rounded-full px-2.5 py-1 flex-shrink-0">0
                                    dipilih</span>
                            </div>

                            <p class="text-xs text-amber-600 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                                Peserta yang udah punya rincian biaya bakal ditanya konfirmasi dulu kalau di-uncek —
                                biar biayanya gak kehapus gak sengaja.
                            </p>

                            <div class="flex flex-col sm:flex-row gap-2">
                                <div class="relative flex-1 min-w-0">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 text-gray-300 absolute left-3.5 top-1/2 -translate-y-1/2"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                                    </svg>
                                    <input type="text" id="peserta-search" placeholder="Cari nama..."
                                        class="w-full border border-gray-200 rounded-lg pl-10 pr-8 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
                                    <button type="button" id="peserta-search-clear"
                                        class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500 transition"
                                        aria-label="Hapus pencarian">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="relative sm:w-40 flex-shrink-0">
                                    <select id="peserta-unit-filter"
                                        class="w-full appearance-none border border-gray-200 rounded-lg pl-3 pr-7 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white text-gray-600">
                                        <option value="">Semua unit</option>
                                        @foreach ($unitKerjaList as $unit)
                                            <option value="{{ strtolower($unit) }}">{{ $unit }}</option>
                                        @endforeach
                                    </select>
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-3.5 w-3.5 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Chip peserta yang sudah dicentang — nempel di sini, gak ketimbun pas list panjang
                            di-scroll --}}
                            <div id="peserta-selected-chips" class="hidden flex flex-wrap gap-1.5"></div>

                            {{-- Tab switcher --}}
                            <div class="flex items-center gap-1.5 border-b border-gray-100">
                                <button type="button" data-tab="pns" data-total="{{ $pesertaPnsList->count() }}"
                                    class="peserta-tab-btn text-sm font-medium px-3 py-2 border-b-2 border-blue-600 text-blue-600 -mb-px transition flex items-center gap-1.5">
                                    PNS
                                    <span
                                        class="peserta-tab-badge text-[11px] font-semibold rounded-full px-1.5 py-0.5 bg-blue-50 text-blue-500">{{ $pesertaPnsList->count() }}</span>
                                </button>
                                <button type="button" data-tab="nonpns" data-total="{{ $pesertaNonPnsList->count() }}"
                                    class="peserta-tab-btn text-sm font-medium px-3 py-2 border-b-2 border-transparent text-gray-400 hover:text-gray-600 -mb-px transition flex items-center gap-1.5">
                                    Non PNS
                                    <span
                                        class="peserta-tab-badge text-[11px] font-semibold rounded-full px-1.5 py-0.5 bg-gray-100 text-gray-400">{{ $pesertaNonPnsList->count() }}</span>
                                </button>
                                <label
                                    class="ml-auto flex items-center gap-1.5 text-xs text-gray-400 cursor-pointer select-none self-center">
                                    <input type="checkbox"
                                        class="peserta-select-all rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="peserta-select-all-label">Pilih semua</span>
                                </label>
                            </div>

                            @foreach ([
                                    ['key' => 'pns', 'list' => $pesertaPnsList],
                                    ['key' => 'nonpns', 'list' => $pesertaNonPnsList],
                                ] as $group)
                                <div data-peserta-panel="{{ $group['key'] }}"
                                    class="{{ $loop->first ? '' : 'hidden' }} flex-1 min-h-[220px] overflow-y-auto divide-y divide-gray-50 -mx-1 pr-1">
                                    @forelse ($group['list'] as $p)
                                        @php
                                            $nama = $p->nama_gelar ?? $p->nama;
                                            $isKaro = str_contains(strtolower($p->jabatan ?? ''), 'kepala biro');
                                        @endphp
                                        <label
                                            class="peserta-item flex items-center gap-2.5 px-1.5 py-2.5 text-sm rounded-md cursor-pointer transition hover:bg-gray-50 has-[:checked]:bg-blue-50/70"
                                            data-nama="{{ strtolower($nama) }}"
                                            data-unit="{{ strtolower($p->unit_kerja ?? '') }}">
                                            <input type="checkbox" name="pegawai_id[]" value="{{ $p->id }}"
                                                class="peserta-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 flex-shrink-0"
                                                data-has-biaya="{{ $pesertaHasBiayaIds->contains($p->id) ? '1' : '0' }}"
                                                data-karo="{{ $isKaro ? '1' : '0' }}" data-nama-pegawai="{{ $nama }}"
                                                {{ in_array($p->id, $oldPesertaIds) ? 'checked' : '' }}>
                                            <span class="min-w-0 flex-1">
                                                <span class="block text-gray-700 truncate">{{ $nama }}</span>
                                                @if ($p->unit_kerja ?? false)
                                                    <span
                                                        class="block text-[11px] text-gray-400 truncate">{{ $p->unit_kerja }}</span>
                                                @endif
                                            </span>
                                            @if ($pesertaHasBiayaIds->contains($p->id))
                                                <span
                                                    class="flex-shrink-0 text-[10px] font-medium text-amber-600 bg-amber-50 rounded-full px-1.5 py-0.5">
                                                    ada biaya
                                                </span>
                                            @endif
                                        </label>
                                    @empty
                                        <p class="px-1 py-3 text-xs text-gray-400">Tidak ada data.</p>
                                    @endforelse
                                    <div class="peserta-empty-search hidden px-1 py-8 text-center">
                                        <p class="text-sm text-gray-400">Tidak ada nama yang cocok.</p>
                                        <button type="button"
                                            class="peserta-reset-filters mt-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700">
                                            Reset pencarian &amp; filter
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- KOLOM KANAN: detail agenda --}}
                    <div
                        class="lg:col-span-3 bg-white shadow-sm rounded-2xl border border-gray-100 p-6 sm:p-7 space-y-6 lg:h-full">

                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Informasi Agenda</h3>
                                    <p class="text-sm text-gray-400">Detail perjalanan dinas — nomor ST, tujuan, dan
                                        anggarannya.</p>
                                </div>
                            </div>
                            @php
                                $statusStyle = match (strtolower($agenda->status ?? '')) {
                                    'draft' => 'bg-gray-100 text-gray-500',
                                    'diajukan', 'proses', 'pending' => 'bg-amber-50 text-amber-600',
                                    'disetujui', 'selesai', 'approved' => 'bg-green-50 text-green-600',
                                    'ditolak', 'rejected' => 'bg-red-50 text-red-600',
                                    default => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            @if ($agenda->status ?? false)
                                <span
                                    class="flex-shrink-0 inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusStyle }}">
                                    {{ ucfirst($agenda->status) }}
                                </span>
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                                Nomor Surat Tugas <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="nomor_st" value="{{ old('nomor_st', $agenda->nomor_st) }}"
                                required placeholder="800/123/ST/2026"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
                            <p class="text-xs text-gray-300 mt-1">Dipakai bareng buat semua peserta.</p>
                        </div>

                        {{-- Muncul otomatis kalau ada Kepala Biro yang dicentang di kolom peserta --}}
                        <div id="nomor-st-karo-wrap" class="hidden rounded-lg transition-shadow duration-500">
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                                Nomor ST Kepala Biro <span class="text-red-400">*</span>
                                <span id="nomor-st-karo-nama" class="text-gray-300 font-normal"></span>
                            </label>
                            <input type="text" id="nomor-st-karo-input" name="nomor_st_karo"
                                value="{{ old('nomor_st_karo', $agenda->nomor_st_karo) }}" placeholder="800/124/ST/2026"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
                            <p class="text-xs text-gray-300 mt-1">Khusus Kepala Biro, biasanya beda nomor dari peserta
                                lain.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Uraian Kegiatan <span
                                    class="text-red-400">*</span></label>
                            <input type="text" name="uraian_kegiatan"
                                value="{{ old('uraian_kegiatan', $agenda->uraian_kegiatan) }}" required
                                placeholder="Rapat koordinasi program tahunan"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Tujuan (Provinsi) <span
                                        class="text-red-400">*</span></label>
                                <select name="tujuan" id="tujuan-select" required data-placeholder="Cari provinsi..."
                                    class="js-searchable w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                                    <option value="" disabled {{ old('tujuan', $agenda->tujuan) ? '' : 'selected' }}>
                                        Pilih provinsi tujuan</option>
                                    @foreach ($provinsiList as $provinsi)
                                        <option value="{{ $provinsi }}" @selected(old('tujuan', $agenda->tujuan) === $provinsi)>{{ $provinsi }}</option>
                                    @endforeach
                                    {{-- Jaga-jaga data lama yang tujuan-nya belum tentu ada di master sbm_rates
                                    (misal diisi bebas sebelum fitur ini ada) — tetap muncul biar gak keganti diam-diam --}}
                                    @if ($agenda->tujuan && !$provinsiList->contains($agenda->tujuan))
                                        <option value="{{ $agenda->tujuan }}" selected>{{ $agenda->tujuan }} (belum ada
                                            di master SBM)</option>
                                    @endif
                                </select>
                                @if ($provinsiList->isEmpty())
                                    <p class="text-xs text-amber-600 mt-1">Belum ada data provinsi di master SBM.</p>
                                @endif
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Kab/Kota</label>
                                <select name="kota_tujuan" id="kota-tujuan-select" data-placeholder="Cari kab/kota..."
                                    class="js-searchable w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition disabled:bg-gray-50 disabled:text-gray-400">
                                    <option value="">Pilih provinsi dulu</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Tanggal Mulai <span
                                        class="text-red-400">*</span></label>
                                <input type="date" name="tanggal_mulai" id="tanggal-mulai"
                                    value="{{ old('tanggal_mulai', $agenda->tanggal_mulai->format('Y-m-d')) }}" required
                                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Tanggal Selesai <span
                                        class="text-red-400">*</span></label>
                                <input type="date" name="tanggal_selesai" id="tanggal-selesai"
                                    value="{{ old('tanggal_selesai', $agenda->tanggal_selesai->format('Y-m-d')) }}" required
                                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                                <p id="durasi-info" class="hidden text-xs text-blue-500 mt-1"></p>
                            </div>
                        </div>

                        <div class="sm:w-1/2 sm:pr-2">
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Alat Angkut <span
                                    class="text-red-400">*</span></label>
                            <div class="relative">
                                <select name="alat_angkut" required
                                    class="w-full appearance-none bg-none border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white">
                                    <option value="" disabled
                                        {{ old('alat_angkut', $agenda->alat_angkut) ? '' : 'selected' }}>— Pilih —
                                    </option>
                                    <option value="darat" @selected(old('alat_angkut', $agenda->alat_angkut) === 'darat')>Angkutan Darat</option>
                                    <option value="udara" @selected(old('alat_angkut', $agenda->alat_angkut) === 'udara')>Angkutan Udara</option>
                                    <option value="laut" @selected(old('alat_angkut', $agenda->alat_angkut) === 'laut')>Angkutan Laut</option>
                                    <option value="darat_udara" @selected(old('alat_angkut', $agenda->alat_angkut) === 'darat_udara')>Angkutan Darat dan Udara</option>
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        {{-- ===== Penanggung jawab ===== --}}
                        <div class="pt-6 border-t border-gray-100">
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <p class="text-xs font-semibold text-gray-500">
                                    Penanggung jawab <span class="text-red-400">*</span>
                                </p>
                                <span id="pj-progress"
                                    class="text-[11px] font-semibold rounded-full px-2 py-0.5 bg-gray-50 text-gray-400 transition">
                                    0/3 dipilih
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1.5">PPK</label>
                                    <div class="relative">
                                        <select name="ppk_id" required data-placeholder="Cari PPK..."
                                            class="js-searchable w-full appearance-none bg-none border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white">
                                            <option value="" disabled
                                                {{ old('ppk_id', $agenda->ppk_id) ? '' : 'selected' }}>— Pilih —
                                            </option>
                                            @forelse ($ppkList as $p)
                                                <option value="{{ $p->id }}"
                                                    {{ old('ppk_id', $agenda->ppk_id) == $p->id ? 'selected' : '' }}>
                                                    {{ $p->nama_gelar ?? $p->nama }}
                                                </option>
                                            @empty
                                                <option value="" disabled>Belum ada pegawai berlabel PPK</option>
                                            @endforelse
                                        </select>
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-3.5 w-3.5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1.5">Bendahara</label>
                                    <div class="relative">
                                        <select name="bendahara_id" required data-placeholder="Cari Bendahara..."
                                            class="js-searchable w-full appearance-none bg-none border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white">
                                            <option value="" disabled
                                                {{ old('bendahara_id', $agenda->bendahara_id) ? '' : 'selected' }}>—
                                                Pilih —</option>
                                            @forelse ($bendaharaList as $p)
                                                <option value="{{ $p->id }}"
                                                    {{ old('bendahara_id', $agenda->bendahara_id) == $p->id ? 'selected' : '' }}>
                                                    {{ $p->nama_gelar ?? $p->nama }}
                                                </option>
                                            @empty
                                                <option value="" disabled>Belum ada pegawai berlabel Bendahara</option>
                                            @endforelse
                                        </select>
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-3.5 w-3.5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1.5">Penanggung Jawab Kegiatan</label>
                                    <div class="relative">
                                        <select name="penanggung_jawab_id" required data-placeholder="Cari Penanggung Jawab..."
                                            class="js-searchable w-full appearance-none bg-none border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white">
                                            <option value="" disabled
                                                {{ old('penanggung_jawab_id', $agenda->penanggung_jawab_id) ? '' : 'selected' }}>
                                                — Pilih —</option>
                                            @forelse ($pjList as $p)
                                                <option value="{{ $p->id }}"
                                                    {{ old('penanggung_jawab_id', $agenda->penanggung_jawab_id) == $p->id ? 'selected' : '' }}>
                                                    {{ $p->nama_gelar ?? $p->nama }}
                                                </option>
                                            @empty
                                                <option value="" disabled>Belum ada pegawai berlabel ini</option>
                                            @endforelse
                                        </select>
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-3.5 w-3.5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== Anggaran & Administrasi (bisa dilipat) ===== --}}
                        <div class="mt-8 pt-6 border-t border-gray-100">
                            <button type="button" id="anggaran-toggle" aria-expanded="true"
                                aria-controls="anggaran-body"
                                class="group w-full flex items-center gap-3 text-left rounded-lg -mx-1 px-1 py-1 hover:bg-gray-50/70 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 transition">
                                <span
                                    class="h-9 w-9 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="block text-sm font-bold text-gray-700">Anggaran &amp;
                                        Administrasi</span>
                                    <span class="block text-xs text-gray-400 truncate">Dipakai bersama untuk memorandum
                                        PNS &amp; Non-PNS, cukup diisi sekali.</span>
                                </span>
                                <svg id="anggaran-chevron" xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-gray-400 flex-shrink-0 transition-transform duration-300"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div id="anggaran-body" class="overflow-hidden transition-all duration-300 ease-out">
                                <div class="pt-5 space-y-5">
                                    @include('agendas.partials.administrasi-anggaran', ['agenda' => $agenda])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-6">
                    <a href="{{ route('agendas.show', $agenda) }}"
                        class="text-sm text-gray-400 hover:text-gray-600 transition">
                        &larr; Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg text-sm shadow-md shadow-blue-600/20 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // State section "Anggaran & Administrasi" — dideklarasikan paling atas supaya
        // aman dipakai fungsi mana pun di bawah (tidak bergantung urutan pemanggilan).
        let anggaranOpen = true;

        // Di halaman edit, peserta sudah tercentang dari awal. Flag ini mencegah
        // highlight "field baru muncul" menyala saat render pertama — highlight
        // cuma untuk perubahan akibat klik user.
        let initialRender = true;

        // ==== Tab switcher (PNS / Non PNS) ====
        function showPesertaPanel(key) {
            document.querySelectorAll('[data-peserta-panel]').forEach(function (panel) {
                panel.classList.toggle('hidden', panel.dataset.pesertaPanel !== key);
            });
            document.querySelectorAll('.peserta-tab-btn').forEach(function (btn) {
                const active = btn.dataset.tab === key;
                btn.classList.toggle('border-blue-600', active);
                btn.classList.toggle('text-blue-600', active);
                btn.classList.toggle('border-transparent', !active);
                btn.classList.toggle('text-gray-400', !active);
                const badge = btn.querySelector('.peserta-tab-badge');
                if (badge) {
                    badge.classList.toggle('bg-blue-50', active);
                    badge.classList.toggle('text-blue-500', active);
                    badge.classList.toggle('bg-gray-100', !active);
                    badge.classList.toggle('text-gray-400', !active);
                }
            });
        }

        document.querySelectorAll('.peserta-tab-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                showPesertaPanel(btn.dataset.tab);
            });
        });

        // ==== Highlight singkat untuk field yang baru muncul karena pilihan peserta ====
        function flashField(wrap) {
            if (!wrap) return;
            wrap.classList.add('ring-2', 'ring-blue-300', 'ring-offset-4');
            setTimeout(function () {
                wrap.classList.remove('ring-2', 'ring-blue-300', 'ring-offset-4');
            }, 1500);
        }

        // Ubah visibilitas wrapper; kalau barusan berubah dari hidden -> tampil, kasih highlight
        // (kecuali pada render pertama). Return true kalau memang baru muncul.
        function setWrapVisible(wrap, visible) {
            const wasHidden = wrap.classList.contains('hidden');
            wrap.classList.toggle('hidden', !visible);
            if (visible && wasHidden) {
                if (!initialRender) flashField(wrap);
                return true;
            }
            return false;
        }

        // ==== Muncul/ilang field Nomor ST Kepala Biro sesuai peserta yg dicentang ====
        function toggleNomorStKaro(checked) {
            const wrap = document.getElementById('nomor-st-karo-wrap');
            const namaLabel = document.getElementById('nomor-st-karo-nama');
            const input = document.getElementById('nomor-st-karo-input');
            if (!wrap) return;

            const karo = checked.find(function (cb) { return cb.dataset.karo === '1'; });

            setWrapVisible(wrap, Boolean(karo));
            if (namaLabel) {
                namaLabel.textContent = karo ? '(' + karo.dataset.namaPegawai + ')' : '';
            }
            // required cuma aktif kalau field-nya kelihatan (ada Karo yg dicentang) —
            // biar gak nahan submit gara-gara field yg lagi disembunyiin.
            if (input) {
                input.required = Boolean(karo);
            }
            syncAnggaranHeight();
        }

        // ==== Nomor Memo PNS/Non-PNS cuma diminta sesuai status peserta yg dicentang ====
        function toggleNomorMemoFields(checked) {
            const pnsWrap = document.getElementById('nomor-memo-pns-wrap');
            const pnsInput = document.getElementById('nomor-memo-pns-input');
            const nonPnsWrap = document.getElementById('nomor-memo-non-pns-wrap');
            const nonPnsInput = document.getElementById('nomor-memo-non-pns-input');
            if (!pnsWrap || !nonPnsWrap) return;

            function statusOf(cb) {
                const panel = cb.closest('[data-peserta-panel]');
                return panel ? panel.dataset.pesertaPanel : null;
            }

            const adaPns = checked.some(function (cb) { return statusOf(cb) === 'pns'; });
            const adaNonPns = checked.some(function (cb) { return statusOf(cb) === 'nonpns'; });

            setWrapVisible(pnsWrap, adaPns);
            setWrapVisible(nonPnsWrap, adaNonPns);
            if (pnsInput) pnsInput.required = adaPns;
            if (nonPnsInput) nonPnsInput.required = adaNonPns;

            syncAnggaranHeight();
        }

        // ==== Counter "X dipilih" + chip peserta terpilih (nempel di atas list) ====
        function updatePesertaCount() {
            const checked = Array.from(document.querySelectorAll('.peserta-checkbox:checked'));

            toggleNomorStKaro(checked);
            toggleNomorMemoFields(checked);

            const counter = document.getElementById('peserta-count');
            if (counter) counter.textContent = checked.length + ' dipilih';

            const chipsWrap = document.getElementById('peserta-selected-chips');
            if (!chipsWrap) return;

            if (checked.length === 0) {
                chipsWrap.classList.add('hidden');
                chipsWrap.innerHTML = '';
                return;
            }

            chipsWrap.classList.remove('hidden');
            chipsWrap.innerHTML = '';
            checked.forEach(function (cb) {
                // pakai data-nama-pegawai (bukan selector DOM) supaya tidak rusak kalau struktur label berubah
                const nama = cb.dataset.namaPegawai ?? '';
                const chip = document.createElement('span');
                chip.className = 'inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-xs font-medium pl-2.5 pr-1.5 py-1 rounded-full';
                chip.innerHTML = '<span></span><button type="button" class="hover:bg-blue-100 rounded-full p-0.5 transition" aria-label="Hapus">'
                    + '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>'
                    + '</button>';
                chip.querySelector('span').textContent = nama;
                chip.querySelector('button').addEventListener('click', function () {
                    if (cb.dataset.hasBiaya === '1') {
                        const ok = confirm('Peserta ini sudah punya rincian biaya. Kalau dihapus, data biayanya bakal ikut hilang pas disimpan. Lanjut?');
                        if (!ok) return;
                    }
                    cb.checked = false;
                    updatePesertaCount();
                });
                chipsWrap.appendChild(chip);
            });
        }

        // ==== Search + filter Unit Kerja, gabung jadi satu fungsi, auto-pindah tab kalau tab aktif kosong ====
        const pesertaSearch = document.getElementById('peserta-search');
        const pesertaSearchClear = document.getElementById('peserta-search-clear');
        const pesertaUnitFilter = document.getElementById('peserta-unit-filter');
        const pesertaSelectAllLabel = document.querySelector('.peserta-select-all-label');

        function applyPesertaFilters() {
            const keyword = (pesertaSearch?.value ?? '').trim().toLowerCase();
            const unit = pesertaUnitFilter?.value ?? '';
            const filterActive = Boolean(keyword || unit);
            const visibleCountByGroup = {};

            if (pesertaSearchClear) pesertaSearchClear.classList.toggle('hidden', !keyword);

            document.querySelectorAll('[data-peserta-panel]').forEach(function (panel) {
                const group = panel.dataset.pesertaPanel;
                const items = panel.querySelectorAll('.peserta-item');
                let visibleCount = 0;

                items.forEach(function (item) {
                    const matchNama = !keyword || item.dataset.nama.includes(keyword);
                    const matchUnit = !unit || item.dataset.unit === unit;
                    const match = matchNama && matchUnit;
                    item.classList.toggle('hidden', !match);
                    if (match) visibleCount++;
                });

                visibleCountByGroup[group] = visibleCount;

                const emptyMsg = panel.querySelector('.peserta-empty-search');
                if (emptyMsg) {
                    emptyMsg.classList.toggle('hidden', items.length === 0 || visibleCount > 0);
                }
            });

            // Sinkronkan badge angka di tab dengan hasil filter yang sedang tampil,
            // supaya "PNS 12" ikut berubah jadi "PNS 3" saat difilter — bukan tetap diam.
            document.querySelectorAll('.peserta-tab-btn').forEach(function (btn) {
                const badge = btn.querySelector('.peserta-tab-badge');
                if (!badge) return;
                badge.textContent = visibleCountByGroup[btn.dataset.tab];
            });

            if (pesertaSelectAllLabel) {
                pesertaSelectAllLabel.textContent = filterActive ? 'Pilih semua (hasil filter)' : 'Pilih semua';
            }

            // kalau tab yang lagi aktif hasilnya nihil, tapi tab satunya ada hasil, pindahin otomatis
            if (filterActive) {
                const activePanel = document.querySelector('[data-peserta-panel]:not(.hidden)');
                const activeGroup = activePanel?.dataset.pesertaPanel;
                if (activeGroup && visibleCountByGroup[activeGroup] === 0) {
                    const otherGroup = Object.keys(visibleCountByGroup).find(function (g) {
                        return g !== activeGroup && visibleCountByGroup[g] > 0;
                    });
                    if (otherGroup) showPesertaPanel(otherGroup);
                }
            }
        }

        if (pesertaSearch) pesertaSearch.addEventListener('input', applyPesertaFilters);
        if (pesertaUnitFilter) pesertaUnitFilter.addEventListener('change', applyPesertaFilters);
        if (pesertaSearchClear) {
            pesertaSearchClear.addEventListener('click', function () {
                if (!pesertaSearch) return;
                pesertaSearch.value = '';
                pesertaSearch.focus();
                applyPesertaFilters();
            });
        }
        document.querySelectorAll('.peserta-reset-filters').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (pesertaSearch) pesertaSearch.value = '';
                if (pesertaUnitFilter) pesertaUnitFilter.value = '';
                applyPesertaFilters();
            });
        });

        // ==== "Pilih semua" — hanya menyentuh baris yang lagi terlihat di tab aktif ====
        const selectAllToggle = document.querySelector('.peserta-select-all');
        if (selectAllToggle) {
            selectAllToggle.addEventListener('change', function () {
                const activePanel = document.querySelector('[data-peserta-panel]:not(.hidden)');
                if (!activePanel) return;
                activePanel.querySelectorAll('.peserta-checkbox').forEach(function (cb) {
                    const item = cb.closest('.peserta-item');
                    if (!item || item.classList.contains('hidden')) return;

                    // Batalin unchecking otomatis kalau peserta ini udah punya biaya —
                    // biar "pilih semua" (nge-uncheck) gak diam-diam ngapus data.
                    if (!selectAllToggle.checked && cb.checked && cb.dataset.hasBiaya === '1') {
                        return;
                    }
                    cb.checked = selectAllToggle.checked;
                });
                updatePesertaCount();
            });
        }

        // ==== Warning sebelum uncek peserta yang udah punya rincian biaya ====
        document.querySelectorAll('.peserta-checkbox').forEach(function (cb) {
            cb.addEventListener('change', function () {
                if (!cb.checked && cb.dataset.hasBiaya === '1') {
                    const ok = confirm('Peserta ini sudah punya rincian biaya. Kalau dihapus, data biayanya bakal ikut hilang pas disimpan. Lanjut?');
                    if (!ok) {
                        cb.checked = true;
                        return;
                    }
                }
                updatePesertaCount();
            });
        });

        // ==== Section "Anggaran & Administrasi" bisa dilipat ====

        // Dipanggil tiap kali ada field di dalamnya yang muncul/hilang, biar tinggi
        // kontainernya ikut nyesuaiin dan isinya gak kepotong.
        function syncAnggaranHeight() {
            const body = document.getElementById('anggaran-body');
            if (body && anggaranOpen) {
                body.style.maxHeight = 'none';
                // overflow visible lagi tiap kali dipanggil selagi section kebuka, soalnya
                // beberapa pemicu (toggleNomorStKaro, dll) bisa nambah tinggi konten setelahnya
                body.style.overflow = 'visible';
            }
        }

        (function () {
            const btn = document.getElementById('anggaran-toggle');
            const body = document.getElementById('anggaran-body');
            const chevron = document.getElementById('anggaran-chevron');
            if (!btn || !body) return;

            function setOpen(next) {
                anggaranOpen = next;
                btn.setAttribute('aria-expanded', next);
                if (chevron) chevron.style.transform = next ? 'rotate(180deg)' : 'rotate(0deg)';

                if (next) {
                    // overflow-hidden cuma dipakai SELAMA transisi buka, biar animasinya mulus
                    body.style.overflow = 'hidden';
                    body.style.maxHeight = body.scrollHeight + 'px';
                    body.style.opacity = '1';
                    // lepas ke 'none' + overflow visible setelah animasi kelar, supaya dropdown
                    // & field yang muncul belakangan (termasuk dropdown searchable MAK/Petugas
                    // Verifikasi/PIC yang kalau kepotong overflow-hidden jadi kelihatan kosong)
                    // gak ketahan/kepotong tinggi kontainernya.
                    setTimeout(function () {
                        if (anggaranOpen) {
                            body.style.maxHeight = 'none';
                            body.style.overflow = 'visible';
                        }
                    }, 320);
                } else {
                    // balik ke hidden dulu sebelum nutup, soalnya pas kebuka overflow-nya
                    // sempat dilepas ke visible — transisi ketutup butuh overflow-hidden lagi
                    body.style.overflow = 'hidden';
                    body.style.maxHeight = body.scrollHeight + 'px';
                    void body.offsetHeight; // paksa reflow biar transisi jalan
                    body.style.maxHeight = '0px';
                    body.style.opacity = '0';
                }
            }

            btn.addEventListener('click', function () { setOpen(!anggaranOpen); });

            // default kebuka; ganti ke setOpen(false) kalau mau default terlipat
            setOpen(true);
        })();

        // ==== Kalau validasi browser menolak submit gara-gara field di section Anggaran
        // yang lagi terlipat, buka otomatis lalu arahkan fokus ke field-nya. ====
        // Pakai capture (true) karena event "invalid" tidak bubble.
        document.getElementById('agenda-form').addEventListener('invalid', function (e) {
            const body = document.getElementById('anggaran-body');
            if (!body || !body.contains(e.target) || anggaranOpen) return;

            document.getElementById('anggaran-toggle').click();
            const target = e.target;
            setTimeout(function () {
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                target.focus({ preventScroll: true });
            }, 350);
        }, true);

        // ==== Badge progres penanggung jawab ====
        (function () {
            const badge = document.getElementById('pj-progress');
            const selects = ['ppk_id', 'bendahara_id', 'penanggung_jawab_id']
                .map(function (n) { return document.querySelector('[name="' + n + '"]'); })
                .filter(Boolean);
            if (!badge || !selects.length) return;

            function render() {
                const terisi = selects.filter(function (s) { return s.value; }).length;
                const lengkap = terisi === selects.length;
                badge.textContent = terisi + '/' + selects.length + ' dipilih';
                badge.classList.toggle('bg-blue-50', lengkap);
                badge.classList.toggle('text-blue-600', lengkap);
                badge.classList.toggle('bg-gray-50', !lengkap);
                badge.classList.toggle('text-gray-400', !lengkap);
            }

            selects.forEach(function (s) { s.addEventListener('change', render); });
            render();
        })();

        // ==== Tanggal selesai gak boleh mundur dari tanggal mulai + info durasi ====
        (function () {
            const mulai = document.getElementById('tanggal-mulai');
            const selesai = document.getElementById('tanggal-selesai');
            const info = document.getElementById('durasi-info');
            if (!mulai || !selesai) return;

            function sync() {
                if (mulai.value) selesai.min = mulai.value;
                if (mulai.value && selesai.value && selesai.value < mulai.value) {
                    selesai.value = mulai.value;
                }

                if (!info) return;
                if (mulai.value && selesai.value) {
                    const hari = Math.round((new Date(selesai.value) - new Date(mulai.value)) / 86400000) + 1;
                    info.textContent = hari + ' hari perjalanan';
                    info.classList.remove('hidden');
                } else {
                    info.classList.add('hidden');
                }
            }

            mulai.addEventListener('change', sync);
            selesai.addEventListener('change', sync);
            sync();
        })();

        updatePesertaCount();
        initialRender = false;
        applyPesertaFilters();

        // ==== Dropdown Kab/Kota cascading sesuai Provinsi (Tujuan) yang dipilih ====
        (function () {
            const KAB_KOTA_MAP = @json($kabKotaMap);
            const provinsiSelect = document.getElementById('tujuan-select');
            const kotaSelect = document.getElementById('kota-tujuan-select');
            if (!provinsiSelect || !kotaSelect) return;

            const currentKota = @json(old('kota_tujuan', $agenda->kota_tujuan));

            function renderKotaOptions() {
                const list = KAB_KOTA_MAP[provinsiSelect.value] || [];
                kotaSelect.innerHTML = '';

                if (!provinsiSelect.value) {
                    kotaSelect.appendChild(new Option('Pilih provinsi dulu', ''));
                    kotaSelect.disabled = true;
                    return;
                }
                if (!list.length) {
                    kotaSelect.appendChild(new Option('Belum ada data kab/kota', ''));
                    kotaSelect.disabled = true;
                    return;
                }

                kotaSelect.disabled = false;
                kotaSelect.appendChild(new Option('Pilih kab/kota', ''));
                list.forEach(function (nama) {
                    const opt = new Option(nama, nama);
                    if (currentKota && currentKota === nama) opt.selected = true;
                    kotaSelect.appendChild(opt);
                });

                // Data lama yang kab/kota-nya belum tentu ada di master — tetap
                // muncul biar gak keganti diam-diam pas halaman dibuka.
                if (currentKota && !list.includes(currentKota)) {
                    const opt = new Option(currentKota + ' (belum ada di master)', currentKota, true, true);
                    kotaSelect.appendChild(opt);
                }
            }

            provinsiSelect.addEventListener('change', renderKotaOptions);
            renderKotaOptions();
        })();

        // ==== Searchable select: ubah <select class="js-searchable"> jadi kotak yang bisa
        // diketik buat nyaring opsi. Dibikin baca opsi & status disabled secara live (bukan
        // di-cache sekali di awal), plus dipantau lewat MutationObserver — supaya select yang
        // opsinya berubah belakangan (kayak Kab/Kota yang nunggu Provinsi) tetap ikut update. ====
        (function () {
            function enhanceSearchable(select) {
                if (!select || select.dataset.searchEnhanced) return;
                select.dataset.searchEnhanced = '1';

                const originalParent = select.parentElement;
                // sembunyikan ikon panah bawaan (kalau markup select ini sudah punya svg sendiri),
                // biar gak dobel sama ikon yang kita pasang di bawah
                const existingIcon = originalParent.querySelector('svg');
                if (existingIcon) existingIcon.style.display = 'none';

                const wrapper = document.createElement('div');
                wrapper.className = 'relative';

                const input = document.createElement('input');
                input.type = 'text';
                input.autocomplete = 'off';
                input.className = select.className.replace('js-searchable', '').trim() + ' pr-9';
                if (select.hasAttribute('required')) input.setAttribute('required', 'required');

                const iconWrap = document.createElement('span');
                iconWrap.className = 'pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400';
                iconWrap.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>';

                const list = document.createElement('ul');
                list.className = 'absolute z-20 top-full left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg py-1 text-sm hidden';

                let highlighted = -1;

                function liveOptions() {
                    return Array.from(select.options).filter(function (o) { return o.value !== ''; });
                }

                function itemEls() {
                    return Array.from(list.children).filter(function (li) { return li.dataset.value !== undefined; });
                }

                function renderList(filterText) {
                    const f = (filterText || '').toLowerCase();
                    list.innerHTML = '';
                    highlighted = -1;
                    const filtered = liveOptions().filter(function (o) {
                        return o.textContent.trim().toLowerCase().includes(f);
                    });
                    if (filtered.length === 0) {
                        const li = document.createElement('li');
                        li.className = 'px-3.5 py-2 text-gray-400 text-xs';
                        li.textContent = 'Tidak ditemukan';
                        list.appendChild(li);
                        return;
                    }
                    filtered.forEach(function (o) {
                        const li = document.createElement('li');
                        li.textContent = o.textContent.trim();
                        li.dataset.value = o.value;
                        li.className = 'px-3.5 py-2 cursor-pointer hover:bg-blue-50 text-gray-700' +
                            (o.value === select.value ? ' bg-blue-50 font-medium text-blue-700' : '');
                        li.addEventListener('mousedown', function (e) {
                            e.preventDefault(); // biar blur gak duluan nutup list sebelum klik kepilih
                            pick(o);
                        });
                        list.appendChild(li);
                    });
                }

                function pick(o) {
                    select.value = o.value;
                    input.value = o.textContent.trim();
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    closeList();
                }

                function openList() {
                    if (select.disabled) return;
                    renderList(input.value);
                    list.classList.remove('hidden');
                }

                function closeList() {
                    list.classList.add('hidden');
                }

                function updateHighlight() {
                    const items = itemEls();
                    items.forEach(function (li, idx) {
                        li.classList.toggle('bg-blue-100', idx === highlighted);
                    });
                    if (items[highlighted]) items[highlighted].scrollIntoView({ block: 'nearest' });
                }

                // Sinkronkan tampilan kotak teks dengan select aslinya — dipanggil pas
                // inisialisasi dan tiap kali select berubah dari luar (mis. Kab/Kota
                // yang opsinya diisi ulang ketika Provinsi diganti).
                function refreshFromSelect() {
                    const current = select.selectedOptions[0];
                    input.value = (current && current.value !== '') ? current.textContent.trim() : '';
                    input.disabled = select.disabled;
                    if (select.disabled) {
                        input.placeholder = current ? current.textContent.trim() : 'Tidak tersedia';
                        closeList();
                    } else {
                        input.placeholder = select.dataset.placeholder || 'Cari & pilih...';
                    }
                }

                input.addEventListener('focus', function () {
                    if (select.disabled) return;
                    input.select();
                    openList();
                });

                input.addEventListener('input', openList);

                input.addEventListener('blur', function () {
                    setTimeout(function () {
                        refreshFromSelect();
                        closeList();
                    }, 120);
                });

                input.addEventListener('keydown', function (e) {
                    if (select.disabled) return;
                    if (list.classList.contains('hidden') && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                        e.preventDefault();
                        openList();
                        return;
                    }
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        const items = itemEls();
                        highlighted = Math.min(highlighted + 1, items.length - 1);
                        updateHighlight();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        highlighted = Math.max(highlighted - 1, 0);
                        updateHighlight();
                    } else if (e.key === 'Enter') {
                        const items = itemEls();
                        if (!list.classList.contains('hidden') && highlighted >= 0 && items[highlighted]) {
                            e.preventDefault();
                            const val = items[highlighted].dataset.value;
                            const opt = liveOptions().find(function (o) { return o.value === val; });
                            if (opt) pick(opt);
                        }
                    } else if (e.key === 'Escape') {
                        closeList();
                    }
                });

                document.addEventListener('click', function (e) {
                    if (!wrapper.contains(e.target)) closeList();
                });

                // Pantau perubahan pada select aslinya (opsi diganti / disabled berubah)
                // yang dipicu skrip lain (mis. cascading Provinsi -> Kab/Kota), biar kotak
                // teks & dropdown kita selalu nyambung sama kondisi terbaru.
                const observer = new MutationObserver(refreshFromSelect);
                observer.observe(select, { attributes: true, attributeFilter: ['disabled'], childList: true });

                refreshFromSelect();

                select.classList.add('hidden');
                originalParent.insertBefore(wrapper, select);
                wrapper.appendChild(input);
                wrapper.appendChild(iconWrap);
                wrapper.appendChild(list);
                wrapper.appendChild(select);
            }

            document.querySelectorAll('select.js-searchable').forEach(enhanceSearchable);
        })();
    </script>
</x-app-layout>