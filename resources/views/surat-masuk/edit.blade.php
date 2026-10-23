<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                    <a href="{{ route('surat-masuk.index') }}" class="hover:text-blue-600 transition">Surat Masuk</a>
                    <span>/</span>
                    <a href="{{ route('surat-masuk.show', $suratMasuk->id) }}" class="hover:text-blue-600 transition">{{ $suratMasuk->nomor_agenda }}</a>
                    <span>/</span>
                    <span class="text-slate-800 font-medium">Edit Surat</span>
                </div>
                <h2 class="font-bold text-2xl text-slate-800 leading-tight">
                    {{ __('Edit Data Surat Masuk') }}
                </h2>
            </div>
            <a href="{{ route('surat-masuk.show', $suratMasuk->id) }}"
                class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Batal</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Display Validation Errors --}}
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs font-medium space-y-1">
                <p class="font-bold text-sm mb-1">Terjadi kesalahan validasi:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <form action="{{ route('surat-masuk.update', $suratMasuk->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Nomor Agenda --}}
                    <div>
                        <label for="nomor_agenda" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Nomor Agenda <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nomor_agenda" id="nomor_agenda" required
                            value="{{ old('nomor_agenda', $suratMasuk->nomor_agenda) }}"
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 font-mono">
                    </div>

                    {{-- Nomor Surat --}}
                    <div>
                        <label for="nomor_surat" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Nomor Surat Masuk <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nomor_surat" id="nomor_surat" required
                            value="{{ old('nomor_surat', $suratMasuk->nomor_surat) }}"
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Asal Surat --}}
                    <div>
                        <label for="asal_surat" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Asal Instansi / Pengirim <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="asal_surat" id="asal_surat" required
                            value="{{ old('asal_surat', $suratMasuk->asal_surat) }}"
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    {{-- Tanggal Surat & Tanggal Diterima --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="tanggal_surat" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Tgl Surat <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_surat" id="tanggal_surat" required
                                value="{{ old('tanggal_surat', $suratMasuk->tanggal_surat ? $suratMasuk->tanggal_surat->format('Y-m-d') : '') }}"
                                class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="tanggal_diterima" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Tgl Diterima <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_diterima" id="tanggal_diterima" required
                                value="{{ old('tanggal_diterima', $suratMasuk->tanggal_diterima ? $suratMasuk->tanggal_diterima->format('Y-m-d') : '') }}"
                                class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                {{-- Perihal Surat --}}
                <div>
                    <label for="perihal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Perihal Surat <span class="text-red-500">*</span>
                    </label>
                    <textarea name="perihal" id="perihal" rows="3" required
                        class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('perihal', $suratMasuk->perihal) }}</textarea>
                </div>

                {{-- Tautan Drive / File Digital --}}
                <div>
                    <label for="drive_link" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Tautan Google Drive / Berkas Digital
                    </label>
                    <input type="url" name="drive_link" id="drive_link"
                        value="{{ old('drive_link', $suratMasuk->drive_link) }}"
                        placeholder="https://drive.google.com/file/d/..."
                        class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-slate-400 mt-1">Masukkan tautan berkas dokumen di Google Drive untuk ditampilkan pada pratinjau digital.</p>
                </div>

                {{-- Submit Button --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('surat-masuk.show', $suratMasuk->id) }}"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>