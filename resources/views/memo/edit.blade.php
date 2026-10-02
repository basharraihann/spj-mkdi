<x-app-layout title="Edit Memo">
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

    <div class="py-8 bg-gradient-to-b from-gray-50/70 to-white min-h-full">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

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

            <div
                class="flex gap-2.5 bg-blue-50/70 border border-blue-100 text-blue-700 text-xs sm:text-sm px-4 py-3 rounded-xl mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <p>Nomor memo di sini gak nempel ke agenda perjalanan dinas manapun — cocok buat keperluan lain yang
                    tetap butuh nomor urut memo resmi.</p>
            </div>

            @php
                $ppkList = $pegawaiList->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'PPK'))->values();
                $bendaharaList = $pegawaiList->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'Bendahara'))->values();
                $pjList = $pegawaiList->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'Penanggung Jawab Kegiatan'))->values();
                $petugasVerifikasiList = $pegawaiList->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'Petugas Verifikasi'))->values();

                $makOptions = $makOptions ?? collect();
                $currentMak = old('mak', $memo->mak);
                $makAdaDiDaftar = $makOptions->contains('mak', $currentMak);
                $tampilkanManual = $makOptions->isEmpty() || ($currentMak && !$makAdaDiDaftar);

                $currentJenis = old('jenis_memo', $memo->jenis_memo);
            @endphp

            <form action="{{ route('memo.update', $memo->id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- ===== Section: Informasi Memo ===== --}}
                <div
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 sm:p-7 lg:p-9">
                    <div class="flex items-center gap-2.5 mb-6">
                        <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Informasi Memo</h3>
                            <p class="text-xs text-gray-400">Nomor, jenis, tanggal, dan uraian kegiatan</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <!-- Nomor Memo -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">
                                Nomor Memo <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="nomor_memo" value="{{ old('nomor_memo', $memo->nomor_memo) }}"
                                required placeholder="325/LS.D1.PPK/KU.00/09/2026"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-mono tracking-tight focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Jenis Memo -->
                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">Jenis
                                    Memo <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <select name="jenis_memo" required
                                        class="w-full appearance-none bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                        @foreach (\App\Models\MemoEntry::JENIS as $value => $label)
                                            <option value="{{ $value }}" @selected($currentJenis === $value)>
                                                {{ $label }}
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

                            <!-- Tanggal -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">
                                    Tanggal Memo <span class="text-red-400">*</span>
                                </label>
                                <input type="date" name="tanggal_memo"
                                    value="{{ old('tanggal_memo', optional($memo->tanggal_memo)->format('Y-m-d')) }}"
                                    required
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            </div>
                        </div>

                        <!-- Uraian -->
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">Nama/Uraian
                                Kegiatan <span class="text-red-400">*</span></label>
                            <input type="text" name="uraian_kegiatan"
                                value="{{ old('uraian_kegiatan', $memo->uraian_kegiatan) }}" required
                                placeholder="Keperluan memo ini"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition placeholder:text-gray-300">
                        </div>
                    </div>
                </div>

                {{-- ===== Section: PIC & Penanggung Jawab ===== --}}
                <div
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 sm:p-7 lg:p-9">
                    <div class="flex items-center gap-2.5 mb-6">
                        <div class="h-8 w-8 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 100-8 4 4 0 000 8zm6 3c0-1.657-3.134-3-7-3s-7 1.343-7 3v2h14v-2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">PIC &amp; Penanggung Jawab</h3>
                            <p class="text-xs text-gray-400">Pegawai yang bertanggung jawab atas memo ini</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <!-- PIC -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">PIC
                                <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <select name="pic_id" required data-placeholder="Cari nama pegawai..."
                                    class="js-searchable w-full appearance-none bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
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

                        <div class="h-px bg-gray-50"></div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1.5">PPK <span
                                        class="text-red-400">*</span></label>
                                <div class="relative">
                                    <select name="ppk_id" required data-placeholder="Cari PPK..."
                                        class="js-searchable w-full appearance-none bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
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
                                    <select name="bendahara_id" required data-placeholder="Cari Bendahara..."
                                        class="js-searchable w-full appearance-none bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
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
                                        data-placeholder="Cari Penanggung Jawab..."
                                        class="js-searchable w-full appearance-none bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
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
                                        data-placeholder="Cari Petugas Verifikasi..."
                                        class="js-searchable w-full appearance-none bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
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
                </div>

                {{-- ===== Section: Anggaran ===== --}}
                <div
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 sm:p-7 lg:p-9">
                    <div class="flex items-center gap-2.5 mb-6">
                        <div class="h-8 w-8 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Anggaran</h3>
                            <p class="text-xs text-gray-400">MAK dan nominal yang diajukan</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <!-- MAK -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">MAK
                                <span class="text-red-400">*</span></label>

                            @if ($makOptions->isNotEmpty())
                                <select id="mak-select" data-placeholder="Cari MAK..."
                                    class="js-searchable w-full appearance-none border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition bg-white mb-2">
                                    <option value="">— Pilih dari daftar MAK —</option>
                                    @foreach ($makOptions as $opt)
                                        <option value="{{ $opt->mak }}" data-uraian-giat="{{ $opt->uraian_giat }}"
                                            data-uraian-komponen="{{ $opt->uraian_komponen }}"
                                            data-uraian-akun-ap="{{ $opt->uraian_akun_ap }}"
                                            data-uraian-belanja="{{ $opt->uraian_belanja }}" @selected($currentMak === $opt->mak)>
                                            {{ $opt->mak }} — {{ $opt->uraian_belanja }}
                                        </option>
                                    @endforeach
                                    <option value="__manual__" @selected($tampilkanManual && $currentMak)>
                                        Ketik manual (belum ada di daftar)
                                    </option>
                                </select>
                            @else
                                <p
                                    class="text-xs text-amber-600 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2 mb-2">
                                    Belum ada master MAK — isi manual di bawah.
                                </p>
                            @endif

                            <input type="text" id="mak-input" name="mak" value="{{ $currentMak }}" required
                                placeholder="7458.ABR.006.075.EE.524119"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-mono focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition placeholder:text-gray-300 {{ $tampilkanManual ? '' : 'hidden' }}">
                            <p class="text-xs text-gray-400 mt-1.5">
                                Format: KodeGiat.KodeKomponen.KodeAkun.KodeBelanja dipisah titik jadi 6 bagian. Kode di
                                bawah ini otomatis terisi dari MAK.
                            </p>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3">
                                <div class="bg-gray-50/60 rounded-lg p-2.5">
                                    <label class="block text-[11px] font-medium text-gray-400 mb-1">Kode Giat</label>
                                    <input type="text" id="preview-kode-giat" readonly tabindex="-1"
                                        class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-xs bg-white text-gray-600">
                                    <p id="preview-uraian-giat" class="text-[11px] text-gray-400 mt-1 leading-snug"></p>
                                </div>
                                <div class="bg-gray-50/60 rounded-lg p-2.5">
                                    <label class="block text-[11px] font-medium text-gray-400 mb-1">Kode
                                        Komponen</label>
                                    <input type="text" id="preview-kode-komponen" readonly tabindex="-1"
                                        class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-xs bg-white text-gray-600">
                                    <p id="preview-uraian-komponen" class="text-[11px] text-gray-400 mt-1 leading-snug">
                                    </p>
                                </div>
                                <div class="bg-gray-50/60 rounded-lg p-2.5">
                                    <label class="block text-[11px] font-medium text-gray-400 mb-1">Kode Akun
                                        (AP)</label>
                                    <input type="text" id="preview-kode-akun-ap" readonly tabindex="-1"
                                        class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-xs bg-white text-gray-600">
                                    <p id="preview-uraian-akun-ap" class="text-[11px] text-gray-400 mt-1 leading-snug">
                                    </p>
                                </div>
                                <div class="bg-gray-50/60 rounded-lg p-2.5">
                                    <label class="block text-[11px] font-medium text-gray-400 mb-1">Kode Belanja</label>
                                    <input type="text" id="preview-kode-belanja" readonly tabindex="-1"
                                        class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-xs bg-white text-gray-600">
                                    <p id="preview-uraian-belanja" class="text-[11px] text-gray-400 mt-1 leading-snug">
                                    </p>
                                </div>
                            </div>

                            @error('mak')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nominal -->
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">Nominal
                                <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span
                                    class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400 pointer-events-none">Rp</span>
                                <input type="text" inputmode="numeric" name="nominal"
                                    value="{{ old('nominal', $memo->nominal) }}" required placeholder="0"
                                    id="nominal-input"
                                    class="w-full border border-gray-200 rounded-xl pl-9 pr-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition placeholder:text-gray-300">
                            </div>
                            @error('nominal')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div
                    class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 bg-white rounded-2xl border border-gray-100 shadow-sm px-5 sm:px-7 py-4">
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

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinning {
            animation: spin 0.8s linear infinite;
        }

        /* Matiin panah bawaan browser di semua <select>, biar cuma panah custom (svg) yang tampil */
        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: none !important;
        }

        select::-ms-expand {
            display: none;
        }
    </style>

    <script>
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

        (function () {
            const nominalInput = document.getElementById('nominal-input');
            if (!nominalInput) return;

            function formatRibuan(angka) {
                const bersih = angka.replace(/\D/g, '');
                return bersih.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            if (nominalInput.value) {
                nominalInput.value = formatRibuan(nominalInput.value);
            }

            nominalInput.addEventListener('input', function () {
                const posDariBelakang = this.value.length - this.selectionStart;
                this.value = formatRibuan(this.value);
                const posBaru = this.value.length - posDariBelakang;
                this.setSelectionRange(posBaru, posBaru);
            });

            const form = nominalInput.closest('form');
            if (form) {
                form.addEventListener('submit', function () {
                    nominalInput.value = nominalInput.value.replace(/\./g, '');
                });
            }
        })();

        // ===== Searchable select: ubah <select> jadi input yang bisa diketik untuk filter opsi =====
        (function () {
            function enhance(select) {
                if (!select || select.dataset.enhanced) return;
                select.dataset.enhanced = '1';

                const container = select.parentElement;
                const options = Array.from(select.options).filter(function (o) { return o.value !== ''; });

                const input = document.createElement('input');
                input.type = 'text';
                input.autocomplete = 'off';
                input.placeholder = select.dataset.placeholder || 'Cari & pilih...';
                input.className = select.className;
                if (select.hasAttribute('required')) input.setAttribute('required', 'required');

                const list = document.createElement('ul');
                list.className = 'absolute z-20 top-full left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg py-1 text-sm hidden';

                let highlighted = -1;

                function itemEls() {
                    return Array.from(list.children).filter(function (li) { return li.dataset.value !== undefined; });
                }

                function renderList(filterText) {
                    const f = (filterText || '').toLowerCase();
                    list.innerHTML = '';
                    highlighted = -1;
                    const filtered = options.filter(function (o) {
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
                            e.preventDefault();
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

                input.addEventListener('focus', function () {
                    input.select();
                    openList();
                });

                input.addEventListener('input', function () {
                    openList();
                });

                input.addEventListener('blur', function () {
                    setTimeout(function () {
                        const current = select.selectedOptions[0];
                        input.value = (current && current.value !== '') ? current.textContent.trim() : '';
                        closeList();
                    }, 120);
                });

                input.addEventListener('keydown', function (e) {
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
                            const opt = options.find(function (o) { return o.value === val; });
                            if (opt) pick(opt);
                        }
                    } else if (e.key === 'Escape') {
                        closeList();
                    }
                });

                document.addEventListener('click', function (e) {
                    if (!container.contains(e.target)) closeList();
                });

                const initial = select.selectedOptions[0];
                if (initial && initial.value !== '') input.value = initial.textContent.trim();

                const wrapper = document.createElement('div');
                wrapper.className = 'relative';
                container.insertBefore(wrapper, select);
                wrapper.appendChild(input);
                wrapper.appendChild(list);
                select.classList.add('hidden');
                wrapper.appendChild(select);
            }

            document.querySelectorAll('select.js-searchable').forEach(enhance);
        })();
    </script>
</x-app-layout>