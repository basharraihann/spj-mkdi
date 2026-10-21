@php
    $oldPesertaIds = old('pegawai_id', isset($agenda) && isset($selectedPesertaIds) ? $selectedPesertaIds->all() : []);

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

    $ppkList = $pegawaiList->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'PPK'))->values();
    $bendaharaList = $pegawaiList->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'Bendahara'))->values();
    $pjList = $pegawaiList->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'Penanggung Jawab Kegiatan'))->values();

    $unitKerjaList = $pegawaiList->pluck('unit_kerja')->filter()->unique()->sort()->values();
@endphp

<div class="mb-6">
    @include('agendas.partials.pilih-surat-tugas', [
        'suratTugasList' => $suratTugasList ?? [],
        'agenda' => $agenda ?? null,
        'isiPeserta' => true,
    ])
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:items-stretch">
    {{-- KOLOM KIRI: PILIH PESERTA --}}
    <div class="lg:col-span-2 lg:sticky lg:top-6 lg:h-full">
        <div class="lg:h-full flex flex-col bg-white shadow-sm rounded-2xl border border-gray-100 p-6 sm:p-7 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-3-3.87" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Peserta</h3>
                    </div>
                </div>
                <span id="peserta-count" class="text-xs font-semibold text-blue-600 bg-blue-50 rounded-full px-2.5 py-1 flex-shrink-0">0 dipilih</span>
            </div>

            @if(isset($agenda))
                <p class="text-xs text-amber-600 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                    Peserta yang udah punya rincian biaya bakal ditanya konfirmasi dulu kalau di-uncek — biar biayanya gak kehapus gak sengaja.
                </p>
            @endif

            <div class="flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1 min-w-0">
                    <input type="text" id="peserta-search" placeholder="Cari nama..." class="w-full border border-gray-200 rounded-lg pl-10 pr-8 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
                </div>
                <div class="relative sm:w-40 flex-shrink-0">
                    <select id="peserta-unit-filter" class="w-full appearance-none border border-gray-200 rounded-lg pl-3 pr-7 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white text-gray-600">
                        <option value="">Semua unit</option>
                        @foreach ($unitKerjaList as $unit)
                            <option value="{{ strtolower($unit) }}">{{ $unit }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="peserta-selected-chips" class="hidden flex flex-wrap gap-1.5"></div>

            <div class="flex items-center gap-1.5 border-b border-gray-100">
                <button type="button" data-tab="pns" data-total="{{ $pesertaPnsList->count() }}" class="peserta-tab-btn text-sm font-medium px-3 py-2 border-b-2 border-blue-600 text-blue-600 -mb-px transition flex items-center gap-1.5">
                    PNS <span class="peserta-tab-badge text-[11px] font-semibold rounded-full px-1.5 py-0.5 bg-blue-50 text-blue-500">{{ $pesertaPnsList->count() }}</span>
                </button>
                <button type="button" data-tab="nonpns" data-total="{{ $pesertaNonPnsList->count() }}" class="peserta-tab-btn text-sm font-medium px-3 py-2 border-b-2 border-transparent text-gray-400 hover:text-gray-600 -mb-px transition flex items-center gap-1.5">
                    Non PNS <span class="peserta-tab-badge text-[11px] font-semibold rounded-full px-1.5 py-0.5 bg-gray-100 text-gray-400">{{ $pesertaNonPnsList->count() }}</span>
                </button>
                <label class="ml-auto flex items-center gap-1.5 text-xs text-gray-400 cursor-pointer select-none self-center">
                    <input type="checkbox" class="peserta-select-all rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="peserta-select-all-label">Pilih semua</span>
                </label>
            </div>

            @foreach ([['key' => 'pns', 'list' => $pesertaPnsList], ['key' => 'nonpns', 'list' => $pesertaNonPnsList]] as $group)
                <div data-peserta-panel="{{ $group['key'] }}" class="{{ $loop->first ? '' : 'hidden' }} flex-1 min-h-[220px] overflow-y-auto divide-y divide-gray-50 -mx-1 pr-1">
                    @forelse ($group['list'] as $p)
                        @php
                            $nama = $p->nama_gelar ?? $p->nama;
                            $isKaro = str_contains(strtolower($p->jabatan ?? ''), 'kepala biro');
                            $hasBiaya = isset($pesertaHasBiayaIds) && $pesertaHasBiayaIds->contains($p->id);
                        @endphp
                        <label class="peserta-item flex items-center gap-2.5 px-1.5 py-2.5 text-sm rounded-md cursor-pointer transition hover:bg-gray-50 has-[:checked]:bg-blue-50/70" data-nama="{{ strtolower($nama) }}" data-unit="{{ strtolower($p->unit_kerja ?? '') }}">
                            <input type="checkbox" name="pegawai_id[]" value="{{ $p->id }}" class="peserta-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 flex-shrink-0" data-has-biaya="{{ $hasBiaya ? '1' : '0' }}" data-karo="{{ $isKaro ? '1' : '0' }}" data-nama-pegawai="{{ $nama }}" {{ in_array($p->id, $oldPesertaIds) ? 'checked' : '' }}>
                            <span class="min-w-0 flex-1">
                                <span class="block text-gray-700 truncate">{{ $nama }}</span>
                                @if ($p->unit_kerja ?? false)
                                    <span class="block text-[11px] text-gray-400 truncate">{{ $p->unit_kerja }}</span>
                                @endif
                            </span>
                            @if ($hasBiaya)
                                <span class="flex-shrink-0 text-[10px] font-medium text-amber-600 bg-amber-50 rounded-full px-1.5 py-0.5">ada biaya</span>
                            @endif
                        </label>
                    @empty
                        <p class="px-1 py-3 text-xs text-gray-400">Tidak ada data.</p>
                    @endforelse
                </div>
            @endforeach
        </div>
    </div>

    {{-- KOLOM KANAN: INFORMASI AGENDA --}}
    <div class="lg:col-span-3 bg-white shadow-sm rounded-2xl border border-gray-100 p-6 sm:p-7 space-y-6 lg:h-full">
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Nomor Surat Tugas <span class="text-red-400">*</span></label>
            <input type="text" name="nomor_st" value="{{ old('nomor_st', $agenda->nomor_st ?? '') }}" required placeholder="800/123/ST/2026" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
        </div>

        <div id="nomor-st-karo-wrap" class="hidden rounded-lg transition-shadow duration-500">
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                Nomor ST Kepala Biro <span class="text-red-400">*</span> <span id="nomor-st-karo-nama" class="text-gray-300 font-normal"></span>
            </label>
            <input type="text" id="nomor-st-karo-input" name="nomor_st_karo" value="{{ old('nomor_st_karo', $agenda->nomor_st_karo ?? '') }}" placeholder="800/124/ST/2026" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Uraian Kegiatan <span class="text-red-400">*</span></label>
            <input type="text" name="uraian_kegiatan" value="{{ old('uraian_kegiatan', $agenda->uraian_kegiatan ?? '') }}" required placeholder="Rapat koordinasi program tahunan" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Tujuan (Provinsi) <span class="text-red-400">*</span></label>
                <select name="tujuan" id="tujuan-select" required data-placeholder="Cari provinsi..." class="js-searchable w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                    <option value="" disabled {{ old('tujuan', $agenda->tujuan ?? '') ? '' : 'selected' }}>Pilih provinsi tujuan</option>
                    @foreach ($provinsiList as $provinsi)
                        <option value="{{ $provinsi }}" @selected(old('tujuan', $agenda->tujuan ?? '') === $provinsi)>{{ $provinsi }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Kab/Kota</label>
                <select name="kota_tujuan" id="kota-tujuan-select" data-placeholder="Cari kab/kota..." class="js-searchable w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                    <option value="">Pilih provinsi dulu</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Tanggal Mulai <span class="text-red-400">*</span></label>
                <input type="date" name="tanggal_mulai" id="tanggal-mulai" value="{{ old('tanggal_mulai', isset($agenda->tanggal_mulai) ? $agenda->tanggal_mulai->format('Y-m-d') : '') }}" required class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Tanggal Selesai <span class="text-red-400">*</span></label>
                <input type="date" name="tanggal_selesai" id="tanggal-selesai" value="{{ old('tanggal_selesai', isset($agenda->tanggal_selesai) ? $agenda->tanggal_selesai->format('Y-m-d') : '') }}" required class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                <p id="durasi-info" class="hidden text-xs text-blue-500 mt-1"></p>
            </div>
        </div>

        <div class="sm:w-1/2 sm:pr-2">
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Alat Angkut <span class="text-red-400">*</span></label>
            <select name="alat_angkut" required class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white">
                <option value="" disabled {{ old('alat_angkut', $agenda->alat_angkut ?? '') ? '' : 'selected' }}>— Pilih —</option>
                <option value="darat" @selected(old('alat_angkut', $agenda->alat_angkut ?? '') === 'darat')>Angkutan Darat</option>
                <option value="udara" @selected(old('alat_angkut', $agenda->alat_angkut ?? '') === 'udara')>Angkutan Udara</option>
                <option value="laut" @selected(old('alat_angkut', $agenda->alat_angkut ?? '') === 'laut')>Angkutan Laut</option>
                <option value="darat_udara" @selected(old('alat_angkut', $agenda->alat_angkut ?? '') === 'darat_udara')>Angkutan Darat dan Udara</option>
            </select>
        </div>

        {{-- PENANGGUNG JAWAB --}}
        <div class="pt-6 border-t border-gray-100">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">PPK</label>
                    <select name="ppk_id" required data-placeholder="Cari PPK..." class="js-searchable w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white">
                        <option value="" disabled {{ old('ppk_id', $agenda->ppk_id ?? '') ? '' : 'selected' }}>— Pilih —</option>
                        @foreach ($ppkList as $p)
                            <option value="{{ $p->id }}" {{ old('ppk_id', $agenda->ppk_id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->nama_gelar ?? $p->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Bendahara</label>
                    <select name="bendahara_id" required data-placeholder="Cari Bendahara..." class="js-searchable w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white">
                        <option value="" disabled {{ old('bendahara_id', $agenda->bendahara_id ?? '') ? '' : 'selected' }}>— Pilih —</option>
                        @foreach ($bendaharaList as $p)
                            <option value="{{ $p->id }}" {{ old('bendahara_id', $agenda->bendahara_id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->nama_gelar ?? $p->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Penanggung Jawab Kegiatan</label>
                    <select name="penanggung_jawab_id" required data-placeholder="Cari Penanggung Jawab..." class="js-searchable w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white">
                        <option value="" disabled {{ old('penanggung_jawab_id', $agenda->penanggung_jawab_id ?? '') ? '' : 'selected' }}>— Pilih —</option>
                        @foreach ($pjList as $p)
                            <option value="{{ $p->id }}" {{ old('penanggung_jawab_id', $agenda->penanggung_jawab_id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->nama_gelar ?? $p->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- SECTION ANGGARAN & ADMINISTRASI --}}
        <div class="mt-8 pt-6 border-t border-gray-100">
            <button type="button" id="anggaran-toggle" aria-expanded="true" aria-controls="anggaran-body" class="group w-full flex items-center gap-3 text-left rounded-lg -mx-1 px-1 py-1 hover:bg-gray-50/70 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 transition">
                <span class="h-9 w-9 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
                <span class="flex-1 min-w-0">
                    <span class="block text-sm font-bold text-gray-700">Anggaran &amp; Administrasi</span>
                    <span class="block text-xs text-gray-400 truncate">Dipakai bersama untuk memorandum PNS &amp; Non-PNS, cukup diisi sekali.</span>
                </span>
                <svg id="anggaran-chevron" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 flex-shrink-0 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div id="anggaran-body" class="overflow-hidden transition-all duration-300 ease-out">
                <div class="pt-5 space-y-5">
                    @include('agendas.partials.administrasi-anggaran', ['agenda' => $agenda ?? null])
                </div>
            </div>
        </div>
    </div>
</div>