@php
    $dasarTetap = 'Daftar Isian Pelaksanaan Anggaran (DIPA) Satuan Kerja Kementerian Koordinator Bidang Pangan Tahun 2025 Nomor: SP DIPA-131.01-0/2026 tanggal 1 Desember 2025';

    $kabKotaMap = $kabKotaMap ?? [];

    $dasarLain = collect(old('dasar', $st->dasar ?? []))
        ->reject(fn($d) => str_starts_with(trim((string) $d), 'Daftar Isian Pelaksanaan Anggaran'))
        ->values()->all();

    $untuk = old('untuk', $st->untuk ?? ['']);

    $pesertaInit = collect(old('peserta', $peserta ?? []))
        ->map(fn($p) => array_merge(
            ['jenis' => 'pns', 'pegawai_id' => null, 'nama' => '', 'nip' => '', 'jabatan' => '', 'tanggal_mulai' => '', 'tanggal_selesai' => ''],
            array_filter((array) $p, fn($v) => $v !== null)
        ))
        ->values()->all();

    $provinsiTerpilih = old('provinsi', $st->provinsi ?? '');
    $kotaTerpilih = old('kota', $st->kota ?? '');

    $fLabel = 'block text-xs font-medium text-slate-600';
    $fInput = 'mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600';
    $fReadonly = 'mt-1 w-full rounded-lg border-slate-300 bg-slate-50 text-sm';
    $fInputPlain = 'w-full rounded-lg border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600';
    $fCard = 'overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm';
    $fHead = 'border-b border-slate-200 bg-slate-50 px-4 py-3 sm:px-6';
    $fBody = 'space-y-4 px-4 py-4 sm:px-6 sm:py-5';
    $fLink = 'text-sm font-medium text-blue-700 hover:underline focus:outline-none focus-visible:underline';
    $fHapus = 'rounded-lg border border-red-200 bg-white px-2.5 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50';
@endphp

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

<div x-data="{
        dasar: @js($dasarLain),
        untuk: @js(array_values($untuk)),
        peserta: @js($pesertaInit),
        pegawai: @js($pegawaiOptions ?? []),
        penandatangan: @js($penandatanganOptions ?? []),
        cari: { pns: '', non_pns: '' },
        provinsi: @js($provinsiTerpilih),
        kota: @js($kotaTerpilih),
        kabKotaMap: @js($kabKotaMap),
        kode: @js(old('kode_klasifikasi', $st->kode_klasifikasi ?? '')),
        tanggal: @js(old('tanggal', optional($st->tanggal ?? null)->format('Y-m-d'))),
        mulai: @js(old('tanggal_mulai', optional($st->tanggal_mulai ?? null)->format('Y-m-d'))),
        selesai: @js(old('tanggal_selesai', optional($st->tanggal_selesai ?? null)->format('Y-m-d'))),
        nomorUrut: @js($st->nomor_urut ?? null),
        nomor: @js($st->nomor ?? ''),
        tahunNomor: @js($st->tahun ?? date('Y')),
        ambilUrl: @js(route('surat-tugas.nomor-berikutnya')),
        loadingNomor: false,
        errNomor: '',
        ttd: @js([
            'nama' => old('nama_penandatangan', $st->nama_penandatangan ?? ''),
            'nip' => old('nip_penandatangan', $st->nip_penandatangan ?? ''),
            'jabatan' => old('jabatan_penandatangan', $st->jabatan_penandatangan ?? ''),
        ]),
        pickTtd: '',

        get daftarKota() { return this.kabKotaMap[this.provinsi] ?? []; },

        async ambilNomor() {
            if (this.nomorUrut || !this.tanggal || !this.kode) return;
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
            } catch (e) {
                this.errNomor = e.message;
            } finally {
                this.loadingNomor = false;
            }
        },

        idx(jenis) { return this.peserta.map((p, i) => i).filter(i => this.peserta[i].jenis === jenis); },
        pool(jenis) { return this.pegawai.filter(x => x.status === jenis); },
        count(jenis) { return this.peserta.filter(x => x.jenis === jenis).length; },
        terpilih(id) { return this.peserta.some(x => String(x.pegawai_id) === String(id)); },
        daftar(jenis) {
            const q = (this.cari[jenis] || '').toLowerCase().trim();
            return this.pool(jenis).filter(p => !q || [p.nama, p.nip, p.jabatan].some(v => (v || '').toLowerCase().includes(q)));
        },
        toggle(jenis, p) {
            const k = this.peserta.findIndex(x => String(x.pegawai_id) === String(p.id));
            if (k >= 0) {
                const x = this.peserta[k];
                if ((x.tanggal_mulai || x.tanggal_selesai) && !confirm('Tanggal tugas ' + x.nama + ' sudah diisi. Tetap dihapus?')) return;
                this.peserta.splice(k, 1);
                return;
            }
            this.peserta.push({ jenis: jenis, pegawai_id: p.id, nama: p.nama, nip: p.nip || '', jabatan: p.jabatan || '', tanggal_mulai: '', tanggal_selesai: '' });
        },
        addManual(jenis) {
            this.peserta.push({ jenis: jenis, pegawai_id: null, nama: '', nip: '', jabatan: '', tanggal_mulai: '', tanggal_selesai: '' });
        },
        setTtd() {
            const p = this.penandatangan.find(x => String(x.id) === String(this.pickTtd));
            this.ttd = p
                ? { nama: p.nama, nip: p.nip || '', jabatan: p.jabatan || '' }
                : { nama: '', nip: '', jabatan: '' };
        }
    }" x-init="pickTtd = (penandatangan.find(x => x.nama === ttd.nama)?.id) ?? '';
            if (!nomorUrut) { ambilNomor(); }
            $watch('tanggal', () => { if (!nomorUrut && tanggal) ambilNomor(); })" class="space-y-5">

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
            <p class="font-medium">Surat tugas belum bisa disimpan. Periksa isian berikut:</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Kolom "tujuan" tidak lagi punya input, tapi nilai lama tetap dikirim supaya tidak terhapus saat ST diedit --}}
    <input type="hidden" name="tujuan" value="{{ old('tujuan', $st->tujuan ?? '') }}">

    <section class="{{ $fCard }}">
        <div class="{{ $fHead }}">
            <h3 class="text-sm font-semibold text-slate-900">Informasi surat dan pelaksanaan</h3>
            <p class="mt-0.5 text-xs text-slate-500">Tempat, tanggal, nomor, rentang pelaksanaan, dan lokasi tujuan.</p>
        </div>
        <div class="{{ $fBody }}">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="{{ $fLabel }}">Tempat</label>
                    <input type="text" name="tempat" value="{{ old('tempat', $st->tempat ?? '') }}" class="{{ $fInput }}">
                </div>
                <div>
                    <label class="{{ $fLabel }}">Tanggal surat</label>
                    <input type="date" name="tanggal" x-model="tanggal" :min="nomorUrut ? tahunNomor + '-01-01' : null"
                        :max="nomorUrut ? tahunNomor + '-12-31' : null" class="{{ $fInput }}">
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $fLabel }}">Nomor ST</label>
                    <input type="hidden" name="kode_klasifikasi" :value="kode">
                    <input type="hidden" name="minta_nomor" :value="nomorUrut ? 0 : 1">
                    <input type="text" readonly :value="nomor || (loadingNomor ? 'Memuat nomor…' : '—')"
                        class="{{ $fReadonly }} font-semibold tabular-nums text-gray-900">
                    <p class="mt-1 text-xs text-slate-500"
                        x-text="nomorUrut ? 'Nomor sudah terkunci.' : 'Terisi otomatis, terkunci setelah klik Simpan.'">
                    </p>
                    <p class="mt-1 text-xs text-red-600" x-show="errNomor" x-text="errNomor"></p>
                </div>

                <div>
                    <label class="{{ $fLabel }}">Tanggal mulai</label>
                    <input type="date" name="tanggal_mulai" x-model="mulai" class="{{ $fInput }}">
                </div>
                <div>
                    <label class="{{ $fLabel }}">Tanggal selesai</label>
                    <input type="date" name="tanggal_selesai" x-model="selesai" :min="mulai || null"
                        class="{{ $fInput }}">
                </div>
                <div>
                    <label class="{{ $fLabel }}">Provinsi</label>
                    <select name="provinsi" x-model="provinsi" @change="kota = ''" required class="{{ $fInput }}">
                        <option value="">— Pilih Provinsi —</option>
                        @foreach (array_keys($kabKotaMap) as $p)
                            <option value="{{ $p }}" @selected($provinsiTerpilih === $p)>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $fLabel }}">Kota/Kabupaten</label>
                    <select name="kota" x-model="kota" required :disabled="!provinsi"
                        class="{{ $fInput }} disabled:cursor-not-allowed disabled:bg-slate-50">
                        <option value="" x-text="!provinsi ? 'Pilih provinsi dulu' : '— Pilih Kota/Kabupaten —'">
                        </option>
                        <template x-for="k in daftarKota" :key="k">
                            <option :value="k" x-text="k" :selected="kota === k"></option>
                        </template>
                    </select>
                </div>
            </div>
        </div>
    </section>

    <section class="{{ $fCard }}">
        <div class="{{ $fHead }}">
            <h3 class="text-sm font-semibold text-slate-900">Menimbang dan dasar</h3>
            <p class="mt-0.5 text-xs text-slate-500">Dasar pertama bersifat tetap. Dasar lain bisa ditambah.</p>
        </div>
        <div class="{{ $fBody }}">
            <div>
                <label class="{{ $fLabel }}">Menimbang</label>
                <textarea name="menimbang" rows="3" class="{{ $fInput }}"
                    placeholder="Bahwa dalam rangka melaksanakan ... dianggap perlu dibuatkan surat tugas ...">{{ old('menimbang', $st->menimbang ?? '') }}</textarea>
            </div>
            <div>
                <label class="{{ $fLabel }} mb-1">Dasar</label>

                <div class="mb-2 flex gap-2">
                    <span class="w-5 pt-2 text-sm text-slate-500">1.</span>
                    <input type="hidden" name="dasar[]" value="{{ $dasarTetap }}">
                    <div class="flex-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                        {{ $dasarTetap }}
                    </div>
                    <span class="w-16 pt-2 text-center text-xs text-slate-400">Tetap</span>
                </div>

                <template x-for="(d, i) in dasar" :key="i">
                    <div class="mb-2 flex gap-2">
                        <span class="w-5 pt-2 text-sm text-slate-500" x-text="(i+2)+'.'"></span>
                        <textarea name="dasar[]" rows="2" x-model="dasar[i]"
                            class="flex-1 {{ $fInputPlain }}"></textarea>
                        <div class="w-16 pt-1.5 text-center">
                            <button type="button" @click="dasar.splice(i,1)" class="{{ $fHapus }}">Hapus</button>
                        </div>
                    </div>
                </template>
                <button type="button" @click="dasar.push('')" class="{{ $fLink }}">+ Tambah dasar</button>
            </div>
        </div>
    </section>

    <section class="{{ $fCard }}">
        <div class="{{ $fHead }}">
            <h3 class="text-sm font-semibold text-slate-900">Memberi tugas</h3>
            <p class="mt-0.5 text-xs text-slate-500">Penerima tugas dan uraian tugas yang dilaksanakan.</p>
        </div>
        <div class="{{ $fBody }}">
            <div>
                <label class="{{ $fLabel }}">Kepada</label>
                <input type="text" name="kepada" value="{{ old('kepada', $st->kepada ?? '') }}" class="{{ $fInput }}">
            </div>
            <div>
                <label class="{{ $fLabel }} mb-1">Untuk</label>
                <template x-for="(u, i) in untuk" :key="i">
                    <div class="mb-2 flex gap-2">
                        <span class="w-5 pt-2 text-sm text-slate-500" x-text="(i+1)+'.'"></span>
                        <textarea name="untuk[]" rows="2" x-model="untuk[i]"
                            class="flex-1 {{ $fInputPlain }}"></textarea>
                        <div class="pt-1.5">
                            <button type="button" @click="untuk.splice(i,1)" class="{{ $fHapus }}">Hapus</button>
                        </div>
                    </div>
                </template>
                <button type="button" @click="untuk.push('')" class="{{ $fLink }}">+ Tambah poin</button>
            </div>
        </div>
    </section>

    <section class="{{ $fCard }}">
        <div class="{{ $fHead }}">
            <h3 class="text-sm font-semibold text-slate-900">Daftar peserta (lampiran)</h3>
            <p class="mt-0.5 text-xs text-slate-500">
                Klik ikon kalender bila peserta berangkat di hari berbeda. Kosongkan jika mengikuti seluruh rentang ST
                (tanggal harus di dalam Tanggal Mulai sampai Tanggal Selesai).
            </p>
        </div>

        <div class="grid items-start gap-4 px-4 py-4 sm:px-6 lg:grid-cols-2">
            @foreach ([
                    'pns' => [
                        'label' => 'PNS',
                        'nipPh' => 'NIP',
                        'wrap' => 'border-slate-200 bg-slate-50',
                        'badge' => 'border-blue-200 bg-blue-50 text-blue-700',
                        'title' => 'text-blue-700',
                        'row' => 'border-slate-200 border-l-4 border-l-blue-500 bg-white',
                        'check' => 'text-blue-600 focus:ring-blue-600',
                        'focus' => 'focus:border-blue-600 focus:ring-blue-600',
                    ],
                    'non_pns' => [
                        'label' => 'Non PNS',
                        'nipPh' => 'NIK / NIP (opsional)',
                        'wrap' => 'border-slate-200 bg-slate-50',
                        'badge' => 'border-amber-200 bg-amber-50 text-amber-700',
                        'title' => 'text-amber-700',
                        'row' => 'border-slate-200 border-l-4 border-l-amber-400 bg-white',
                        'check' => 'text-amber-500 focus:ring-amber-500',
                        'focus' => 'focus:border-amber-500 focus:ring-amber-500',
                    ],
                ] as $jenis => $cfg)
                <div class="space-y-3 rounded-xl border p-3 sm:p-4 {{ $cfg['wrap'] }}">
                    <div class="flex items-center gap-2">
                        <h4 class="rounded-full border px-3 py-1 text-sm font-semibold {{ $cfg['badge'] }}">
                            {{ $cfg['label'] }}
                        </h4>
                        <span class="text-xs text-slate-500" x-text="count('{{ $jenis }}') + ' orang'"></span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        {{-- Enter di kotak cari tidak boleh mengirim form (bisa menyimpan ST dan mengambil nomor) --}}
                        <input type="search" x-model="cari.{{ $jenis }}" @keydown.enter.prevent
                            placeholder="Cari pegawai {{ $cfg['label'] }} (nama / NIP / jabatan)…"
                            class="min-w-[12rem] flex-1 rounded-lg border-slate-300 bg-white text-sm {{ $cfg['focus'] }}">
                        <button type="button" @click="addManual('{{ $jenis }}')"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">+
                            Isi manual</button>
                    </div>

                    <div
                        class="max-h-44 divide-y divide-slate-100 overflow-y-auto rounded-lg border border-slate-200 bg-white">
                        <template x-for="p in daftar('{{ $jenis }}')" :key="p.id">
                            <label class="flex cursor-pointer items-center gap-3 px-3 py-2 text-sm hover:bg-slate-50">
                                <input type="checkbox" :checked="terpilih(p.id)" @change="toggle('{{ $jenis }}', p)"
                                    class="h-4 w-4 rounded border-slate-300 {{ $cfg['check'] }}">
                                <span class="font-medium text-slate-800" x-text="p.nama"></span>
                                <span class="truncate text-xs text-slate-500"
                                    x-text="[p.nip, p.jabatan].filter(Boolean).join(' · ')"></span>
                            </label>
                        </template>
                        <p x-show="daftar('{{ $jenis }}').length === 0" class="px-3 py-3 text-sm text-slate-500"
                            x-text="pool('{{ $jenis }}').length === 0 ? 'Belum ada data pegawai {{ $cfg['label'] }}.' : 'Tidak ada yang cocok.'">
                        </p>
                    </div>

                    <div class="space-y-2">
                        <template x-for="i in idx('{{ $jenis }}')" :key="'{{ $jenis }}-' + i">
                            <div x-data="{ open: !!(peserta[i].tanggal_mulai || peserta[i].tanggal_selesai) }"
                                class="rounded-lg border p-2 {{ $cfg['row'] }}">
                                <input type="hidden" :name="`peserta[${i}][jenis]`" :value="peserta[i].jenis">
                                <input type="hidden" :name="`peserta[${i}][pegawai_id]`"
                                    :value="peserta[i].pegawai_id ?? ''">

                                <div class="flex items-center gap-2">
                                    <input type="text" :name="`peserta[${i}][nama]`" x-model="peserta[i].nama"
                                        placeholder="Nama"
                                        class="min-w-0 flex-1 rounded-lg border-slate-300 text-sm {{ $cfg['focus'] }}">
                                    <button type="button" @click="open = !open" title="Atur tanggal tugas sendiri"
                                        :aria-pressed="open"
                                        :class="open ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50'"
                                        class="shrink-0 rounded-lg border p-2 transition">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                            stroke-linejoin="round" aria-hidden="true">
                                            <rect x="3" y="4.5" width="18" height="16" rx="2" />
                                            <path d="M3 9.5h18M8 2.5v4M16 2.5v4" />
                                        </svg>
                                    </button>
                                    <button type="button" @click="peserta.splice(i,1)"
                                        class="shrink-0 {{ $fHapus }}">Hapus</button>
                                </div>
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    <input type="text" :name="`peserta[${i}][nip]`" x-model="peserta[i].nip"
                                        placeholder="{{ $cfg['nipPh'] }}"
                                        class="min-w-0 rounded-lg border-slate-300 text-sm {{ $cfg['focus'] }}">
                                    <input type="text" :name="`peserta[${i}][jabatan]`" x-model="peserta[i].jabatan"
                                        placeholder="Jabatan"
                                        class="min-w-0 rounded-lg border-slate-300 text-sm {{ $cfg['focus'] }}">
                                </div>

                                <div x-show="open" x-cloak class="mt-2 grid gap-2 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs text-slate-500">Tanggal tugas (mulai)</label>
                                        <input type="date" :name="`peserta[${i}][tanggal_mulai]`"
                                            x-model="peserta[i].tanggal_mulai" :min="mulai || null" :max="selesai || null"
                                            class="w-full rounded-lg border-slate-300 text-sm {{ $cfg['focus'] }}">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs text-slate-500">Sampai (opsional)</label>
                                        <input type="date" :name="`peserta[${i}][tanggal_selesai]`"
                                            x-model="peserta[i].tanggal_selesai"
                                            :min="peserta[i].tanggal_mulai || mulai || null" :max="selesai || null"
                                            class="w-full rounded-lg border-slate-300 text-sm {{ $cfg['focus'] }}">
                                    </div>
                                </div>
                            </div>
                        </template>
                        <p x-show="count('{{ $jenis }}') === 0" class="text-sm text-slate-500">Belum ada peserta
                            {{ $cfg['label'] }}.
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="{{ $fCard }}">
        <div class="{{ $fHead }}">
            <h3 class="text-sm font-semibold text-slate-900">Penandatangan</h3>
            <p class="mt-0.5 text-xs text-slate-500">Daftar diambil dari data pegawai yang ditetapkan sebagai
                penandatangan.</p>
        </div>
        <div class="{{ $fBody }}">
            <div>
                <label class="{{ $fLabel }}">Pilih penandatangan</label>
                <select x-model="pickTtd" @change="setTtd()" class="{{ $fInput }}">
                    <option value="">— pilih penandatangan —</option>
                    <template x-for="p in penandatangan" :key="p.id">
                        <option :value="p.id" x-text="p.nama + (p.jabatan ? ' — ' + p.jabatan : '')"></option>
                    </template>
                </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="sm:col-span-3">
                    <label class="{{ $fLabel }}">Jabatan</label>
                    <input type="text" name="jabatan_penandatangan" x-model="ttd.jabatan" readonly
                        class="{{ $fReadonly }}">
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $fLabel }}">Nama</label>
                    <input type="text" name="nama_penandatangan" x-model="ttd.nama" readonly class="{{ $fReadonly }}">
                </div>
                <div>
                    <label class="{{ $fLabel }}">NIP</label>
                    <input type="text" name="nip_penandatangan" x-model="ttd.nip" readonly class="{{ $fReadonly }}">
                </div>
            </div>
        </div>
    </section>

    <div
        class="sticky bottom-0 z-10 -mx-4 flex justify-end gap-2 border-t border-slate-200 bg-white/90 px-4 py-3 backdrop-blur sm:mx-0 sm:rounded-xl sm:border sm:shadow-sm">
        <a href="{{ route('surat-tugas.index') }}"
            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Batal</a>
        <button type="submit"
            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">Simpan</button>
    </div>
</div>