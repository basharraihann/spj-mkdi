<x-app-layout title="Input Disposisi">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Input Disposisi</h2>
    </x-slot>

    <div class="py-6 sm:py-8 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
        <form method="POST" action="{{ route('disposisi.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    Periksa kembali isian yang ditandai merah.
                </div>
            @endif

            {{-- Data surat --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm space-y-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">1. Data surat masuk</h3>
                    <p class="text-xs text-gray-500">Sesuai yang tertulis di surat.</p>
                </div>

                @php
                    $input = 'w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none';
                    $lbl = 'block text-xs font-semibold text-gray-600 mb-1.5';
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="nomor_surat" class="{{ $lbl }}">Nomor surat <span class="text-red-400">*</span></label>
                        <input id="nomor_surat" name="nomor_surat" value="{{ old('nomor_surat') }}" required class="{{ $input }}"
                            placeholder="mis. 123/KU.01/XI/2026">
                        @error('nomor_surat') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="nomor_agenda" class="{{ $lbl }}">Nomor agenda</label>
                        <input id="nomor_agenda" name="nomor_agenda" value="{{ old('nomor_agenda') }}" class="{{ $input }}"
                            placeholder="Nomor buku agenda TU">
                        @error('nomor_agenda') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="asal_surat" class="{{ $lbl }}">Asal surat <span class="text-red-400">*</span></label>
                        <input id="asal_surat" name="asal_surat" value="{{ old('asal_surat') }}" required class="{{ $input }}"
                            placeholder="Instansi / pengirim">
                        @error('asal_surat') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="sifat" class="{{ $lbl }}">Sifat <span class="text-red-400">*</span></label>
                        <select id="sifat" name="sifat" class="{{ $input }}">
                            @foreach (\App\Models\DisposisiSurat::SIFAT as $k => $v)
                                <option value="{{ $k }}" @selected(old('sifat', 'biasa') === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                        @error('sifat') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="tanggal_surat" class="{{ $lbl }}">Tanggal surat</label>
                        <input id="tanggal_surat" type="date" name="tanggal_surat" value="{{ old('tanggal_surat') }}" class="{{ $input }}">
                        @error('tanggal_surat') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tanggal_terima" class="{{ $lbl }}">Tanggal diterima <span class="text-red-400">*</span></label>
                        <input id="tanggal_terima" type="date" name="tanggal_terima" required
                            value="{{ old('tanggal_terima', now()->toDateString()) }}" class="{{ $input }}">
                        @error('tanggal_terima') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="lampiran" class="{{ $lbl }}">Lampiran</label>
                        <input id="lampiran" name="lampiran" value="{{ old('lampiran') }}" class="{{ $input }}"
                            placeholder="mis. 1 berkas">
                    </div>
                </div>

                <div>
                    <label for="perihal" class="{{ $lbl }}">Perihal <span class="text-red-400">*</span></label>
                    <textarea id="perihal" name="perihal" rows="2" required class="{{ $input }}">{{ old('perihal') }}</textarea>
                    @error('perihal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="file" class="{{ $lbl }}">Scan / file surat
                        <span class="font-normal text-gray-400">(PDF, JPG, PNG, DOC; maks 10 MB)</span></label>
                    <input id="file" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                        class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100">
                    @error('file') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Jadwal kegiatan (opsional): dipakai kalender & Google Calendar --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm space-y-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">2. Jadwal kegiatan <span class="text-xs font-normal text-gray-400">(opsional)</span></h3>
                    <p class="text-xs text-gray-500">Isi kalau surat berisi undangan / acara. Disposisi akan tampil di tanggal ini
                        pada Kalender Disposisi dan Google Calendar penerima. Kalau kosong, dipakai batas waktu.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label for="tanggal_kegiatan" class="{{ $lbl }}">Tanggal</label>
                        <input id="tanggal_kegiatan" type="date" name="tanggal_kegiatan" value="{{ old('tanggal_kegiatan') }}" class="{{ $input }}">
                        @error('tanggal_kegiatan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="waktu_mulai" class="{{ $lbl }}">Jam mulai</label>
                        <input id="waktu_mulai" type="time" name="waktu_mulai" value="{{ old('waktu_mulai') }}" class="{{ $input }}">
                        @error('waktu_mulai') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="waktu_selesai" class="{{ $lbl }}">Jam selesai</label>
                        <input id="waktu_selesai" type="time" name="waktu_selesai" value="{{ old('waktu_selesai') }}" class="{{ $input }}">
                        @error('waktu_selesai') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tempat" class="{{ $lbl }}">Tempat</label>
                        <input id="tempat" name="tempat" value="{{ old('tempat') }}" class="{{ $input }}" placeholder="Ruang / Zoom">
                        @error('tempat') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

{{-- Diteruskan ke Eselon II --}}
<div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm space-y-4">
    <div>
        <h3 class="text-base font-semibold text-gray-900">3. Diteruskan ke Eselon II</h3>
        <p class="text-xs text-gray-500">Surat masuk ke Eselon II untuk didisposisikan. Instruksi dan penerima berikutnya ditentukan oleh Eselon II.</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="sm:col-span-2">
            <label for="kepada" class="{{ $lbl }}">Ditujukan ke <span class="text-red-400">*</span></label>
            <select id="kepada" name="kepada[]" required class="{{ $input }}">
                @php
                    $satuUnit = $es2->where('satu_unit', true);
                    $awal = $satuUnit->count() === 1 ? [$satuUnit->first()['id']] : [];
                    $terpilih = array_map('intval', (array) old('kepada', $awal));
                @endphp
                @forelse ($es2 as $p)
                    <option value="{{ $p['id'] }}" @selected(in_array($p['id'], $terpilih, true))>
                        {{ $p['nama'] }} · {{ $p['unit'] }}
                    </option>
                @empty
                    <option value="" disabled selected>Belum ada user berperan Eselon II</option>
                @endforelse
            </select>
            @error('kepada') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            @error('kepada.*') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="batas_waktu" class="{{ $lbl }}">Batas waktu <span class="font-normal text-gray-400">(jika ada di surat)</span></label>
            <input id="batas_waktu" type="date" name="batas_waktu" value="{{ old('batas_waktu') }}" class="{{ $input }}">
            @error('batas_waktu') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>
    <div>
        <label for="catatan" class="{{ $lbl }}">Catatan untuk Eselon II</label>
        <textarea id="catatan" name="catatan" rows="2" class="{{ $input }}" placeholder="Opsional">{{ old('catatan') }}</textarea>
        @error('catatan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>
</div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('disposisi.index', ['tab' => 'surat']) }}"
                    class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit"
                    class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Simpan &amp; Kirim ke Eselon II
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
