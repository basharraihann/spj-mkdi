@php
    $dasar = old('dasar', $st->dasar ?: ['']);
    $untuk = old('untuk', $st->untuk ?: ['']);
    $pesertaInit = old('peserta', $peserta);
@endphp

<div x-data="{
        dasar: @js(array_values($dasar)),
        untuk: @js(array_values($untuk)),
        peserta: @js(array_values($pesertaInit)),
        pegawai: @js($pegawaiOptions),
        pick: '',
        addPegawai() {
            const p = this.pegawai.find(x => String(x.id) === String(this.pick));
            if (!p) return;
            if (this.peserta.some(x => String(x.pegawai_id) === String(p.id))) { this.pick = ''; return; }
            this.peserta.push({ pegawai_id: p.id, nama: p.nama, nip: p.nip, jabatan: p.jabatan });
            this.pick = '';
        }
    }" class="space-y-6">

    @if ($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Info surat --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h3 class="font-semibold text-gray-800">Informasi Surat</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tempat</label>
                <input type="text" name="tempat" value="{{ old('tempat', $st->tempat) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal Surat</label>
                <input type="date" name="tanggal" value="{{ old('tanggal', optional($st->tanggal)->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Kode Klasifikasi</label>
                <input type="text" name="kode_klasifikasi" value="{{ old('kode_klasifikasi', $st->kode_klasifikasi) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                <p class="text-xs text-gray-500 mt-1">Nomor otomatis dibuat saat diterbitkan.</p>
            </div>
        </div>
    </div>

    {{-- Menimbang & Dasar --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h3 class="font-semibold text-gray-800">Menimbang &amp; Dasar</h3>
        <div>
            <label class="block text-sm font-medium text-gray-700">Menimbang</label>
            <textarea name="menimbang" rows="3" class="mt-1 w-full rounded-lg border-gray-300 text-sm"
                placeholder="Bahwa dalam rangka melaksanakan ... dianggap perlu dibuatkan surat tugas ...">{{ old('menimbang', $st->menimbang) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dasar</label>
            <template x-for="(d, i) in dasar" :key="i">
                <div class="flex gap-2 mb-2">
                    <span class="pt-2 text-sm text-gray-500 w-5" x-text="(i+1)+'.'"></span>
                    <textarea name="dasar[]" rows="2" x-model="dasar[i]" class="flex-1 rounded-lg border-gray-300 text-sm"></textarea>
                    <button type="button" @click="dasar.splice(i,1)" class="text-red-500 text-sm px-2">Hapus</button>
                </div>
            </template>
            <button type="button" @click="dasar.push('')" class="text-sm text-blue-600 hover:underline">+ Tambah dasar</button>
        </div>
    </div>

    {{-- Kepada & Untuk --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h3 class="font-semibold text-gray-800">Memberi Tugas</h3>
        <div>
            <label class="block text-sm font-medium text-gray-700">Kepada</label>
            <input type="text" name="kepada" value="{{ old('kepada', $st->kepada) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Untuk</label>
            <template x-for="(u, i) in untuk" :key="i">
                <div class="flex gap-2 mb-2">
                    <span class="pt-2 text-sm text-gray-500 w-5" x-text="(i+1)+'.'"></span>
                    <textarea name="untuk[]" rows="2" x-model="untuk[i]" class="flex-1 rounded-lg border-gray-300 text-sm"></textarea>
                    <button type="button" @click="untuk.splice(i,1)" class="text-red-500 text-sm px-2">Hapus</button>
                </div>
            </template>
            <button type="button" @click="untuk.push('')" class="text-sm text-blue-600 hover:underline">+ Tambah poin</button>
        </div>
    </div>

    {{-- Peserta --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h3 class="font-semibold text-gray-800">Daftar Peserta (Lampiran)</h3>
        <div class="flex flex-wrap gap-2">
            <select x-model="pick" class="flex-1 min-w-[12rem] rounded-lg border-gray-300 text-sm">
                <option value="">— pilih dari data pegawai —</option>
                <template x-for="p in pegawai" :key="p.id">
                    <option :value="p.id" x-text="p.nama + (p.nip ? ' (' + p.nip + ')' : '')"></option>
                </template>
            </select>
            <button type="button" @click="addPegawai()" class="px-3 py-2 rounded-lg bg-blue-600 text-white text-sm">Tambah</button>
            <button type="button" @click="peserta.push({pegawai_id:null,nama:'',nip:'',jabatan:''})" class="px-3 py-2 rounded-lg border text-sm">Isi manual</button>
        </div>

        <div class="space-y-2">
            <template x-for="(p, i) in peserta" :key="i">
                <div class="grid md:grid-cols-12 gap-2 items-start border rounded-lg p-3">
                    <input type="hidden" :name="`peserta[${i}][pegawai_id]`" :value="p.pegawai_id ?? ''">
                    <div class="md:col-span-4">
                        <input type="text" :name="`peserta[${i}][nama]`" x-model="p.nama" placeholder="Nama" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div class="md:col-span-3">
                        <input type="text" :name="`peserta[${i}][nip]`" x-model="p.nip" placeholder="NIP (opsional)" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div class="md:col-span-4">
                        <input type="text" :name="`peserta[${i}][jabatan]`" x-model="p.jabatan" placeholder="Jabatan" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div class="md:col-span-1 text-right">
                        <button type="button" @click="peserta.splice(i,1)" class="text-red-500 text-sm">Hapus</button>
                    </div>
                </div>
            </template>
            <p x-show="peserta.length === 0" class="text-sm text-gray-500">Belum ada peserta.</p>
        </div>
    </div>

    {{-- Penandatangan --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h3 class="font-semibold text-gray-800">Penandatangan</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700">Jabatan</label>
                <input type="text" name="jabatan_penandatangan" value="{{ old('jabatan_penandatangan', $st->jabatan_penandatangan) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Nama</label>
                <input type="text" name="nama_penandatangan" value="{{ old('nama_penandatangan', $st->nama_penandatangan) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">NIP (opsional)</label>
                <input type="text" name="nip_penandatangan" value="{{ old('nip_penandatangan', $st->nip_penandatangan) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-2 pb-4">
        <a href="{{ route('surat-tugas.index') }}" class="px-4 py-2 rounded-lg border text-sm">Batal</a>
        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm">Simpan Draf</button>
    </div>
</div>