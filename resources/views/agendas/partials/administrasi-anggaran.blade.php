@php
    $agenda = $agenda ?? null;
    $makOutstanding = $makOutstanding ?? [];
    $makOutstandingDetail = $makOutstandingDetail ?? [];
    $kebutuhan = (int) ($totalBiaya ?? 0); // total biaya agenda, pembanding sisa MAK

    $fmtRp = fn($n) => ($n < 0 ? '-Rp ' : 'Rp ') . number_format(abs((int) $n), 0, ',', '.');

    // Sama seperti PPK / Bendahara / Penanggung Jawab Kegiatan di form utama —
    // dicocokkan pakai substring karena role_penandatangan bisa berisi lebih
    // dari satu label (mis. "PPK, Petugas Verifikasi").
    $petugasVerifikasiList = ($pegawaiList ?? collect())
        ->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'Petugas Verifikasi'))
        ->values();

    // Nomor memo cuma relevan untuk status peserta yang ada di agenda ini.
    $statusPeserta = $agenda ? $agenda->pegawai->map(fn($p) => $p->status_kepegawaian ?? 'PNS') : collect();
    $adaPns = $statusPeserta->isEmpty() || $statusPeserta->contains('PNS');
    $adaNonPns = $statusPeserta->isEmpty() || $statusPeserta->contains('Non PNS');

    $makOptions = $makOptions ?? collect();
    $currentMak = old('mak', $agenda->mak ?? null);
    $makAdaDiDaftar = $makOptions->contains('mak', $currentMak);
    // Manual input kelihatan default kalau: gak ada master MAK sama sekali,
    // ATAU mak yang lagi tersimpan itu gak ketemu di daftar (misal diisi
    // manual sebelumnya / belum sempat ditambahin ke master).
    $tampilkanManual = $makOptions->isEmpty() || ($currentMak && !$makAdaDiDaftar);

    // "Payung" = MAK tanpa kode belanja (segmen ke-6).
    // Contoh: 7458.ABR.006.075.EE.524111 -> payung 7458.ABR.006.075.EE
    $makGroups = $makOptions
        ->sortBy('mak')
        ->groupBy(fn($o) => \Illuminate\Support\Str::beforeLast($o->mak, '.'));

    $currentOpt = $makOptions->firstWhere('mak', $currentMak);
    // Label di kolom pencarian = MAK lengkap + uraian belanja
    $currentLabel = $currentOpt
        ? $currentOpt->mak . ' — ' . $currentOpt->uraian_belanja
        : ($tampilkanManual && $currentMak ? 'Ketik manual (belum ada di daftar)' : '');

    $inputCls = 'w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300 bg-white';
    $selectCls = 'js-searchable w-full appearance-none bg-none border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white';
@endphp

{{-- ===================== KARTU 1 — PILIH MAK ===================== --}}
<section class="bg-white shadow-sm rounded-2xl border border-gray-100">
    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </span>
        <div class="min-w-0">
            <h3 class="text-sm font-semibold text-gray-800">Pilih Anggaran (MAK)</h3>
            <p class="text-xs text-gray-400">Sisa anggaran dibandingkan langsung dengan total biaya agenda.</p>
        </div>
    </div>

    <div class="p-5">
        <label class="block text-xs font-semibold text-gray-500 mb-1.5">MAK <span class="text-red-400">*</span></label>

        @if ($makOptions->isNotEmpty())
            {{-- Select asli disembunyikan (TANPA js-searchable). Tetap dipakai sebagai
            sumber nilai supaya skrip sinkron MAK / auto-isi uraian di bawah tetap jalan. --}}
            <select id="mak-select" class="hidden" tabindex="-1" aria-hidden="true">
                <option value="" data-label="" {{ !$currentMak ? 'selected' : '' }}>— Pilih dari daftar MAK —</option>
                @foreach ($makGroups as $payung => $items)
                    @foreach ($items as $opt)
                        <option value="{{ $opt->mak }}" data-label="{{ $opt->mak }} — {{ $opt->uraian_belanja }}"
                            data-uraian-giat="{{ $opt->uraian_giat }}" data-uraian-komponen="{{ $opt->uraian_komponen }}"
                            data-uraian-akun-ap="{{ $opt->uraian_akun_ap }}" data-uraian-belanja="{{ $opt->uraian_belanja }}"
                            data-pagu="{{ (int) $opt->pagu }}" data-realisasi="{{ (int) $opt->realisasi }}"
                            data-sisa="{{ (int) $opt->sisa }}" data-outstanding="{{ $makOutstanding[$opt->mak] ?? 0 }}"
                            data-outstanding-sendiri="{{ (int) ($makOutstandingDetail[$opt->mak]['sendiri'] ?? 0) }}"
                            data-outstanding-lain="{{ (int) ($makOutstandingDetail[$opt->mak]['lain'] ?? 0) }}"
                            data-periode="{{ $opt->periode }}"
                            data-per-tanggal="{{ $opt->per_tanggal ? \Carbon\Carbon::parse($opt->per_tanggal)->translatedFormat('d M Y') : '' }}"
                            @selected($currentMak === $opt->mak)>
                            {{ $opt->mak }}
                        </option>
                    @endforeach
                @endforeach
                <option value="__manual__" data-label="Ketik manual (belum ada di daftar)" @selected($tampilkanManual && $currentMak)>
                    Ketik manual (belum ada di daftar)
                </option>
            </select>

            {{-- Dropdown MAK buatan sendiri: header per payung + item dengan MAK lengkap & sisa anggaran --}}
            <div id="mak-combo" class="relative">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                </svg>
                <input type="text" id="mak-search" autocomplete="off" placeholder="Cari MAK atau uraian belanja..."
                    value="{{ $currentLabel }}"
                    class="w-full border border-gray-200 rounded-lg pl-10 pr-10 py-2.5 text-sm font-medium text-gray-800 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white placeholder:text-gray-400 placeholder:font-normal">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>

                <div id="mak-panel"
                    class="hidden absolute z-30 left-0 right-0 mt-1.5 bg-white border border-gray-200 rounded-xl shadow-xl max-h-80 sm:max-h-[26rem] overflow-y-auto">
                    @foreach ($makGroups as $payung => $items)
                        @php $first = $items->first(); @endphp
                        <div class="mak-group">
                            {{-- Header grup --}}
                            <div class="sticky top-0 z-10 px-4 py-2 bg-gray-50/95 backdrop-blur border-y border-gray-100">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <span
                                        class="font-mono text-[11px] font-semibold text-indigo-700 bg-white border border-gray-200 rounded px-1.5 py-0.5 break-all">{{ $payung }}</span>
                                    <span class="text-[11px] text-gray-400">{{ $items->count() }} akun belanja</span>
                                </div>
                                <div class="text-xs font-semibold text-gray-700 mt-1 leading-snug">
                                    {{ $first->uraian_giat }}
                                </div>
                                <div class="text-[11px] text-gray-500 leading-snug">{{ $first->uraian_komponen }}
                                </div>
                            </div>

                            <div class="ml-3 sm:ml-4 my-1 border-l border-gray-200">
                                @foreach ($items as $opt)
                                    @php
                                        $efektif = (int) $opt->sisa - ($makOutstanding[$opt->mak] ?? 0);
                                        $kurang = $kebutuhan > 0 && $efektif < $kebutuhan;
                                    @endphp
                                    {{-- border-l-2 transparan + -ml-px: menimpa garis vertikal grup;
                                    berubah biru kalau item ini yang terpilih. --}}
                                    <button type="button" data-mak="{{ $opt->mak }}" data-efektif="{{ $efektif }}"
                                        data-search="{{ mb_strtolower($opt->mak . ' ' . $opt->uraian_belanja . ' ' . $opt->uraian_giat . ' ' . $opt->uraian_komponen) }}"
                                        class="mak-item w-full text-left flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-4 -ml-px pl-4 pr-3.5 py-2.5 border-l-2 border-transparent hover:bg-blue-50 transition">
                                        <span class="flex-1 min-w-0">
                                            {{-- MAK lengkap: payung diredupkan, kode belanja ditebalkan --}}
                                            <span class="block font-mono text-[11px] leading-tight break-all"><span
                                                    class="text-gray-400">{{ $payung }}.</span><span
                                                    class="font-semibold text-gray-800">{{ \Illuminate\Support\Str::afterLast($opt->mak, '.') }}</span></span>
                                            <span
                                                class="block text-sm text-gray-700 mt-0.5 leading-snug">{{ $opt->uraian_belanja }}</span>
                                        </span>

                                        <span class="flex items-center justify-between sm:justify-end gap-3 flex-shrink-0">
                                            <span class="sm:text-right">
                                                <span
                                                    class="block text-[10px] uppercase tracking-wide text-gray-400 leading-none">Sisa
                                                    tersedia</span>
                                                <span
                                                    class="block mt-1 text-sm font-semibold tabular-nums leading-none {{ $kurang ? 'text-red-600' : 'text-emerald-600' }}">{{ $fmtRp($efektif) }}</span>
                                            </span>
                                            @if ($kurang)
                                                <span
                                                    class="mak-kurang rounded-full bg-red-50 border border-red-100 text-red-600 px-2 py-0.5 text-[10px] font-semibold whitespace-nowrap">Tidak
                                                    cukup</span>
                                            @endif
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="mak-check hidden h-4 w-4 text-blue-600 flex-shrink-0" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <p id="mak-empty" class="hidden px-4 py-3 text-sm text-gray-400">MAK tidak ditemukan.</p>

                    <button type="button" data-mak="__manual__" data-search="ketik manual"
                        class="mak-item w-full text-left px-4 py-2.5 text-sm text-blue-600 font-medium border-t border-gray-100 hover:bg-blue-50 transition">
                        + Ketik manual (belum ada di daftar)
                    </button>
                </div>
            </div>
        @else
            <p class="text-xs text-amber-600 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2 mb-2">
                Belum ada master MAK — isi manual dulu di bawah.
            </p>
        @endif

        <input type="text" id="mak-input" name="mak" value="{{ $currentMak }}" required
            placeholder="7458.ABR.006.075.EE.524119"
            class="mt-2 w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm font-mono focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300 {{ $tampilkanManual ? '' : 'hidden' }}">
        <p class="text-xs text-gray-400 mt-1.5">
            Format: KodeGiat.KodeKomponen.KodeAkun.KodeBelanja dipisah titik jadi 6 bagian. Kode di bawah otomatis
            terisi dari MAK.
        </p>
        @error('mak')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror

        {{-- Kartu sisa anggaran MAK + perbandingan dengan total biaya agenda.
        Hanya tampil di halaman yang mengirim $totalBiaya (step 3: agendas.anggaran). --}}
        @includeWhen(isset($totalBiaya), 'agendas.partials.sisa-mak-card')
    </div>
</section>

{{-- ===================== KARTU 2 — KLASIFIKASI ===================== --}}
<section class="bg-white shadow-sm rounded-2xl border border-gray-100">
    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
        </span>
        <div class="min-w-0">
            <h3 class="text-sm font-semibold text-gray-800">Klasifikasi Anggaran</h3>
            <p class="text-xs text-gray-400">Terisi otomatis dari MAK yang dipilih, tetap bisa disesuaikan.</p>
        </div>
    </div>

    <div class="p-5">
        <div class="ag-klas">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Kode Giat</label>
                <input type="text" id="preview-kode-giat" readonly tabindex="-1" value="{{ $agenda->kode_giat ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm bg-gray-50 text-gray-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Uraian Giat <span
                        class="text-red-400">*</span></label>
                <input type="text" name="uraian_giat" value="{{ old('uraian_giat', $agenda->uraian_giat ?? null) }}"
                    required
                    placeholder="Rekomendasi Kebijakan Program Prioritas Nasional Bidang Tata Niaga dan Distribusi Pangan"
                    class="{{ $inputCls }}">
                @error('uraian_giat')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Kode Komponen</label>
                <input type="text" id="preview-kode-komponen" readonly tabindex="-1"
                    value="{{ $agenda->kode_komponen ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm bg-gray-50 text-gray-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Uraian Komponen <span
                        class="text-red-400">*</span></label>
                <input type="text" name="uraian_komponen"
                    value="{{ old('uraian_komponen', $agenda->uraian_komponen ?? null) }}" required
                    placeholder="Sinkronisasi Kebijakan Bidang Koordinasi Tata Niaga dan Distribusi Pangan"
                    class="{{ $inputCls }}">
                @error('uraian_komponen')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Kode Akun (AP)</label>
                <input type="text" id="preview-kode-akun-ap" readonly tabindex="-1"
                    value="{{ $agenda->kode_akun_ap ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm bg-gray-50 text-gray-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Uraian Akun (AP) <span
                        class="text-red-400">*</span></label>
                <input type="text" name="uraian_akun_ap"
                    value="{{ old('uraian_akun_ap', $agenda->uraian_akun_ap ?? null) }}" required
                    placeholder="Alokasi Perjalanan Dinas Pimpinan" class="{{ $inputCls }}">
                @error('uraian_akun_ap')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Kode Belanja</label>
                <input type="text" id="preview-kode-belanja" readonly tabindex="-1"
                    value="{{ $agenda->kode_belanja ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm bg-gray-50 text-gray-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Uraian Belanja <span
                        class="text-red-400">*</span></label>
                <input type="text" name="uraian_belanja"
                    value="{{ old('uraian_belanja', $agenda->uraian_belanja ?? null) }}" required
                    placeholder="Belanja Perjalanan Dinas Biasa" class="{{ $inputCls }}">
                @error('uraian_belanja')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</section>

{{-- ===================== KARTU 3 — MEMORANDUM & PENANGGUNG JAWAB ===================== --}}
<section class="bg-white shadow-sm rounded-2xl border border-gray-100">
    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </span>
        <div class="min-w-0">
            <h3 class="text-sm font-semibold text-gray-800">Memorandum & Penanggung Jawab</h3>
            <p class="text-xs text-gray-400">Nomor memo dan petugas yang terlibat pada memorandum.</p>
        </div>
    </div>

    <div class="p-5 space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Petugas Verifikasi <span
                        class="text-red-400">*</span></label>
                <div class="relative">
                    <select name="petugas_verifikasi_id" required data-placeholder="Cari Petugas Verifikasi..."
                        class="{{ $selectCls }}">
                        <option value="" disabled {{ old('petugas_verifikasi_id', $agenda->petugas_verifikasi_id ?? null) ? '' : 'selected' }}>
                            — Pilih —</option>
                        @forelse ($petugasVerifikasiList as $pv)
                            <option value="{{ $pv->id }}" @selected(old('petugas_verifikasi_id', $agenda->petugas_verifikasi_id ?? null) == $pv->id)>
                                {{ $pv->nama_gelar ?? $pv->nama }}
                            </option>
                        @empty
                            <option value="" disabled>Belum ada pegawai berlabel Petugas Verifikasi</option>
                        @endforelse
                    </select>
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                @error('petugas_verifikasi_id')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                    PIC <span class="text-gray-400 font-normal">(yang mengambil nomor memo)</span>
                    <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <select name="pic_id" required data-placeholder="Cari nama pegawai..." class="{{ $selectCls }}">
                        <option value="" disabled {{ old('pic_id', $agenda->pic_id ?? null) ? '' : 'selected' }}>—
                            Pilih —</option>
                        @forelse (($pegawaiList ?? collect()) as $p)
                            <option value="{{ $p->id }}" @selected(old('pic_id', $agenda->pic_id ?? null) == $p->id)>
                                {{ $p->nama_gelar ?? $p->nama }}
                            </option>
                        @empty
                            <option value="" disabled>Belum ada data pegawai</option>
                        @endforelse
                    </select>
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                @error('pic_id')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="h-px bg-gray-100"></div>

        <div>
            <h4 class="text-xs font-semibold text-gray-500 mb-1">Nomor Memorandum</h4>
            @unless ($adaPns && $adaNonPns)
                <p class="text-[11px] text-gray-400 mb-3">
                    Agenda ini hanya berisi peserta {{ $adaPns ? 'PNS' : 'Non PNS' }}, jadi hanya perlu satu nomor memo.
                </p>
            @endunless

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 {{ $adaPns && $adaNonPns ? 'mt-2' : '' }}">
                @if ($adaPns)
                    <div id="nomor-memo-pns-wrap">
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Nomor Memo (PNS) <span
                                class="text-red-400">*</span></label>
                        <div class="flex gap-2">
                            <input type="text" id="nomor-memo-pns-input" name="nomor_memo_pns"
                                value="{{ old('nomor_memo_pns', $agenda->nomor_memo_pns ?? null) }}"
                                placeholder="380.KU.00.00/2026" class="flex-1 min-w-0 {{ $inputCls }}">
                            <button type="button" id="btn-ambil-memo-pns"
                                class="flex-shrink-0 inline-flex items-center gap-1 border border-blue-200 text-blue-600 hover:bg-blue-50 font-semibold px-3 py-2.5 rounded-lg text-xs transition whitespace-nowrap">
                                Ambil Nomor
                            </button>
                        </div>
                        @error('nomor_memo_pns')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if ($adaNonPns)
                    <div id="nomor-memo-non-pns-wrap">
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Nomor Memo (Non PNS) <span
                                class="text-red-400">*</span></label>
                        <div class="flex gap-2">
                            <input type="text" id="nomor-memo-non-pns-input" name="nomor_memo_non_pns"
                                value="{{ old('nomor_memo_non_pns', $agenda->nomor_memo_non_pns ?? null) }}"
                                placeholder="324/LS.D1.PPK/KU.00/07/2026" class="flex-1 min-w-0 {{ $inputCls }}">
                            <button type="button" id="btn-ambil-memo-non-pns"
                                class="flex-shrink-0 inline-flex items-center gap-1 border border-blue-200 text-blue-600 hover:bg-blue-50 font-semibold px-3 py-2.5 rounded-lg text-xs transition whitespace-nowrap">
                                Ambil Nomor
                            </button>
                        </div>
                        @error('nomor_memo_non_pns')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<script>
    // Pecah MAK jadi 4 preview kode (readonly, gak disubmit — kode aslinya
    // diturunkan ulang di server dari field "mak" pas divalidasi)
    (function () {
        const makInput = document.getElementById('mak-input');
        if (!makInput) return;

        const previewGiat = document.getElementById('preview-kode-giat');
        const previewKomponen = document.getElementById('preview-kode-komponen');
        const previewAkun = document.getElementById('preview-kode-akun-ap');
        const previewBelanja = document.getElementById('preview-kode-belanja');

        function splitMak() {
            const parts = makInput.value.split('.');
            if (parts.length === 6) {
                previewGiat.value = parts.slice(0, 3).join('.');
                previewKomponen.value = parts[3];
                previewAkun.value = parts[4];
                previewBelanja.value = parts[5];
            } else {
                previewGiat.value = '';
                previewKomponen.value = '';
                previewAkun.value = '';
                previewBelanja.value = '';
            }
        }

        makInput.addEventListener('input', splitMak);
        splitMak(); // isi preview saat halaman pertama kali dibuka (mode edit)
    })();

    // Dropdown pilih MAK dari master (kalau ada) -> sinkron ke input MAK yang
    // sebenarnya disubmit, sekalian auto-isi 4 field uraian.
    (function () {
        const makSelect = document.getElementById('mak-select');
        const makInput = document.getElementById('mak-input');
        if (!makSelect || !makInput) return;

        const uraianGiat = document.querySelector('input[name="uraian_giat"]');
        const uraianKomponen = document.querySelector('input[name="uraian_komponen"]');
        const uraianAkunAp = document.querySelector('input[name="uraian_akun_ap"]');
        const uraianBelanja = document.querySelector('input[name="uraian_belanja"]');

        makSelect.addEventListener('change', function () {
            if (makSelect.value === '__manual__') {
                makInput.classList.remove('hidden');
                makInput.value = '';
                makInput.focus();
                makInput.dispatchEvent(new Event('input')); // reset preview kode
                return;
            }

            makInput.classList.add('hidden');
            makInput.value = makSelect.value;
            makInput.dispatchEvent(new Event('input')); // update preview kode giat/komponen/akun/belanja

            const opt = makSelect.selectedOptions[0];
            if (!opt || !opt.value) return;

            if (uraianGiat) uraianGiat.value = opt.dataset.uraianGiat || '';
            if (uraianKomponen) uraianKomponen.value = opt.dataset.uraianKomponen || '';
            if (uraianAkunAp) uraianAkunAp.value = opt.dataset.uraianAkunAp || '';
            if (uraianBelanja) uraianBelanja.value = opt.dataset.uraianBelanja || '';
        });
    })();

    // Dropdown MAK buatan sendiri (header per payung + daftar MAK lengkap).
    // Cuma UI: pilihan diteruskan ke <select id="mak-select"> yang disembunyikan,
    // lalu event "change"-nya ditangani skrip sinkron di atas.
    (function () {
        const combo = document.getElementById('mak-combo');
        const select = document.getElementById('mak-select');
        if (!combo || !select) return;

        const search = document.getElementById('mak-search');
        const panel = document.getElementById('mak-panel');
        const empty = document.getElementById('mak-empty');
        const items = Array.from(panel.querySelectorAll('.mak-item'));
        const groups = Array.from(panel.querySelectorAll('.mak-group'));

        function labelTerpilih() {
            const opt = select.selectedOptions[0];
            return opt ? (opt.dataset.label || '') : '';
        }

        // Tandai item terpilih: latar biru muda, garis biru di kiri, dan centang di kanan.
        // Tombol "Ketik manual" tidak punya .mak-check, jadi cuma kena latar.
        function tandaiTerpilih() {
            items.forEach(function (el) {
                const aktif = el.dataset.mak === select.value && select.value !== '';
                const cek = el.querySelector('.mak-check');

                el.classList.toggle('bg-blue-50', aktif);

                if (cek) {
                    cek.classList.toggle('hidden', !aktif);
                    el.classList.toggle('border-blue-500', aktif);
                    el.classList.toggle('border-transparent', !aktif);
                }
            });
        }

        function filter(q) {
            q = q.trim().toLowerCase();
            let adaHasil = false;

            groups.forEach(function (g) {
                let adaDiGrup = false;
                g.querySelectorAll('.mak-item').forEach(function (el) {
                    const cocok = !q || el.dataset.search.includes(q);
                    el.classList.toggle('hidden', !cocok);
                    if (cocok) adaDiGrup = true;
                });
                g.classList.toggle('hidden', !adaDiGrup);
                if (adaDiGrup) adaHasil = true;
            });

            empty.classList.toggle('hidden', adaHasil);
        }

        function buka() {
            filter('');
            tandaiTerpilih();
            panel.classList.remove('hidden');
        }

        function tutup() {
            panel.classList.add('hidden');
            search.value = labelTerpilih(); // kembalikan teks kalau user batal milih
        }

        search.addEventListener('focus', function () {
            search.select();
            buka();
        });
        search.addEventListener('click', function () {
            if (panel.classList.contains('hidden')) buka();
        });
        search.addEventListener('input', function () {
            panel.classList.remove('hidden');
            filter(search.value);
        });
        search.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                tutup();
                search.blur();
            }
        });

        items.forEach(function (el) {
            // mousedown supaya kepilih sebelum input kehilangan fokus
            el.addEventListener('mousedown', function (e) {
                e.preventDefault();
                select.value = el.dataset.mak;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                panel.classList.add('hidden');
                search.value = labelTerpilih();
                search.blur();
            });
        });

        document.addEventListener('mousedown', function (e) {
            if (!combo.contains(e.target) && !panel.classList.contains('hidden')) tutup();
        });
    })();

    // Tombol "Ambil Nomor" PNS/Non-PNS. Nomor beneran "kepakai" cuma pas anggaran
    // ini disimpan, jadi sebelum submit kita hitung sendiri di client: klik
    // pertama ambil dari server, klik berikutnya (di form yg sama) tinggal +1
    // dari yang sudah diambil — biar PNS & Non-PNS gak kebagian nomor yang sama.
    (function () {
        const btnPns = document.getElementById('btn-ambil-memo-pns');
        const btnNonPns = document.getElementById('btn-ambil-memo-non-pns');
        const inputPns = document.getElementById('nomor-memo-pns-input');
        const inputNonPns = document.getElementById('nomor-memo-non-pns-input');
        if (!btnPns && !btnNonPns) return;

        let sesiUrutan = null;   // urutan tertinggi yang sudah dibagikan di form ini
        let sesiEkor = '';
        const nomorPerInput = new Map(); // input -> urutan miliknya

        async function ambilNomorBerikutnya() {
            if (sesiUrutan !== null) {
                sesiUrutan += 1;
                return { urutan: sesiUrutan, ekor: sesiEkor };
            }
            const res = await fetch('{{ route('memo.nomor-berikutnya') }}');
            const data = await res.json();
            sesiUrutan = data.urutan;
            sesiEkor = data.ekor;
            return data;
        }

        async function isiInput(input, btn) {
            if (!input || !btn) return;

            // Kolom ini sudah punya nomor: pakai lagi, jangan naikkan counter
            if (nomorPerInput.has(input)) {
                input.value = nomorPerInput.get(input) + sesiEkor;
                input.dispatchEvent(new Event('input'));
                return;
            }

            const teksAsli = btn.textContent;
            btn.disabled = true;
            btn.textContent = '...';
            try {
                const { urutan, ekor } = await ambilNomorBerikutnya();
                nomorPerInput.set(input, urutan);
                input.value = urutan + ekor;
                input.dispatchEvent(new Event('input'));
            } catch (e) {
                alert('Gagal mengambil nomor memo. Coba lagi.');
            } finally {
                btn.disabled = false;
                btn.textContent = teksAsli;
            }
        }

        if (btnPns) btnPns.addEventListener('click', () => isiInput(inputPns, btnPns));
        if (btnNonPns) btnNonPns.addEventListener('click', () => isiInput(inputNonPns, btnNonPns));
    })();
</script>