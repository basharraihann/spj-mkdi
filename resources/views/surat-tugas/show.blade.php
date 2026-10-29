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

    // Dikirim dari SuratTugasController@show. Untuk jalur Sesdep: true hanya kalau semua tahap disetujui
    // dan user adalah petugas tahap kontrol atau admin. Fallback ke $bolehKelola untuk ST jalur lama.
    $jalurSesdep = $st->memakaiAlurSesdep();
    $bolehTerbitkan = $bolehTerbitkan ?? ($jalurSesdep ? false : $bolehKelola);

    $errAlur = collect(['file_ttd', 'terbit', 'alur'])->map(fn($k) => $errors->first($k))->filter()->values();
    $errLaporan = $errors->first('notulensi') ?: ($errors->first('materi') ?: ($errors->first('materi.*') ?: $errors->first('laporan')));

    $adaTtd = filled($st->file_ttd_path ?? null);
    $namaUnit = method_exists($st, 'unit') ? optional($st->unit)->nama : null;

    // Token tampilan (disamakan dengan halaman Disposisi & Lembar Kontrol)
    $card = 'overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs';
    $cardHead = 'flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3 sm:px-5';
    $cardTitle = 'text-sm font-semibold text-slate-900';
    $kecil = 'text-[10px] font-semibold uppercase tracking-wide text-slate-400';
    $label = 'text-xs text-slate-500';
    $input = 'w-full rounded-lg border-slate-200 py-2 text-[13px] focus:border-blue-500 focus:ring-blue-500';
    $btnBase = 'inline-flex items-center justify-center rounded-xl px-3.5 py-2 text-[13px] font-semibold shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2';
    $btnPrimary = $btnBase . ' bg-blue-700 text-white hover:bg-blue-800 focus-visible:ring-blue-600';
    $btnSuccess = $btnBase . ' bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:ring-emerald-600';
    $btnOutline = $btnBase . ' border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 focus-visible:ring-slate-400';

    $jenisWarna = [
        'pns' => ['pill' => 'border-blue-200 bg-blue-50 text-blue-700', 'avatar' => 'bg-blue-100 text-blue-700'],
        'non_pns' => ['pill' => 'border-amber-200 bg-amber-50 text-amber-700', 'avatar' => 'bg-amber-100 text-amber-700'],
    ];

    $tampilTerbitkan = $st->status === 'draf' && $bolehTerbitkan;
@endphp
<x-app-layout>
    <div class="max-w-7xl mx-auto space-y-5 px-4 py-5 sm:px-6 lg:px-8">

        @if ($errAlur->isNotEmpty())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700" role="alert">
                @foreach ($errAlur as $msg)
                    <p>{{ $msg }}</p>
                @endforeach
            </div>
        @endif

        {{-- ===== Kartu judul ===== --}}
        <div class="{{ $card }}">
            <div class="px-4 pt-4 sm:px-6 sm:pt-5">
                <a href="{{ route('surat-tugas.index') }}"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 transition hover:text-slate-800">
                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke daftar
                </a>

                <div class="mt-2.5 flex flex-wrap items-start justify-between gap-x-4 gap-y-3">
                    <div class="min-w-0">
                        <p class="{{ $kecil }}">Surat Tugas</p>
                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1.5">
                            <h1 class="break-all text-xl font-bold leading-tight tracking-tight text-slate-900">
                                {{ $st->nomor_tampil }}</h1>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $badge['pill'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                {{ $st->status_label }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if ($adaTtd && Route::has('surat-tugas.ttd'))
                            <a href="{{ route('surat-tugas.ttd', $st) }}" target="_blank" rel="noopener"
                                class="{{ $btnOutline }}">Unduh ST (TTD)</a>
                        @endif

                        @if ($st->status === 'terbit' && \Illuminate\Support\Facades\Route::has('surat-tugas.selesai'))
                            <form method="POST" action="{{ route('surat-tugas.selesai', $st) }}">
                                @csrf
                                <button class="{{ $btnSuccess }}">Tandai selesai</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <dl
                class="mt-4 grid gap-x-6 gap-y-4 border-t border-slate-100 px-4 py-4 text-[13px] sm:grid-cols-2 sm:px-6 lg:grid-cols-4">
                <div class="min-w-0">
                    <dt class="{{ $kecil }}">Tempat, tanggal</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $st->tempat }}, {{ $st->tanggal_indo }}</dd>
                </div>
                <div class="min-w-0">
                    <dt class="{{ $kecil }}">Pelaksanaan</dt>
                    <dd class="mt-1 font-medium text-slate-900">
                        @if ($st->tanggal_mulai)
                            {{ $st->tanggal_mulai->locale('id')->translatedFormat('j F Y') }}
                            @if ($st->tanggal_selesai && !$st->tanggal_selesai->isSameDay($st->tanggal_mulai))
                                <span class="font-normal text-slate-400">s.d.</span>
                                {{ $st->tanggal_selesai->locale('id')->translatedFormat('j F Y') }}
                            @endif
                            @if ($st->kota || $st->provinsi)
                                <span class="block text-xs font-normal text-slate-500">
                                    {{ collect([$st->kota, $st->provinsi])->filter()->implode(', ') }}</span>
                            @endif
                        @else
                            -
                        @endif
                    </dd>
                </div>
                <div class="min-w-0">
                    <dt class="{{ $kecil }}">Penandatangan</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $st->nama_penandatangan }}
                        <span class="block text-xs font-normal text-slate-500">{{ $st->jabatan_penandatangan }}</span>
                    </dd>
                </div>
                <div class="min-w-0">
                    <dt class="{{ $kecil }}">Unit pembuat</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $namaUnit ?: '-' }}</dd>
                </div>

                @if ($adaTtd)
                    <div class="min-w-0 sm:col-span-2 lg:col-span-4">
                        <dt class="{{ $kecil }}">Ditandatangani dan diterbitkan</dt>
                        <dd class="mt-1 font-medium text-slate-900">
                            {{ optional($st->penandatanganiOleh ?? null)->name ?? '-' }}
                            <span class="font-normal text-slate-500">&middot;
                                {{ optional($st->ditandatangani_at)->format('d/m/Y H:i') ?? '-' }}</span>
                        </dd>
                    </div>
                @endif
            </dl>

            {{-- Strip penerbitan --}}
            @if ($tampilTerbitkan)
                <div class="border-t border-slate-100 bg-slate-50/60 px-4 py-3.5 sm:px-6">
                    <form method="POST" action="{{ route('surat-tugas.terbitkan', $st) }}"
                        enctype="multipart/form-data"
                        class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                        onsubmit="return confirm('Terbitkan ST ini? Setelah terbit, isi ST dikunci dan tidak bisa diedit, dan peserta yang punya akun akan mendapat tugas laporan.')">
                        @csrf
                        <label class="flex min-w-0 flex-col gap-1">
                            <span class="{{ $kecil }}">
                                Penerbitan &middot; PDF hasil TTD
                                @if ($jalurSesdep)
                                    <span class="text-red-600">*</span>
                                @else
                                    <span class="font-normal normal-case tracking-normal">(opsional)</span>
                                @endif
                            </span>
                            <input type="file" name="file_ttd" accept="application/pdf,.pdf" @required($jalurSesdep)
                                class="max-w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 file:ring-1 file:ring-inset file:ring-slate-200">
                        </label>
                        <button class="{{ $btnPrimary }} shrink-0">Terbitkan</button>
                    </form>
                </div>
            @endif
        </div>

        {{-- ===== Konten utama + samping ===== --}}
        <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_22rem]">

            {{-- ---------- Kolom utama ---------- --}}
            <div class="min-w-0 space-y-5">

                @include('surat-tugas._kontrol', ['st' => $st])

                {{-- Isi surat --}}
                <section class="{{ $card }}">
                    <div class="{{ $cardHead }}">
                        <h3 class="{{ $cardTitle }}">Isi surat</h3>
                    </div>
                    <div class="divide-y divide-slate-100 px-4 text-[13px] leading-6 text-slate-700 sm:px-5">
                        <div class="grid gap-1 py-3.5 sm:grid-cols-[6.5rem_minmax(0,1fr)] sm:gap-4">
                            <h4 class="{{ $kecil }} pt-1">Menimbang</h4>
                            <p>{{ $st->menimbang ?: '-' }}</p>
                        </div>

                        <div class="grid gap-1 py-3.5 sm:grid-cols-[6.5rem_minmax(0,1fr)] sm:gap-4">
                            <h4 class="{{ $kecil }} pt-1">Dasar</h4>
                            <ol class="list-decimal space-y-1 pl-5 marker:text-slate-400">
                                @forelse ($st->dasar ?? [] as $d)
                                    <li>{{ $d }}</li>
                                @empty
                                    <li class="-ml-5 list-none">-</li>
                                @endforelse
                            </ol>
                        </div>

                        <div class="grid gap-1 py-3.5 sm:grid-cols-[6.5rem_minmax(0,1fr)] sm:gap-4">
                            <h4 class="{{ $kecil }} pt-1">Kepada</h4>
                            <p>{{ $st->kepada }}</p>
                        </div>

                        <div class="grid gap-1 py-3.5 sm:grid-cols-[6.5rem_minmax(0,1fr)] sm:gap-4">
                            <h4 class="{{ $kecil }} pt-1">Untuk</h4>
                            <ol class="list-decimal space-y-1 pl-5 marker:text-slate-400">
                                @foreach ($st->untuk ?? [] as $u)
                                    <li>{{ $u }}</li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </section>

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
                                class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $warnaLaporan }}">
                                {{ $statusLaporan }}
                            </span>
                        </div>

                        <div class="space-y-4 px-4 py-4 text-[13px] sm:px-5 sm:py-5">

                            <div>
                                <div class="{{ $kecil }} mb-1.5">Penugasan laporan</div>
                                @forelse ($tugasLapor as $tg)
                                    @php $nm = $tg->user->name ?? '—'; @endphp
                                    <div
                                        class="flex items-center justify-between gap-3 border-b border-slate-100 py-2 last:border-0">
                                        <div class="flex min-w-0 items-center gap-2.5">
                                            <span
                                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[11px] font-semibold text-slate-600">{{ mb_strtoupper(mb_substr($nm, 0, 1)) }}</span>
                                            <span class="truncate text-slate-800">{{ $nm }}</span>
                                        </div>
                                        <span
                                            class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset {{ $warnaTugas[$tg->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">{{ ucfirst($tg->status) }}</span>
                                    </div>
                                @empty
                                    <p class="rounded-lg bg-slate-50 px-3 py-2.5 text-xs text-slate-500">Tidak ada
                                        peserta yang akunnya dipetakan ke pegawai, jadi tidak ada tugas lapor otomatis.
                                        Admin masih bisa mengisi laporan.</p>
                                @endforelse
                                @if ($tugasLapor->isNotEmpty())
                                    <p class="mt-2 text-xs text-slate-500">Cukup satu peserta yang mengirim laporan;
                                        tugas peserta lain otomatis ditutup.</p>
                                @endif
                            </div>

                            @if ($errLaporan)
                                <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-xs text-red-700"
                                    role="alert">
                                    {{ $errLaporan }}</div>
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

                                    <p class="rounded-lg px-3 py-2 text-xs text-red-700 border border-red-200 bg-red-50"
                                        x-show="err" x-cloak x-text="err" role="alert"></p>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">
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
                                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">
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

                                    <div class="py-3.5">
                                        <h4 class="{{ $kecil }} mb-2">Notulensi / Berkas Laporan Hasil (PDF)</h4>
                                        @if ($bolehBukaBerkas)
                                            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 transition hover:border-slate-300">
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
                                        <div class="py-3.5">
                                            <h4 class="{{ $kecil }} mb-2">Materi ({{ count($daftarMateri) }} file)</h4>
                                            <div class="space-y-2">
                                                @foreach ($daftarMateri as $fileMateri)
                                                    @if (filled($fileMateri))
                                                        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 transition hover:border-slate-300">
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
                                        <div class="py-3.5">
                                            <h4 class="{{ $kecil }}">Hasil Rapat / Ringkasan</h4>
                                            <p class="mt-1 whitespace-pre-line">{{ $laporan->notulensi }}</p>
                                        </div>
                                    @endif

                                    @if (filled($laporan->keputusan))
                                        <div class="py-3.5">
                                            <h4 class="{{ $kecil }}">Keputusan</h4>
                                            <p class="mt-1 whitespace-pre-line">{{ $laporan->keputusan }}</p>
                                        </div>
                                    @endif

                                    @if ($laporan->tindakLanjut->isNotEmpty())
                                        <div class="py-3.5">
                                            <h4 class="{{ $kecil }} mb-1.5">Tindak lanjut</h4>
                                            @foreach ($laporan->tindakLanjut as $t)
                                                <div class="flex gap-2.5 border-b border-slate-100 py-2 last:border-0">
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
                                        <div class="pt-3.5">
                                            <p class="rounded-lg bg-slate-50 px-3 py-2.5 text-xs text-slate-500">
                                                Dikirim oleh <strong>{{ $laporan->dikirimOleh->name ?? '-' }}</strong> pada {{ optional($laporan->dikirim_at)->format('d/m/Y H:i') }}.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <p class="rounded-lg bg-slate-50 px-3 py-2.5 text-xs text-slate-500">Laporan belum diisi.</p>
                            @endif
                        </div>
                    </section>
                @endif
            </div>

            {{-- ---------- Kolom samping ---------- --}}
            <aside class="min-w-0 space-y-5 lg:sticky lg:top-4">

                {{-- Peserta --}}
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

                    <ul class="divide-y divide-slate-100 px-4 sm:px-5">
                        @forelse ($st->peserta as $p)
                            @php $w = $jenisWarna[$p->jenis] ?? $jenisWarna['non_pns']; @endphp
                            <li class="flex items-start gap-2.5 py-3">
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[11px] font-semibold {{ $w['avatar'] }}">{{ mb_strtoupper(mb_substr($p->nama, 0, 1)) }}</span>
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
                            <li class="py-6 text-center text-[13px] text-slate-500">Belum ada peserta.</li>
                        @endforelse
                    </ul>
                </section>

                {{-- Pengelompokan (Tusi) --}}
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

                    <div class="px-4 py-4 text-[13px] sm:px-5">
                        @if ($bolehUbahTusi)
                            <form method="POST" action="{{ route('surat-tugas.tusi', $st) }}" class="space-y-2.5">
                                @csrf
                                @method('PATCH')

                                <select name="tusi_id" aria-label="Pengelompokan (Tusi)" class="{{ $input }}">
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

                                @error('tusi_id')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror

                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xs leading-relaxed text-slate-500">Bisa diubah setelah ST terbit dan
                                        tidak memengaruhi isi surat.</p>
                                    <button class="{{ $btnPrimary }} shrink-0">Simpan</button>
                                </div>
                            </form>
                        @else
                            <p class="text-xs leading-relaxed text-slate-500">Hanya admin atau pembuat ST yang dapat
                                mengubah pengelompokan.</p>
                        @endif
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>