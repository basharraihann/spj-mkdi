@php
    $badge = [
        'draf' => ['pill' => 'bg-slate-100 text-slate-700 ring-slate-200', 'dot' => 'bg-slate-400'],
        'terbit' => ['pill' => 'bg-blue-50 text-blue-700 ring-blue-200', 'dot' => 'bg-blue-500'],
        'selesai' => ['pill' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'dot' => 'bg-emerald-500'],
        'batal' => ['pill' => 'bg-red-50 text-red-700 ring-red-200', 'dot' => 'bg-red-500'],
    ][$st->status] ?? ['pill' => 'bg-slate-100 text-slate-700 ring-slate-200', 'dot' => 'bg-slate-400'];

    $adaTanggal = $st->peserta->contains(fn($p) => (bool) $p->tanggal_mulai);
    $jmlPns = $st->peserta->where('jenis', 'pns')->count();
    $jmlNonPns = $st->peserta->where('jenis', '!=', 'pns')->count();

    $tusiOptions = $tusiOptions ?? collect();
    $bolehUbahTusi = $bolehUbahTusi ?? false;
    $bolehKelola = $bolehKelola ?? false;
    $tusiNonaktifDipakai = $st->tusi && !$tusiOptions->contains('id', $st->tusi_id);

    $card = 'overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm';
    $cardHead = 'flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-2.5';
    $cardTitle = 'text-sm font-semibold text-slate-900';
    $label = 'text-xs text-slate-500';
    $input = 'w-full rounded-md border-slate-300 py-1.5 text-[13px] focus:border-blue-600 focus:ring-blue-600';
    $btnBase = 'inline-flex items-center justify-center rounded-md px-3 py-1.5 text-[13px] font-semibold shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2';
    $btnPrimary = $btnBase . ' bg-blue-600 text-white hover:bg-blue-700 focus-visible:ring-blue-600';
    $btnSuccess = $btnBase . ' bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:ring-emerald-600';
    $btnOutline = $btnBase . ' border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus-visible:ring-slate-400';

    $jenisWarna = [
        'pns' => ['pill' => 'border-blue-200 bg-blue-50 text-blue-700', 'avatar' => 'bg-blue-100 text-blue-700'],
        'non_pns' => ['pill' => 'border-amber-200 bg-amber-50 text-amber-700', 'avatar' => 'bg-amber-100 text-amber-700'],
    ];
@endphp
<x-app-layout>
    <div class="max-w-7xl mx-auto space-y-4 px-4 py-4 sm:px-6 lg:px-8">

        <div class="{{ $card }} px-4 py-3">
            <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
                <div class="flex min-w-0 flex-wrap items-center gap-x-2.5 gap-y-1">
                    <h1 class="break-all text-lg font-semibold leading-tight text-slate-900">{{ $st->nomor_tampil }}</h1>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $badge['pill'] }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $badge['dot'] }}"></span>
                        {{ $st->status_label }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if ($st->status === 'draf' && $bolehKelola)
                        <form method="POST" action="{{ route('surat-tugas.terbitkan', $st) }}"
                            onsubmit="return confirm('Terbitkan ST ini? Setelah terbit, isi ST dikunci dan tidak bisa diedit, dan peserta yang punya akun akan mendapat tugas laporan.')">
                            @csrf
                            <button class="{{ $btnPrimary }}">Terbitkan</button>
                        </form>
                    @endif

                    @if ($st->status === 'terbit' && \Illuminate\Support\Facades\Route::has('surat-tugas.selesai'))
                        <form method="POST" action="{{ route('surat-tugas.selesai', $st) }}">
                            @csrf
                            <button class="{{ $btnSuccess }}">Tandai selesai</button>
                        </form>
                    @endif

                    <a href="{{ route('surat-tugas.index') }}" class="{{ $btnOutline }}">Kembali ke daftar</a>
                </div>
            </div>

            <dl class="mt-3 grid gap-x-6 gap-y-2 border-t border-slate-100 pt-3 text-[13px] sm:grid-cols-3">
                <div>
                    <dt class="{{ $label }}">Tempat, tanggal</dt>
                    <dd class="font-medium text-slate-900">{{ $st->tempat }}, {{ $st->tanggal_indo }}</dd>
                </div>
                <div>
                    <dt class="{{ $label }}">Pelaksanaan</dt>
                    <dd class="font-medium text-slate-900">
                        @if ($st->tanggal_mulai)
                            {{ $st->tanggal_mulai->locale('id')->translatedFormat('j F Y') }}
                            @if ($st->tanggal_selesai && !$st->tanggal_selesai->isSameDay($st->tanggal_mulai))
                                <span class="font-normal text-slate-400">s.d.</span>
                                {{ $st->tanggal_selesai->locale('id')->translatedFormat('j F Y') }}
                            @endif
                            @if ($st->kota || $st->provinsi)
                                <span class="font-normal text-slate-500">&middot;
                                    {{ collect([$st->kota, $st->provinsi])->filter()->implode(', ') }}</span>
                            @endif
                        @else
                            -
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="{{ $label }}">Penandatangan</dt>
                    <dd class="font-medium text-slate-900">{{ $st->nama_penandatangan }}
                        <span class="font-normal text-slate-500">&middot; {{ $st->jabatan_penandatangan }}</span>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="grid items-start gap-4 lg:grid-cols-3">

            <div class="space-y-4 lg:col-span-2">

                <section class="{{ $card }}">
                    <div class="{{ $cardHead }}">
                        <h3 class="{{ $cardTitle }}">Isi surat</h3>
                    </div>
                    <div class="divide-y divide-slate-100 px-4 text-[13px] leading-6 text-slate-700">
                        <div class="grid gap-0.5 py-3 sm:grid-cols-[6rem_1fr] sm:gap-4">
                            <h4 class="font-medium text-slate-500">Menimbang</h4>
                            <p>{{ $st->menimbang ?: '-' }}</p>
                        </div>

                        <div class="grid gap-0.5 py-3 sm:grid-cols-[6rem_1fr] sm:gap-4">
                            <h4 class="font-medium text-slate-500">Dasar</h4>
                            <ol class="list-decimal space-y-1 pl-5 marker:text-slate-400">
                                @forelse ($st->dasar ?? [] as $d)
                                    <li>{{ $d }}</li>
                                @empty
                                    <li class="-ml-5 list-none">-</li>
                                @endforelse
                            </ol>
                        </div>

                        <div class="grid gap-0.5 py-3 sm:grid-cols-[6rem_1fr] sm:gap-4">
                            <h4 class="font-medium text-slate-500">Kepada</h4>
                            <p>{{ $st->kepada }}</p>
                        </div>

                        <div class="grid gap-0.5 py-3 sm:grid-cols-[6rem_1fr] sm:gap-4">
                            <h4 class="font-medium text-slate-500">Untuk</h4>
                            <ol class="list-decimal space-y-1 pl-5 marker:text-slate-400">
                                @foreach ($st->untuk ?? [] as $u)
                                    <li>{{ $u }}</li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </section>

                <section class="{{ $card }}">
                    <div class="{{ $cardHead }}">
                        <h3 class="{{ $cardTitle }}">Pengelompokan (Tusi)</h3>
                        @if ($st->tusi)
                            <span
                                class="rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200">{{ $st->tusi->nama }}</span>
                        @else
                            <span
                                class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">Belum
                                dikelompokkan</span>
                        @endif
                    </div>

                    <div class="px-4 py-3 text-[13px]">
                        @if ($bolehUbahTusi)
                            <form method="POST" action="{{ route('surat-tugas.tusi', $st) }}"
                                class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                @csrf
                                @method('PATCH')

                                <select name="tusi_id" aria-label="Pengelompokan (Tusi)"
                                    class="{{ $input }} sm:max-w-sm">
                                    <option value="">— Belum dikelompokkan —</option>
                                    @if ($tusiNonaktifDipakai)
                                        <option value="{{ $st->tusi_id }}" selected>{{ $st->tusi->nama }}
                                            (nonaktif)</option>
                                    @endif
                                    @foreach ($tusiOptions as $t)
                                        <option value="{{ $t->id }}" @selected((int) old('tusi_id', $st->tusi_id) === (int) $t->id)>
                                            {{ $t->nama }}</option>
                                    @endforeach
                                </select>

                                <button class="{{ $btnPrimary }}">Simpan</button>
                            </form>

                            @error('tusi_id')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror

                            <p class="mt-1.5 text-xs text-slate-500">Bisa diubah setelah ST terbit dan tidak
                                memengaruhi isi surat.</p>
                        @else
                            <p class="text-xs text-slate-500">Hanya admin atau pembuat ST yang dapat mengubah
                                pengelompokan.</p>
                        @endif
                    </div>
                </section>

                @include('surat-tugas._kontrol', ['st' => $st])

                @include('surat-tugas._format-pdf', ['st' => $st])

                @if (in_array($st->status, ['terbit', 'selesai']))
                    @php
                        $laporan = $laporan ?? null;
                        $tugasLapor = $tugasLapor ?? collect();
                        $bolehIsiLaporan = $bolehIsiLaporan ?? false;

                        $statusLaporan = $laporan ? ($laporan->status === 'dikirim' ? 'Dikirim' : 'Draf') : 'Belum diisi';
                        $warnaLaporan = [
                            'Dikirim' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                            'Draf' => 'bg-amber-50 text-amber-700 ring-amber-200',
                            'Belum diisi' => 'bg-slate-100 text-slate-700 ring-slate-200',
                        ][$statusLaporan];
                        $warnaTugas = [
                            'menunggu' => 'bg-amber-50 text-amber-700 ring-amber-200',
                            'selesai' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                            'ditutup' => 'bg-slate-100 text-slate-500 ring-slate-200',
                        ];

                        $adaBerkas = $laporan && filled($laporan->file_laporan_path);
                        $bolehBukaBerkas = $adaBerkas
                            && app(\App\Services\LaporanStService::class)->bolehUnduh($st, auth()->user());
                    @endphp

                    <section class="{{ $card }}">
                        <div class="{{ $cardHead }}">
                            <h3 class="{{ $cardTitle }}">Laporan hasil ST</h3>
                            <span
                                class="rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $warnaLaporan }}">
                                {{ $statusLaporan }}
                            </span>
                        </div>

                        <div class="space-y-4 px-4 py-4 text-[13px]">

                            <div>
                                <div class="{{ $label }} mb-1">Penugasan laporan</div>
                                @forelse ($tugasLapor as $tg)
                                    @php $nm = $tg->user->name ?? '—'; @endphp
                                    <div
                                        class="flex items-center justify-between gap-3 border-b border-slate-100 py-1.5 last:border-0">
                                        <div class="flex min-w-0 items-center gap-2.5">
                                            <span
                                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[11px] font-semibold text-slate-600">{{ mb_strtoupper(mb_substr($nm, 0, 1)) }}</span>
                                            <span class="truncate text-slate-800">{{ $nm }}</span>
                                        </div>
                                        <span
                                            class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset {{ $warnaTugas[$tg->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">{{ ucfirst($tg->status) }}</span>
                                    </div>
                                @empty
                                    <p class="rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-500">Tidak ada peserta
                                        yang akunnya dipetakan ke pegawai, jadi tidak ada tugas lapor otomatis. Admin
                                        masih bisa mengisi laporan.</p>
                                @endforelse
                                @if ($tugasLapor->isNotEmpty())
                                    <p class="mt-1.5 text-xs text-slate-500">Cukup satu peserta yang mengirim laporan;
                                        tugas peserta lain otomatis ditutup.</p>
                                @endif
                            </div>

                            @if ($errors->any())
                                <div class="rounded-md border border-red-200 bg-red-50 p-2.5 text-xs text-red-700"
                                    role="alert">
                                    {{ $errors->first() }}</div>
                            @endif

                            @if ($bolehIsiLaporan)
                                <form method="POST" action="{{ route('surat-tugas.laporan.simpan', $st) }}"
                                    enctype="multipart/form-data" class="space-y-5 border-t border-slate-100 pt-4"
                                    x-data="{
                                        notulensiFile: null,
                                        materiFiles: [],
                                        err: '',

                                        ukuran(b) {
                                            return b >= 1048576
                                                ? (b / 1048576).toFixed(1) + ' MB'
                                                : Math.max(1, Math.round(b / 1024)) + ' KB';
                                        },
                                        ext(f) { return (f.name.split('.').pop() || '').toLowerCase(); },

                                        syncNotulensi() {
                                            const dt = new DataTransfer();
                                            if (this.notulensiFile) dt.items.add(this.notulensiFile);
                                            this.$refs.notulensi.files = dt.files;
                                        },
                                        syncMateri() {
                                            const dt = new DataTransfer();
                                            this.materiFiles.forEach(f => dt.items.add(f));
                                            this.$refs.materi.files = dt.files;
                                        },

                                        pilihNotulensi(e) {
                                            const f = e.target.files[0] || null;
                                            this.err = '';
                                            if (f && this.ext(f) !== 'pdf') {
                                                this.err = 'Notulensi harus berformat PDF.';
                                            } else if (f && f.size > 5 * 1048576) {
                                                this.err = 'Ukuran notulensi maksimal 5 MB.';
                                            } else if (f) {
                                                this.notulensiFile = f;
                                            }
                                            this.syncNotulensi();
                                        },
                                        hapusNotulensi() {
                                            this.notulensiFile = null;
                                            this.err = '';
                                            this.syncNotulensi();
                                        },

                                        tambahMateri(e) {
                                            const salah = [];
                                            Array.from(e.target.files).forEach(f => {
                                                if (!['pdf', 'ppt', 'pptx'].includes(this.ext(f))) {
                                                    salah.push(f.name + ' (format harus PDF, PPT, atau PPTX)');
                                                } else if (f.size > 10 * 1048576) {
                                                    salah.push(f.name + ' (lebih dari 10 MB)');
                                                } else if (!this.materiFiles.some(x => x.name === f.name && x.size === f.size && x.lastModified === f.lastModified)) {
                                                    this.materiFiles.push(f);
                                                }
                                            });
                                            this.err = salah.length ? 'Tidak ditambahkan: ' + salah.join('; ') + '.' : '';
                                            this.syncMateri();
                                        },
                                        hapusMateri(i) {
                                            this.materiFiles.splice(i, 1);
                                            this.err = '';
                                            this.syncMateri();
                                        },
                                        kosongkanMateri() {
                                            this.materiFiles = [];
                                            this.err = '';
                                            this.syncMateri();
                                        }
                                    }">
                                    @csrf

                                    <p class="rounded-md px-3 py-2 text-xs text-red-700 border border-red-200 bg-red-50"
                                        x-show="err" x-cloak x-text="err" role="alert"></p>

                                    <div>
                                        <label class="{{ $label }} mb-1.5 block font-medium text-slate-700">
                                            Notulensi <span class="text-slate-400 font-normal">(1 file PDF, maks. 5 MB)</span> <span class="text-red-600">*</span>
                                        </label>

                                        <div class="group relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-blue-200 bg-blue-50/20 p-6 text-center transition hover:border-blue-400 hover:bg-blue-50/40">
                                            <input id="notulensi" type="file" name="notulensi" accept="application/pdf,.pdf"
                                                x-ref="notulensi"
                                                class="absolute inset-0 z-50 h-full w-full cursor-pointer opacity-0"
                                                x-on:change="pilihNotulensi($event)">

                                            <div class="flex flex-col items-center justify-center space-y-2">
                                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100/70 text-blue-600 transition-transform group-hover:scale-105">
                                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                    </svg>
                                                </div>
                                                <div class="text-xs text-slate-600">
                                                    <span class="font-semibold text-blue-600 hover:underline">Klik untuk memilih notulensi</span> atau tarik & lepas file ke sini
                                                </div>
                                                <p class="text-[11px] text-slate-400">Format PDF (Maksimal 1 File). Memilih file baru akan mengganti file ini.</p>
                                            </div>
                                        </div>

                                        <template x-if="notulensiFile">
                                            <div class="mt-2.5 flex items-center justify-between gap-2 rounded-lg bg-blue-50 px-3 py-2 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-200">
                                                <div class="flex min-w-0 items-center gap-2">
                                                    <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span class="truncate"><strong x-text="notulensiFile.name"></strong></span>
                                                    <span class="shrink-0 font-normal text-slate-500" x-text="ukuran(notulensiFile.size)"></span>
                                                </div>
                                                <button type="button" @click="hapusNotulensi()" title="Batalkan file ini"
                                                    aria-label="Batalkan file notulensi"
                                                    style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;flex-shrink:0;border-radius:9999px;background:#fee2e2;color:#dc2626;">
                                                    <svg style="width:12px;height:12px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>

                                    <div>
                                        <label class="{{ $label }} mb-1.5 block font-medium text-slate-700">
                                            Materi <span class="text-slate-400 font-normal">(bisa upload banyak file; PDF, PPT, PPTX; maks. 10 MB per file)</span>
                                        </label>

                                        <div class="group relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-emerald-200 bg-emerald-50/20 p-6 text-center transition hover:border-emerald-400 hover:bg-emerald-50/40">
                                            <input id="materi" type="file" name="materi[]" accept=".pdf,.ppt,.pptx" multiple
                                                x-ref="materi"
                                                class="absolute inset-0 z-50 h-full w-full cursor-pointer opacity-0"
                                                x-on:change="tambahMateri($event)">

                                            <div class="flex flex-col items-center justify-center space-y-2">
                                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100/70 text-emerald-600 transition-transform group-hover:scale-105">
                                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                    </svg>
                                                </div>
                                                <div class="text-xs text-slate-600">
                                                    <span class="font-semibold text-emerald-600 hover:underline">Klik untuk memilih materi</span> atau tarik & lepas file ke sini
                                                </div>
                                                <p class="text-[11px] text-slate-400">Klik lagi untuk menambah file; file yang sudah dipilih tidak diganti.</p>
                                            </div>
                                        </div>

                                        <template x-if="materiFiles.length > 0">
                                            <div class="mt-2.5 space-y-1.5">
                                                <div class="flex items-center justify-between text-xs text-slate-500">
                                                    <span><strong x-text="materiFiles.length"></strong> file dipilih</span>
                                                    <button type="button" @click="kosongkanMateri()" class="text-xs text-slate-500 hover:underline">Kosongkan semua</button>
                                                </div>
                                                <template x-for="(f, i) in materiFiles" :key="f.name + f.size + f.lastModified">
                                                    <div class="flex items-center justify-between gap-2 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                                        <div class="flex min-w-0 items-center gap-2">
                                                            <svg class="h-4 w-4 shrink-0 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                            <span class="truncate">Materi <span x-text="i + 1"></span>: <strong x-text="f.name"></strong></span>
                                                            <span class="shrink-0 font-normal text-slate-500" x-text="ukuran(f.size)"></span>
                                                        </div>
                                                        <button type="button" @click="hapusMateri(i)" title="Batalkan file ini"
                                                            aria-label="Batalkan file materi"
                                                            style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;flex-shrink:0;border-radius:9999px;background:#fee2e2;color:#dc2626;">
                                                            <svg style="width:12px;height:12px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>

                                    @if ($adaBerkas)
                                        <div class="flex items-center gap-2 rounded-lg bg-slate-100/80 px-3 py-2 text-xs text-slate-600">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            <span>Berkas tersimpan saat ini:</span>
                                            <a href="{{ route('surat-tugas.laporan.berkas', $st) }}" target="_blank"
                                                class="font-semibold text-blue-600 hover:underline">
                                                {{ $laporan->file_laporan_nama ?: 'laporan.pdf' }}
                                            </a>
                                        </div>
                                    @endif

                                    <div class="flex flex-col-reverse gap-2.5 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
                                        <button name="aksi" value="draf"
                                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-200 bg-white px-4 py-2 text-xs font-semibold text-blue-600 shadow-sm transition hover:border-blue-300 hover:bg-blue-50/60 focus:outline-none focus:ring-2 focus:ring-blue-500/20 active:scale-95">
                                            <svg class="h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                            </svg>
                                            <span>Simpan draf</span>
                                        </button>

                                        <button name="aksi" value="kirim"
                                            onclick="return confirm('Kirim laporan? Setelah dikirim laporan dikunci, tugas peserta lain ditutup, dan ST ditandai selesai.')"
                                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-md shadow-blue-500/20 transition hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/30 focus:outline-none focus:ring-2 focus:ring-blue-500/40 active:scale-95">
                                            <svg class="h-4 w-4 text-blue-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                            </svg>
                                            <span>Kirim laporan</span>
                                        </button>
                                    </div>
                                </form>
                            @elseif ($laporan)
                                <div class="divide-y divide-slate-100 border-t border-slate-100 leading-6 text-slate-700">
                                    
                                    <div class="py-3">
                                        <h4 class="font-medium text-slate-500 mb-1.5">Notulensi / Berkas Laporan Hasil (PDF)</h4>
                                        @if ($bolehBukaBerkas)
                                            <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50/50 p-2.5 transition hover:border-slate-300">
                                                <div class="flex min-w-0 items-center gap-2.5">
                                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V7l-5-5H7a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                    <span class="truncate text-xs font-semibold text-slate-800">{{ $laporan->file_laporan_nama ?: 'Notulensi.pdf' }}</span>
                                                </div>

                                                <div class="flex shrink-0 items-center gap-1.5">
                                                    <a href="{{ route('surat-tugas.laporan.berkas', $st) }}" target="_blank"
                                                        class="inline-flex items-center gap-1 rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-blue-600 shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-blue-50 transition">
                                                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        <span>Lihat</span>
                                                    </a>

                                                    <a href="{{ route('surat-tugas.laporan.berkas', $st) }}?download=1" download
                                                        class="inline-flex items-center gap-1 rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-slate-50 transition">
                                                        <svg class="h-3.5 w-3.5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                        </svg>
                                                        <span>Unduh</span>
                                                    </a>
                                                </div>
                                            </div>
                                        @elseif ($adaBerkas)
                                            <p class="mt-1 text-xs text-slate-500">{{ $laporan->file_laporan_nama ?: 'Notulensi.pdf' }} (Hanya dapat dibuka oleh peserta yang ditugaskan / admin)</p>
                                        @else
                                            <p class="mt-1 text-xs text-slate-400">-</p>
                                        @endif
                                    </div>

                                    @if (filled($laporan->materi))
                                        @php
                                            $daftarMateri = array_map('trim', explode(',', $laporan->materi));
                                        @endphp
                                        <div class="py-3">
                                            <h4 class="font-medium text-slate-500 mb-2">Materi ({{ count($daftarMateri) }} file)</h4>
                                            <div class="space-y-2">
                                                @foreach ($daftarMateri as $fileMateri)
                                                    @if (filled($fileMateri))
                                                        <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50/50 p-2.5 transition hover:border-slate-300">
                                                            <div class="flex min-w-0 items-center gap-2.5">
                                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                    </svg>
                                                                </div>
                                                                <span class="truncate text-xs font-semibold text-slate-800">{{ $fileMateri }}</span>
                                                            </div>

                                                            <div class="flex shrink-0 items-center gap-1.5">
                                                                <a href="{{ route('surat-tugas.laporan.materi', [$st, 'filename' => $fileMateri]) }}" target="_blank"
                                                                    class="inline-flex items-center gap-1 rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-emerald-600 shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-emerald-50 transition">
                                                                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                    </svg>
                                                                    <span>Lihat</span>
                                                                </a>

                                                                <a href="{{ route('surat-tugas.laporan.materi', [$st, 'filename' => $fileMateri]) }}?download=1" download
                                                                    class="inline-flex items-center gap-1 rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-slate-50 transition">
                                                                    <svg class="h-3.5 w-3.5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                                    </svg>
                                                                    <span>Unduh</span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if (filled($laporan->notulensi) && !str_starts_with($laporan->notulensi, 'Berkas Notulensi:'))
                                        <div class="py-3">
                                            <h4 class="font-medium text-slate-500">Hasil Rapat / Ringkasan</h4>
                                            <p class="mt-0.5 whitespace-pre-line">{{ $laporan->notulensi }}</p>
                                        </div>
                                    @endif

                                    @if (filled($laporan->keputusan))
                                        <div class="py-3">
                                            <h4 class="font-medium text-slate-500">Keputusan</h4>
                                            <p class="mt-0.5 whitespace-pre-line">{{ $laporan->keputusan }}</p>
                                        </div>
                                    @endif

                                    @if ($laporan->tindakLanjut->isNotEmpty())
                                        <div class="py-3">
                                            <h4 class="mb-1 font-medium text-slate-500">Tindak lanjut</h4>
                                            @foreach ($laporan->tindakLanjut as $t)
                                                <div class="flex gap-2.5 border-b border-slate-100 py-1.5 last:border-0">
                                                    <span
                                                        class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[11px] font-semibold text-slate-600">{{ $loop->iteration }}</span>
                                                    <div class="min-w-0">
                                                        <div>{{ $t->uraian }}</div>
                                                        <div class="text-xs text-slate-500">
                                                            PIC: {{ $t->pic ?: '-' }} · Tenggat:
                                                            {{ $t->tenggat ? $t->tenggat->locale('id')->translatedFormat('j F Y') : '-' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($laporan->isDikirim())
                                        <div class="pt-3">
                                            <p class="rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-500">
                                                Dikirim oleh <strong>{{ $laporan->dikirimOleh->name ?? '-' }}</strong> pada {{ optional($laporan->dikirim_at)->format('d/m/Y H:i') }}.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <p class="rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-500">Laporan belum diisi.</p>
                            @endif
                        </div>
                    </section>
                @endif
            </div>

            <aside class="lg:sticky lg:top-4">
                <section class="{{ $card }}">
                    <div class="{{ $cardHead }}">
                        <h3 class="{{ $cardTitle }}">Peserta
                            <span class="ml-0.5 font-normal text-slate-500">({{ $st->peserta->count() }})</span>
                        </h3>
                        <div class="flex gap-1.5 text-[11px] font-medium">
                            @if ($jmlPns)
                                <span
                                    class="rounded-full border px-2 py-0.5 {{ $jenisWarna['pns']['pill'] }}">PNS {{ $jmlPns }}</span>
                            @endif
                            @if ($jmlNonPns)
                                <span
                                    class="rounded-full border px-2 py-0.5 {{ $jenisWarna['non_pns']['pill'] }}">Non PNS {{ $jmlNonPns }}</span>
                            @endif
                        </div>
                    </div>

                    <ul class="divide-y divide-slate-100 px-4">
                        @forelse ($st->peserta as $p)
                            @php $w = $jenisWarna[$p->jenis] ?? $jenisWarna['non_pns']; @endphp
                            <li class="flex items-start gap-2.5 py-2.5">
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[11px] font-semibold {{ $w['avatar'] }}">{{ mb_strtoupper(mb_substr($p->nama, 0, 1)) }}</span>
                                <div class="min-w-0 flex-1 text-xs">
                                    <div class="flex flex-wrap items-center gap-x-1.5 gap-y-0.5">
                                        <span class="text-[13px] font-semibold text-slate-900">{{ $p->nama }}</span>
                                        <span
                                            class="rounded-full border px-1.5 py-px text-[10px] font-medium {{ $w['pill'] }}">{{ $p->jenis_label }}</span>
                                    </div>
                                    @if ($p->jabatan)
                                        <div class="text-slate-600">{{ $p->jabatan }}</div>
                                    @endif
                                    @if ($p->nip)
                                        <div class="tabular-nums text-slate-500">NIP. {{ $p->nip }}</div>
                                    @endif
                                    @if ($adaTanggal)
                                        <div class="mt-0.5 inline-flex items-center gap-1 text-slate-500">
                                            <svg class="h-3 w-3 text-slate-400" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                                aria-hidden="true">
                                                <rect x="3" y="4.5" width="18" height="16" rx="2" />
                                                <path d="M3 9.5h18M8 2.5v4M16 2.5v4" />
                                            </svg>
                                            {{ $p->tanggal_tugas_indo ?? 'Sesuai rentang ST' }}
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="py-5 text-center text-[13px] text-slate-500">Belum ada peserta.</li>
                        @endforelse
                    </ul>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>