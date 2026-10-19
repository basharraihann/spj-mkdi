@php
    // Badge status ST (kelas Tailwind literal supaya ikut ter-build)
    $badge = [
        'draf' => ['pill' => 'bg-slate-100 text-slate-700 ring-slate-200', 'dot' => 'bg-slate-400'],
        'terbit' => ['pill' => 'bg-blue-50 text-blue-700 ring-blue-200', 'dot' => 'bg-blue-500'],
        'selesai' => ['pill' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'dot' => 'bg-emerald-500'],
        'batal' => ['pill' => 'bg-red-50 text-red-700 ring-red-200', 'dot' => 'bg-red-500'],
    ][$st->status] ?? ['pill' => 'bg-slate-100 text-slate-700 ring-slate-200', 'dot' => 'bg-slate-400'];

    $adaTanggal = $st->peserta->contains(fn($p) => (bool) $p->tanggal_mulai);
    $jmlPns = $st->peserta->where('jenis', 'pns')->count();
    $jmlNonPns = $st->peserta->where('jenis', '!=', 'pns')->count();

    // Pengelompokan (tusi) — variabel dikirim dari SuratTugasController@show
    $tusiOptions = $tusiOptions ?? collect();
    $bolehUbahTusi = $bolehUbahTusi ?? false;
    $bolehKelola = $bolehKelola ?? false;
    // tusi yang sedang dipakai tapi sudah dinonaktifkan tetap ditampilkan di dropdown
    $tusiNonaktifDipakai = $st->tusi && !$tusiOptions->contains('id', $st->tusi_id);

    // Kelas yang dipakai berulang (versi compact)
    $card = 'overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm';
    $cardHead = 'flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-2.5';
    $cardTitle = 'text-sm font-semibold text-slate-900';
    $label = 'text-xs text-slate-500';
    $input = 'w-full rounded-md border-slate-300 py-1.5 text-[13px] focus:border-blue-600 focus:ring-blue-600';
    $btnBase = 'inline-flex items-center justify-center rounded-md px-3 py-1.5 text-[13px] font-semibold shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2';
    $btnPrimary = $btnBase . ' bg-blue-600 text-white hover:bg-blue-700 focus-visible:ring-blue-600';
    $btnSuccess = $btnBase . ' bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:ring-emerald-600';
    $btnOutline = $btnBase . ' border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus-visible:ring-slate-400';

    // Warna jenis peserta (PNS biru, Non PNS amber)
    $jenisWarna = [
        'pns' => ['pill' => 'border-blue-200 bg-blue-50 text-blue-700', 'avatar' => 'bg-blue-100 text-blue-700'],
        'non_pns' => ['pill' => 'border-amber-200 bg-amber-50 text-amber-700', 'avatar' => 'bg-amber-100 text-amber-700'],
    ];
@endphp
<x-app-layout>
    <div class="max-w-7xl mx-auto space-y-4 px-4 py-4 sm:px-6 lg:px-8">

        {{-- Header + ringkasan --}}
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

            {{-- ===== Kolom utama ===== --}}
            <div class="space-y-4 lg:col-span-2">

                {{-- Isi surat --}}
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

                {{-- Laporan hasil ST --}}
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

                        $barisTL = old('tindak_lanjut', $laporan
                            ? $laporan->tindakLanjut->map(fn($t) => [
                                'uraian' => $t->uraian,
                                'pic' => $t->pic,
                                'tenggat' => optional($t->tenggat)->format('Y-m-d'),
                            ])->values()->all()
                            : []);
                        if (empty($barisTL)) {
                            $barisTL = [['uraian' => '', 'pic' => '', 'tenggat' => '']];
                        }
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

                            {{-- Penugasan --}}
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
                                {{-- Form isi laporan (Alpine untuk baris tindak lanjut) --}}
                                <form method="POST" action="{{ route('surat-tugas.laporan.simpan', $st) }}"
                                    class="space-y-4 border-t border-slate-100 pt-4"
                                    x-data="{ rows: @js($barisTL) }">
                                    @csrf

                                    <div>
                                        <label for="notulensi" class="{{ $label }}">Notulensi / hasil rapat
                                            <span class="text-red-600">*</span></label>
                                        <textarea id="notulensi" name="notulensi" rows="6" class="{{ $input }} mt-1">{{ old('notulensi', $laporan?->notulensi) }}</textarea>
                                    </div>

                                    <div>
                                        <label for="keputusan" class="{{ $label }}">Keputusan</label>
                                        <textarea id="keputusan" name="keputusan" rows="2" class="{{ $input }} mt-1">{{ old('keputusan', $laporan?->keputusan) }}</textarea>
                                    </div>

                                    <div>
                                        <div class="{{ $label }} mb-1.5">Tindak lanjut</div>
                                        <div class="space-y-1.5">
                                            <template x-for="(row, i) in rows" :key="i">
                                                <div class="grid items-center gap-1.5 md:grid-cols-12">
                                                    <input type="text" x-model="row.uraian"
                                                        :name="`tindak_lanjut[${i}][uraian]`"
                                                        placeholder="Uraian tindak lanjut"
                                                        class="{{ $input }} md:col-span-6">
                                                    <input type="text" x-model="row.pic"
                                                        :name="`tindak_lanjut[${i}][pic]`" placeholder="PIC"
                                                        class="{{ $input }} md:col-span-3">
                                                    <input type="date" x-model="row.tenggat"
                                                        :name="`tindak_lanjut[${i}][tenggat]`"
                                                        class="{{ $input }} md:col-span-2">
                                                    <div class="md:col-span-1 md:text-right">
                                                        <button type="button" @click="rows.splice(i, 1)"
                                                            x-show="rows.length > 1" title="Hapus baris"
                                                            class="rounded-md border border-red-200 bg-white px-2 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50">Hapus</button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                        <button type="button"
                                            @click="rows.push({ uraian: '', pic: '', tenggat: '' })"
                                            class="mt-1.5 text-[13px] font-medium text-blue-700 hover:underline focus:outline-none focus-visible:underline">+
                                            Tambah tindak lanjut</button>
                                    </div>

                                    <div
                                        class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-3 sm:flex-row sm:justify-end">
                                        <button name="aksi" value="draf" class="{{ $btnOutline }}">Simpan
                                            draf</button>
                                        <button name="aksi" value="kirim" class="{{ $btnSuccess }}"
                                            onclick="return confirm('Kirim laporan? Setelah dikirim laporan dikunci, tugas peserta lain ditutup, dan ST ditandai selesai.')">Kirim
                                            laporan</button>
                                    </div>
                                </form>
                            @elseif ($laporan)
                                {{-- Hanya lihat --}}
                                <div
                                    class="divide-y divide-slate-100 border-t border-slate-100 leading-6 text-slate-700">
                                    <div class="py-3">
                                        <h4 class="font-medium text-slate-500">Notulensi / hasil rapat</h4>
                                        <p class="mt-0.5 whitespace-pre-line">{{ $laporan->notulensi ?: '-' }}</p>
                                    </div>
                                    <div class="py-3">
                                        <h4 class="font-medium text-slate-500">Keputusan</h4>
                                        <p class="mt-0.5 whitespace-pre-line">{{ $laporan->keputusan ?: '-' }}</p>
                                    </div>
                                    <div class="py-3">
                                        <h4 class="mb-1 font-medium text-slate-500">Tindak lanjut</h4>
                                        @forelse ($laporan->tindakLanjut as $t)
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
                                        @empty
                                            <p class="text-slate-500">-</p>
                                        @endforelse
                                    </div>
                                    @if ($laporan->isDikirim())
                                        <div class="pt-3">
                                            <p class="rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-500">Dikirim
                                                oleh {{ $laporan->dikirimOleh->name ?? '-' }}
                                                pada {{ optional($laporan->dikirim_at)->format('d/m/Y H:i') }}.</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <p class="rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-500">Laporan belum
                                    diisi.</p>
                            @endif
                        </div>
                    </section>
                @endif
            </div>

            {{-- ===== Sidebar: Peserta ===== --}}
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