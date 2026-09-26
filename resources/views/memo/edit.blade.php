<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-600/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Nomor Memo</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div
                    class="flex gap-2.5 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl mb-5 animate-[fadeIn_.2s_ease-out]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $ppkList = $pegawaiList->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'PPK'))->values();
                $bendaharaList = $pegawaiList->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'Bendahara'))->values();
                $pjList = $pegawaiList->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'Penanggung Jawab Kegiatan'))->values();
                $petugasVerifikasiList = $pegawaiList->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'Petugas Verifikasi'))->values();

                $makOptions = $makOptions ?? collect();
                $currentMak = old('mak', $memo->mak);
                $makAdaDiDaftar = $makOptions->contains('mak', $currentMak);
                $tampilkanManual = $makOptions->isEmpty() || ($currentMak && !$makAdaDiDaftar);
            @endphp

            <form action="{{ route('memo.update', $memo->id) }}" method="POST"
                class="bg-white shadow-sm hover:shadow-md rounded-2xl border border-gray-100 p-5 sm:p-8 space-y-6 transition-shadow duration-300">
                @csrf
                @method('PUT')

                <!-- Nomor Memo -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">
                        Nomor Memo <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="nomor_memo" value="{{ old('nomor_memo', $memo->nomor_memo) }}" required
                        placeholder="325/LS.D1.PPK/KU.00/09/2026"
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-mono tracking-tight focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                </div>

                <!-- Jenis Memo -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">Jenis
                        Memo <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <select name="jenis_memo" required
                            class="w-full appearance-none bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            <option value="konsumsi" @selected(old('jenis_memo', $memo->jenis_memo) === 'konsumsi')>Konsumsi</option>
                            <option value="honorarium" @selected(old('jenis_memo', $memo->jenis_memo) === 'honorarium')>Honorarium</option>
                        </select>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- Tanggal -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">
                        Tanggal Memo <span class="text-red-400">*</span>
                    </label>
                    <input type="date" name="tanggal_memo"
                        value="{{ old('tanggal_memo', optional($memo->tanggal_memo)->format('Y-m-d')) }}" required
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                </div>

                <!-- Uraian -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">Nama/Uraian
                        Kegiatan <span class="text-red-400">*</span></label>
                    <input type="text" name="uraian_kegiatan" value="{{ old('uraian_kegiatan', $memo->uraian_kegiatan) }}"
                        required placeholder="Keperluan memo ini"
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition placeholder:text-gray-300">
                </div>

                <!-- PIC -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">PIC <span
                            class="text-red-400">*</span></label>
                    <div class="relative">
                        <select name="pic_id" required
                            class="w-full appearance-none bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            @foreach ($pegawaiList as $p)
                                <option value="{{ $p->id }}" @selected(old('pic_id', $memo->pic_id) == $p->id)>
                                    {{ $p->nama_gelar ?? $p->nama }}
                                </option>
                            @endforeach
                        </select>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- Penanggung Jawab -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">Penanggung
                        Jawab</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">PPK <span
                                    class="text-red-400">*</span></label>
                            <div class="relative">
                                <select name="ppk_id" required
                                    class="w-full appearance-none bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                    @forelse ($ppkList as $p)
                                        <option value="{{ $p->id }}" @selected(old('ppk_id', $memo->ppk_id) == $p->id)>
                                            {{ $p->nama_gelar ?? $p->nama }}
                                        </option>
                                    @empty
                                        <option value="" disabled>Belum ada pegawai berlabel PPK</option>
                                    @endforelse
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Bendahara <span
                                    class="text-red-400">*</span></label>
                            <div class="relative">
                                <select name="bendahara_id" required
                                    class="w-full appearance-none bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                    @forelse ($bendaharaList as $p)
                                        <option value="{{ $p->id }}" @selected(old('bendahara_id', $memo->bendahara_id) == $p->id)>
                                            {{ $p->nama_gelar ?? $p->nama }}
                                        </option>
                                    @empty
                                        <option value="" disabled>Belum ada pegawai berlabel Bendahara</option>
                                    @endforelse
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Penanggung Jawab Kegiatan <span
                                    class="text-red-400">*</span></label>
                            <div class="relative">
                                <select name="penanggung_jawab_id" required
                                    class="w-full appearance-none bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                    @forelse ($pjList as $p)
                                        <option value="{{ $p->id }}" @selected(old('penanggung_jawab_id', $memo->penanggung_jawab_id) == $p->id)>
                                            {{ $p->nama_gelar ?? $p->nama }}
                                        </option>
                                    @empty
                                        <option value="" disabled>Belum ada pegawai berlabel ini</option>
                                    @endforelse
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Petugas Verifikasi <span
                                    class="text-red-400">*</span></label>
                            <div class="relative">
                                <select name="petugas_verifikasi_id" required
                                    class="w-full appearance-none bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                    @forelse ($petugasVerifikasiList as $p)
                                        <option value="{{ $p->id }}" @selected(old('petugas_verifikasi_id', $memo->petugas_verifikasi_id) == $p->id)>
                                            {{ $p->nama_gelar ?? $p->nama }}
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
                        </div>
                    </div>
                </div>

                <!-- MAK -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">MAK
                        <span class="text-red-400">*</span></label>

                    @if ($makOptions->isNotEmpty())
                        <select id="mak-select"
                            class="w-full appearance-none border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition bg-white mb-2">
                            <option value="">— Pilih dari daftar MAK —</option>
                            @foreach ($makOptions as $opt)
                                <option value="{{ $opt->mak }}"
                                    data-uraian-giat="{{ $opt->uraian_giat }}"
                                    data-uraian-komponen="{{ $opt->uraian_komponen }}"
                                    data-uraian-akun-ap="{{ $opt->uraian_akun_ap }}"
                                    data-uraian-belanja="{{ $opt->uraian_belanja }}"
                                    @selected($currentMak === $opt->mak)>
                                    {{ $opt->mak }} — {{ $opt->uraian_belanja }}
                                </option>
                            @endforeach
                            <option value="__manual__" @selected($tampilkanManual && $currentMak)>
                                Ketik manual (belum ada di daftar)
                            </option>
                        </select>
                    @else
                        <p class="text-xs text-amber-600 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2 mb-2">
                            Belum ada master MAK — isi manual di bawah.
                        </p>
                    @endif

                    <input type="text" id="mak-input" name="mak" value="{{ $currentMak }}" required
                        placeholder="7458.ABR.006.075.EE.524119"
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-mono focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition placeholder:text-gray-300 {{ $tampilkanManual ? '' : 'hidden' }}">
                    <p class="text-xs text-gray-400 mt-1.5">
                        Format: KodeGiat.KodeKomponen.KodeAkun.KodeBelanja dipisah titik jadi 6 bagian. Kode di bawah
                        ini otomatis terisi dari MAK.
                    </p>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3">
                        <div>
                            <label class="block text-[11px] text-gray-400 mb-1">Kode Giat</label>
                            <input type="text" id="preview-kode-giat" readonly tabindex="-1"
                                class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-xs bg-gray-50 text-gray-500">
                            <p id="preview-uraian-giat" class="text-[11px] text-gray-400 mt-1 leading-snug"></p>
                        </div>
                        <div>
                            <label class="block text-[11px] text-gray-400 mb-1">Kode Komponen</label>
                            <input type="text" id="preview-kode-komponen" readonly tabindex="-1"
                                class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-xs bg-gray-50 text-gray-500">
                            <p id="preview-uraian-komponen" class="text-[11px] text-gray-400 mt-1 leading-snug"></p>
                        </div>
                        <div>
                            <label class="block text-[11px] text-gray-400 mb-1">Kode Akun (AP)</label>
                            <input type="text" id="preview-kode-akun-ap" readonly tabindex="-1"
                                class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-xs bg-gray-50 text-gray-500">
                            <p id="preview-uraian-akun-ap" class="text-[11px] text-gray-400 mt-1 leading-snug"></p>
                        </div>
                        <div>
                            <label class="block text-[11px] text-gray-400 mb-1">Kode Belanja</label>
                            <input type="text" id="preview-kode-belanja" readonly tabindex="-1"
                                class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-xs bg-gray-50 text-gray-500">
                            <p id="preview-uraian-belanja" class="text-[11px] text-gray-400 mt-1 leading-snug"></p>
                        </div>
                    </div>

                    @error('mak')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nominal -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">Nominal
                        <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <span
                            class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400 pointer-events-none">Rp</span>
                        <input type="number" name="nominal" value="{{ old('nominal', $memo->nominal) }}" min="0" required
                            placeholder="0"
                            class="w-full border border-gray-200 rounded-xl pl-9 pr-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition placeholder:text-gray-300">
                    </div>
                </div>

                <!-- Keterangan -->
                <div>
                    <label
                        class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">Keterangan</label>
                    <textarea name="keterangan" rows="3"
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition resize-none">{{ old('keterangan', $memo->keterangan) }}</textarea>
                </div>

                <!-- Actions -->
                <div
                    class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 pt-2 border-t border-gray-50 pt-5">
                    <a href="{{ route('memo.index') }}"
                        class="w-full sm:w-auto text-center text-sm text-gray-400 hover:text-gray-600 transition">
                        &larr; Batal
                    </a>
                    <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-semibold px-5 py-2.5 rounded-xl text-sm shadow-md shadow-blue-600/20 transition-all">
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

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        // Pecah MAK jadi 4 preview kode (readonly, gak disubmit)
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
            splitMak();
        })();

        // Dropdown pilih MAK dari master -> sinkron ke input MAK + preview uraian
        (function () {
            const makSelect = document.getElementById('mak-select');
            const makInput = document.getElementById('mak-input');
            if (!makSelect || !makInput) return;

            const uraianGiat = document.getElementById('preview-uraian-giat');
            const uraianKomponen = document.getElementById('preview-uraian-komponen');
            const uraianAkunAp = document.getElementById('preview-uraian-akun-ap');
            const uraianBelanja = document.getElementById('preview-uraian-belanja');

            function isiUraian(opt) {
                uraianGiat.textContent = opt?.dataset.uraianGiat || '';
                uraianKomponen.textContent = opt?.dataset.uraianKomponen || '';
                uraianAkunAp.textContent = opt?.dataset.uraianAkunAp || '';
                uraianBelanja.textContent = opt?.dataset.uraianBelanja || '';
            }

            makSelect.addEventListener('change', function () {
                if (makSelect.value === '__manual__') {
                    makInput.classList.remove('hidden');
                    makInput.value = '';
                    makInput.focus();
                    makInput.dispatchEvent(new Event('input'));
                    isiUraian(null);
                    return;
                }

                makInput.classList.add('hidden');
                makInput.value = makSelect.value;
                makInput.dispatchEvent(new Event('input'));
                isiUraian(makSelect.selectedOptions[0]);
            });

            isiUraian(makSelect.selectedOptions[0]);
        })();
    </script>
</x-app-layout>
