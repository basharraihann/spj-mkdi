<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="font-semibold text-lg sm:text-xl text-slate-800 leading-tight">
                {{ __('Detail & Disposisi Surat') }}
            </h2>
            <a href="{{ route('disposisi.index') }}"
                class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition w-fit">
                <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali ke Daftar Disposisi</span>
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6 max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

        {{-- Pesan Flash Success / Error --}}
        @if (session('success'))
            <div class="p-3.5 sm:p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
                <svg class="h-4 w-4 text-emerald-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 sm:p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs font-medium flex items-center gap-2">
                <svg class="h-4 w-4 text-red-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- ===== 1. Detail Surat & Preview Berkas Dual Mode ===== --}}
        <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-6">
            
            {{-- Info Ringkasan Surat Masuk --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 bg-slate-50/80 rounded-xl border border-slate-100 text-xs">
                <div class="space-y-1">
                    <span class="text-slate-400 font-medium block">Nomor Surat Masuk</span>
                    <span class="font-semibold text-slate-800 break-words block">{{ $disposisi->suratMasuk->nomor_surat ?? '-' }}</span>
                </div>
                <div class="space-y-1">
                    <span class="text-slate-400 font-medium block">Perihal</span>
                    <span class="font-semibold text-blue-600 break-words block leading-snug">{{ $disposisi->suratMasuk->perihal ?? '-' }}</span>
                </div>
                <div class="space-y-1">
                    <span class="text-slate-400 font-medium block">Asal Surat / Pengirim</span>
                    <span class="font-medium text-slate-700 break-words block">{{ $disposisi->suratMasuk->asal_surat ?? '-' }}</span>
                </div>
                <div class="space-y-1">
                    <span class="text-slate-400 font-medium block">Nomor Agenda</span>
                    <span class="font-mono font-bold text-slate-900 break-words block">{{ $disposisi->suratMasuk->nomor_agenda ?? '-' }}</span>
                </div>
            </div>

            {{-- Component Tab Switcher (Card Preview vs Interactive Viewer) --}}
            @if (!empty($disposisi->suratMasuk->drive_link))
                @php
                    $driveLink = $disposisi->suratMasuk->drive_link;
                    
                    preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $driveLink, $matches);
                    $fileId = $matches[1] ?? null;

                    $embedUrl = $driveLink;
                    if (str_contains($embedUrl, 'drive.google.com') && !str_contains($embedUrl, '/preview')) {
                        $embedUrl = preg_replace('/\/view.*/', '/preview', $embedUrl);
                    }

                    $thumbnailUrl = $fileId 
                        ? "https://drive.google.com/thumbnail?id={$fileId}&sz=w1000" 
                        : null;
                @endphp

                <div x-data="{ tab: 'interactive' }" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    
                    {{-- Header Tab Switcher Presisi di Tengah --}}
                    <div class="flex items-center justify-center border-b border-slate-100 bg-slate-50/50 p-2.5">
                        <div class="grid grid-cols-2 gap-1 w-full sm:w-auto rounded-xl bg-slate-200/60 p-1">
                            {{-- Tab 1: Card Preview --}}
                            <button type="button" @click="tab = 'card'"
                                :class="tab === 'card' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-700'"
                                class="flex items-center justify-center gap-1.5 rounded-lg px-4 py-1.5 text-xs font-semibold transition">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                </svg>
                                <span>Card Preview</span>
                            </button>

                            {{-- Tab 2: Interactive Viewer --}}
                            <button type="button" @click="tab = 'interactive'"
                                :class="tab === 'interactive' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-700'"
                                class="flex items-center justify-center gap-1.5 rounded-lg px-4 py-1.5 text-xs font-semibold transition">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <span>Interactive Viewer</span>
                            </button>
                        </div>
                    </div>

                    {{-- Isi Konten Tab --}}
                    <div class="p-4 sm:p-6">
                        
                        {{-- KONTEN TAB 1: CARD PREVIEW --}}
                        <div x-show="tab === 'card'" x-cloak class="max-w-md mx-auto">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm space-y-4">
                                <div class="relative aspect-4/3 rounded-xl overflow-hidden bg-slate-100 border border-slate-100 flex items-center justify-center">
                                    @if ($thumbnailUrl)
                                        <img src="{{ $thumbnailUrl }}" alt="Preview Dokumen" class="w-full h-full object-cover">
                                    @else
                                        <div class="text-center p-4">
                                            <svg class="h-12 w-12 text-slate-300 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3h7.5M6 20.25h12A2.25 2.25 0 0020.25 18V6.75A2.25 2.25 0 0018 4.5H6A2.25 2.25 0 003.75 6.75v11.25A2.25 2.25 0 006 20.25z" />
                                            </svg>
                                            <span class="text-xs text-slate-400">Preview Berkas PDF</span>
                                        </div>
                                    @endif

                                    <span class="absolute top-3 left-3 bg-red-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-md flex items-center gap-1">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg>
                                        PDF Document
                                    </span>
                                </div>

                                <div>
                                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-blue-600 uppercase tracking-wide">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM19 18H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3z"/></svg>
                                        <span>Google Drive File</span>
                                    </div>
                                    <h4 class="font-semibold text-sm text-slate-800 mt-0.5 truncate">
                                        Dokumen PDF - Google Drive ({{ $fileId ? Str::limit($fileId, 10) : 'Berkas' }})
                                    </h4>
                                </div>

                                <div class="grid grid-cols-2 gap-2 bg-slate-50/80 p-3 rounded-xl border border-slate-100 text-xs">
                                    <div>
                                        <span class="text-slate-400 block text-[10px]">Status Akses:</span>
                                        <span class="font-semibold text-emerald-600 flex items-center gap-1 mt-0.5">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                            Publik
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[10px]">ID File:</span>
                                        <span class="font-mono text-slate-600 truncate block mt-0.5">{{ $fileId ?: '-' }}</span>
                                    </div>
                                </div>

                                <a href="{{ $driveLink }}" target="_blank"
                                    class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                    </svg>
                                    <span>Buka di Google Drive</span>
                                </a>
                            </div>
                        </div>

                        {{-- KONTEN TAB 2: INTERACTIVE VIEWER --}}
                        <div x-show="tab === 'interactive'" x-cloak class="space-y-3">
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <svg class="h-4 w-4 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                                <span>Viewer interaktif langsung dari Google Drive untuk membaca, zoom, atau cetak dokumen.</span>
                            </div>

                            <div class="w-full h-[380px] sm:h-[580px] rounded-2xl overflow-hidden border border-slate-200 bg-slate-900 shadow-inner">
                                <iframe src="{{ $embedUrl }}" class="w-full h-full" frameborder="0" allow="autoplay"></iframe>
                            </div>
                        </div>

                    </div>
                </div>
            @endif
        </div>

        {{-- ===== 2. Pohon Hierarki Disposisi ===== --}}
        <div class="bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">POHON HIERARKI DISPOSISI</h3>

            <div class="space-y-4">
                @include('disposisi.partials.tree-item', ['parentId' => 0, 'byParent' => $byParent])
            </div>
        </div>

        {{-- ===== 3. Form Disposisi Lanjutan / Penunjukan Tugas Staf ===== --}}
        <div class="bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            
            @if ($isStaff)
                {{-- TAMPILAN KHUSUS LEVEL STAF / PELAKSANA (TIDAK BISA DISPOSISI) --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">PENUGASAN STAF / PELAKSANA</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Disposisi ini ditujukan untuk diselesaikan oleh Anda sebagai pelaksana tugas.</p>
                        </div>
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-blue-50 text-blue-700 border border-blue-200/60 uppercase">
                            Penunjukan Pelaksana
                        </span>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                        <span class="text-xs font-semibold text-slate-700 block">Instruksi Pimpinan:</span>
                        <p class="text-xs text-slate-800 italic bg-white p-3 rounded-lg border border-slate-200/60">
                            "{{ $disposisi->instruksi }}"
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                        <span class="text-xs text-slate-500">Tugas ini otomatis masuk ke menu <strong class="text-slate-700">Tugas Saya</strong>.</span>
                        
                        <a href="{{ url('/tugas-saya') }}" 
                            class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-md transition inline-flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Tindak Lanjut di Menu "Tugas Saya"</span>
                        </a>
                    </div>
                </div>

            @elseif ($bolehDisposisi && in_array($disposisi->status, ['baru', 'dibaca']))
                {{-- TAMPILAN FORM DISPOSISI KHUSUS PEJABAT (TU, DEPUTI, ASDEP) --}}
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4">FORM DISPOSISI LANJUTAN</h3>

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('disposisi.store', $disposisi->id) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">
                            Pilih Penerima Disposisi <span class="text-red-600">*</span>
                        </label>
                        <div class="max-h-52 overflow-y-auto rounded-xl border border-slate-200 p-3 space-y-2 bg-slate-50/50">
                            @forelse ($users as $u)
                                <label class="flex items-center gap-2 text-xs font-medium text-slate-700 cursor-pointer hover:text-blue-600">
                                    <input type="checkbox" name="penerima_ids[]" value="{{ $u->id }}"
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                        @checked(is_array(old('penerima_ids')) && in_array($u->id, old('penerima_ids')))>
                                    <span>{{ $u->name }}</span>
                                    @if ($u->pegawai?->jabatan)
                                        <span class="text-[11px] text-slate-400">({{ $u->pegawai->jabatan }})</span>
                                    @endif
                                </label>
                            @empty
                                <p class="text-xs text-slate-400">Tidak ada pengguna lain yang dapat menerima disposisi.</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Instruksi / Catatan Tugas <span class="text-red-600">*</span>
                        </label>
                        <textarea name="instruksi" rows="3" required placeholder="Tulis instruksi penugasan..."
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">{{ old('instruksi') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tenggat Waktu</label>
                            <input type="date" name="tenggat_waktu" value="{{ old('tenggat_waktu') }}"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Sifat <span class="text-red-600">*</span>
                            </label>
                            <select name="sifat" required class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                                <option value="biasa" @selected(old('sifat') === 'biasa')>Biasa</option>
                                <option value="segera" @selected(old('sifat') === 'segera')>Segera</option>
                                <option value="rahasia" @selected(old('sifat') === 'rahasia')>Rahasia</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="perlu_st" id="perlu_st" value="1"
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            @checked(old('perlu_st'))>
                        <label for="perlu_st" class="text-xs text-slate-700 font-medium cursor-pointer">
                            Perlu Penerbitan Surat Tugas (ST)
                        </label>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-md transition inline-flex items-center justify-center gap-2">
                            <span>Kirim Disposisi</span>
                            <svg class="h-3.5 w-3.5 text-blue-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </div>
                </form>
            @else
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 text-center font-medium flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 text-emerald-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span>Disposisi ini sudah Anda kirimkan/teruskan.</span>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>