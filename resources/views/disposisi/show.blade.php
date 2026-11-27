<x-app-layout title="Detail Disposisi">
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Disposisi</h2>
            <a href="{{ route('disposisi.index') }}"
                class="inline-flex flex-none items-center gap-1.5 text-sm font-medium text-slate-600 hover:text-slate-900">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                {{ $bolehTeruskan ? 'Semua disposisi' : 'Kalender disposisi' }}
            </a>
        </div>
    </x-slot>

    @php
        $badgeSifat = [
            'biasa' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'segera' => 'bg-amber-50 text-amber-800 ring-amber-200',
            'sangat_segera' => 'bg-red-50 text-red-700 ring-red-200',
            'rahasia' => 'bg-purple-50 text-purple-700 ring-purple-200',
        ];
        $tgl = fn($d) => $d ? $d->translatedFormat('d M Y') : '–';

        // Gaya input, sama dengan form input surat
        $dasar = 'block w-full rounded-lg bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 border shadow-none outline-none transition focus:ring-4';
        $normal = 'border-slate-300 hover:border-slate-400 focus:border-blue-500 focus:ring-blue-100';
        $salah = 'border-red-400 focus:border-red-500 focus:ring-red-100';
        $lbl = 'block text-sm font-medium text-slate-700 mb-1.5';
        $galat = 'mt-1.5 text-xs text-red-600';
        $wajib = '<span class="text-red-500" aria-hidden="true">*</span>';

        // Form mana yang dibuka lagi setelah validasi gagal
        $formLama = in_array(old('_aksi'), ['teruskan', 'selesai'], true) ? old('_aksi') : '';
        $idLama = (int) old('_disposisi');

        $selesai = $surat->status === 'selesai';
    @endphp

    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                role="status">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            {{-- ============ Kiri: data surat + posisi ============ --}}
            <div class="min-w-0 space-y-6">

                {{-- Data surat --}}
                <section class="rounded-2xl border border-slate-200 bg-white" aria-labelledby="judul-surat">
                    <header class="border-b border-slate-100 px-6 py-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $badgeSifat[$surat->sifat] ?? $badgeSifat['biasa'] }}">{{ $surat->sifat_label }}</span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $selesai ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-blue-50 text-blue-700 ring-blue-200' }}">
                                <span
                                    class="h-1.5 w-1.5 rounded-full {{ $selesai ? 'bg-emerald-500' : 'bg-blue-500' }}"></span>
                                {{ $selesai ? 'Selesai' : 'Proses' }}
                            </span>
                        </div>
                        <h3 id="judul-surat"
                            class="mt-3 text-base font-semibold leading-snug text-slate-900 break-words">
                            {{ $surat->perihal }}</h3>
                    </header>

                    <dl class="divide-y divide-slate-100 px-6 text-sm">
                        @foreach ([
                                'Nomor surat' => $surat->nomor_surat,
                                'Asal surat' => $surat->asal_surat,
                                'Tanggal surat' => $tgl($surat->tanggal_surat),
                                'Diterima' => $tgl($surat->tanggal_terima),
                                'Nomor agenda' => $surat->nomor_agenda ?: '–',
                                'Lampiran' => $surat->lampiran ?: '–',
                                'Jadwal' => $surat->tanggal_kegiatan
                                    ? $tgl($surat->tanggal_kegiatan) . ($surat->waktu_label ? ', ' . $surat->waktu_label : '') . ($surat->tempat ? ' · ' . $surat->tempat : '')
                                    : '–',
                                'Dicatat oleh' => ($surat->pembuat?->name ?? '-') . ($surat->unit ? ' · ' . $surat->unit->nama : ''),
                            ] as $k => $v)
                            <div class="flex gap-4 py-2.5">
                                <dt class="w-28 shrink-0 text-slate-500">{{ $k }}</dt>
                                <dd class="min-w-0 break-words text-slate-900">{{ $v }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    {{-- Aksi surat --}}
                    <div class="space-y-2 border-t border-slate-100 px-6 py-4">
                        @if ($surat->link_berkas)
                            <a href="{{ $surat->link_berkas }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200">
                                Buka PDF surat
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M10 6H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4M14 4h6v6M20 4 10 14" />
                                </svg>
                            </a>
                        @elseif ($surat->file_path)
                            <a href="{{ route('disposisi.berkas', $surat) }}" target="_blank"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200">
                                Lihat file surat
                                @if ($surat->file_name)
                                    <span
                                        class="max-w-[8rem] truncate text-xs font-normal text-blue-100">{{ $surat->file_name }}</span>
                                @endif
                            </a>
                        @endif

                        <a href="{{ route('disposisi.lembar', $surat) }}" target="_blank"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-slate-200">
                            <svg class="h-4 w-4 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z" />
                                <path d="M14 3v5h5" />
                            </svg>
                            Lembar disposisi (PDF)
                        </a>

                        @if (($bolehUbah ?? false) || $bolehHapus)
                            <div class="flex gap-2">
                                @if ($bolehUbah ?? false)
                                    <a href="{{ route('disposisi.edit', $surat) }}"
                                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-slate-200">
                                        <svg class="h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9" />
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z" />
                                        </svg>
                                        Ubah
                                    </a>
                                @endif
                                @if ($bolehHapus)
                                    <form method="POST" action="{{ route('disposisi.destroy', $surat) }}" class="flex-1"
                                        onsubmit="return confirm('Hapus surat ini beserta seluruh disposisinya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-red-100">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path
                                                    d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m2 0v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6" />
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                </section>

                {{-- Posisi surat --}}
                @php
                    // Eselon II yang menandai "ikut" saat meneruskan
                    $ikutSerta = $surat->disposisi()->with('kepada.unit')->where('ikut', true)->get();
                @endphp
                <section class="rounded-2xl border border-slate-200 bg-white" aria-labelledby="judul-posisi">
                    <header class="border-b border-slate-100 px-6 py-4">
                        <h3 id="judul-posisi" class="text-base font-semibold text-slate-900">Posisi surat sekarang</h3>
                        <p class="text-xs text-slate-500">Penerima yang belum meneruskan atau menyelesaikan.</p>
                    </header>
                    <ul class="divide-y divide-slate-100 px-6">
                        @forelse ($posisi as $d)
                            <li class="flex items-center gap-3 py-3">
                                <x-avatar :user="$d->kepada" class="h-9 w-9 text-xs" />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-slate-900">{{ $d->kepada?->name ?? '-' }}
                                    </p>
                                    <p class="truncate text-xs text-slate-500">{{ $d->kepada?->unit?->nama }}</p>
                                </div>
                                <span
                                    class="flex-none rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $d->status === 'baru' ? 'bg-red-50 text-red-700 ring-red-200' : 'bg-amber-50 text-amber-700 ring-amber-200' }}">{{ $d->status_label }}</span>

                                {{-- Batalkan: hanya pengirim, selama penerima belum meneruskan/menyelesaikan --}}
                                @if ((int) $d->dari_user_id === (int) auth()->id() && in_array($d->status, ['baru', 'dibaca'], true))
                                    <form method="POST" action="{{ route('disposisi.batalkan', $d) }}" class="flex-none"
                                        onsubmit="return confirm(@js('Batalkan disposisi ke ' . ($d->kepada?->name ?? 'penerima') . '?'))">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="rounded-lg border border-red-200 px-2.5 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-red-100">Batalkan</button>
                                    </form>
                                @endif
                            </li>
                        @empty
                            <li class="flex items-center gap-2 py-4 text-sm text-emerald-700">
                                <svg class="h-5 w-5 flex-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm4.7 7.7a1 1 0 0 0-1.4-1.4L10.5 13.1l-1.8-1.8a1 1 0 1 0-1.4 1.4l2.5 2.5a1 1 0 0 0 1.4 0l5.5-5.5Z"
                                        clip-rule="evenodd" />
                                </svg>
                                Semua disposisi sudah ditindaklanjuti.
                            </li>
                        @endforelse
                    </ul>

                    {{-- Eselon II yang ikut bersama staf --}}
                    @if ($ikutSerta->isNotEmpty())
                        <div class="border-t border-slate-100 px-6 py-3">
                            <p class="mb-2 text-xs font-medium text-slate-500">Ikut serta bersama staf</p>
                            <ul class="space-y-2">
                                @foreach ($ikutSerta as $d)
                                    <li class="flex items-center gap-3">
                                        <x-avatar :user="$d->kepada" class="h-8 w-8 text-xs" />
                                        <p class="min-w-0 flex-1 truncate text-sm font-medium text-slate-900">
                                            {{ $d->kepada?->name ?? '-' }}</p>
                                        <span
                                            class="flex-none rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-200">Ikut</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </section>
            </div>

            {{-- ============ Kanan: permohonan saya + feedback + alur ============ --}}
            <div class="min-w-0 space-y-6 lg:col-span-2">

                {{-- Permohonan disposisi untuk saya --}}
                @foreach ($milikSaya as $m)
                    @php $formIni = $idLama === $m->id; @endphp
                    <section x-data="{ aksi: @js($formIni ? $formLama : '') }"
                        class="rounded-2xl border border-slate-200 border-t-4 border-t-blue-600 bg-white">
                        <div class="flex flex-wrap items-start justify-between gap-4 p-6">
                            <div class="flex min-w-0 flex-1 items-start gap-3">
                                <x-avatar :user="$m->dari" class="h-11 w-11 text-sm" />
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Permohonan
                                        disposisi</p>
                                    <p class="mt-0.5 text-sm text-slate-700">
                                        Diajukan oleh <span
                                            class="font-semibold text-slate-900">{{ $m->dari?->name ?? '-' }}</span>
                                    </p>
                                    @if ($m->batas_waktu)
                                        <p
                                            class="mt-1 inline-flex items-center gap-1.5 text-xs {{ $m->terlambat ? 'font-semibold text-red-600' : 'text-slate-500' }}">
                                            <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10" />
                                                <path d="M12 6v6l4 2" />
                                            </svg>
                                            Batas waktu
                                            {{ $m->batas_waktu->translatedFormat('d M Y') }}{{ $m->terlambat ? ' (terlambat)' : '' }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @if ($bolehTeruskan)
                                    <button type="button" @click="aksi = aksi === 'teruskan' ? '' : 'teruskan'"
                                        :class="aksi === 'teruskan' ? 'border-blue-600 bg-blue-600 text-white' : 'border-blue-200 bg-white text-blue-700 hover:bg-blue-50'"
                                        class="rounded-lg border px-4 py-2 text-sm font-semibold transition focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100">{{ $m->status === 'diteruskan' ? 'Ubah disposisi' : 'Teruskan' }}</button>
                                @endif
                                <button type="button" @click="aksi = aksi === 'selesai' ? '' : 'selesai'"
                                    :class="aksi === 'selesai' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50'"
                                    class="rounded-lg border px-4 py-2 text-sm font-semibold transition focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-100">Selesaikan</button>
                            </div>
                        </div>

                        @if (count($m->instruksi_label) || $m->catatan)
                            <div class="space-y-3 border-t border-slate-100 px-6 py-4">
                                @if (count($m->instruksi_label))
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($m->instruksi_label as $ins)
                                            <span
                                                class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-800 ring-1 ring-inset ring-blue-100">{{ $ins }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if ($m->catatan)
                                    <p
                                        class="whitespace-pre-line rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                        {{ $m->catatan }}</p>
                                @endif
                            </div>
                        @endif

                        {{-- Form teruskan (mode ubah kalau sudah pernah diteruskan) --}}
                        @if ($bolehTeruskan)
                            @php
                                $edit = $m->status === 'diteruskan';
                                $idPenerima = fn($c) => $c->pluck('kepada_user_id')->map(fn($i) => (int) $i);
                                $anakSaya = $surat->disposisi->where('parent_id', $m->id);
                                $anakBisaUbah = $anakSaya->whereIn('status', ['baru', 'dibaca']);
                                $acuan = $anakBisaUbah->first() ?? $anakSaya->first();
                                $awal = $edit ? [
                                    'kepada' => $idPenerima($anakBisaUbah)->values()->all(),
                                    'instruksi' => (array) ($acuan?->instruksi ?? []),
                                    'catatan' => $acuan?->catatan,
                                    'batas_waktu' => $acuan?->batas_waktu?->format('Y-m-d'),
                                ] : [];
                                // Terkunci: sudah punya disposisi dan bukan penerima yang masih bisa saya ubah
                                $sudahDapat = $idPenerima($surat->disposisi)->diff($idPenerima($anakBisaUbah))->values()->all();
                                $barusan = session()->hasOldInput();
                            @endphp

                            <form x-show="aksi === 'teruskan'" x-cloak method="POST"
                                action="{{ $edit ? route('disposisi.teruskan.ubah', $m) : route('disposisi.teruskan', $m) }}"
                                class="space-y-4 border-t border-slate-100 p-6">
                                @csrf
                                @if ($edit) @method('PUT') @endif
                                <input type="hidden" name="_aksi" value="teruskan">
                                <input type="hidden" name="_disposisi" value="{{ $m->id }}">

                                @if ($edit)
                                    <p class="rounded-lg border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
                                        Disposisi ini sudah Anda teruskan. Lepas centang untuk menarik penerima (hanya yang belum
                                        memproses), atau centang untuk menambah penerima baru.
                                    </p>
                                @endif

                                @if (auth()->user()->role === \App\Models\User::ROLE_ESELON2)
                                    <label
                                        class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-blue-100 bg-blue-50/60 px-3.5 py-2.5 text-sm text-slate-700">
                                        <input type="checkbox" name="ikut" value="1" @checked($barusan ? old('ikut') : $m->ikut)
                                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        Saya ikut (hadir / menangani bersama staf)
                                    </label>
                                @endif

                                @include('disposisi._tujuan', [
                                    'idForm' => 'teruskan-' . $m->id,
                                    'sudahDapat' => $sudahDapat,
                                    'awal' => $awal,
                                ])

                                <div class="flex justify-end">
                                    <button
                                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200">{{ $edit ? 'Simpan perubahan' : 'Kirim disposisi' }}</button>
                                </div>
                            </form>
                        @endif

                        {{-- Form selesai --}}
                        <form x-show="aksi === 'selesai'" x-cloak method="POST"
                            action="{{ route('disposisi.selesai', $m) }}" enctype="multipart/form-data"
                            class="space-y-4 border-t border-slate-100 p-6">
                            @csrf
                            <input type="hidden" name="_aksi" value="selesai">
                            <input type="hidden" name="_disposisi" value="{{ $m->id }}">
                            <div>
                                <label for="laporan-{{ $m->id }}" class="{{ $lbl }}">Laporan tindak lanjut
                                    {!! $wajib !!}</label>
                                <textarea id="laporan-{{ $m->id }}" name="laporan" rows="3"
                                    class="{{ $dasar }} {{ $formIni && $errors->has('laporan') ? $salah : $normal }}"
                                    placeholder="Apa yang sudah dikerjakan atau hasilnya">{{ $formIni ? old('laporan') : '' }}</textarea>
                                @if ($formIni)
                                    @error('laporan')
                                    <p class="{{ $galat }}">{{ $message }}</p> @enderror
                                @endif
                            </div>
                            <div>
                                <label for="file-{{ $m->id }}" class="{{ $lbl }}">Lampiran <span
                                        class="font-normal text-slate-400">(opsional, maks 10 MB)</span></label>
                                <input id="file-{{ $m->id }}" type="file" name="file"
                                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                    class="block w-full rounded-lg border border-slate-300 bg-white text-sm text-slate-600 file:mr-3 file:cursor-pointer file:border-0 file:bg-emerald-50 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-emerald-700 hover:file:bg-emerald-100">
                                @if ($formIni)
                                    @error('file')
                                    <p class="{{ $galat }}">{{ $message }}</p> @enderror
                                @endif
                            </div>
                            @if ($m->status === 'diteruskan')
                                <p class="rounded-lg border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
                                    Disposisi ini sudah Anda teruskan. Menyelesaikan berarti Anda menutup bagian Anda.
                                </p>
                            @endif
                            <div class="flex justify-end">
                                <button
                                    class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-200">Tandai
                                    selesai</button>
                            </div>
                        </form>
                    </section>
                @endforeach

                {{-- Feedback --}}
                <section class="rounded-2xl border border-slate-200 bg-white" aria-labelledby="judul-feedback">
                    <header class="flex items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
                        <div>
                            <h3 id="judul-feedback" class="text-base font-semibold text-slate-900">Feedback</h3>
                            <p class="text-xs text-slate-500">Tanggapan dari penerima maupun pengirim disposisi.</p>
                        </div>
                        @if ($surat->feedback->count())
                            <span
                                class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">{{ $surat->feedback->count() }}</span>
                        @endif
                    </header>

                    <div class="space-y-4 p-6">
                        <ul class="space-y-3">
                            @forelse ($surat->feedback as $f)
                                @php $punyaSaya = (int) $f->user_id === (int) auth()->id(); @endphp
                                <li class="flex gap-3">
                                    <x-avatar :user="$f->user" class="h-9 w-9 text-xs" />
                                    <div
                                        class="min-w-0 flex-1 rounded-xl border px-4 py-3 {{ $punyaSaya ? 'border-blue-100 bg-blue-50/60' : 'border-slate-100 bg-slate-50' }}">
                                        <div
                                            class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5 text-xs">
                                            <span class="font-semibold text-slate-900">{{ $f->user?->name ?? '-' }}
                                                @if ($f->user?->unit?->nama)
                                                    <span class="font-normal text-slate-400">· {{ $f->user->unit->nama }}</span>
                                                @endif
                                            </span>
                                            <span
                                                class="text-slate-400">{{ $f->created_at?->translatedFormat('d M Y H:i') }}</span>
                                        </div>
                                        <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $f->isi }}</p>
                                    </div>
                                </li>
                            @empty
                                <li
                                    class="rounded-lg border border-dashed border-slate-300 px-4 py-4 text-center text-sm text-slate-500">
                                    Belum ada feedback.</li>
                            @endforelse
                        </ul>

                        <form method="POST" action="{{ route('disposisi.feedback', $surat) }}"
                            class="space-y-3 border-t border-slate-100 pt-4">
                            @csrf
                            <label for="isi-feedback" class="sr-only">Tulis feedback</label>
                            <textarea id="isi-feedback" name="isi" rows="2" required maxlength="2000"
                                class="{{ $dasar }} {{ $errors->has('isi') ? $salah : $normal }}"
                                placeholder="Tulis feedback, mis. siap hadir, sudah ditindaklanjuti, atau perlu data tambahan">{{ old('isi') }}</textarea>
                            @error('isi')
                            <p class="{{ $galat }}">{{ $message }}</p> @enderror
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                @if ($milikSaya->isNotEmpty())
                                    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600">
                                        <input type="checkbox" name="tandai_selesai" value="1"
                                            class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        Sekalian tandai disposisi saya selesai
                                    </label>
                                @else
                                    <span></span>
                                @endif
                                <button
                                    class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus-visible:ring-4 focus-visible:ring-slate-300">Kirim
                                    feedback</button>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- Alur disposisi --}}
                <section class="rounded-2xl border border-slate-200 bg-white" aria-labelledby="judul-alur">
                    <header class="border-b border-slate-100 px-6 py-4">
                        <h3 id="judul-alur" class="text-base font-semibold text-slate-900">Alur disposisi</h3>
                        <p class="text-xs text-slate-500">Urutan dari pencatat surat sampai penerima terakhir.</p>
                    </header>
                    <div class="p-6">
                        @if (count($pohon))
                            <ul class="space-y-3">
                                @foreach ($pohon as $n)
                                    @include('disposisi._node', ['n' => $n])
                                @endforeach
                            </ul>
                        @else
                            <p
                                class="rounded-lg border border-dashed border-slate-300 px-4 py-4 text-center text-sm text-slate-500">
                                Belum ada disposisi.</p>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>