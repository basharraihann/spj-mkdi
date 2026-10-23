<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                    <a href="{{ route('surat-masuk.index') }}" class="hover:text-blue-600 transition">Surat Masuk</a>
                    <span>/</span>
                    <span class="text-slate-800 font-medium">Detail Surat</span>
                </div>
                <h2 class="font-bold text-2xl text-slate-800 leading-tight">
                    {{ $suratMasuk->nomor_agenda }}
                </h2>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('surat-masuk.index') }}"
                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali</span>
                </a>

                @if(auth()->user()?->isTu() || auth()->user()?->isAdmin() || in_array(auth()->user()->role ?? '', ['admin', 'tu', 'tu-d2', 'admin-d2'], true))
                    <a href="{{ route('surat-masuk.edit', $suratMasuk->id) }}"
                        class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-xl transition flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Edit Surat</span>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Grid Informasi Surat + Embedded Preview --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Kolom Kiri: Detail Informasi --}}
            <div class="lg:col-span-1 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Status Disposisi</span>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                        {{ ucfirst($suratMasuk->status ?? 'baru') }}
                    </span>
                </div>

                <div class="space-y-4 text-sm">
                    <div>
                        <label class="text-xs font-medium text-slate-400 block mb-1">Perihal Surat</label>
                        <p class="font-semibold text-slate-800 leading-relaxed">{{ $suratMasuk->perihal }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                        <div>
                            <label class="text-xs font-medium text-slate-400 block mb-1">Nomor Surat</label>
                            <p class="font-medium text-slate-700 font-mono text-xs">{{ $suratMasuk->nomor_surat }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-400 block mb-1">Asal Surat</label>
                            <p class="font-medium text-slate-700">{{ $suratMasuk->asal_surat }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                        <div>
                            <label class="text-xs font-medium text-slate-400 block mb-1">Tanggal Surat</label>
                            <p class="font-medium text-slate-700">{{ $suratMasuk->tanggal_surat ? $suratMasuk->tanggal_surat->format('d M Y') : '-' }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-400 block mb-1">Tanggal Diterima</label>
                            <p class="font-medium text-slate-700">{{ $suratMasuk->tanggal_diterima ? $suratMasuk->tanggal_diterima->format('d M Y') : '-' }}</p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <label class="text-xs font-medium text-slate-400 block mb-1">Dicatat Oleh</label>
                        <p class="font-medium text-slate-700">{{ $suratMasuk->createdBy->name ?? 'Staf TU Persuratan' }}</p>
                    </div>

                    @if($suratMasuk->drive_link)
                        <div class="pt-3">
                            <a href="{{ $suratMasuk->drive_link }}" target="_blank"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-medium rounded-xl transition shadow-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                <span>Buka Dokumen Asli (Google Drive)</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Kolom Kanan: Preview Dokumen Google Drive --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-col h-[520px]">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3 px-2">
                    <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Preview Berkas Surat</span>
                    </h3>
                    <span class="text-xs text-slate-400">PDF / Dokumen Digital</span>
                </div>

                <div class="flex-1 w-full bg-slate-100 rounded-xl overflow-hidden border border-slate-200">
                    @if($suratMasuk->embed_drive_url)
                        <iframe src="{{ $suratMasuk->embed_drive_url }}" class="w-full h-full border-0" allow="autoplay"></iframe>
                    @else
                        <div class="h-full flex flex-col items-center justify-center text-slate-400 space-y-2 p-6 text-center">
                            <svg class="w-12 h-12 stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-sm">Tidak ada lampiran dokumen digital untuk surat ini.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Bagian Riwayat Rantai Disposisi --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-800 mb-6 flex items-center gap-2 border-b border-slate-100 pb-3">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8" />
                </svg>
                <span>Riwayat Rantai Disposisi Digital</span>
            </h3>

            @if($suratMasuk->disposisi->isEmpty())
                <p class="text-sm text-slate-400 py-4 text-center">Belum ada rantai disposisi yang dibuat untuk surat ini.</p>
            @else
                <div class="relative pl-6 border-l-2 border-blue-100 space-y-6">
                    @foreach($suratMasuk->disposisi as $dispo)
                        <div class="relative group">
                            {{-- Dot Timeline --}}
                            <div class="absolute -left-[31px] top-1 w-4 h-4 rounded-full bg-blue-600 border-4 border-white shadow-sm"></div>

                            <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/80 space-y-2">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                    <div class="text-sm font-semibold text-slate-800">
                                        <span class="text-blue-600">{{ $dispo->pengirim->name ?? 'Pengirim' }}</span>
                                        <span class="text-slate-400 mx-1.5">➔</span>
                                        <span class="text-slate-900">{{ $dispo->penerima->name ?? 'Penerima' }}</span>
                                    </div>
                                    <span class="text-xs text-slate-400 font-mono">
                                        {{ $dispo->created_at ? $dispo->created_at->format('d M Y, H:i') : '-' }} WIB
                                    </span>
                                </div>

                                <p class="text-sm text-slate-600 bg-white p-3 rounded-lg border border-slate-100 italic">
                                    "{{ $dispo->instruksi }}"
                                </p>

                                <div class="flex items-center justify-between text-xs text-slate-500 pt-1">
                                    <div class="flex items-center gap-2">
                                        <span class="capitalize font-medium">Sifat: <strong class="text-slate-700">{{ $dispo->sifat }}</strong></span>
                                        @if($dispo->perlu_st)
                                            <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-semibold">Perlu ST</span>
                                        @endif
                                    </div>
                                    <span class="capitalize px-2 py-0.5 rounded text-[11px] font-medium 
                                        {{ $dispo->status === 'selesai' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                        Status: {{ $dispo->status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-app-layout>