{{-- Form bersama untuk input & ubah surat. Mode ubah aktif kalau $surat terisi. --}}

{{-- Pemilih tanggal berbahasa Indonesia (flatpickr). Kalau CDN gagal dimuat, input tanggal bawaan browser tetap jalan. --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">

@php
    $surat = $surat ?? null;
    $edit = (bool) $surat;
    $es2 = $es2 ?? collect();

    // Ambil nilai: old() dulu, lalu data surat (mode ubah), lalu bawaan
    $nilai = fn (string $f, $bawaan = null) => old($f, $edit ? $surat->{$f} : $bawaan);
    $tgl = fn (string $f, $bawaan = null) => old($f, $edit ? $surat->{$f}?->toDateString() : $bawaan);

    // Gaya input. Warna border dipisah supaya state error tidak bentrok dengan state normal.
    $dasar = 'block w-full rounded-lg bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 border shadow-none outline-none transition focus:ring-4';
    $kelas = fn (string $f) => $dasar . ' ' . ($errors->has($f)
        ? 'border-red-400 focus:border-red-500 focus:ring-red-100'
        : 'border-slate-300 hover:border-slate-400 focus:border-blue-500 focus:ring-blue-100');
    $lbl = 'block text-sm font-medium text-slate-700 mb-1.5';
    $bantu = 'mt-1.5 text-xs text-slate-500';
    $galat = 'mt-1.5 text-xs text-red-600';
    $wajib = '<span class="text-red-500" aria-hidden="true">*</span>';

    // Ikon kalender di kanan input tanggal (muncul setelah pemilih tanggal aktif)
    $ikonKalender = '<svg class="ikon-tgl hidden pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>';

    // Jenis dokumen: "undangan masuk" disembunyikan (cukup 3 pilihan).
    // Lebih bersih kalau dihapus langsung dari konstanta JENIS di controller, lalu baris reject() ini dibuang.
    $daftarJenis = collect(\App\Http\Controllers\JadwalPegawaiController::JENIS)
        ->reject(fn ($label, $key) => str_contains(strtolower($key . ' ' . $label), 'undangan'));
    $jenisAwal = $nilai('jenis') ?: 'surat_masuk';
    $sifatAwal = $nilai('sifat') ?: 'biasa';

    // Pilihan jam 24 jam, tampil "12.30", nilai terkirim "12:30"
    $langkah = 15; // menit antar pilihan (ubah ke 30 kalau mau lebih ringkas)
    $jam = [];
    for ($m = 0; $m < 24 * 60; $m += $langkah) {
        $jam[sprintf('%02d:%02d', intdiv($m, 60), $m % 60)] = sprintf('%02d.%02d', intdiv($m, 60), $m % 60);
    }
    $mulaiLama = substr((string) old('waktu_mulai', $edit ? $surat->waktu_mulai : ''), 0, 5);
    $selesaiLama = substr((string) old('waktu_selesai', $edit ? $surat->waktu_selesai : ''), 0, 5);

    // Tujuan (hanya mode input)
    $terpilih = [];
    $userEs2 = collect();
    if (! $edit) {
        // Kalau hanya ada satu Eselon II di unit pencatat, langsung terpilih
        $satuUnit = $es2->where('satu_unit', true);
        $awal = $satuUnit->count() === 1 ? [$satuUnit->first()['id']] : [];
        $terpilih = array_map('intval', (array) old('kepada', $awal));

        // Model User para penerima, untuk foto profil (cara baca foto ada di <x-avatar>, sama dengan kalender).
        // Lebih rapi kalau dikirim dari controller sebagai key 'foto' di tiap item $es2.
        if ($es2->isNotEmpty()) {
            $userEs2 = \App\Models\User::whereIn('id', $es2->pluck('id'))->get()->keyBy('id');
        }
    }

    $urlBatal = $edit
        ? route('disposisi.show', $surat)
        : route('disposisi.index', ['tab' => 'surat']);
@endphp

<div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4" role="alert">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 flex-none text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                <div class="text-sm">
                    <p class="font-medium text-red-800">Surat belum bisa disimpan. Perbaiki isian berikut:</p>
                    <ul class="mt-1.5 list-disc space-y-0.5 pl-5 text-red-700">
                        @foreach (collect($errors->all())->unique()->take(6) as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ $edit ? route('disposisi.update', $surat) : route('disposisi.store') }}" novalidate
        x-data="{ jenis: @js($jenisAwal), sifat: @js($sifatAwal), kirim: false }"
        @submit="kirim = true" @pageshow.window="kirim = false">
        @csrf
        @if ($edit)
            @method('PUT')
        @endif

        {{-- Kolom kanan lebarnya tetap supaya kartu penerima tidak sempit atau melebar; kolom kiri mengisi sisanya --}}
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem] xl:grid-cols-[minmax(0,1fr)_26rem]">

            {{-- ============ Kolom kiri: data surat + jadwal ============ --}}
            <div class="min-w-0 space-y-6">

                {{-- 1. Data surat --}}
                <section class="rounded-2xl border border-slate-200 bg-white" aria-labelledby="judul-surat">
                    <header class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                        <span class="grid h-7 w-7 place-items-center rounded-full bg-blue-600 text-xs font-semibold text-white">1</span>
                        <div>
                            <h3 id="judul-surat" class="text-base font-semibold text-slate-900">Data surat</h3>
                            <p class="text-xs text-slate-500">
                                @if ($edit)
                                    Sesuai yang tertulis di surat. Perubahan jadwal, tempat, link, atau isi otomatis memperbarui event Google Calendar.
                                @else
                                    Isi sesuai yang tertulis di surat.
                                @endif
                            </p>
                        </div>
                    </header>

                    <div class="space-y-5 p-6">
                        {{-- Jenis dokumen --}}
                        <fieldset>
                            <legend class="{{ $lbl }}">Jenis dokumen {!! $wajib !!}</legend>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($daftarJenis as $k => $v)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="jenis" value="{{ $k }}" x-model="jenis" class="peer sr-only">
                                        <span class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-1.5 text-sm text-slate-600 transition
                                            hover:border-slate-400 peer-checked:border-blue-600 peer-checked:bg-blue-600 peer-checked:text-white
                                            peer-focus-visible:ring-4 peer-focus-visible:ring-blue-100">{{ $v }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('jenis') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                        </fieldset>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="nomor_surat" class="{{ $lbl }}">Nomor surat {!! $wajib !!}</label>
                                <input id="nomor_surat" name="nomor_surat" value="{{ $nilai('nomor_surat') }}" required
                                    class="{{ $kelas('nomor_surat') }}" placeholder="mis. 123/KU.01/XI/2026">
                                @error('nomor_surat') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="asal_surat" class="{{ $lbl }}">Asal surat {!! $wajib !!}</label>
                                <input id="asal_surat" name="asal_surat" value="{{ $nilai('asal_surat') }}" required
                                    class="{{ $kelas('asal_surat') }}" placeholder="Instansi atau pengirim">
                                @error('asal_surat') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                            <div>
                                <label for="tanggal_surat" class="{{ $lbl }}">Tanggal surat</label>
                                <div class="relative">
                                    <input id="tanggal_surat" type="date" name="tanggal_surat" value="{{ $tgl('tanggal_surat') }}"
                                        class="{{ $kelas('tanggal_surat') }} tgl-id pr-10">
                                    {!! $ikonKalender !!}
                                </div>
                                @error('tanggal_surat') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="tanggal_terima" class="{{ $lbl }}">Tanggal diterima {!! $wajib !!}</label>
                                <div class="relative">
                                    <input id="tanggal_terima" type="date" name="tanggal_terima" required
                                        value="{{ $tgl('tanggal_terima', now()->toDateString()) }}"
                                        class="{{ $kelas('tanggal_terima') }} tgl-id pr-10">
                                    {!! $ikonKalender !!}
                                </div>
                                @error('tanggal_terima') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="lampiran" class="{{ $lbl }}">Lampiran</label>
                                <input id="lampiran" name="lampiran" value="{{ $nilai('lampiran') }}"
                                    class="{{ $kelas('lampiran') }}" placeholder="mis. 1 berkas">
                                @error('lampiran') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="sifat" class="{{ $lbl }}">Sifat surat {!! $wajib !!}</label>
                            <select id="sifat" name="sifat" x-model="sifat" class="{{ $kelas('sifat') }} sm:max-w-xs">
                                @foreach (\App\Models\DisposisiSurat::SIFAT as $k => $v)
                                    <option value="{{ $k }}" @selected($sifatAwal === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                            <p x-show="sifat === 'rahasia'" x-cloak class="{{ $bantu }}">
                                Surat rahasia tidak dikirim ke Google Calendar staf unit.
                            </p>
                            @error('sifat') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="perihal" class="{{ $lbl }}">Perihal {!! $wajib !!}</label>
                            <textarea id="perihal" name="perihal" rows="2" required
                                class="{{ $kelas('perihal') }}" placeholder="Pokok isi surat">{{ $nilai('perihal') }}</textarea>
                            @error('perihal') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="isi_ringkas" class="{{ $lbl }}">Isi surat atau agenda</label>
                            <textarea id="isi_ringkas" name="isi_ringkas" rows="4"
                                class="{{ $kelas('isi_ringkas') }}">{{ $nilai('isi_ringkas') }}</textarea>
                            <p class="{{ $bantu }}">Ringkasan ini tampil di email undangan.</p>
                            @error('isi_ringkas') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="link_berkas" class="{{ $lbl }}">Link PDF surat</label>
                            @if ($edit && $surat->file_path)
                                <p class="mb-2 text-xs text-slate-500">Surat ini masih punya file hasil upload lama:
                                    <a href="{{ route('disposisi.berkas', $surat) }}" target="_blank" class="text-blue-600 hover:underline">{{ $surat->file_name ?: 'Lihat berkas' }}</a>.
                                    Isi link di bawah kalau ingin menggantinya dengan link PDF.
                                </p>
                            @endif
                            <input id="link_berkas" type="url" name="link_berkas" value="{{ $nilai('link_berkas') }}"
                                class="{{ $kelas('link_berkas') }}" placeholder="https://drive.google.com/file/d/...">
                            <p class="{{ $bantu }}">Dari Google Drive atau OneDrive, tampil di event kalender. Pastikan akses file sudah dibagikan ke penerima disposisi dan staf unit.</p>
                            @error('link_berkas') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                {{-- 2. Jadwal kegiatan --}}
                <section class="rounded-2xl border border-slate-200 bg-white" aria-labelledby="judul-jadwal">
                    <header class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                        <span class="grid h-7 w-7 place-items-center rounded-full bg-blue-600 text-xs font-semibold text-white">2</span>
                        <div class="flex-1">
                            <h3 id="judul-jadwal" class="text-base font-semibold text-slate-900">Jadwal kegiatan</h3>
                            <p class="text-xs text-slate-500">Isi kalau surat berisi undangan atau acara.</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">Opsional</span>
                    </header>

                    <div class="space-y-5 p-6">
                        {{-- Keterangan berubah mengikuti jenis dokumen --}}
                        <div class="flex gap-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                            <svg class="h-5 w-5 flex-none text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                            <p x-show="jenis === 'surat_masuk'">
                                Surat masuk yang punya jadwal otomatis masuk ke Google Calendar semua staf di unit Anda, kecuali sifat rahasia.
                                Disposisi juga tampil di Kalender Disposisi pada tanggal kegiatan.
                            </p>
                            <p x-show="jenis !== 'surat_masuk'" x-cloak>
                                Jenis dokumen ini hanya didisposisikan ke Eselon II. Disposisi tampil di Kalender Disposisi pada tanggal kegiatan.
                                Kalau tanggal kosong, kalender memakai batas waktu.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="tanggal_kegiatan" class="{{ $lbl }}">Tanggal kegiatan</label>
                                <div class="relative">
                                    <input id="tanggal_kegiatan" type="date" name="tanggal_kegiatan" value="{{ $tgl('tanggal_kegiatan') }}"
                                        class="{{ $kelas('tanggal_kegiatan') }} tgl-id pr-10">
                                    {!! $ikonKalender !!}
                                </div>
                                @error('tanggal_kegiatan') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="tempat" class="{{ $lbl }}">Tempat</label>
                                <input id="tempat" name="tempat" value="{{ $nilai('tempat') }}"
                                    class="{{ $kelas('tempat') }}" placeholder="Ruang rapat atau Zoom">
                                @error('tempat') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <span class="{{ $lbl }}">Waktu (WIB)</span>
                            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 sm:max-w-md">
                                <div>
                                    <label for="waktu_mulai" class="sr-only">Jam mulai</label>
                                    <select id="waktu_mulai" name="waktu_mulai" class="{{ $kelas('waktu_mulai') }}">
                                        <option value="">Jam mulai</option>
                                        @foreach ($jam as $v => $tampil)
                                            <option value="{{ $v }}" @selected($mulaiLama === $v)>{{ $tampil }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <span class="text-slate-400" aria-hidden="true">sampai</span>
                                <div>
                                    <label for="waktu_selesai" class="sr-only">Jam selesai</label>
                                    <select id="waktu_selesai" name="waktu_selesai" class="{{ $kelas('waktu_selesai') }}">
                                        <option value="">Jam selesai</option>
                                        @foreach ($jam as $v => $tampil)
                                            <option value="{{ $v }}" @selected($selesaiLama === $v)>{{ $tampil }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <p class="{{ $bantu }}">Jam selesai terisi otomatis satu jam setelah jam mulai.</p>
                            @error('waktu_mulai') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                            @error('waktu_selesai') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="link_daring" class="{{ $lbl }}">Link daring</label>
                            <input id="link_daring" type="url" name="link_daring" value="{{ $nilai('link_daring') }}"
                                class="{{ $kelas('link_daring') }}" placeholder="https://zoom.us/j/... atau https://meet.google.com/...">
                            <p class="{{ $bantu }}">Isi kalau kegiatan diadakan lewat Zoom atau Meet.</p>
                            @error('link_daring') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>
            </div>

            {{-- ============ Kolom kanan: tujuan + aksi (tetap terlihat saat scroll di layar lebar) ============ --}}
            <aside class="min-w-0 lg:sticky lg:top-6">
                <section class="rounded-2xl border border-slate-200 border-t-4 border-t-blue-600 bg-white" aria-labelledby="judul-tujuan">
                    <header class="flex items-center gap-3 px-6 pt-5">
                        <span class="grid h-7 w-7 place-items-center rounded-full bg-blue-600 text-xs font-semibold text-white">3</span>
                        <div>
                            <h3 id="judul-tujuan" class="text-base font-semibold text-slate-900">
                                {{ $edit ? 'Penerima disposisi' : 'Teruskan ke Eselon II' }}
                            </h3>
                            <p class="text-xs text-slate-500">
                                {{ $edit ? 'Penerima dan alur tidak berubah di sini.' : 'Instruksi dan penerima berikutnya ditentukan oleh Eselon II.' }}
                            </p>
                        </div>
                    </header>

                    <div class="space-y-5 p-6">
                        @if ($edit)
                            <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                                Kalau jadwal, tempat, atau link diubah, event Google Calendar penerima dan staf unit diperbarui otomatis.
                                Kalau tanggal kegiatan dikosongkan, jenis diganti bukan Surat Masuk, atau sifat jadi Rahasia,
                                event unit dibatalkan dan staf menerima email pembatalan dari Google.
                            </div>
                        @else
                            {{-- Pilih penerima lewat kartu berfoto profil bulat. Klik untuk memilih, klik lagi untuk membatalkan. --}}
                            <fieldset class="min-w-0">
                                <legend class="{{ $lbl }}">Ditujukan ke {!! $wajib !!}</legend>

                                @if ($es2->isEmpty())
                                    <p class="rounded-lg border border-dashed border-slate-300 px-4 py-3 text-sm text-slate-500">
                                        Belum ada user berperan Eselon II.
                                    </p>
                                @else
                                    <div class="-m-1 max-h-80 space-y-2 overflow-y-auto p-1">
                                        @foreach ($es2 as $p)
                                            <label class="relative block cursor-pointer">
                                                <input type="checkbox" name="kepada[]" value="{{ $p['id'] }}"
                                                    @checked(in_array($p['id'], $terpilih, true)) class="peer sr-only">

                                                <span class="flex items-center gap-3 rounded-xl border border-slate-300 bg-white p-3 pr-11 transition
                                                    hover:border-slate-400 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:ring-1 peer-checked:ring-blue-600
                                                    peer-focus-visible:ring-4 peer-focus-visible:ring-blue-100">
                                                    <x-avatar :user="$userEs2[$p['id']] ?? null" :src="$p['foto'] ?? null" :nama="$p['nama']" class="h-11 w-11 text-sm" />
                                                    <span class="min-w-0 flex-1">
                                                        <span class="block break-words text-sm font-medium leading-snug text-slate-900">{{ $p['nama'] }}</span>
                                                        <span class="mt-0.5 block break-words text-xs leading-snug text-slate-500">{{ $p['unit'] }}</span>
                                                    </span>
                                                </span>

                                                {{-- Penanda: lingkaran kosong (belum dipilih) / centang biru (terpilih) --}}
                                                <span class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 rounded-full border-2 border-slate-300 peer-checked:hidden" aria-hidden="true"></span>
                                                <svg class="pointer-events-none absolute right-3 top-1/2 hidden h-5 w-5 -translate-y-1/2 text-blue-600 peer-checked:block" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm4.7 7.7a1 1 0 0 0-1.4-1.4L10.5 13.1l-1.8-1.8a1 1 0 1 0-1.4 1.4l2.5 2.5a1 1 0 0 0 1.4 0l5.5-5.5Z" clip-rule="evenodd"/></svg>
                                            </label>
                                        @endforeach
                                    </div>
                                    <p class="{{ $bantu }}">Klik kartu untuk memilih, klik lagi untuk membatalkan.</p>
                                @endif

                                @error('kepada') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                                @error('kepada.*') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                            </fieldset>

                            <div>
                                <label for="batas_waktu" class="{{ $lbl }}">Batas waktu</label>
                                <div class="relative">
                                    <input id="batas_waktu" type="date" name="batas_waktu" value="{{ old('batas_waktu') }}"
                                        class="{{ $kelas('batas_waktu') }} tgl-id pr-10">
                                    {!! $ikonKalender !!}
                                </div>
                                <p class="{{ $bantu }}">Isi kalau surat menyebut batas waktu.</p>
                                @error('batas_waktu') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="catatan" class="{{ $lbl }}">Catatan untuk Eselon II</label>
                                <textarea id="catatan" name="catatan" rows="3" class="{{ $kelas('catatan') }}"
                                    placeholder="Opsional">{{ old('catatan') }}</textarea>
                                @error('catatan') <p class="{{ $galat }}">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>

                    <footer class="flex flex-wrap items-center justify-end gap-2 rounded-b-2xl border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <a href="{{ $urlBatal }}"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-slate-200">
                            Batal
                        </a>
                        <button type="submit" :disabled="kirim"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200 disabled:cursor-not-allowed disabled:opacity-60">
                            <svg x-show="kirim" x-cloak class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 3a9 9 0 1 0 9 9" /></svg>
                            <span x-show="!kirim">{{ $edit ? 'Simpan perubahan' : 'Simpan dan kirim' }}</span>
                            <span x-show="kirim" x-cloak>Menyimpan...</span>
                        </button>
                    </footer>
                </section>
            </aside>
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