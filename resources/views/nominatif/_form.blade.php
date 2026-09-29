{{--
Form bersama untuk Buat & Edit Nominatif Honorarium Narasumber.
Variabel: $action, $method, $submitLabel, $nominatif (null saat buat), $pegawaiList
--}}
@php
    $nominatif = $nominatif ?? null;

    $ppkList = $pegawaiList->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'PPK'))->values();
    $bendaharaList = $pegawaiList->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'Bendahara'))->values();

    // Narasumber awal: dari old() kalau validasi gagal, dari database kalau edit, atau satu baris kosong
    $rows = old('peserta');
    $rowsBaru = false;
    if ($rows === null) {
        $rows = $nominatif
            ? $nominatif->peserta->map(fn($p) => [
                'nama' => $p->nama,
                'npwp' => $p->npwp,
                'instansi' => $p->instansi,
                'golongan' => $p->golongan,
                'honor' => $p->honor,
                'oj' => $p->oj,
                'pajak_persen' => $p->pajak_persen,
            ])->values()->all()
            : [];
    }
    if (empty($rows)) {
        $rows = [['oj' => 1]];
        $rowsBaru = true;
    }

    // Tarif honorarium narasumber per OJ (standar biaya). Ubah di sini kalau tarif berubah.
    $tarifSbm = [
        'menteri' => ['label' => 'Menteri/Pejabat Negara/Wakil Menteri', 'honor' => 1700000],
        'eselon1' => ['label' => 'Pejabat Eselon I/yang disetarakan', 'honor' => 1400000],
        'eselon2' => ['label' => 'Pejabat Eselon II/yang disetarakan', 'honor' => 1000000],
        'eselon3' => ['label' => 'Pejabat Eselon III ke bawah/yang disetarakan', 'honor' => 900000],
    ];

    // Warna aksen per kartu narasumber (bergantian). Class ditulis lengkap supaya terbaca Tailwind.
    $warnaKartu = [
        ['card' => 'border-l-blue-400 bg-blue-50/50', 'badge' => 'bg-blue-100 text-blue-700', 'netto' => 'text-blue-600'],
        ['card' => 'border-l-emerald-400 bg-emerald-50/50', 'badge' => 'bg-emerald-100 text-emerald-700', 'netto' => 'text-emerald-600'],
        ['card' => 'border-l-amber-400 bg-amber-50/60', 'badge' => 'bg-amber-100 text-amber-700', 'netto' => 'text-amber-600'],
        ['card' => 'border-l-violet-400 bg-violet-50/50', 'badge' => 'bg-violet-100 text-violet-700', 'netto' => 'text-violet-600'],
        ['card' => 'border-l-rose-400 bg-rose-50/50', 'badge' => 'bg-rose-100 text-rose-700', 'netto' => 'text-rose-600'],
    ];

    // Cadangan kalau API wilayah tidak bisa diakses
    $provinsiCadangan = ['Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Kepulauan Riau', 'Jambi', 'Sumatera Selatan', 'Kepulauan Bangka Belitung', 'Bengkulu', 'Lampung', 'DKI Jakarta', 'Banten', 'Jawa Barat', 'Jawa Tengah', 'DI Yogyakarta', 'Jawa Timur', 'Bali', 'Nusa Tenggara Barat', 'Nusa Tenggara Timur', 'Kalimantan Barat', 'Kalimantan Tengah', 'Kalimantan Selatan', 'Kalimantan Timur', 'Kalimantan Utara', 'Sulawesi Utara', 'Gorontalo', 'Sulawesi Tengah', 'Sulawesi Barat', 'Sulawesi Selatan', 'Sulawesi Tenggara', 'Maluku', 'Maluku Utara', 'Papua', 'Papua Barat', 'Papua Barat Daya', 'Papua Selatan', 'Papua Tengah', 'Papua Pegunungan'];

    $formConfig = [
        'rows' => array_values($rows),
        'baru' => $rowsBaru,
        'tarif' => array_values($tarifSbm),
        'warna' => $warnaKartu,
    ];

    $inputClass = 'w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition placeholder:text-gray-300';
    $selectClass = 'w-full appearance-none bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition';
    $labelClass = 'block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase';
    $subLabel = 'block text-xs text-gray-400 mb-1.5';
    $chevron = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>';
@endphp

<div class="py-8 bg-gradient-to-b from-gray-50/70 to-white min-h-full">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        @if ($errors->any())
            <div
                class="flex gap-2.5 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl mb-5 animate-[fadeIn_.2s_ease-out]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
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
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            <p>Hasilnya PDF 2 halaman: halaman 1 Daftar Honorarium Narasumber (rincian honor, pajak, netto, dan
                tanda tangan PPK), halaman 2 Daftar Hadir Narasumber.</p>
        </div>

        <form action="{{ $action }}" method="POST" class="space-y-5" x-data="nominatifForm(@js($formConfig))">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            {{-- ===== Section: Informasi Kegiatan ===== --}}
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
                        <h3 class="text-sm font-semibold text-gray-800">Informasi Kegiatan</h3>
                        <p class="text-xs text-gray-400">Uraian kegiatan, tanggal, dan lokasi yang tampil di dokumen</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}">Nama Kegiatan <span class="text-red-400">*</span></label>
                        <input type="text" name="uraian_kegiatan"
                            value="{{ old('uraian_kegiatan', $nominatif->uraian_kegiatan ?? '') }}" required
                            placeholder="Rapat Monitoring dan Evaluasi Nilai Ekonomi Karbon dan Gas Rumah Kaca"
                            class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Tanggal <span class="text-red-400">*</span></label>
                        <input type="date" name="tanggal"
                            value="{{ old('tanggal', optional($nominatif?->tanggal)->toDateString() ?? now()->toDateString()) }}"
                            required class="{{ $inputClass }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4"
                    x-data="lokasi(@js(['provinsi' => old('provinsi', $nominatif->provinsi ?? ''), 'kota' => old('kota', $nominatif->kota ?? ''), 'cadangan' => $provinsiCadangan]))">
                    <div>
                        <label class="{{ $labelClass }}">Provinsi <span class="text-red-400">*</span></label>
                        <input type="text" name="provinsi" list="daftar-provinsi" autocomplete="off" x-model="provinsi"
                            @input="ubahProvinsi()" required placeholder="Ketik atau pilih provinsi"
                            class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Kota/Kabupaten <span class="text-red-400">*</span></label>
                        <input type="text" name="kota" list="daftar-kota" autocomplete="off" x-model="kota" required
                            :disabled="!provinsi"
                            :placeholder="!provinsi ? 'Pilih provinsi dulu' : (loadingKota ? 'Memuat...' : 'Ketik atau pilih kota/kabupaten')"
                            class="{{ $inputClass }} disabled:bg-gray-50 disabled:cursor-not-allowed">
                    </div>

                    <datalist id="daftar-provinsi">
                        <template x-for="p in daftarProvinsi" :key="p.name">
                            <option :value="p.name"></option>
                        </template>
                    </datalist>
                    <datalist id="daftar-kota">
                        <template x-for="k in daftarKota" :key="k">
                            <option :value="k"></option>
                        </template>
                    </datalist>
                </div>
            </div>

            {{-- ===== Section: Penandatangan ===== --}}
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
                        <h3 class="text-sm font-semibold text-gray-800">Penandatangan</h3>
                        <p class="text-xs text-gray-400">Pegawai yang menandatangani dokumen</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="{{ $subLabel }}">PPK <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <select name="ppk_id" required data-placeholder="Cari PPK..."
                                class="js-searchable {{ $selectClass }}">
                                <option value="" disabled {{ old('ppk_id', $nominatif->ppk_id ?? '') ? '' : 'selected' }}>— Pilih —</option>
                                @forelse ($ppkList as $p)
                                    <option value="{{ $p->id }}" @selected(old('ppk_id', $nominatif->ppk_id ?? null) == $p->id)>
                                        {{ $p->nama_gelar ?? $p->nama }}
                                    </option>
                                @empty
                                    <option value="" disabled>Belum ada pegawai berlabel PPK</option>
                                @endforelse
                            </select>
                            {!! $chevron !!}
                        </div>
                    </div>
                    <div>
                        <label class="{{ $subLabel }}">Bendahara <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <select name="bendahara_id" required data-placeholder="Cari Bendahara..."
                                class="js-searchable {{ $selectClass }}">
                                <option value="" disabled {{ old('bendahara_id', $nominatif->bendahara_id ?? '') ? '' : 'selected' }}>— Pilih —</option>
                                @forelse ($bendaharaList as $p)
                                    <option value="{{ $p->id }}" @selected(old('bendahara_id', $nominatif->bendahara_id ?? null) == $p->id)>
                                        {{ $p->nama_gelar ?? $p->nama }}
                                    </option>
                                @empty
                                    <option value="" disabled>Belum ada pegawai berlabel Bendahara</option>
                                @endforelse
                            </select>
                            {!! $chevron !!}
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Section: Narasumber ===== --}}
            <div
                class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 sm:p-7 lg:p-9">
                <div class="flex items-center justify-between gap-3 mb-6">
                    <div class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Narasumber</h3>
                            <p class="text-xs text-gray-400">Bruto = honor x OJ. Pajak = bruto x tarif. Netto = bruto
                                dikurangi pajak.</p>
                        </div>
                    </div>
                    <span class="flex-shrink-0 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2.5 py-1"
                        x-text="rows.length + ' orang'"></span>
                </div>

                <div class="space-y-4">
                    <template x-for="(row, i) in rows" :key="row.k">
                        <div class="border border-gray-100 border-l-4 rounded-xl p-4 sm:p-5 space-y-4 shadow-sm"
                            :class="warna[i % warna.length].card">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span
                                        class="h-7 w-7 rounded-full text-xs font-bold flex items-center justify-center flex-shrink-0"
                                        :class="warna[i % warna.length].badge" x-text="i + 1"></span>
                                    <span class="text-sm font-semibold text-gray-700 truncate"
                                        x-text="row.nama || ('Narasumber ' + (i + 1))"></span>
                                </div>
                                <button type="button" @click="removeRow(i)" x-show="rows.length > 1"
                                    class="text-xs text-rose-500 hover:text-rose-700 font-medium px-2 py-1 rounded-lg hover:bg-rose-50 transition flex-shrink-0">Hapus</button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="{{ $subLabel }}">Nama <span class="text-red-400">*</span></label>
                                    <input type="text" :name="'peserta[' + i + '][nama]'" x-model="row.nama" required
                                        autocomplete="off" class="{{ $inputClass }} bg-white">
                                </div>
                                <div>
                                    <label class="{{ $subLabel }}">NPWP <span class="text-red-400">*</span></label>
                                    <input type="text" inputmode="numeric" :name="'peserta[' + i + '][npwp]'"
                                        x-model="row.npwp" required placeholder="00.000.000.0-000.000"
                                        class="{{ $inputClass }} bg-white">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-2">
                                    <label class="{{ $subLabel }}">Instansi <span class="text-red-400">*</span></label>
                                    <input type="text" :name="'peserta[' + i + '][instansi]'" x-model="row.instansi"
                                        required placeholder="Biro Perencanaan, Kementerian ..."
                                        class="{{ $inputClass }} bg-white">
                                </div>
                                <div>
                                    <label class="{{ $subLabel }}">Golongan <span class="text-red-400">*</span></label>
                                    <input type="text" list="daftar-golongan" :name="'peserta[' + i + '][golongan]'"
                                        x-model="row.golongan" @input="onGolongan(row)" required autocomplete="off"
                                        placeholder="IV/a" class="{{ $inputClass }} bg-white">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <div class="col-span-2 sm:col-span-1" x-data="{ open: false }"
                                    @click.outside="open = false" @keydown.escape="open = false">
                                    <label class="{{ $subLabel }}">Honor per OJ <span
                                            class="text-red-400">*</span></label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400 pointer-events-none">Rp</span>
                                        <input type="text" inputmode="numeric" :name="'peserta[' + i + '][honor]'"
                                            :value="fmt(row.honor)" @input="setHonor(row, $event)" @focus="open = true"
                                            autocomplete="off" required placeholder="0"
                                            class="w-full border border-gray-200 bg-white rounded-xl pl-9 pr-9 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition placeholder:text-gray-300">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none transition-transform"
                                            :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>

                                        {{-- Saran tarif: klik untuk mengisi, atau tetap ketik sendiri --}}
                                        <div x-show="open" x-cloak
                                            class="absolute z-20 top-full left-0 mt-1 w-full sm:w-96 bg-white border border-gray-200 rounded-xl shadow-lg py-1 text-sm">
                                            <p class="px-3.5 pt-1.5 pb-1 text-[11px] text-gray-400">Tarif honorarium
                                                narasumber, atau ketik nominal sendiri</p>
                                            <template x-for="t in tarif" :key="t.honor">
                                                <button type="button" @mousedown.prevent
                                                    @click="row.honor = String(t.honor); open = false"
                                                    class="w-full flex items-center justify-between gap-3 px-3.5 py-2 text-left hover:bg-blue-50 transition"
                                                    :class="Number(digits(row.honor)) === t.honor ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700'">
                                                    <span class="text-xs leading-snug" x-text="t.label"></span>
                                                    <span class="text-xs font-semibold flex-shrink-0"
                                                        x-text="'Rp ' + rupiah(t.honor)"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="{{ $subLabel }}">OJ <span class="text-red-400">*</span></label>
                                    <input type="number" min="1" step="1" inputmode="numeric"
                                        :name="'peserta[' + i + '][oj]'" x-model="row.oj" required
                                        class="{{ $inputClass }} bg-white">
                                </div>
                                <div>
                                    <label class="{{ $subLabel }}">Tarif pajak (%)</label>
                                    <input type="number" min="0" max="100" step="0.01" inputmode="decimal"
                                        :name="'peserta[' + i + '][pajak_persen]'" x-model="row.pajak_persen"
                                        @input="row.auto = false" placeholder="0" class="{{ $inputClass }} bg-white">
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 pt-3 border-t border-gray-200/70 text-xs">
                                <div>
                                    <div class="text-gray-400">Bruto</div>
                                    <div class="mt-0.5 text-sm font-semibold text-gray-800"
                                        x-text="'Rp ' + rupiah(bruto(row))"></div>
                                </div>
                                <div>
                                    <div class="text-gray-400">Pajak</div>
                                    <div class="mt-0.5 text-sm font-semibold text-gray-800"
                                        x-text="'Rp ' + rupiah(pajak(row))"></div>
                                </div>
                                <div>
                                    <div class="text-gray-400">Netto</div>
                                    <div class="mt-0.5 text-sm font-semibold" :class="warna[i % warna.length].netto"
                                        x-text="'Rp ' + rupiah(netto(row))"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <button type="button" @click="addRow()"
                    class="mt-4 w-full inline-flex items-center justify-center gap-1.5 border border-dashed border-blue-200 text-blue-600 hover:bg-blue-50 active:scale-[0.99] font-semibold px-3.5 py-3 rounded-xl text-xs transition-all">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Narasumber
                </button>

                {{-- Ringkasan total --}}
                <div class="mt-8 rounded-2xl bg-gray-50/80 border border-gray-100 p-5 sm:p-6">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Ringkasan Total</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                        <div class="bg-white border border-gray-100 rounded-xl px-5 py-4">
                            <div class="text-xs text-gray-400 mb-1.5">Total bruto</div>
                            <div class="text-lg font-semibold text-gray-900 tabular-nums"
                                x-text="'Rp ' + rupiah(sum('bruto'))"></div>
                        </div>

                        <div class="bg-white border border-gray-100 rounded-xl px-5 py-4">
                            <div class="text-xs text-gray-400 mb-1.5">Total pajak</div>
                            <div class="text-lg font-semibold text-gray-900 tabular-nums"
                                x-text="'Rp ' + rupiah(sum('pajak'))"></div>
                        </div>

                        <div class="bg-blue-50 border border-blue-100 rounded-xl px-5 py-4">
                            <div class="text-xs text-blue-500 mb-1.5">Total netto</div>
                            <div class="text-lg font-bold text-blue-700 tabular-nums"
                                x-text="'Rp ' + rupiah(sum('netto'))"></div>
                        </div>
                    </div>
                </div>
            </div>

            <datalist id="daftar-golongan">
                @foreach (['IV/a', 'IV/b', 'IV/c', 'IV/d', 'III/a', 'III/b', 'III/c', 'III/d', 'II/a', 'II/b', 'II/c', 'II/d', 'I/a', 'I/b', 'I/c', 'I/d'] as $gol)
                    <option value="{{ $gol }}"></option>
                @endforeach
            </datalist>

            {{-- Actions --}}
            <div
                class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 bg-white rounded-2xl border border-gray-100 shadow-sm px-5 sm:px-7 py-4">
                <a href="{{ route('nominatif.index') }}"
                    class="w-full sm:w-auto text-center text-sm text-gray-400 hover:text-gray-600 transition">
                    &larr; Batal
                </a>
                <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-semibold px-5 py-2.5 rounded-xl text-sm shadow-md shadow-blue-600/20 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ $submitLabel }}
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }

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

    /* Matikan panah bawaan browser di semua <select>, cukup panah custom (svg) */
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
    document.addEventListener('alpine:init', function () {
        // ===== Provinsi -> Kota/Kabupaten (data dari API wilayah Indonesia) =====
        // Kalau API tidak bisa diakses, provinsi pakai daftar cadangan dan kota bisa diketik bebas.
        Alpine.data('lokasi', function (cfg) {
            const API = 'https://emsifa.github.io/api-wilayah-indonesia/api';
            return {
                provinsi: cfg.provinsi || '',
                kota: cfg.kota || '',
                daftarProvinsi: cfg.cadangan.map(function (n) { return { id: null, name: n }; }),
                daftarKota: [],
                loadingKota: false,

                async init() {
                    try {
                        const res = await fetch(API + '/provinces.json');
                        const data = await res.json();
                        this.daftarProvinsi = data.map((p) => ({ id: p.id, name: this.judul(p.name) }));
                    } catch (e) { /* pakai daftar cadangan */ }
                    // Mode edit: muat kota untuk provinsi tersimpan, kota lama tetap dipertahankan
                    if (this.provinsi) await this.muatKota(true);
                },

                // "KOTA JAKARTA PUSAT" -> "Kota Jakarta Pusat", singkatan tetap kapital
                judul(s) {
                    return String(s).toLowerCase()
                        .replace(/\b\w/g, function (c) { return c.toUpperCase(); })
                        .replace(/\bDki\b/, 'DKI').replace(/\bDi\b/, 'DI');
                },

                async ubahProvinsi() {
                    await this.muatKota(false);
                },

                async muatKota(pertahankan) {
                    const p = this.daftarProvinsi.find((x) =>
                        x.id && x.name.toLowerCase() === this.provinsi.trim().toLowerCase());
                    if (!p) {
                        this.daftarKota = [];
                        return;
                    }
                    this.loadingKota = true;
                    try {
                        const res = await fetch(API + '/regencies/' + p.id + '.json');
                        const data = await res.json();
                        this.daftarKota = data.map((k) => this.judul(k.name));
                        // Ganti provinsi -> kosongkan kota kalau tidak ada di provinsi baru
                        if (!pertahankan && !this.daftarKota.includes(this.kota)) this.kota = '';
                    } catch (e) {
                        this.daftarKota = [];
                    }
                    this.loadingKota = false;
                },
            };
        });

        Alpine.data('nominatifForm', function (cfg) {
            return {
                seq: 0,
                rows: [],
                tarif: cfg.tarif,
                warna: cfg.warna,

                init() {
                    this.rows = cfg.rows.map((r) => this.baris(r, cfg.baru));
                },

                // Bentuk seragam untuk satu baris narasumber.
                // auto = tarif pajak masih boleh diisi otomatis dari golongan.
                baris(r, baru) {
                    r = r || {};
                    return {
                        k: ++this.seq,
                        nama: r.nama || '',
                        npwp: r.npwp || '',
                        instansi: r.instansi || '',
                        golongan: r.golongan || '',
                        honor: this.digits(r.honor),
                        oj: (r.oj === undefined || r.oj === null || r.oj === '') ? 1 : r.oj,
                        pajak_persen: (r.pajak_persen === undefined || r.pajak_persen === null) ? '' : r.pajak_persen,
                        auto: !!baru,
                    };
                },

                addRow() {
                    this.rows.push(this.baris({}, true));
                    this.$nextTick(() => {
                        const inputs = this.$root.querySelectorAll('input[name$="[nama]"]');
                        if (inputs.length) inputs[inputs.length - 1].focus();
                    });
                },

                removeRow(i) {
                    if (this.rows.length > 1) this.rows.splice(i, 1);
                },

                // Saran tarif pajak dari golongan: IV = 15%, III = 5%, I/II = 0%.
                // Hanya mengisi kalau tarif belum diubah manual, dan tetap bisa diedit.
                onGolongan(row) {
                    if (!row.auto) return;
                    const g = String(row.golongan || '').trim().toUpperCase();
                    let persen = null;
                    if (/^(IV|4)/.test(g)) persen = 15;
                    else if (/^(III|3)/.test(g)) persen = 5;
                    else if (/^(II|I|1|2)/.test(g)) persen = 0;
                    if (persen !== null) row.pajak_persen = persen;
                },

                digits(v) {
                    return String(v === null || v === undefined ? '' : v).replace(/\D/g, '');
                },

                // Angka dengan titik ribuan; kosong tetap kosong
                fmt(v) {
                    const d = this.digits(v);
                    return d === '' ? '' : Number(d).toLocaleString('id-ID');
                },

                rupiah(n) {
                    return Number(n || 0).toLocaleString('id-ID');
                },

                setHonor(row, e) {
                    row.honor = this.digits(e.target.value);
                    e.target.value = this.fmt(row.honor);
                },

                bruto(row) {
                    return (Number(this.digits(row.honor)) || 0) * (parseInt(row.oj, 10) || 0);
                },

                pajak(row) {
                    const persen = parseFloat(String(row.pajak_persen).replace(',', '.')) || 0;
                    return Math.round(this.bruto(row) * persen / 100);
                },

                netto(row) {
                    return this.bruto(row) - this.pajak(row);
                },

                sum(fn) {
                    return this.rows.reduce((s, r) => s + this[fn](r), 0);
                },
            };
        });
    });

    // ===== Searchable select: ubah <select> jadi input yang bisa diketik untuk filter opsi =====
    (function () {
        function enhance(select) {
            if (!select || select.dataset.enhanced) return;
            select.dataset.enhanced = '1';

            const container = select.parentElement; // div.relative yang juga berisi ikon panah
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
                        e.preventDefault(); // biar blur tidak menutup list sebelum klik terpilih
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

            // Bungkus input + list dalam wrapper "relative" sendiri supaya dropdown
            // selalu muncul tepat di bawah kotaknya.
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