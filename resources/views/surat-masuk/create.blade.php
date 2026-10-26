<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Input Surat Masuk Baru') }}
            </h2>
            <a href="{{ route('surat-masuk.index') }}"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Penampil Pesan Kesalahan Validasi --}}
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs font-medium space-y-1">
                <p class="font-bold text-sm mb-1">Gagal menyimpan data! Periksa kembali inputan berikut:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <form action="{{ route('surat-masuk.store') }}" method="POST" class="space-y-4">
                @csrf

                {{-- TUJUAN SURAT: otomatis sesuai unit TU yang login --}}
                <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-200/80 space-y-2">
                    @if (auth()->user()->unit_id)
                        <p class="text-sm font-bold text-blue-900">Surat akan diteruskan ke</p>
                        @if ($target)
                            <p class="text-sm text-slate-800">
                                <span class="font-semibold">{{ $target->name }}</span>
                                @if ($unitNama)
                                    <span class="block text-xs text-slate-500">{{ $unitNama }}</span>
                                @endif
                            </p>
                        @else
                            <p class="text-sm text-red-700">Belum ada pejabat terdaftar di unit Anda. Hubungi admin.</p>
                        @endif
                    @else
                        <label class="block text-sm font-bold text-blue-900">
                            Unit Tujuan <span class="text-red-600">*</span>
                        </label>
                        <select name="unit_id" required
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Pilih unit --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>
                                    {{ $unit->kode }} - {{ $unit->nama }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Nomor Agenda: dibuat server saat disimpan --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nomor Agenda / Disposisi</label>
                        <div
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-100/80 px-3 py-2 text-sm text-slate-500">
                            Dibuat otomatis saat disimpan
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Contoh format: AG-007/D2/X/2026</p>
                    </div>

                    {{-- Nomor Surat Fisik/Asal (Opsional) --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700">
                            Nomor Surat Physical/Asal <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                        </label>
                        <input type="text" name="nomor_surat" value="{{ old('nomor_surat') }}"
                            placeholder="Contoh: B-123/KEMENTAN/2026"
                            class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="text-[11px] text-slate-400 mt-1">Biarkan kosong jika surat fisik tidak memiliki nomor
                            resmi.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Asal Instansi / Pengirim <span
                                class="text-red-600">*</span></label>
                        <input type="text" name="asal_surat" value="{{ old('asal_surat') }}" required
                            placeholder="Contoh: Kementerian Pertanian"
                            class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tanggal Surat <span
                                class="text-red-600">*</span></label>
                        <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', date('Y-m-d')) }}"
                            required
                            class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Perihal / Ringkasan Isi Surat <span
                            class="text-red-600">*</span></label>
                    <textarea name="perihal" rows="3" required placeholder="Jelaskan ringkasan isi surat..."
                        class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('perihal') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Link Google Drive Berkas Surat
                        (PDF/Word/Excel/PPT)</label>
                    <input type="url" name="drive_link" value="{{ old('drive_link') }}"
                        placeholder="https://drive.google.com/file/d/..."
                        class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-slate-500 mt-1">Pastikan akses link Google Drive sudah diatur ke 'Anyone with
                        link / Siapa saja yang memiliki link'.</p>
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <a href="{{ route('surat-masuk.index') }}"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition">Batal</a>
                    <button type="submit" @disabled(auth()->user()->unit_id && !$target)
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium rounded-xl shadow-sm transition">
                        Simpan &amp; Teruskan Surat ➔
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>