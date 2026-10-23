<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Buat Draf Surat / Dokumen Keluar Baru') }}
            </h2>
            <a href="{{ route('surat-keluar.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ jenisDokumen: 'nota_dinas' }">

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs font-medium space-y-1">
                <p class="font-bold text-sm mb-1">Gagal menyimpan draf! Periksa kembali inputan berikut:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
            <form action="{{ route('surat-keluar.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Pilihan Jenis Dokumen --}}
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <label class="block text-sm font-bold text-slate-800">
                        Pilih Jenis Dokumen Surat <span class="text-red-600">*</span>
                    </label>
                    <select name="jenis_dokumen" x-model="jenisDokumen" required
                        class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 font-semibold">
                        <option value="nota_dinas">Nota Dinas (TND / ND)</option>
                        <option value="undangan">Undangan / Surat Dinas Eksternal</option>
                        <option value="telaah_staf">Telaah Staf</option>
                        <option value="notula">Notula Rapat</option>
                        <option value="surat_pernyataan">Surat Pernyataan</option>
                    </select>
                </div>

                {{-- Header Umum --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Surat / Dokumen</label>
                        <input type="text" name="nomor_surat" value="{{ old('nomor_surat') }}" placeholder="Contoh: B-12/HM.02/2026 atau kosongi jika Draf"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Surat <span class="text-red-600">*</span></label>
                        <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', date('Y-m-d')) }}" required
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Hal / Perihal / Judul <span class="text-red-600">*</span></label>
                        <input type="text" name="hal" value="{{ old('hal') }}" required placeholder="Subjek atau perihal dokumen..."
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Derajat Klasifikasi / Sifat <span class="text-red-600">*</span></label>
                        <select name="sifat" required class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                            <option value="biasa">Biasa (B)</option>
                            <option value="terbatas">Terbatas (T)</option>
                            <option value="rahasia">Rahasia (R)</option>
                        </select>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- 1. DYNAMIC FORM: NOTA DINAS                              --}}
                {{-- ======================================================== --}}
                <div x-show="jenisDokumen === 'nota_dinas'" x-cloak class="space-y-4 pt-4 border-t border-slate-200">
                    <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider">Isian Khusus Nota Dinas</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Yth. (Tujuan)</label>
                            <input type="text" name="tujuan" value="{{ old('tujuan', 'Sekretaris Kementerian Koordinator') }}"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Dari</label>
                            <input type="text" name="dari" value="{{ old('dari', 'Deputi Bidang Koordinasi Usaha Pangan dan Pertanian') }}"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Lampiran (Opsional)</label>
                        <input type="text" name="isi_payload[lampiran]" value="{{ old('isi_payload.lampiran') }}" placeholder="Contoh: 1 (Satu) Berkas"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Isi Ringkasan Nota Dinas</label>
                        <textarea name="isi_payload[isi_ringkasan]" rows="5" placeholder="Tuliskan isi laporan/penjelasan nota dinas..."
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">{{ old('isi_payload.isi_ringkasan') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pejabat Penandatangan</label>
                            <input type="text" name="isi_payload[nama_penandatangan]" value="{{ old('isi_payload.nama_penandatangan') }}"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">NIP Pejabat Penandatangan</label>
                            <input type="text" name="isi_payload[nip_penandatangan]" value="{{ old('isi_payload.nip_penandatangan') }}"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- 2. DYNAMIC FORM: UNDANGAN EKSTERNAL                      --}}
                {{-- ======================================================== --}}
                <div x-show="jenisDokumen === 'undangan'" x-cloak class="space-y-4 pt-4 border-t border-slate-200">
                    <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider">Isian Khusus Surat Undangan</h4>
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Yth. (Penerima Undangan)</label>
                        <input type="text" name="tujuan" value="{{ old('tujuan') }}" placeholder="Contoh: Para Pejabat Eselon II di Lingkungan Kemenko Pangan"
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Hari / Tanggal Acara</label>
                            <input type="text" name="isi_payload[hari_tanggal]" value="{{ old('isi_payload.hari_tanggal') }}" placeholder="Senin / 26 Oktober 2026"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Waktu / Pukul</label>
                            <input type="text" name="isi_payload[waktu]" value="{{ old('isi_payload.waktu', '09.00 WIB s.d. Selesai') }}"
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tempat</label>
                            <input type="text" name="isi_payload[tempat]" value="{{ old('isi_payload.tempat') }}" placeholder="Ruang Rapat lantai 3..."
                                class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Acara / Agenda</label>
                        <input type="text" name="isi_payload[acara]" value="{{ old('isi_payload.acara') }}" placeholder="Rapat Koordinasi Evaluasi Kinerja..."
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- 3. DYNAMIC FORM: TELAAH STAF                            --}}
                {{-- ======================================================== --}}
                <div x-show="jenisDokumen === 'telaah_staf'" x-cloak class="space-y-4 pt-4 border-t border-slate-200">
                    <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider">Isian Khusus Telaah Staf</h4>
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">I. Persoalan</label>
                        <textarea name="isi_payload[persoalan]" rows="2" class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">II. Praanggapan</label>
                        <textarea name="isi_payload[praanggapan]" rows="2" class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">III. Fakta yang Mempengaruhi</label>
                        <textarea name="isi_payload[fakta]" rows="2" class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">IV. Analisis</label>
                        <textarea name="isi_payload[analisis]" rows="3" class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">V. Kesimpulan</label>
                        <textarea name="isi_payload[kesimpulan]" rows="2" class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">VI. Saran</label>
                        <textarea name="isi_payload[saran]" rows="2" class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- 4. DYNAMIC FORM: NOTULA RAPAT                            --}}
                {{-- ======================================================== --}}
                <div x-show="jenisDokumen === 'notula'" x-cloak class="space-y-4 pt-4 border-t border-slate-200">
                    <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider">Isian Khusus Notula Rapat</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Dasar Rapat</label>
                            <input type="text" name="isi_payload[dasar]" class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Waktu & Tempat</label>
                            <input type="text" name="isi_payload[waktu_tempat]" class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Peserta Rapat</label>
                        <textarea name="isi_payload[peserta]" rows="2" placeholder="Daftar peserta rapat..." class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Uraian Pelaksanaan / Poin Pembahasan</label>
                        <textarea name="isi_payload[pelaksanaan]" rows="4" class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- 5. DYNAMIC FORM: SURAT PERNYATAAN                       --}}
                {{-- ======================================================== --}}
                <div x-show="jenisDokumen === 'surat_pernyataan'" x-cloak class="space-y-4 pt-4 border-t border-slate-200">
                    <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider">Isian Khusus Surat Pernyataan</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Yang Menyatakan</label>
                            <input type="text" name="isi_payload[nama_menyatakan]" class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">NIP Yang Menyatakan</label>
                            <input type="text" name="isi_payload[nip_menyatakan]" class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Isi Pernyataan</label>
                        <textarea name="isi_payload[isi_pernyataan]" rows="3" placeholder="Dengan ini menyatakan bahwa..." class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                </div>

                <div class="pt-4 flex justify-end gap-3 border-t border-slate-200">
                    <a href="{{ route('surat-keluar.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">Batal</a>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-md transition">Simpan Draf Dokumen ➔</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>