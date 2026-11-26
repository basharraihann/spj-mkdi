<x-app-layout title="Input Disposisi">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Input Disposisi</h2>
    </x-slot>

    {{-- Pemilih tanggal berbahasa Indonesia (flatpickr). Kalau CDN gagal dimuat, input tanggal bawaan browser tetap jalan. --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">

    <div class="py-6 sm:py-8 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
        <form method="POST" action="{{ route('disposisi.store') }}" class="space-y-5">
            @csrf

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    Periksa kembali isian yang ditandai merah.
                </div>
            @endif

            @php
                $input = 'w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none';
                $lbl = 'block text-xs font-semibold text-gray-600 mb-1.5';
                $lblJadwal = $lbl . ' uppercase tracking-wide';

                // Ikon kalender di kanan input tanggal (muncul setelah pemilih tanggal aktif)
                $ikonKalender = '<svg class="ikon-tgl hidden pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>';

                // Pilihan jam 24 jam, tampil "12.30", nilai terkirim "12:30"
                $langkah = 15; // menit antar pilihan (ubah ke 30 kalau mau lebih ringkas)
                $jam = [];
                for ($m = 0; $m < 24 * 60; $m += $langkah) {
                    $jam[sprintf('%02d:%02d', intdiv($m, 60), $m % 60)] = sprintf('%02d.%02d', intdiv($m, 60), $m % 60);
                }
                $mulaiLama = substr((string) old('waktu_mulai'), 0, 5);
                $selesaiLama = substr((string) old('waktu_selesai'), 0, 5);
            @endphp

            {{-- Data surat --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm space-y-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">1. Data surat</h3>
                    <p class="text-xs text-gray-500">Sesuai yang tertulis di surat.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="jenis" class="{{ $lbl }}">Jenis dokumen <span class="text-red-400">*</span></label>
                        <select id="jenis" name="jenis" class="{{ $input }}">
                            @foreach (\App\Http\Controllers\JadwalPegawaiController::JENIS as $k => $v)
                                <option value="{{ $k }}" @selected(old('jenis', 'surat_masuk') === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                        @error('jenis') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <p class="sm:col-span-2 text-xs text-gray-500 sm:pt-6">
                        Surat Masuk yang punya jadwal kegiatan otomatis masuk ke Google Calendar semua staf di unit Anda
                        (kecuali sifat Rahasia). Jenis lain hanya didisposisikan ke Eselon II.
                    </p>
                </div>

                <div>
                    <label for="nomor_surat" class="{{ $lbl }}">Nomor surat <span class="text-red-400">*</span></label>
                    <input id="nomor_surat" name="nomor_surat" value="{{ old('nomor_surat') }}" required class="{{ $input }}"
                        placeholder="mis. 123/KU.01/XI/2026">
                    @error('nomor_surat') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
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
                        <div class="relative">
                            <input id="tanggal_surat" type="date" name="tanggal_surat" value="{{ old('tanggal_surat') }}" class="{{ $input }} tgl-id pr-10">
                            {!! $ikonKalender !!}
                        </div>
                        @error('tanggal_surat') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tanggal_terima" class="{{ $lbl }}">Tanggal diterima <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input id="tanggal_terima" type="date" name="tanggal_terima" required
                                value="{{ old('tanggal_terima', now()->toDateString()) }}" class="{{ $input }} tgl-id pr-10">
                            {!! $ikonKalender !!}
                        </div>
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
                    <label for="isi_ringkas" class="{{ $lbl }}">Isi surat / agenda
                        <span class="font-normal text-gray-400">(ringkasan, tampil di email undangan)</span></label>
                    <textarea id="isi_ringkas" name="isi_ringkas" rows="4" class="{{ $input }}">{{ old('isi_ringkas') }}</textarea>
                    @error('isi_ringkas') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="link_berkas" class="{{ $lbl }}">Link PDF surat
                        <span class="font-normal text-gray-400">(Google Drive / OneDrive, tampil di event kalender)</span></label>
                    <input id="link_berkas" type="url" name="link_berkas" value="{{ old('link_berkas') }}" class="{{ $input }}"
                        placeholder="https://drive.google.com/file/d/...">
                    <p class="text-xs text-gray-500 mt-1">Pastikan akses file sudah dibagikan ke penerima disposisi dan staf unit.</p>
                    @error('link_berkas') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
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
                        <label for="tanggal_kegiatan" class="{{ $lblJadwal }}">Tanggal</label>
                        <div class="relative">
                            <input id="tanggal_kegiatan" type="date" name="tanggal_kegiatan" value="{{ old('tanggal_kegiatan') }}" class="{{ $input }} tgl-id pr-10">
                            {!! $ikonKalender !!}
                        </div>
                        @error('tanggal_kegiatan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="waktu_mulai" class="{{ $lblJadwal }}">Jam mulai (WIB)</label>
                        <select id="waktu_mulai" name="waktu_mulai" class="{{ $input }}">
                            <option value="">Pilih jam</option>
                            @foreach ($jam as $nilai => $tampil)
                                <option value="{{ $nilai }}" @selected($mulaiLama === $nilai)>{{ $tampil }}</option>
                            @endforeach
                        </select>
                        @error('waktu_mulai') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="waktu_selesai" class="{{ $lblJadwal }}">Jam selesai (WIB)</label>
                        <select id="waktu_selesai" name="waktu_selesai" class="{{ $input }}">
                            <option value="">Selesai</option>
                            @foreach ($jam as $nilai => $tampil)
                                <option value="{{ $nilai }}" @selected($selesaiLama === $nilai)>{{ $tampil }}</option>
                            @endforeach
                        </select>
                        @error('waktu_selesai') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tempat" class="{{ $lblJadwal }}">Tempat</label>
                        <input id="tempat" name="tempat" value="{{ old('tempat') }}" class="{{ $input }}" placeholder="Ruang / Zoom">
                        @error('tempat') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label for="link_daring" class="{{ $lbl }}">Link daring <span class="font-normal text-gray-400">(Zoom / Meet, jika ada)</span></label>
                    <input id="link_daring" type="url" name="link_daring" value="{{ old('link_daring') }}" class="{{ $input }}" placeholder="https://...">
                    @error('link_daring') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
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
                        <div class="relative">
                            <input id="batas_waktu" type="date" name="batas_waktu" value="{{ old('batas_waktu') }}" class="{{ $input }} tgl-id pr-10">
                            {!! $ikonKalender !!}
                        </div>
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

    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tanggal tampil "Kamis, 26 November 2026", nilai yang dikirim ke server tetap Y-m-d
            if (window.flatpickr && flatpickr.l10ns && flatpickr.l10ns.id) {
                document.querySelectorAll('input.tgl-id').forEach(function (el) {
                    flatpickr(el, {
                        locale: flatpickr.l10ns.id,
                        dateFormat: 'Y-m-d',
                        altInput: true,
                        altFormat: 'l, j F Y',
                        altInputClass: el.className + ' bg-white cursor-pointer',
                        disableMobile: true
                    });
                });
                document.querySelectorAll('.ikon-tgl').forEach(function (ikon) {
                    ikon.classList.remove('hidden');
                });
            }

            // Pilih jam mulai -> jam selesai otomatis +1 jam (kalau belum diisi / lebih awal dari jam mulai)
            var mulai = document.getElementById('waktu_mulai');
            var selesai = document.getElementById('waktu_selesai');
            if (mulai && selesai) {
                mulai.addEventListener('change', function () {
                    if (!mulai.value) return;
                    var bagian = mulai.value.split(':').map(Number);
                    var akhir = bagian[0] * 60 + bagian[1] + 60;
                    if (akhir >= 24 * 60) return;
                    var nilai = String(Math.floor(akhir / 60)).padStart(2, '0') + ':' + String(akhir % 60).padStart(2, '0');
                    if (!selesai.value || selesai.value <= mulai.value) {
                        selesai.value = nilai;
                    }
                });
            }
        });
    </script>
</x-app-layout>
