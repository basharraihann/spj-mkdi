@php
    $surat = $disposisi->suratMasuk;
    $sayaPenerima = (int) $disposisi->penerima_id === (int) auth()->id();
    $adaTindak = $bolehLapor || $bolehDisposisi;
    $adaBerkas = !empty($surat?->drive_link);

    // Link berkas -> URL preview Google Drive
    $embedUrl = $surat?->drive_link;
    if ($embedUrl && str_contains($embedUrl, 'drive.google.com') && !str_contains($embedUrl, '/preview')) {
        $embedUrl = preg_replace('/\/view.*/', '/preview', $embedUrl);
    }

    // Progres penugasan (anak langsung dari disposisi ini, selain tembusan)
    $anak = $isStaff ? collect() : collect($byParent->get($disposisi->id, []))->where('is_tembusan', false);
    $totalAnak = $anak->count();
    $selesaiAnak = $anak->where('status', 'selesai')->count();
    $persen = $totalAnak > 0 ? (int) round($selesaiAnak / $totalAnak * 100) : 0;

    // Tab awal
    $tabAwal = $adaTindak
        ? 'tindak'
        : (($laporanList->isNotEmpty() && $disposisi->status === 'selesai') ? 'laporan' : 'hierarki');

    // Pill: 11px semibold (sama dengan angka dokumen di Agenda)
    $pill = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold capitalize ring-1 ring-inset';
    $warnaStatus = [
        'baru' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'dibaca' => 'bg-slate-100 text-slate-600 ring-slate-200',
        'diproses' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'selesai' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    ];
    $labelStatus = ['baru' => 'Baru', 'dibaca' => 'Dibaca', 'diproses' => 'Diproses', 'selesai' => 'Selesai'];
    $warnaSifat = [
        'biasa' => 'bg-slate-100 text-slate-600 ring-slate-200',
        'segera' => 'bg-red-50 text-red-700 ring-red-200',
        'rahasia' => 'bg-amber-50 text-amber-800 ring-amber-200',
    ];

    // Token tipografi (disamakan dengan halaman Agenda)
    $kecil = 'text-[10px] font-semibold uppercase tracking-wide text-slate-400';   // label kecil / header kolom
    $label = 'text-xs font-semibold text-slate-500';                               // label input
    $input = 'w-full rounded-xl border-slate-200 text-sm px-3.5 py-2 focus:border-blue-500 focus:ring-blue-500';
    $chipCek = 'flex items-center gap-2 px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-medium text-slate-600 cursor-pointer transition hover:border-blue-300 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 has-[:checked]:text-blue-700';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">Detail Disposisi</h2>
                <p class="hidden sm:block text-sm text-slate-500 mt-0.5">Informasi surat, hierarki disposisi, dan tindak lanjut</p>
            </div>
            <a href="{{ route('disposisi.index') }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold rounded-xl border border-slate-200 transition shrink-0">
                <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6 max-w-6xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4">

        {{-- ===== Pesan flash ===== --}}
        @if (session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-2">
                <svg class="h-4 w-4 text-emerald-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium flex items-center gap-2">
                <svg class="h-4 w-4 text-red-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- ===== Ringkasan surat ===== --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="{{ $kecil }}">Perihal</p>
                    <h3 class="mt-1 text-lg font-bold text-slate-900 leading-snug break-words">
                        {{ $surat?->perihal ?? '-' }}
                    </h3>
                </div>

                <div class="flex flex-wrap items-center gap-1.5 shrink-0">
                    <span class="{{ $pill }} {{ $warnaStatus[$disposisi->status] ?? $warnaStatus['dibaca'] }}">
                        {{ $labelStatus[$disposisi->status] ?? ucfirst($disposisi->status) }}
                    </span>
                    @if ($disposisi->sifat)
                        <span class="{{ $pill }} {{ $warnaSifat[$disposisi->sifat] ?? $warnaSifat['biasa'] }}">
                            {{ $disposisi->sifat }}
                        </span>
                    @endif
                    @if ($disposisi->tenggat_waktu)
                        <span class="{{ $pill }} bg-white text-slate-600 ring-slate-200">
                            Tenggat {{ $disposisi->tenggat_waktu->format('d M Y') }}
                        </span>
                    @endif
                </div>
            </div>

            <dl class="grid grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-3 px-4 sm:px-5 pb-4 sm:pb-5 text-xs">
                <div class="min-w-0">
                    <dt class="{{ $kecil }}">Nomor Surat</dt>
                    <dd class="mt-1 font-medium text-slate-700 break-words">{{ $surat?->nomor_surat ?? '-' }}</dd>
                </div>
                <div class="min-w-0">
                    <dt class="{{ $kecil }}">Asal Surat</dt>
                    <dd class="mt-1 font-medium text-slate-700 break-words">{{ $surat?->asal_surat ?? '-' }}</dd>
                </div>
                <div class="min-w-0">
                    <dt class="{{ $kecil }}">Nomor Agenda</dt>
                    <dd class="mt-1 font-mono font-medium text-slate-700 break-words">{{ $surat?->nomor_agenda ?? '-' }}</dd>
                </div>
                <div class="min-w-0">
                    <dt class="{{ $kecil }}">Tanggal Surat</dt>
                    <dd class="mt-1 font-medium text-slate-700">
                        {{ $surat?->tanggal_surat ? \Carbon\Carbon::parse($surat->tanggal_surat)->format('d M Y') : '-' }}
                    </dd>
                </div>
            </dl>

            {{-- Progres penugasan --}}
            @if ($totalAnak > 0)
                <div class="px-4 sm:px-5 py-3 border-t border-slate-100 bg-slate-50/60 rounded-b-2xl flex items-center gap-3">
                    <span class="{{ $kecil }} shrink-0">Progres</span>
                    <div class="h-1.5 flex-1 rounded-full bg-slate-200 overflow-hidden">
                        <div class="h-full rounded-full {{ $persen === 100 ? 'bg-emerald-500' : 'bg-blue-600' }} transition-all"
                            style="width: {{ $persen }}%"></div>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-700 shrink-0">{{ $selesaiAnak }}/{{ $totalAnak }} selesai</span>
                </div>
            @endif
        </div>

        {{-- ===== Catatan status singkat ===== --}}
        @if ($disposisi->is_tembusan && $sayaPenerima)
            <div class="px-4 py-3 rounded-xl bg-violet-50 border border-violet-200 text-sm text-violet-800 flex items-start gap-2">
                <svg class="h-4 w-4 text-violet-600 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <span>Ini adalah <strong class="font-semibold">tembusan</strong>. Laporan pegawai akan otomatis masuk ke Anda dan tidak ada tindakan yang diperlukan.</span>
            </div>
        @elseif ($sayaPenerima && !$adaTindak)
            <div class="px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-800 flex items-center gap-2 font-medium">
                <svg class="h-4 w-4 text-emerald-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                <span>Disposisi ini sudah Anda teruskan.</span>
            </div>
        @endif

        {{-- ===== Panel utama (tab) ===== --}}
        <div x-data="{ tab: '{{ $tabAwal }}' }" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs">

            {{-- Bar tab --}}
            <div class="flex gap-1 overflow-x-auto border-b border-slate-200 px-2 sm:px-3">
                @if ($adaTindak)
                    <button type="button" @click="tab = 'tindak'"
                        :class="tab === 'tindak' ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                        class="px-3.5 py-3 text-sm font-semibold border-b-2 -mb-px whitespace-nowrap transition">
                        Tindak Lanjut
                    </button>
                @endif

                <button type="button" @click="tab = 'hierarki'"
                    :class="tab === 'hierarki' ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    class="px-3.5 py-3 text-sm font-semibold border-b-2 -mb-px whitespace-nowrap transition">
                    {{ $isStaff ? 'Penugasan Anda' : 'Hierarki Disposisi' }}
                </button>

                @if ($laporanList->isNotEmpty())
                    <button type="button" @click="tab = 'laporan'"
                        :class="tab === 'laporan' ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                        class="px-3.5 py-3 text-sm font-semibold border-b-2 -mb-px whitespace-nowrap transition inline-flex items-center gap-1.5">
                        <span>Laporan</span>
                        <span class="rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold px-1.5 py-0.5 leading-none">{{ $laporanList->count() }}</span>
                    </button>
                @endif

                @if ($adaBerkas)
                    <button type="button" @click="tab = 'berkas'"
                        :class="tab === 'berkas' ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                        class="px-3.5 py-3 text-sm font-semibold border-b-2 -mb-px whitespace-nowrap transition">
                        Berkas Surat
                    </button>
                @endif
            </div>

            {{-- ===== TAB: Tindak Lanjut ===== --}}
            @if ($adaTindak)
                <div x-show="tab === 'tindak'" x-cloak
                    x-data="{ aksi: '{{ ($bolehDisposisi && !$errors->laporan->any()) ? 'teruskan' : 'lapor' }}' }">

                    {{-- Pilihan aksi (hanya bila Eselon 2 punya dua opsi) --}}
                    @if ($bolehDisposisi && $bolehLapor)
                        <div class="px-4 sm:px-5 pt-4">
                            <div class="inline-grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1">
                                <button type="button" @click="aksi = 'teruskan'"
                                    :class="aksi === 'teruskan' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-700'"
                                    class="rounded-lg px-4 py-1.5 text-sm font-semibold transition">
                                    Teruskan ke Pegawai
                                </button>
                                <button type="button" @click="aksi = 'lapor'"
                                    :class="aksi === 'lapor' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-700'"
                                    class="rounded-lg px-4 py-1.5 text-sm font-semibold transition">
                                    Kerjakan Sendiri
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- ---------- Form Laporan ---------- --}}
                    @if ($bolehLapor)
                                    <div x-show="aksi === 'lapor'" x-cloak class="p-4 sm:p-5 space-y-4">
                                        <div>
                                            <h3 class="text-lg font-bold text-slate-800">
                                                {{ $isStaff ? 'Penugasan & Laporan Pekerjaan' : 'Kerjakan Sendiri & Kirim Laporan' }}
                                            </h3>
                                            <p class="text-sm text-slate-400 mt-0.5">
                                                {{ $isStaff
                        ? 'Kerjakan tugas ini, lalu kirim laporan. Laporan akan diterima atasan Anda.'
                        : 'Jika tidak didelegasikan ke pegawai, Anda dapat langsung mengerjakan dan melaporkannya.' }}
                                            </p>
                                        </div>

                                        <div class="rounded-xl bg-slate-50 border border-slate-200/80 p-3.5 space-y-2">
                                            <span class="{{ $kecil }}">Instruksi Pimpinan</span>
                                            @if (is_array($disposisi->petunjuk) && count($disposisi->petunjuk))
                                                <div class="flex flex-wrap gap-1.5">
                                                    @foreach ($disposisi->petunjuk as $pt)
                                                        <span class="px-2 py-0.5 rounded-md bg-blue-50 border border-blue-100 text-[10px] font-medium text-blue-700">{{ $pt }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <p class="text-xs font-medium text-slate-700 whitespace-pre-line">{{ $disposisi->instruksi }}</p>
                                        </div>

                                        @if ($errors->laporan->any())
                                            <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-xs text-red-700">
                                                {{ $errors->laporan->first() }}
                                            </div>
                                        @endif

                                        <form action="{{ route('disposisi.laporan', $disposisi->id) }}" method="POST"
                                            x-data="{ kirim: false }" @submit="kirim = true" class="space-y-4">
                                            @csrf
                                            <div class="flex flex-col gap-1">
                                                <label class="{{ $label }}">
                                                    Isi Laporan <span class="text-red-600">*</span>
                                                </label>
                                                <textarea name="isi" rows="4" required placeholder="Tuliskan hasil pekerjaan / tindak lanjut..."
                                                    class="{{ $input }}">{{ old('isi') }}</textarea>
                                            </div>
                                            <div class="flex flex-col gap-1">
                                                <label class="{{ $label }}">
                                                    Link Berkas Laporan (Google Drive) <span class="text-slate-400 font-normal">(opsional)</span>
                                                </label>
                                                <input type="url" name="drive_link" value="{{ old('drive_link') }}" placeholder="https://drive.google.com/..."
                                                    class="{{ $input }}">
                                            </div>
                                            <div class="flex justify-end">
                                                <button type="submit" :disabled="kirim"
                                                    class="w-full sm:w-auto px-6 py-2 bg-blue-700 hover:bg-blue-800 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl shadow-sm transition inline-flex items-center justify-center gap-2">
                                                    <span>Kirim Laporan</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                    @endif

                    {{-- ---------- Form Disposisi Lanjutan ---------- --}}
                    @if ($bolehDisposisi)
                                    @php
                                        $isDeputi = auth()->user()->isEselon1();
                                        $petunjukKiri = array_slice(\App\Models\Disposisi::PETUNJUK, 0, 8);
                                        $petunjukKanan = array_slice(\App\Models\Disposisi::PETUNJUK, 8);
                                        $petunjukLama = (array) old('petunjuk', []);
                                        $sifatLama = old('sifat', 'biasa');
                                        $dipilihLama = array_map('intval', (array) old('penerima_ids', []));
                                    @endphp

                                    <div x-show="aksi === 'teruskan'" x-cloak>
                                        <div class="px-4 sm:px-5 py-3 mt-3 border-y border-slate-100 bg-slate-50/60">
                                            <h3 class="text-lg font-bold text-slate-800">
                                                {{ $isDeputi ? 'Disposisi Pimpinan' : 'Teruskan ke Pegawai' }}
                                            </h3>
                                            <p class="text-sm text-slate-400 mt-0.5">
                                                {{ $isDeputi
                        ? 'Pilih Asdep / Sesdep penerima. Petunjuk bisa disamakan atau diatur berbeda untuk tiap penerima.'
                        : 'Pilih pegawai di unit Anda. Petunjuk bisa disamakan atau diatur berbeda untuk tiap pegawai.' }}
                                            </p>
                                        </div>

                                        @if ($errors->any() && !$errors->laporan->any())
                                            <div class="mx-4 sm:mx-5 mt-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-xs text-red-700">
                                                {{ $errors->first() }}
                                            </div>
                                        @endif

                                        <form action="{{ route('disposisi.store', $disposisi->id) }}" method="POST"
                                            x-data="{
                                                jumlah: {{ count($dipilihLama) }},
                                                kirim: false,
                                                tenggat: '{{ old('tenggat_waktu') }}',
                                                hari(n) {
                                                    const d = new Date();
                                                    d.setDate(d.getDate() + n);
                                                    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                                                }
                                            }"
                                            @change="jumlah = $el.querySelectorAll('.cek-penerima:checked').length"
                                            @submit="kirim = true">
                                            @csrf

                                            <div class="grid grid-cols-1 lg:grid-cols-5 lg:divide-x divide-slate-100">

                                                {{-- ===== Kolom kiri: penerima ===== --}}
                                                <div class="lg:col-span-2 p-4 sm:p-5 space-y-3">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span class="{{ $kecil }} !text-slate-500">
                                                            {{ $isDeputi ? 'Penerima (Eselon II)' : 'Pegawai Pelaksana' }}
                                                            <span class="text-red-600">*</span>
                                                        </span>
                                                        <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-xs font-semibold">
                                                            <span x-text="jumlah"></span> dipilih
                                                        </span>
                                                    </div>

                                                    <p class="text-xs text-slate-400">
                                                        Centang <strong class="font-semibold text-slate-500">"Atur petunjuk khusus"</strong> pada penerima yang butuh petunjuk berbeda dari pengaturan umum.
                                                    </p>

                                                    <div class="space-y-2 lg:max-h-[34rem] lg:overflow-y-auto lg:pr-1">
                                                        @forelse ($users as $u)
                                                            @php
                                                                $inisial = collect(explode(' ', trim($u->name)))
                                                                    ->filter()->take(2)
                                                                    ->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                                                                    ->implode('');
                                                                $dipilih = in_array($u->id, $dipilihLama, true);
                                                                $khususAktif = (bool) old('khusus.' . $u->id . '.aktif');
                                                                $khususPetunjuk = (array) old('khusus.' . $u->id . '.petunjuk', []);
                                                                $khususSifat = old('khusus.' . $u->id . '.sifat', '');
                                                            @endphp

                                                            <div x-data="{ pilih: {{ $dipilih ? 'true' : 'false' }}, beda: {{ $khususAktif ? 'true' : 'false' }} }"
                                                                :class="pilih ? 'border-blue-500 bg-blue-50/40 ring-1 ring-blue-500/30' : 'border-slate-200 bg-white hover:border-slate-300'"
                                                                class="rounded-xl border transition">

                                                                <label class="flex items-center gap-3 p-3 cursor-pointer">
                                                                    <span :class="pilih ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'"
                                                                        class="h-9 w-9 shrink-0 rounded-lg text-xs font-bold flex items-center justify-center transition">
                                                                        {{ $inisial }}
                                                                    </span>

                                                                    <span class="min-w-0 flex-1">
                                                                        <span class="block text-sm font-semibold text-slate-800 leading-tight">{{ $u->name }}</span>
                                                                        @if ($u->unit)
                                                                            <span class="block text-xs text-slate-500 leading-snug mt-0.5">
                                                                                <span class="font-semibold text-slate-600">{{ $u->unit->kode }}</span>
                                                                                <span class="text-slate-300">·</span>
                                                                                {{ $u->unit->nama }}
                                                                            </span>
                                                                        @elseif ($u->pegawai?->jabatan)
                                                                            <span class="block text-xs text-slate-500 mt-0.5">{{ $u->pegawai->jabatan }}</span>
                                                                        @endif
                                                                    </span>

                                                                    <input type="checkbox" name="penerima_ids[]" value="{{ $u->id }}" x-model="pilih"
                                                                        class="cek-penerima h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                                                </label>

                                                                {{-- Pengaturan khusus (muncul setelah dipilih) --}}
                                                                <div x-show="pilih" x-cloak class="px-3 pb-3">
                                                                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                                                                        <input type="checkbox" name="khusus[{{ $u->id }}][aktif]" value="1" x-model="beda"
                                                                            class="h-3.5 w-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                                                        <span>Atur petunjuk khusus untuk {{ strtok($u->name, ' ') }}</span>
                                                                    </label>

                                                                    <div x-show="beda" x-cloak class="mt-2.5 space-y-3 rounded-lg bg-white border border-slate-200 p-3">
                                                                        <div>
                                                                            <span class="{{ $label }} block mb-1.5">Petunjuk khusus</span>
                                                                            <div class="grid grid-cols-1 gap-1 max-h-44 overflow-y-auto pr-1">
                                                                                @foreach (\App\Models\Disposisi::PETUNJUK as $p)
                                                                                    <label class="{{ $chipCek }}">
                                                                                        <input type="checkbox" name="khusus[{{ $u->id }}][petunjuk][]" value="{{ $p }}"
                                                                                            class="h-3.5 w-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                                                                            @checked(in_array($p, $khususPetunjuk, true))>
                                                                                        <span>{{ $p }}</span>
                                                                                    </label>
                                                                                @endforeach
                                                                            </div>
                                                                        </div>

                                                                        <div class="flex flex-col gap-1">
                                                                            <label class="{{ $label }}">Catatan khusus</label>
                                                                            <textarea name="khusus[{{ $u->id }}][instruksi]" rows="2"
                                                                                placeholder="Opsional jika sudah ada petunjuk khusus."
                                                                                class="{{ $input }}">{{ old('khusus.' . $u->id . '.instruksi') }}</textarea>
                                                                        </div>

                                                                        <div class="grid grid-cols-2 gap-2">
                                                                            <div class="flex flex-col gap-1">
                                                                                <label class="{{ $label }}">Batas waktu</label>
                                                                                <input type="date" name="khusus[{{ $u->id }}][tenggat_waktu]"
                                                                                    value="{{ old('khusus.' . $u->id . '.tenggat_waktu') }}"
                                                                                    class="{{ $input }}">
                                                                            </div>
                                                                            <div class="flex flex-col gap-1">
                                                                                <label class="{{ $label }}">Sifat</label>
                                                                                <select name="khusus[{{ $u->id }}][sifat]" class="{{ $input }}">
                                                                                    <option value="">Ikut umum</option>
                                                                                    <option value="biasa" @selected($khususSifat === 'biasa')>Biasa</option>
                                                                                    <option value="segera" @selected($khususSifat === 'segera')>Segera</option>
                                                                                    <option value="rahasia" @selected($khususSifat === 'rahasia')>Rahasia</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-xs text-slate-400">Yang dikosongkan mengikuti pengaturan umum.</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <p class="text-sm text-slate-400 py-4 text-center">Belum ada penerima yang dapat dipilih.</p>
                                                        @endforelse
                                                    </div>
                                                </div>

                                                {{-- ===== Kolom kanan: pengaturan umum ===== --}}
                                                <div class="lg:col-span-3 p-4 sm:p-5 space-y-5">
                                                    <div class="flex items-center gap-3">
                                                        <span class="{{ $kecil }} !text-slate-500">Pengaturan umum (semua penerima)</span>
                                                        <div class="h-px flex-1 bg-slate-200"></div>
                                                    </div>

                                                    {{-- Petunjuk --}}
                                                    <div class="space-y-2">
                                                        <div class="flex items-baseline gap-2">
                                                            <span class="{{ $label }}">Petunjuk</span>
                                                            <span class="text-xs text-slate-400">(centang yang sesuai)</span>
                                                        </div>

                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-2 gap-y-1.5">
                                                            @foreach ([$petunjukKiri, $petunjukKanan] as $kolom)
                                                                <div class="space-y-1.5">
                                                                    @foreach ($kolom as $p)
                                                                        <label class="{{ $chipCek }} has-[:checked]:font-semibold">
                                                                            <input type="checkbox" name="petunjuk[]" value="{{ $p }}"
                                                                                class="h-3.5 w-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                                                                @checked(in_array($p, $petunjukLama, true))>
                                                                            <span>{{ $p }}</span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>

                                                    {{-- Catatan --}}
                                                    <div class="flex flex-col gap-1">
                                                        <label class="{{ $label }}">Catatan Tambahan</label>
                                                        <textarea name="instruksi" rows="2"
                                                            placeholder="Opsional jika sudah mencentang petunjuk."
                                                            class="{{ $input }}">{{ old('instruksi') }}</textarea>
                                                        <p class="text-xs text-slate-400">Tiap penerima wajib punya minimal satu petunjuk atau catatan, baik dari pengaturan umum maupun khusus.</p>
                                                    </div>

                                                    {{-- Batas waktu & sifat --}}
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                                        <div class="flex flex-col gap-1">
                                                            <label class="{{ $label }}">Batas Waktu</label>
                                                            <input type="date" name="tenggat_waktu" x-model="tenggat" class="{{ $input }}">
                                                            <div class="flex flex-wrap gap-1 pt-1">
                                                                <button type="button" @click="tenggat = hari(0)" class="px-2 py-1 rounded-md bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-[11px] font-semibold text-slate-600 transition">Hari ini</button>
                                                                <button type="button" @click="tenggat = hari(1)" class="px-2 py-1 rounded-md bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-[11px] font-semibold text-slate-600 transition">Besok</button>
                                                                <button type="button" @click="tenggat = hari(3)" class="px-2 py-1 rounded-md bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-[11px] font-semibold text-slate-600 transition">3 hari</button>
                                                                <button type="button" @click="tenggat = hari(7)" class="px-2 py-1 rounded-md bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-[11px] font-semibold text-slate-600 transition">1 minggu</button>
                                                                <button type="button" @click="tenggat = ''" class="px-2 py-1 rounded-md text-[11px] font-semibold text-slate-400 hover:text-red-600 transition">Hapus</button>
                                                            </div>
                                                        </div>

                                                        <div class="flex flex-col gap-1">
                                                            <span class="{{ $label }}">Sifat <span class="text-red-600">*</span></span>
                                                            <div class="grid grid-cols-3 gap-1.5">
                                                                <label class="flex items-center justify-center px-2 py-2 rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-600 cursor-pointer transition hover:border-slate-300 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 has-[:checked]:text-blue-700">
                                                                    <input type="radio" name="sifat" value="biasa" class="sr-only" @checked($sifatLama === 'biasa')>
                                                                    Biasa
                                                                </label>
                                                                <label class="flex items-center justify-center px-2 py-2 rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-600 cursor-pointer transition hover:border-red-200 has-[:checked]:border-red-500 has-[:checked]:bg-red-50 has-[:checked]:text-red-700">
                                                                    <input type="radio" name="sifat" value="segera" class="sr-only" @checked($sifatLama === 'segera')>
                                                                    Segera
                                                                </label>
                                                                <label class="flex items-center justify-center px-2 py-2 rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-600 cursor-pointer transition hover:border-amber-200 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-700">
                                                                    <input type="radio" name="sifat" value="rahasia" class="sr-only" @checked($sifatLama === 'rahasia')>
                                                                    Rahasia
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Bar kirim (menempel di bawah layar, tidak perlu scroll) --}}
                                            <div class="sticky bottom-0 z-10 flex items-center justify-between gap-3 rounded-b-2xl border-t border-slate-200 bg-white/95 backdrop-blur px-4 sm:px-5 py-3">
                                                <p class="text-sm text-slate-500">
                                                    <span class="font-semibold text-slate-800" x-text="jumlah"></span> penerima dipilih
                                                </p>
                                                <button type="submit" :disabled="jumlah === 0 || kirim"
                                                    class="px-6 py-2 bg-blue-700 hover:bg-blue-800 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl shadow-sm transition inline-flex items-center justify-center gap-2">
                                                    <span>Kirim Disposisi</span>
                                                    <svg class="h-4 w-4 text-blue-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                    @endif
                </div>
            @endif

            {{-- ===== TAB: Hierarki ===== --}}
            <div x-show="tab === 'hierarki'" x-cloak class="p-4 sm:p-5">
                <div class="space-y-4">
                    @include('disposisi.partials.tree-item', ['parentId' => 0, 'byParent' => $byParent])
                </div>
            </div>

            {{-- ===== TAB: Laporan ===== --}}
            @if ($laporanList->isNotEmpty())
                <div x-show="tab === 'laporan'" x-cloak class="p-4 sm:p-5 space-y-3">
                    @foreach ($laporanList as $lap)
                        <div class="rounded-xl border border-slate-200 border-l-4 border-l-emerald-500 bg-white p-3.5 space-y-1.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-semibold text-slate-800">{{ $lap['nama'] }}</span>
                                <span class="text-xs text-slate-400">{{ $lap['waktu']->format('d M Y, H:i') }}</span>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">{{ $lap['isi'] }}</p>
                            @if ($lap['drive_link'])
                                <a href="{{ $lap['drive_link'] }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-600 hover:text-blue-700">
                                    Buka berkas laporan ➔
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- ===== TAB: Berkas Surat ===== --}}
            @if ($adaBerkas)
                <div x-show="tab === 'berkas'" x-cloak class="p-4 sm:p-5 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm text-slate-400">Dokumen dimuat langsung dari Google Drive.</p>
                        <a href="{{ $surat->drive_link }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-[11px] font-semibold text-slate-700 transition shrink-0">
                            <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                            <span>Buka di tab baru</span>
                        </a>
                    </div>

                    {{-- iframe baru dimuat saat tab dibuka --}}
                    <template x-if="tab === 'berkas'">
                        <div class="w-full h-[70vh] rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                            <iframe src="{{ $embedUrl }}" class="w-full h-full" frameborder="0" allow="autoplay"></iframe>
                        </div>
                    </template>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>