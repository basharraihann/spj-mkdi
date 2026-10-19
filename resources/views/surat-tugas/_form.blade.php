@php
    $dasarTetap = 'Daftar Isian Pelaksanaan Anggaran (DIPA) Satuan Kerja Kementerian Koordinator Bidang Pangan Tahun 2025 Nomor: SP DIPA-131.01-0/2026 tanggal 1 Desember 2025';

    $kabKotaMap = $kabKotaMap ?? [];

    // dasar tambahan saja (dasar tetap dibuang supaya tidak dobel saat edit / validasi gagal)
    $dasarLain = collect(old('dasar', $st->dasar ?: []))
        ->reject(fn($d) => str_starts_with(trim((string) $d), 'Daftar Isian Pelaksanaan Anggaran'))
        ->values()->all();

    $untuk = old('untuk', $st->untuk ?: ['']);

    $pesertaInit = collect(old('peserta', $peserta))
        ->map(fn($p) => array_merge(['jenis' => 'pns'], (array) $p))
        ->values()->all();

    $provinsiTerpilih = old('provinsi', $st->provinsi ?? '');
    $kotaTerpilih = old('kota', $st->kota ?? '');
@endphp

<div x-data="{
        dasar: @js($dasarLain),
        untuk: @js(array_values($untuk)),
        peserta: @js($pesertaInit),
        pegawai: @js($pegawaiOptions),
        penandatangan: @js($penandatanganOptions),
        pick: { pns: '', non_pns: '' },
        kode: @js(old('kode_klasifikasi', $st->kode_klasifikasi)),
        tanggal: @js(old('tanggal', optional($st->tanggal)->format('Y-m-d'))),
        nomorUrut: @js($st->nomor_urut),
        nomor: @js($st->nomor),
        tahunNomor: @js($st->tahun),
        ambilUrl: @js(route('surat-tugas.nomor-berikutnya')),
        mintaNomor: false,
        loadingNomor: false,
        errNomor: '',
        ttd: @js([
            'nama' => old('nama_penandatangan', $st->nama_penandatangan),
            'nip' => old('nip_penandatangan', $st->nip_penandatangan),
            'jabatan' => old('jabatan_penandatangan', $st->jabatan_penandatangan),
        ]),
        pickTtd: '',

        async ambilNomor() {
            this.loadingNomor = true; this.errNomor = '';
            try {
                const res = await fetch(this.ambilUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ tanggal: this.tanggal, kode_klasifikasi: this.kode }),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal mengambil nomor.');
                this.nomor = data.nomor;
                this.mintaNomor = true;
            } catch (e) {
                this.errNomor = e.message;
            } finally {
                this.loadingNomor = false;
            }
        },

        pool(jenis) { return this.pegawai.filter(x => x.status === jenis); },
        count(jenis) { return this.peserta.filter(x => x.jenis === jenis).length; },
        addPegawai(jenis) {
            const p = this.pegawai.find(x => String(x.id) === String(this.pick[jenis]));
            if (!p) return;
            if (this.peserta.some(x => String(x.pegawai_id) === String(p.id))) { this.pick[jenis] = ''; return; }
            this.peserta.push({ jenis: jenis, pegawai_id: p.id, nama: p.nama, nip: p.nip || '', jabatan: p.jabatan || '' });
            this.pick[jenis] = '';
        },
        addManual(jenis) {
            this.peserta.push({ jenis: jenis, pegawai_id: null, nama: '', nip: '', jabatan: '' });
        },
        setTtd() {
            const p = this.penandatangan.find(x => String(x.id) === String(this.pickTtd));
            this.ttd = p
                ? { nama: p.nama, nip: p.nip || '', jabatan: p.jabatan || '' }
                : { nama: '', nip: '', jabatan: '' };
        }
    }" x-init="pickTtd = (penandatangan.find(x => x.nama === ttd.nama)?.id) ?? '';
            $watch('tanggal', () => { if (!nomorUrut) mintaNomor = false; });
            $watch('kode', () => { if (!nomorUrut) mintaNomor = false; })" class="space-y-6">

    @if ($errors->any())
        <div class="rounded-lg border p-4 text-sm" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c;">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $e)
            <li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Info surat --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h3 class="font-semibold text-gray-800">Informasi Surat</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tempat</label>
                <input type="text" name="tempat" value="{{ old('tempat', $st->tempat) }}"
                    class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal Surat</label>
                <input type="date" name="tanggal" x-model="tanggal" :min="nomorUrut ? tahunNomor + '-01-01' : null"
                    :max="nomorUrut ? tahunNomor + '-12-31' : null"
                    class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Nomor ST</label>
                <div class="mt-1 flex gap-2">
                    <input type="text" name="kode_klasifikasi" x-model="kode" placeholder="Kode klasifikasi"
                        :readonly="!!nomorUrut" :class="nomorUrut ? 'bg-gray-50' : ''"
                        class="w-full rounded-lg border-gray-300 text-sm">
                    <button type="button" @click="ambilNomor()"
                        :disabled="loadingNomor || !!nomorUrut || !kode || !tanggal"
                        class="px-3 py-2 rounded-lg bg-blue-600 text-white text-sm whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loadingNomor">Ambil Nomor</span>
                        <span x-show="loadingNomor">Memproses...</span>
                    </button>
                </div>

                <input type="hidden" name="minta_nomor" :value="mintaNomor ? 1 : 0">

                {{-- nomor sudah tersimpan (terkunci) --}}
                <p class="text-xs mt-1" x-show="nomorUrut">
                    Nomor ST: <span class="font-semibold text-gray-800" x-text="nomor"></span>
                    <span class="text-gray-500">(sudah terkunci)</span>
                </p>
                {{-- hasil Ambil Nomor, belum tersimpan --}}
                <p class="text-xs mt-1" x-show="!nomorUrut && mintaNomor">
                    Nomor ST: <span class="font-semibold text-gray-800" x-text="nomor"></span>
                    <span class="text-gray-500">(baru terkunci setelah klik Simpan)</span>
                </p>
                <p class="text-xs text-gray-500 mt-1" x-show="!nomorUrut && !mintaNomor">
                    Klik Ambil Nomor untuk melihat nomor berikutnya. Nomor dikunci saat Simpan.
                </p>
                <p class="text-xs text-red-600 mt-1" x-show="errNomor" x-text="errNomor"></p>
            </div>
        </div>
    </div>

    {{-- Pelaksanaan --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h3 class="font-semibold text-gray-800">Pelaksanaan Tugas</h3>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai"
                    value="{{ old('tanggal_mulai', optional($st->tanggal_mulai)->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai"
                    value="{{ old('tanggal_selesai', optional($st->tanggal_selesai)->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-lg border-gray-300 text-sm">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4"
            x-data="lokasi(@js(['provinsi' => $provinsiTerpilih, 'kota' => $kotaTerpilih, 'map' => $kabKotaMap]))">
            <div>
                <label class="block text-sm font-medium text-gray-700">Provinsi</label>
                <div class="relative mt-1">
                    <select name="provinsi" x-model="provinsi" @change="ubahProvinsi()" required
                        class="js-searchable w-full rounded-lg border-gray-300 text-sm"
                        data-placeholder="Cari provinsi...">
                        <option value="" disabled {{ $provinsiTerpilih ? '' : 'selected' }}>— Pilih Provinsi —</option>
                        @foreach (array_keys($kabKotaMap) as $p)
                            <option value="{{ $p }}" @selected($provinsiTerpilih === $p)>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Kota/Kabupaten</label>
                <div class="relative mt-1">
                    <select name="kota" x-model="kota" required :disabled="!provinsi"
                        class="js-searchable w-full rounded-lg border-gray-300 text-sm disabled:bg-gray-50 disabled:cursor-not-allowed"
                        data-placeholder="Cari kota/kabupaten...">
                        <option value="" disabled selected
                            x-text="!provinsi ? 'Pilih provinsi dulu' : '— Pilih Kota/Kabupaten —'"></option>
                        <template x-for="k in daftarKota" :key="k">
                            <option :value="k" x-text="k" :selected="kota === k"></option>
                        </template>
                    </select>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Tujuan / Lokasi</label>
            <input type="text" name="tujuan" value="{{ old('tujuan', $st->tujuan) }}"
                placeholder="mis. Hotel / kantor / tempat acara" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
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

            {{-- Dasar 1: tetap --}}
            <div class="flex gap-2 mb-2">
                <span class="pt-2 text-sm text-gray-500 w-5">1.</span>
                <input type="hidden" name="dasar[]" value="{{ $dasarTetap }}">
                <div class="flex-1 rounded-lg border bg-gray-50 text-gray-700 text-sm px-3 py-2">{{ $dasarTetap }}</div>
                <span class="text-xs text-gray-400 px-2 pt-2 w-14 text-center">Tetap</span>
            </div>

            <template x-for="(d, i) in dasar" :key="i">
                <div class="flex gap-2 mb-2">
                    <span class="pt-2 text-sm text-gray-500 w-5" x-text="(i+2)+'.'"></span>
                    <textarea name="dasar[]" rows="2" x-model="dasar[i]"
                        class="flex-1 rounded-lg border-gray-300 text-sm"></textarea>
                    <button type="button" @click="dasar.splice(i,1)"
                        class="text-red-500 text-sm px-2 w-14">Hapus</button>
                </div>
            </template>
            <button type="button" @click="dasar.push('')" class="text-sm text-blue-600 hover:underline">+ Tambah
                dasar</button>
        </div>
    </div>

    {{-- Kepada & Untuk --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h3 class="font-semibold text-gray-800">Memberi Tugas</h3>
        <div>
            <label class="block text-sm font-medium text-gray-700">Kepada</label>
            <input type="text" name="kepada" value="{{ old('kepada', $st->kepada) }}"
                class="mt-1 w-full rounded-lg border-gray-300 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Untuk</label>
            <template x-for="(u, i) in untuk" :key="i">
                <div class="flex gap-2 mb-2">
                    <span class="pt-2 text-sm text-gray-500 w-5" x-text="(i+1)+'.'"></span>
                    <textarea name="untuk[]" rows="2" x-model="untuk[i]"
                        class="flex-1 rounded-lg border-gray-300 text-sm"></textarea>
                    <button type="button" @click="untuk.splice(i,1)" class="text-red-500 text-sm px-2">Hapus</button>
                </div>
            </template>
            <button type="button" @click="untuk.push('')" class="text-sm text-blue-600 hover:underline">+ Tambah
                poin</button>
        </div>
    </div>

    {{-- Peserta: PNS & Non PNS --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-6">
        <h3 class="font-semibold text-gray-800">Daftar Peserta (Lampiran)</h3>

        @foreach ([
                'pns' => ['label' => 'PNS', 'nipPlaceholder' => 'NIP'],
                'non_pns' => ['label' => 'Non PNS', 'nipPlaceholder' => 'NIK / NIP (opsional)'],
            ] as $jenis => $cfg)
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <h4 class="text-sm font-semibold text-gray-700">{{ $cfg['label'] }}</h4>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600"
                        x-text="count('{{ $jenis }}') + ' orang'"></span>
                </div>

                <div class="flex flex-wrap gap-2">
                    <select x-model="pick.{{ $jenis }}" class="flex-1 min-w-[12rem] rounded-lg border-gray-300 text-sm">
                        <option value="">— pilih pegawai {{ $cfg['label'] }} —</option>
                        <template x-for="p in pool('{{ $jenis }}')" :key="p.id">
                            <option :value="p.id" x-text="p.nama + (p.nip ? ' (' + p.nip + ')' : '')"></option>
                        </template>
                    </select>
                    <button type="button" @click="addPegawai('{{ $jenis }}')"
                        class="px-3 py-2 rounded-lg bg-blue-600 text-white text-sm">Tambah</button>
                    <button type="button" @click="addManual('{{ $jenis }}')" class="px-3 py-2 rounded-lg border text-sm">Isi
                        manual</button>
                </div>

                <div class="space-y-2">
                    {{-- loop semua peserta (index tetap utuh), tampilkan yang sesuai jenis --}}
                    <template x-for="(p, i) in peserta" :key="i">
                        <div x-show="p.jenis === '{{ $jenis }}'"
                            class="grid md:grid-cols-12 gap-2 items-start border rounded-lg p-3">
                            <input type="hidden" :name="`peserta[${i}][jenis]`" :value="p.jenis">
                            <input type="hidden" :name="`peserta[${i}][pegawai_id]`" :value="p.pegawai_id ?? ''">
                            <div class="md:col-span-4">
                                <input type="text" :name="`peserta[${i}][nama]`" x-model="p.nama" placeholder="Nama"
                                    class="w-full rounded-lg border-gray-300 text-sm">
                            </div>
                            <div class="md:col-span-3">
                                <input type="text" :name="`peserta[${i}][nip]`" x-model="p.nip"
                                    placeholder="{{ $cfg['nipPlaceholder'] }}"
                                    class="w-full rounded-lg border-gray-300 text-sm">
                            </div>
                            <div class="md:col-span-4">
                                <input type="text" :name="`peserta[${i}][jabatan]`" x-model="p.jabatan"
                                    placeholder="Jabatan" class="w-full rounded-lg border-gray-300 text-sm">
                            </div>
                            <div class="md:col-span-1 text-right">
                                <button type="button" @click="peserta.splice(i,1)"
                                    class="text-red-500 text-sm">Hapus</button>
                            </div>
                        </div>
                    </template>
                    <p x-show="count('{{ $jenis }}') === 0" class="text-sm text-gray-500">Belum ada peserta
                        {{ $cfg['label'] }}.
                    </p>
                </div>
            </div>

            @if ($jenis === 'pns')
            <hr> @endif
        @endforeach
    </div>

    {{-- Penandatangan --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
        <h3 class="font-semibold text-gray-800">Penandatangan</h3>

        <div>
            <label class="block text-sm font-medium text-gray-700">Pilih penandatangan</label>
            <select x-model="pickTtd" @change="setTtd()" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                <option value="">— pilih penandatangan —</option>
                <template x-for="p in penandatangan" :key="p.id">
                    <option :value="p.id" x-text="p.nama + (p.jabatan ? ' — ' + p.jabatan : '')"></option>
                </template>
            </select>
            <p class="text-xs text-gray-500 mt-1">Daftar diambil dari data pegawai yang ditetapkan sebagai
                penandatangan.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700">Jabatan</label>
                <input type="text" name="jabatan_penandatangan" x-model="ttd.jabatan" readonly
                    class="mt-1 w-full rounded-lg border-gray-300 bg-gray-50 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Nama</label>
                <input type="text" name="nama_penandatangan" x-model="ttd.nama" readonly
                    class="mt-1 w-full rounded-lg border-gray-300 bg-gray-50 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">NIP</label>
                <input type="text" name="nip_penandatangan" x-model="ttd.nip" readonly
                    class="mt-1 w-full rounded-lg border-gray-300 bg-gray-50 text-sm">
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-2 pb-4">
        <a href="{{ route('surat-tugas.index') }}" class="px-4 py-2 rounded-lg border text-sm">Batal</a>
        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm">Simpan</button>
    </div>
</div>

@include('partials.searchable-select')