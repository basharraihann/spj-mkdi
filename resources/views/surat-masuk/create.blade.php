<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Input Surat Masuk Baru') }}
            </h2>
            <a href="{{ route('surat-masuk.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition">
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
                
                {{-- PEMISAHAN ALUR SURAT MASUK (MEETING 23/10/26) --}}
                <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-200/80 space-y-2">
                    <label class="block text-sm font-bold text-blue-900">
                        Jalur Tujuan Surat Masuk <span class="text-red-600">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 p-3 bg-white rounded-xl border border-slate-200 cursor-pointer hover:border-blue-500 transition">
                            <input type="radio" name="jalur_tujuan" value="deputi" @checked(old('jalur_tujuan', 'deputi') === 'deputi') class="text-blue-600 focus:ring-blue-500">
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Jalur Ibu Deputi (Eselon I)</span>
                                <span class="block text-[11px] text-slate-500">Diteruskan langsung ke Inbox Disposisi Deputi 2</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-white rounded-xl border border-slate-200 cursor-pointer hover:border-blue-500 transition">
                            <input type="radio" name="jalur_tujuan" value="eselon2" @checked(old('jalur_tujuan') === 'eselon2') class="text-blue-600 focus:ring-blue-500">
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Jalur Asdep / Karo (Eselon II)</span>
                                <span class="block text-[11px] text-slate-500">Diteruskan ke Inbox Disposisi Asdep 2</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- 1. Nomor Agenda / Disposisi --}}
                    @php
                        // Konversi Bulan Angka ke Romawi (1-12)
                        $romawiBulan = [
                            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
                            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
                        ];
                        $now = \Carbon\Carbon::now();
                        $bulanRomawi = $romawiBulan[$now->month] ?? 'X';
                        
                        // Kode Unit D2 (Deputi 2)
                        $agendaAuto = 'AG-' . sprintf('%03d', (\App\Models\SuratMasuk::max('id') + 1)) . '/D2/' . $bulanRomawi . '/' . $now->year;
                    @endphp
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nomor Agenda / Disposisi</label>
                        <input type="text" value="{{ old('nomor_agenda', $agendaAuto) }}" readonly
                            class="mt-1 w-full rounded-xl border-slate-200 bg-slate-100/80 text-sm font-semibold text-slate-700 cursor-not-allowed focus:ring-0">
                        <input type="hidden" name="nomor_agenda" value="{{ old('nomor_agenda', $agendaAuto) }}">
                        <p class="text-[11px] text-slate-400 mt-1">Nomor agenda dibuat otomatis oleh sistem saat disimpan.</p>
                    </div>

                    {{-- 2. Nomor Surat Physical/Asal (Opsional) --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700">
                            Nomor Surat Physical/Asal <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                        </label>
                        <input type="text" name="nomor_surat" value="{{ old('nomor_surat') }}" placeholder="Contoh: B-123/KEMENTAN/2026"
                            class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="text-[11px] text-slate-400 mt-1">Biarkan kosong jika surat fisik tidak memiliki nomor resmi.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Asal Instansi / Pengirim <span class="text-red-600">*</span></label>
                        <input type="text" name="asal_surat" value="{{ old('asal_surat') }}" required placeholder="Contoh: Kementerian Pertanian"
                            class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tanggal Surat <span class="text-red-600">*</span></label>
                        <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', date('Y-m-d')) }}" required
                            class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Perihal / Ringkasan Isi Surat <span class="text-red-600">*</span></label>
                    <textarea name="perihal" rows="3" required placeholder="Jelaskan ringkasan isi surat..."
                        class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('perihal') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Link Google Drive Berkas Surat (PDF/Word/Excel/PPT)</label>
                    <input type="url" name="drive_link" value="{{ old('drive_link') }}" placeholder="https://drive.google.com/file/d/..."
                        class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-slate-500 mt-1">Pastikan akses link Google Drive sudah diatur ke 'Anyone with link / Siapa saja yang memiliki link'.</p>
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <a href="{{ route('surat-masuk.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition">Batal</a>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-sm transition">Simpan & Teruskan Surat ➔</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>