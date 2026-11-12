<x-app-layout title="Nominatif Custom">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-600/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ ($nominatif ?? null) ? 'Edit Nominatif Custom' : 'Buat Nominatif Custom' }}
            </h2>
        </div>
    </x-slot>

    @php
        $nominatif = $nominatif ?? null;

        $ppkList = $pegawaiList->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'PPK'))->values();
        if ($ppkList->isEmpty()) {
            $ppkList = $pegawaiList;
        }

        $bendaharaList = $pegawaiList->filter(fn($p) => str_contains($p->role_penandatangan ?? '', 'Bendahara'))->values();
        if ($bendaharaList->isEmpty()) {
            $bendaharaList = $pegawaiList;
        }

        // Item awal: dari old() kalau validasi gagal, dari database kalau edit, atau satu baris kosong
        $rows = old('items');
        if ($rows === null) {
            $rows = $nominatif
                ? $nominatif->items->map(fn($i) => [
                    'uraian' => $i->uraian,
                    'harga' => $i->harga,
                    'jumlah' => $i->jumlah,
                    'satuan' => $i->satuan,
                    'ppn_persen' => $i->ppn_persen,
                    'pph22_persen' => $i->pph22_persen,
                    'pph23_persen' => $i->pph23_persen,
                ])->values()->all()
                : [];
        }
        if (empty($rows)) {
            $rows = [['jumlah' => 1, 'satuan' => 'PKT']];
        }

        $inputClass = 'w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition placeholder:text-gray-300';
        $selectClass = 'w-full appearance-none bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition';
        $labelClass = 'block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase';
        $subLabel = 'block text-xs text-gray-400 mb-1.5';
        $chevron = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>';
    @endphp

    {{-- Latar halaman --}}
    <div class="bg-gradient-to-b from-gray-50/70 to-white min-h-full">
        {{-- Wrapper konten: satu-satunya sumber padding vertikal --}}
        <div class="py-6 sm:py-8 px-4 sm:px-6 lg:px-8 max-w-screen-2xl mx-auto">

            @if ($errors->any())
                <div
                    class="flex gap-2.5 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl mb-5 animate-[fadeIn_.2s_ease-out]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

            {{-- Grid 5 bagian: form (2) + preview (3). x-data di sini supaya preview bisa baca `rows`. --}}
            <div class="grid grid-cols-1 xl:grid-cols-5 gap-6 items-start"
                x-data="customForm(@js(array_values($rows)))">

                <form action="{{ $action }}" method="POST" class="space-y-5 min-w-0 xl:col-span-2">
                    @csrf
                    @if ($method !== 'POST')
                        @method($method)
                    @endif

                    {{-- ===== Section: Informasi Nominatif ===== --}}
                    <div
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 sm:p-6">
                        <div class="flex items-center gap-2.5 mb-6">
                            <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800">Informasi Nominatif</h3>
                                <p class="text-xs text-gray-400">Judul dan tanggal yang tampil di dokumen</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <label class="{{ $labelClass }}">Judul Nominatif <span
                                        class="text-red-400">*</span></label>
                                <input type="text" name="uraian_kegiatan"
                                    value="{{ old('uraian_kegiatan', $nominatif->uraian_kegiatan ?? '') }}" required
                                    placeholder="Pembayaran Fullboard Hotel ... dalam rangka ..."
                                    class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">Tanggal <span class="text-red-400">*</span></label>
                                <input type="date" name="tanggal"
                                    value="{{ old('tanggal', optional($nominatif?->tanggal)->toDateString() ?? now()->toDateString()) }}"
                                    required class="{{ $inputClass }}">
                            </div>
                        </div>
                    </div>

                    {{-- ===== Section: Penandatangan ===== --}}
                    <div
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 sm:p-6">
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
                                        <option value="" disabled
                                            {{ old('ppk_id', $nominatif->ppk_id ?? '') ? '' : 'selected' }}>— Pilih —
                                        </option>
                                        @foreach ($ppkList as $p)
                                            <option value="{{ $p->id }}" data-nip="{{ $p->nip }}"
                                                @selected(old('ppk_id', $nominatif->ppk_id ?? null) == $p->id)>
                                                {{ $p->nama_gelar ?? $p->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    {!! $chevron !!}
                                </div>
                            </div>
                            <div>
                                <label class="{{ $subLabel }}">Bendahara <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <select name="bendahara_id" required data-placeholder="Cari Bendahara..."
                                        class="js-searchable {{ $selectClass }}">
                                        <option value="" disabled
                                            {{ old('bendahara_id', $nominatif->bendahara_id ?? '') ? '' : 'selected' }}>
                                            — Pilih —</option>
                                        @foreach ($bendaharaList as $p)
                                            <option value="{{ $p->id }}" data-nip="{{ $p->nip }}"
                                                @selected(old('bendahara_id', $nominatif->bendahara_id ?? null) == $p->id)>
                                                {{ $p->nama_gelar ?? $p->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    {!! $chevron !!}
                                </div>
                            </div>
                            <div>
                                <label class="{{ $subLabel }}">Penanggung Jawab Kegiatan <span
                                        class="text-red-400">*</span></label>
                                <div class="relative">
                                    <select name="penanggung_jawab_id" required data-placeholder="Cari pegawai..."
                                        class="js-searchable {{ $selectClass }}">
                                        <option value="" disabled
                                            {{ old('penanggung_jawab_id', $nominatif->penanggung_jawab_id ?? '') ? '' : 'selected' }}>
                                            — Pilih —</option>
                                        @foreach ($pegawaiList as $p)
                                            <option value="{{ $p->id }}" data-nip="{{ $p->nip }}"
                                                @selected(old('penanggung_jawab_id', $nominatif->penanggung_jawab_id ?? null) == $p->id)>
                                                {{ $p->nama_gelar ?? $p->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    {!! $chevron !!}
                                </div>
                            </div>
                            <div>
                                <label class="{{ $subLabel }}">PIC <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <select name="pic_id" required data-placeholder="Cari PIC..."
                                        class="js-searchable {{ $selectClass }}">
                                        <option value="" disabled
                                            {{ old('pic_id', $nominatif->pic_id ?? '') ? '' : 'selected' }}>
                                            — Pilih —</option>
                                        @foreach ($pegawaiList as $p)
                                            <option value="{{ $p->id }}"
                                                @selected(old('pic_id', $nominatif->pic_id ?? null) == $p->id)>
                                                {{ $p->nama_gelar ?? $p->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    {!! $chevron !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Section: Item (satu kolom + preset pajak) ===== --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sm:p-6">
                        <div class="flex items-center justify-between gap-3 mb-5">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="h-8 w-8 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800">Rincian Item</h3>
                                    <p class="text-xs text-gray-400">Isi uraian, harga, dan jumlah. Pajak pilih lewat
                                        tombol.</p>
                                </div>
                            </div>
                            <span
                                class="flex-shrink-0 text-xs font-medium text-amber-700 bg-amber-50 rounded-full px-2.5 py-1"
                                x-text="rows.length + ' item'"></span>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(row, i) in rows" :key="row.k">
                                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 space-y-4">

                                    {{-- Judul item + hapus --}}
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wide"
                                            x-text="'Item ' + (i + 1)"></span>
                                        <button type="button" @click="removeRow(i)" x-show="rows.length > 1"
                                            class="text-xs text-rose-500 hover:text-rose-700 font-medium">Hapus</button>
                                    </div>

                                    {{-- Uraian --}}
                                    <div>
                                        <label class="{{ $subLabel }}">Uraian <span
                                                class="text-red-400">*</span></label>
                                        <textarea rows="2" :name="'items[' + i + '][uraian]'" x-model="row.uraian"
                                            required placeholder="Paket Fullboard Meeting Ibis Styles Bogor Pajajaran"
                                            class="{{ $inputClass }} bg-white resize-y"></textarea>
                                    </div>

                                    {{-- Harga x Jumlah Satuan --}}
                                    <div class="flex flex-wrap items-end gap-3">
                                        <div class="flex-1 min-w-[160px]">
                                            <label class="{{ $subLabel }}">Harga <span
                                                    class="text-red-400">*</span></label>
                                            <div class="relative">
                                                <span
                                                    class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400">Rp</span>
                                                <input type="text" inputmode="numeric"
                                                    :name="'items[' + i + '][harga]'" :value="fmt(row.harga)"
                                                    @input="setHarga(row, $event)" autocomplete="off" required
                                                    placeholder="0"
                                                    class="w-full border border-gray-200 bg-white rounded-xl pl-9 pr-3 py-2.5 text-sm text-right tabular-nums focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none">
                                            </div>
                                        </div>
                                        <span class="pb-3 text-gray-400 text-sm">&times;</span>
                                        <div class="w-24">
                                            <label class="{{ $subLabel }}">Jumlah</label>
                                            <input type="number" min="0.01" step="0.01" inputmode="decimal"
                                                :name="'items[' + i + '][jumlah]'" x-model="row.jumlah" required
                                                class="{{ $inputClass }} bg-white text-center">
                                        </div>
                                        <div class="w-28">
                                            <label class="{{ $subLabel }}">Satuan</label>
                                            <input type="text" :name="'items[' + i + '][satuan]'"
                                                x-model="row.satuan" autocomplete="off" placeholder="PKT"
                                                class="{{ $inputClass }} bg-white">
                                        </div>
                                    </div>

                                    {{-- Preset pajak --}}
                                    <div class="space-y-3">
                                        <div>
                                            <label class="{{ $subLabel }}">PPN</label>
                                            <div class="flex flex-wrap gap-2">
                                                <template x-for="p in [0, 11, 12]" :key="'ppn' + p">
                                                    <button type="button" @click="row.ppn_persen = p"
                                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition"
                                                        :class="pct(row.ppn_persen) === p ?
                                                            'bg-blue-600 border-blue-600 text-white' :
                                                            'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'"
                                                        x-text="p + '%'"></button>
                                                </template>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="{{ $subLabel }}">PPh 22</label>
                                            <div class="flex flex-wrap gap-2">
                                                <template x-for="p in [0, 1.5, 3]" :key="'p22' + p">
                                                    <button type="button" @click="row.pph22_persen = p"
                                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition"
                                                        :class="pct(row.pph22_persen) === p ?
                                                            'bg-blue-600 border-blue-600 text-white' :
                                                            'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'"
                                                        x-text="p + '%'"></button>
                                                </template>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="{{ $subLabel }}">PPh 23</label>
                                            <div class="flex flex-wrap gap-2">
                                                <template x-for="p in [0, 2, 4]" :key="'p23' + p">
                                                    <button type="button" @click="row.pph23_persen = p"
                                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition"
                                                        :class="pct(row.pph23_persen) === p ?
                                                            'bg-blue-600 border-blue-600 text-white' :
                                                            'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'"
                                                        x-text="p + '%'"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Hidden: nilai pajak tetap terkirim --}}
                                    <input type="hidden" :name="'items[' + i + '][ppn_persen]'"
                                        :value="row.ppn_persen">
                                    <input type="hidden" :name="'items[' + i + '][pph22_persen]'"
                                        :value="row.pph22_persen">
                                    <input type="hidden" :name="'items[' + i + '][pph23_persen]'"
                                        :value="row.pph23_persen">

                                    {{-- Total item --}}
                                    <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                                        <span class="text-xs text-gray-400">Total item</span>
                                        <span class="text-base font-bold text-gray-900 tabular-nums"
                                            x-text="'Rp ' + rupiah(total(row))"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="addRow()"
                            class="mt-4 w-full inline-flex items-center justify-center gap-1.5 border border-dashed border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold px-3.5 py-3 rounded-xl text-xs transition">
                            + Tambah Item
                        </button>

                        {{-- Ringkasan total --}}
                        <div class="mt-8 rounded-2xl bg-gray-50/80 border border-gray-100 p-5 sm:p-6">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Ringkasan Total
                            </p>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-white border border-gray-100 rounded-xl px-5 py-4">
                                    <div class="text-xs text-gray-400 mb-1.5">Sub total</div>
                                    <div class="text-base font-semibold text-gray-900 tabular-nums whitespace-nowrap"
                                        x-text="'Rp ' + rupiah(sum('sub'))"></div>
                                </div>
                                <div class="bg-white border border-gray-100 rounded-xl px-5 py-4">
                                    <div class="text-xs text-gray-400 mb-1.5">PPN</div>
                                    <div class="text-base font-semibold text-gray-900 tabular-nums whitespace-nowrap"
                                        x-text="'Rp ' + rupiah(sum('ppn'))"></div>
                                </div>
                                <div class="bg-white border border-gray-100 rounded-xl px-5 py-4">
                                    <div class="text-xs text-gray-400 mb-1.5">PPh 22</div>
                                    <div class="text-base font-semibold text-gray-900 tabular-nums whitespace-nowrap"
                                        x-text="'Rp ' + rupiah(sum('pph22'))"></div>
                                </div>
                                <div class="bg-white border border-gray-100 rounded-xl px-5 py-4">
                                    <div class="text-xs text-gray-400 mb-1.5">PPh 23</div>
                                    <div class="text-base font-semibold text-gray-900 tabular-nums whitespace-nowrap"
                                        x-text="'Rp ' + rupiah(sum('pph23'))"></div>
                                </div>
                                <div class="col-span-2 bg-white border border-gray-100 rounded-xl px-5 py-4">
                                    <div class="text-xs text-gray-400 mb-1.5">Total dibayar</div>
                                    <div class="text-base font-semibold text-gray-900 tabular-nums whitespace-nowrap"
                                        x-text="'Rp ' + rupiah(sum('total'))"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div
                        class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 bg-white rounded-2xl border border-gray-100 shadow-sm px-5 sm:px-6 py-4">
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

                {{-- Preview (kolom kanan, 3 bagian) --}}
                @include('nominatif.custom._preview-custom')
            </div>
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
            Alpine.data('customForm', function (initial) {
                return {
                    seq: 0,
                    rows: [],

                    init() {
                        this.rows = initial.map((r) => this.baris(r));
                    },

                    // Bentuk seragam untuk satu baris item
                    baris(r) {
                        r = r || {};
                        return {
                            k: ++this.seq,
                            uraian: r.uraian || '',
                            harga: this.digits(r.harga),
                            jumlah: (r.jumlah === undefined || r.jumlah === null || r.jumlah === '') ? 1 : r.jumlah,
                            satuan: r.satuan || '',
                            ppn_persen: r.ppn_persen ?? 0,
                            pph22_persen: r.pph22_persen ?? 0,
                            pph23_persen: r.pph23_persen ?? 0,
                        };
                    },

                    addRow() {
                        this.rows.push(this.baris({ satuan: 'PKT' }));
                        this.$nextTick(() => {
                            const inputs = this.$root.querySelectorAll('textarea[name$="[uraian]"]');
                            if (inputs.length) inputs[inputs.length - 1].focus();
                        });
                    },

                    removeRow(i) {
                        if (this.rows.length > 1) this.rows.splice(i, 1);
                    },

                    digits(v) {
                        return window.fmt.digits(v);
                    },

                    // Angka dengan titik ribuan; kosong tetap kosong
                    fmt(v) {
                        return window.fmt.input(v);
                    },

                    rupiah(n) {
                        return window.fmt.num(n);
                    },

                    setHarga(row, e) {
                        row.harga = this.digits(e.target.value);
                        e.target.value = this.fmt(row.harga);
                    },

                    pct(v) {
                        return parseFloat(String(v === null || v === undefined ? '' : v).replace(',', '.')) || 0;
                    },

                    sub(row) {
                        return Math.round((Number(this.digits(row.harga)) || 0) * (parseFloat(row.jumlah) || 0));
                    },
                    ppn(row) {
                        return Math.round(this.sub(row) * this.pct(row.ppn_persen) / 100);
                    },
                    pph22(row) {
                        return Math.round(this.sub(row) * this.pct(row.pph22_persen) / 100);
                    },
                    pph23(row) {
                        return Math.round(this.sub(row) * this.pct(row.pph23_persen) / 100);
                    },
                    total(row) {
                        return this.sub(row) + this.ppn(row) - this.pph22(row) - this.pph23(row);
                    },

                    sum(fn) {
                        return this.rows.reduce((s, r) => s + this[fn](r), 0);
                    },
                };
            });
        });
    </script>
</x-app-layout>