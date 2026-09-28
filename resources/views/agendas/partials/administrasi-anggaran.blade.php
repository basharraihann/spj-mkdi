@php
    $agenda = $agenda ?? null;

    // Sama seperti PPK / Bendahara / Penanggung Jawab Kegiatan di form utama —
    // dicocokkan pakai substring karena role_penandatangan bisa berisi lebih
    // dari satu label (mis. "PPK, Petugas Verifikasi").
    $petugasVerifikasiList = ($pegawaiList ?? collect())
        ->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'Petugas Verifikasi'))
        ->values();
@endphp

<div>
    <div class="space-y-4">
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">MAK <span
                    class="text-red-400">*</span></label>

            @php
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
                $currentLabel = $currentOpt
                    ? \Illuminate\Support\Str::afterLast($currentOpt->mak, '.') . ' — ' . $currentOpt->uraian_belanja
                    : ($tampilkanManual && $currentMak ? 'Ketik manual (belum ada di daftar)' : '');
            @endphp

            @if ($makOptions->isNotEmpty())
                {{-- Select asli disembunyikan (TANPA js-searchable). Tetap dipakai sebagai
                sumber nilai supaya skrip sinkron MAK / auto-isi uraian di bawah tetap jalan. --}}
                <select id="mak-select" class="hidden" tabindex="-1" aria-hidden="true">
                    <option value="" data-label="" {{ !$currentMak ? 'selected' : '' }}>— Pilih dari daftar MAK —
                    </option>
                    @foreach ($makGroups as $payung => $items)
                        @foreach ($items as $opt)
                            <option value="{{ $opt->mak }}"
                                data-label="{{ \Illuminate\Support\Str::afterLast($opt->mak, '.') }} — {{ $opt->uraian_belanja }}"
                                data-uraian-giat="{{ $opt->uraian_giat }}" data-uraian-komponen="{{ $opt->uraian_komponen }}"
                                data-uraian-akun-ap="{{ $opt->uraian_akun_ap }}" data-uraian-belanja="{{ $opt->uraian_belanja }}"
                                @selected($currentMak === $opt->mak)>
                                {{ $opt->mak }}
                            </option>
                        @endforeach
                    @endforeach
                    <option value="__manual__" data-label="Ketik manual (belum ada di daftar)" @selected($tampilkanManual && $currentMak)>
                        Ketik manual (belum ada di daftar)
                    </option>
                </select>

                {{-- Dropdown MAK buatan sendiri: header ringkas per payung + item yang dijorok --}}
                <div id="mak-combo" class="relative mb-2">
                    <input type="text" id="mak-search" autocomplete="off" placeholder="Cari MAK..."
                        value="{{ $currentLabel }}"
                        class="w-full border border-gray-200 rounded-lg pl-3.5 pr-10 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white placeholder:text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>

                    <div id="mak-panel"
                        class="hidden absolute z-30 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-96 overflow-y-auto">
                        @foreach ($makGroups as $payung => $items)
                            @php $first = $items->first(); @endphp
                            <div class="mak-group">
                                {{-- Header ringkas: abu-abu muda, teks kecil, satu baris per uraian --}}
                                <div class="sticky top-0 z-10 px-3.5 py-1.5 bg-gray-50 border-y border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="font-mono text-[11px] font-semibold text-indigo-700 bg-white border border-gray-200 rounded px-1.5 py-0.5">{{ $payung }}</span>
                                        <span class="text-[11px] text-gray-400">{{ $items->count() }} akun belanja</span>
                                    </div>
                                    <div class="text-xs font-semibold text-gray-700 mt-1 truncate"
                                        title="{{ $first->uraian_giat }}">{{ $first->uraian_giat }}</div>
                                    <div class="text-[11px] text-gray-500 truncate" title="{{ $first->uraian_komponen }}">
                                        {{ $first->uraian_komponen }}</div>
                                </div>

                                {{-- Item dijorok + garis vertikal tipis supaya kelihatan anak dari header --}}
                                <div class="ml-4 my-1 border-l border-gray-200">
                                    @foreach ($items as $opt)
                                        {{-- border-l-2 transparan + -ml-px: menimpa garis vertikal grup;
                                        berubah biru kalau item ini yang terpilih. --}}
                                        <button type="button" data-mak="{{ $opt->mak }}"
                                            data-search="{{ mb_strtolower($opt->mak . ' ' . $opt->uraian_belanja . ' ' . $opt->uraian_giat . ' ' . $opt->uraian_komponen) }}"
                                            class="mak-item w-full text-left flex items-baseline gap-3 -ml-px pl-4 pr-3.5 py-1.5 text-sm text-gray-700 border-l-2 border-transparent hover:bg-blue-50 transition">
                                            <span
                                                class="font-mono text-xs text-gray-600 w-14 flex-shrink-0">{{ \Illuminate\Support\Str::afterLast($opt->mak, '.') }}</span>
                                            <span class="flex-1">{{ $opt->uraian_belanja }}</span>
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="mak-check hidden h-4 w-4 text-blue-600 flex-shrink-0 self-center" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <p id="mak-empty" class="hidden px-3.5 py-3 text-sm text-gray-400">MAK tidak ditemukan.</p>

                        <button type="button" data-mak="__manual__" data-search="ketik manual"
                            class="mak-item w-full text-left px-3.5 py-2 text-sm text-blue-600 font-medium border-t border-gray-100 hover:bg-blue-50 transition">
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
                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm font-mono focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300 {{ $tampilkanManual ? '' : 'hidden' }}">
            <p class="text-xs text-gray-400 mt-1">
                Format: KodeGiat.KodeKomponen.KodeAkun.KodeBelanja dipisah titik jadi 6 bagian. Kode di bawah ini
                otomatis terisi dari MAK.
            </p>
            @error('mak')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Kode Giat</label>
                <input type="text" id="preview-kode-giat" readonly tabindex="-1" value="{{ $agenda->kode_giat ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm bg-gray-50 text-gray-500">
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Uraian Giat <span
                        class="text-red-400">*</span></label>
                <input type="text" name="uraian_giat" value="{{ old('uraian_giat', $agenda->uraian_giat ?? null) }}"
                    required
                    placeholder="Rekomendasi Kebijakan Program Prioritas Nasional Bidang Tata Niaga dan Distribusi Pangan"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
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
            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Uraian Komponen <span
                        class="text-red-400">*</span></label>
                <input type="text" name="uraian_komponen"
                    value="{{ old('uraian_komponen', $agenda->uraian_komponen ?? null) }}" required
                    placeholder="Sinkronisasi Kebijakan Bidang Koordinasi Tata Niaga dan Distribusi Pangan"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
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
            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Uraian Akun (AP) <span
                        class="text-red-400">*</span></label>
                <input type="text" name="uraian_akun_ap"
                    value="{{ old('uraian_akun_ap', $agenda->uraian_akun_ap ?? null) }}" required
                    placeholder="Alokasi Perjalanan Dinas Pimpinan"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
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
            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Uraian Belanja <span
                        class="text-red-400">*</span></label>
                <input type="text" name="uraian_belanja"
                    value="{{ old('uraian_belanja', $agenda->uraian_belanja ?? null) }}" required
                    placeholder="Belanja Perjalanan Dinas Biasa"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
                @error('uraian_belanja')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Petugas Verifikasi <span
                        class="text-red-400">*</span></label>
                <div class="relative">
                    <select name="petugas_verifikasi_id" required data-placeholder="Cari Petugas Verifikasi..."
                        class="js-searchable w-full appearance-none bg-none border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white">
                        <option value="" disabled {{ old('petugas_verifikasi_id', $agenda->petugas_verifikasi_id ?? null) ? '' : 'selected' }}>— Pilih —</option>
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
        </div>

        <div class="h-px bg-gray-100"></div>

        <div>
            <h5 class="text-xs font-semibold text-gray-500 mb-1">Memorandum</h5>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div id="nomor-memo-pns-wrap">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Nomor Memo (PNS) <span
                            class="text-red-400">*</span></label>
                    <div class="flex gap-2">
                        <input type="text" id="nomor-memo-pns-input" name="nomor_memo_pns"
                            value="{{ old('nomor_memo_pns', $agenda->nomor_memo_pns ?? null) }}"
                            placeholder="380.KU.00.00/2026"
                            class="flex-1 min-w-0 border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
                        <button type="button" id="btn-ambil-memo-pns"
                            class="flex-shrink-0 inline-flex items-center gap-1 border border-blue-200 text-blue-600 hover:bg-blue-50 font-semibold px-3 py-2.5 rounded-lg text-xs transition whitespace-nowrap">
                            Ambil Nomor
                        </button>
                    </div>

                    @error('nomor_memo_pns')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div id="nomor-memo-non-pns-wrap">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Nomor Memo (Non PNS) <span
                            class="text-red-400">*</span></label>
                    <div class="flex gap-2">
                        <input type="text" id="nomor-memo-non-pns-input" name="nomor_memo_non_pns"
                            value="{{ old('nomor_memo_non_pns', $agenda->nomor_memo_non_pns ?? null) }}"
                            placeholder="324/LS.D1.PPK/KU.00/07/2026"
                            class="flex-1 min-w-0 border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
                        <button type="button" id="btn-ambil-memo-non-pns"
                            class="flex-shrink-0 inline-flex items-center gap-1 border border-blue-200 text-blue-600 hover:bg-blue-50 font-semibold px-3 py-2.5 rounded-lg text-xs transition whitespace-nowrap">
                            Ambil Nomor
                        </button>
                    </div>
                    @error('nomor_memo_non_pns')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- PIC: siapa yang mengambil nomor memo di atas, ditaruh setelah field
            nomor memo biar konteksnya jelas nyambung. --}}
            <div class="mt-4">
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                    PIC <span class="text-gray-400 font-normal">(yang mengambil nomor memo ini)</span>
                    <span class="text-red-400">*</span>
                </label>
                <div class="relative sm:w-1/2">
                    <select name="pic_id" required data-placeholder="Cari nama pegawai..."
                        class="js-searchable w-full appearance-none bg-none border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white">
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
    </div>
</div>

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

    // Dropdown MAK buatan sendiri (header per payung + daftar kode belanja).
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

        // Tandai item terpilih: latar biru muda, teks tebal, garis biru di kiri, dan centang di kanan.
        // Tombol "Ketik manual" tidak punya .mak-check, jadi cuma kena latar + tebal.
        function tandaiTerpilih() {
            items.forEach(function (el) {
                const aktif = el.dataset.mak === select.value && select.value !== '';
                const cek = el.querySelector('.mak-check');

                el.classList.toggle('bg-blue-50', aktif);
                el.classList.toggle('font-semibold', aktif);

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

    // Preview Uraian Memo PNS/Non-PNS, disusun otomatis dari "Uraian Kegiatan".
    // Bukan input terpisah — nilai final juga dihitung ulang di server pas simpan,
    // preview ini cuma biar user tau isinya bakal kayak apa.
    (function () {
        const uraianKegiatanInput = document.querySelector('input[name="uraian_kegiatan"]');
        const previewPns = document.getElementById('uraian-memo-pns-preview');
        const previewNonPns = document.getElementById('uraian-memo-non-pns-preview');
        if (!uraianKegiatanInput || !previewPns || !previewNonPns) return;

        function updateUraianMemoPreview() {
            const uraian = uraianKegiatanInput.value.trim();
            previewPns.textContent = uraian
                ? 'Sehubungan dengan Perjalanan ASN ' + uraian
                : 'Sehubungan dengan Perjalanan ASN …';
            previewNonPns.textContent = uraian
                ? 'Sehubungan dengan Perjalanan NON ASN ' + uraian
                : 'Sehubungan dengan Perjalanan NON ASN …';
        }

        uraianKegiatanInput.addEventListener('input', updateUraianMemoPreview);
        updateUraianMemoPreview();
    })();

    // Tombol "Ambil Nomor" PNS/Non-PNS. Nomor beneran "kepakai" cuma pas agenda
    // ini disimpan, jadi sebelum submit kita hitung sendiri di client: klik
    // pertama ambil dari server, klik berikutnya (di form yg sama) tinggal +1
    // dari yang sudah diambil — biar PNS & Non-PNS gak kebagian nomor yang sama.
    (function () {
        const btnPns = document.getElementById('btn-ambil-memo-pns');
        const btnNonPns = document.getElementById('btn-ambil-memo-non-pns');
        const inputPns = document.getElementById('nomor-memo-pns-input');
        const inputNonPns = document.getElementById('nomor-memo-non-pns-input');
        if (!btnPns && !btnNonPns) return;

        let sesiUrutan = null;
        let sesiEkor = '';

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
            const teksAsli = btn.textContent;
            btn.disabled = true;
            btn.textContent = '...';
            try {
                const { urutan, ekor } = await ambilNomorBerikutnya();
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