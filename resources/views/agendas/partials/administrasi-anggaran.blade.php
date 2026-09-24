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
            @endphp

            @if ($makOptions->isNotEmpty())
                <select id="mak-select"
                    class="w-full appearance-none border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white mb-2">
                    <option value="" {{ !$currentMak ? 'selected' : '' }}>— Pilih dari daftar MAK —</option>
                    @foreach ($makOptions as $opt)
                        <option value="{{ $opt->mak }}" data-uraian-giat="{{ $opt->uraian_giat }}"
                            data-uraian-komponen="{{ $opt->uraian_komponen }}" data-uraian-akun-ap="{{ $opt->uraian_akun_ap }}"
                            data-uraian-belanja="{{ $opt->uraian_belanja }}" @selected($currentMak === $opt->mak)>
                            {{ $opt->mak }} — {{ $opt->uraian_belanja }}
                        </option>
                    @endforeach
                    <option value="__manual__" @selected($tampilkanManual && $currentMak)>
                        Ketik manual (belum ada di daftar)
                    </option>
                </select>
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
                    <select name="petugas_verifikasi_id" required
                        class="w-full appearance-none bg-none border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white">
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
                            placeholder="323/LS.D1.PPK/KU.00/07/2026"
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
                    <select name="pic_id" required
                        class="w-full appearance-none bg-none border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white">
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