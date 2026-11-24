<x-app-layout title="Memorandum">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Memorandum') }}
        </h2>
    </x-slot>

    @php
        // ---- Status anggaran: outstanding -> sakti -> sp2d (+ batal) ----
        $statusLabel = ['outstanding' => 'Outstanding', 'sakti' => 'SAKTI', 'sp2d' => 'SP2D', 'batal' => 'Batal'];
        $urutStatus = ['outstanding' => 0, 'sakti' => 1, 'sp2d' => 2];
        $maju = ['sakti' => 'sp2d']; // satu-satunya perpindahan manual tanpa alasan
        $statusBadge = fn($s) => match ($s) {
            'sakti' => 'bg-blue-50 text-blue-700 border-blue-200',
            'sp2d' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'batal' => 'bg-gray-100 text-gray-500 border-gray-200',
            default => 'bg-amber-50 text-amber-700 border-amber-200',
        };
        $statusDot = fn($s) => match ($s) {
            'sakti' => 'bg-blue-500',
            'sp2d' => 'bg-emerald-500',
            'batal' => 'bg-gray-400',
            default => 'bg-amber-500',
        };
        $statusBar = fn($s) => match ($s) {
            'sakti' => 'bg-blue-500',
            'sp2d' => 'bg-emerald-500',
            default => 'bg-amber-400',
        };

        // ---- Tahap alur memo ----
        $tahapLabel = [
            'draft' => 'Draft',
            'dikirim' => 'Dikirim, menunggu K2',
            'lolos_spk' => 'Lolos K2, menunggu SAKTI',
            'sakti' => 'SAKTI, menunggu K3',
            'otp_ppk' => 'OTP PPK',
            'ppspm' => 'PPSPM',
            'dikembalikan' => 'Dikembalikan',
        ];
        $tahapDot = fn($t) => match ($t) {
            'dikirim' => 'bg-blue-500',
            'lolos_spk' => 'bg-indigo-500',
            'sakti' => 'bg-sky-500',
            'otp_ppk' => 'bg-violet-500',
            'ppspm' => 'bg-emerald-500',
            'dikembalikan' => 'bg-red-500',
            default => 'bg-gray-400',
        };
        $tahapText = fn($t) => match ($t) {
            'dikembalikan' => 'text-red-600',
            'ppspm' => 'text-emerald-700',
            default => 'text-gray-600',
        };

        // Opsi dropdown status manual. Harus sejalan dengan MemoTahapService::ubahStatusManual().
        // SAKTI dari outstanding tidak ada di sini: hanya lewat tombol "Input SAKTI".
        $opsiManual = function ($st, $tahap) {
            $draft = in_array($tahap, ['draft', 'dikembalikan'], true);
            return match (true) {
                $draft && $st === 'outstanding' => ['batal'],
                $draft && $st === 'batal' => ['outstanding'],
                $tahap === 'ppspm' && $st === 'sakti' => ['sp2d', 'batal'],
                $tahap === 'ppspm' && $st === 'sp2d' => ['sakti', 'batal'],
                default => [],
            };
        };

        $keuangan = auth()->user()->can('kelola-anggaran');
        $isAdmin = in_array(auth()->user()->role ?? null, ['admin', 'super_admin'], true);
        $logMap = $logMap ?? [];
        $berkasMap = $berkasMap ?? [];

        // Hitung data tampilan sekali saja
        $groups = $groups->map(function ($g) {
            $tanggal = $g['tanggal_memo'] ?? null;
            $tanggalIso = optional($tanggal)->format('Y-m-d');
            $tanggalTampil = optional($tanggal)->translatedFormat('d M Y');
            $tanggalTampilLengkap = optional($tanggal)->translatedFormat('d F Y');

            $searchBlob = strtolower(collect([
                collect($g['items'])->pluck('nomor_memo')->implode(' '),
                $g['uraian_kegiatan'] ?? '',
                $g['pic'] ?? '',
                $g['unit'] ?? '',
                $g['jenis_label'] ?? '',
                $tanggalIso,
                $tanggalTampil,
                $tanggalTampilLengkap,
            ])->filter()->implode(' '));

            // Warna badge per jenis. Jenis yang belum terdaftar jatuh ke default (abu-abu).
            $jenisClass = match ($g['jenis']) {
                'perdin' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'konsumsi' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'honorarium' => 'bg-amber-50 text-amber-700 border-amber-200',
                'atk' => 'bg-sky-50 text-sky-700 border-sky-200',
                'seminar_kit' => 'bg-violet-50 text-violet-700 border-violet-200',
                'sewa_ruangan' => 'bg-rose-50 text-rose-700 border-rose-200',
                'fullboard_meeting' => 'bg-teal-50 text-teal-700 border-teal-200',
                'fullday_meeting' => 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200',
                default => 'bg-gray-50 text-gray-700 border-gray-200',
            };

            $deleteLabel = $g['agenda_id']
                ? 'agenda ini beserta seluruh nomor memonya (PNS dan Non PNS)'
                : 'nomor memo ini';

            $statuses = collect($g['items'])->map(fn($i) => $i['status_anggaran'] ?? 'outstanding');
            $tahaps = collect($g['items'])->map(fn($i) => $i['tahap'] ?? 'draft');

            return $g + [
                'tanggal_iso' => $tanggalIso,
                'tanggal_tampil' => $tanggalTampil,
                'search_blob' => $searchBlob,
                'jenis_class' => $jenisClass,
                'delete_label' => $deleteLabel,
                'delete_text' => $g['agenda_id'] ? 'Hapus agenda' : 'Hapus memo',
                'status_blob' => $statuses->unique()->implode(' '),
                'tahap_blob' => $tahaps->unique()->implode(' '),
                // Hapus hanya boleh kalau semua memo di grup masih outstanding/batal
                // DAN belum dikirim ke Keuangan (draft / dikembalikan).
                'bisa_hapus' => $statuses->every(fn($s) => in_array($s, ['outstanding', 'batal'], true))
                    && $tahaps->every(fn($t) => in_array($t, ['draft', 'dikembalikan'], true)),
            ];
        });

        // Jenis dokumen + nomor SPP/SPBy per memo (kunci = id memo, mis. entry-12 / agenda-3-pns).
        // Satu query untuk semua kartu.
        $sppPerMemo = \Illuminate\Support\Facades\DB::table('memo_progres')
            ->whereIn('memo_key', $groups->flatMap(fn($g) => collect($g['items'])->pluck('id'))->filter()->all())
            ->whereNotNull('nomor_spp_spby')
            ->get(['memo_key', 'jenis_dokumen', 'nomor_spp_spby'])
            ->keyBy('memo_key');

        // Opsi filter unit (hanya dipakai Keuangan)
        $unitOptions = $groups->pluck('unit')->filter(fn($u) => filled($u) && $u !== '-')->unique()->sort()->values();

        $statusClass = fn($status) => $status === 'PNS'
            ? 'bg-blue-50 text-blue-700 border-blue-200'
            : 'bg-orange-50 text-orange-700 border-orange-200';
    @endphp

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-[96rem] mx-auto space-y-5">

        {{-- ============ BAGIAN ATAS: judul, nomor berikutnya, filter ============ --}}
        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-4 sm:p-6 space-y-5">

            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="min-w-0">
                    <h3 class="text-lg font-bold text-gray-800">Memorandum</h3>
                    <p class="text-sm text-gray-500">Diurutkan dari nomor memo terbaru.</p>
                </div>
                <span
                    class="text-xs font-semibold text-gray-500 bg-gray-50 border border-gray-100 rounded-full px-3 py-1.5"
                    aria-live="polite">
                    <span id="memo-visible-count">{{ $groups->count() }}</span> dari {{ $groups->count() }} kegiatan
                </span>
            </div>

            {{-- Nomor memo berikutnya + tombol buat --}}
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl bg-blue-50 border border-blue-100 px-4 py-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-blue-700">Nomor memo berikutnya</p>
                    <p class="text-xl font-bold text-gray-900 tabular-nums break-all">
                        {{ $nomorBerikutnya['nomor'] }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">Dihitung otomatis dari nomor terbesar yang sudah dipakai.
                    </p>
                </div>
<div class="flex w-full sm:w-auto shrink-0 flex-col sm:flex-row items-stretch sm:items-center gap-2">
    @if ($isAdmin)
        <a href="{{ route('memo.nomor-setting') }}"
            class="inline-flex items-center justify-center gap-1.5 bg-white hover:bg-blue-100 text-blue-700 font-semibold px-4 py-2 rounded-lg text-sm border border-blue-200 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            Pengaturan nomor
        </a>
    @endif

    <a href="{{ route('memo.create') }}"
        class="inline-flex w-full sm:w-auto items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm shadow-md shadow-blue-600/20 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Buat Memorandum
    </a>
</div>
            </div>

            {{-- Filter instan, tanpa reload --}}
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="memo-filter-cari" class="text-xs font-semibold text-gray-600">Cari</label>
                    <input type="text" id="memo-filter-cari" autocomplete="off"
                        placeholder="Nomor memo, uraian, PIC, atau unit..."
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-64 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                </div>

                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="memo-filter-jenis" class="text-xs font-semibold text-gray-600">Jenis</label>
                    <select id="memo-filter-jenis"
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-44 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                        <option value="semua" selected>Semua Jenis</option>
                        <option value="perdin">Perjalanan Dinas</option>
                        @foreach (\App\Models\MemoEntry::JENIS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="memo-filter-status" class="text-xs font-semibold text-gray-600">Status</label>
                    <select id="memo-filter-status"
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-40 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                        <option value="">Semua Status</option>
                        @foreach ($statusLabel as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="memo-filter-tahap" class="text-xs font-semibold text-gray-600">Tahap</label>
                    <select id="memo-filter-tahap"
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-52 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                        <option value="">Semua Tahap</option>
                        @foreach ($tahapLabel as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($keuangan)
                    <div class="flex flex-col gap-1 w-full sm:w-auto">
                        <label for="memo-filter-unit" class="text-xs font-semibold text-gray-600">Unit</label>
                        <select id="memo-filter-unit"
                            class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-56 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                            <option value="">Semua Unit</option>
                            @foreach ($unitOptions as $namaUnit)
                                <option value="{{ $namaUnit }}">{{ $namaUnit }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="memo-filter-pic" class="text-xs font-semibold text-gray-600">PIC</label>
                    <select id="memo-filter-pic"
                        class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm w-full sm:w-44 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                        <option value="">Semua PIC</option>
                        @foreach ($picOptions as $namaPic)
                            <option value="{{ $namaPic }}">{{ $namaPic }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1 w-[calc(50%-0.375rem)] sm:w-auto min-w-0">
                    <label for="memo-filter-dari" class="text-xs font-semibold text-gray-600">Dari tanggal</label>
                    <input type="date" id="memo-filter-dari"
                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-full sm:w-40 min-w-0 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                </div>

                <div class="flex flex-col gap-1 w-[calc(50%-0.375rem)] sm:w-auto min-w-0">
                    <label for="memo-filter-sampai" class="text-xs font-semibold text-gray-600">Sampai tanggal</label>
                    <input type="date" id="memo-filter-sampai"
                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-full sm:w-40 min-w-0 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                </div>

                <button type="button" id="memo-filter-reset"
                    class="text-sm text-gray-500 hover:text-gray-700 transition px-1 py-2 cursor-pointer">
                    Reset
                </button>
            </div>
        </div>

        {{-- ============ DAFTAR: satu kartu per kegiatan ============ --}}
        {{--
            Tata letak memakai CONTAINER QUERY (lihat <style> di bawah), bukan breakpoint layar.
            Yang diukur adalah lebar area daftar itu sendiri, jadi hasilnya sama di Chrome, Edge,
            zoom berapa pun, dan sidebar terbuka atau tertutup.
        --}}

        <div class="memo-board">
            {{-- Header kolom: tampil otomatis saat area cukup lebar (diatur lewat container query di <style> bawah) --}}
            <div class="memo-head text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-3">
                <div class="memo-head-kegiatan pl-10">Kegiatan</div>
                <div class="memo-head-right">
                    <div>Nomor Memo</div>
                    <div>Status</div>
                    <div class="text-right">Nominal</div>
                    <div>Aksi</div>
                    <div>Dokumen</div>
                </div>
            </div>

            <div id="memo-list" class="space-y-3">
                @forelse ($groups as $g)
                    <article
                        class="js-memo-item bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5 memo-cols"
                        data-search="{{ $g['search_blob'] }}" data-jenis="{{ $g['jenis'] }}" data-pic="{{ $g['pic'] }}"
                        data-unit="{{ $g['unit'] ?? '' }}" data-status="{{ $g['status_blob'] }}"
                        data-tahap="{{ $g['tahap_blob'] }}" data-tanggal="{{ $g['tanggal_iso'] }}">

                        {{-- Info kegiatan --}}
                        <div class="flex gap-3 min-w-0">
                            <span
                                class="js-memo-number inline-flex h-7 min-w-7 px-2 items-center justify-center rounded-lg bg-gray-100 text-xs font-semibold text-gray-500 tabular-nums shrink-0">{{ $loop->iteration }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                                    <span
                                        class="inline-flex items-center whitespace-nowrap px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $g['jenis_class'] }}">
                                        {{ $g['jenis_label'] }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $g['tanggal_tampil'] ?: '-' }}</span>
                                </div>

                                <h3 class="text-sm font-semibold text-gray-800 leading-snug break-words mt-2">
                                    {{ $g['uraian_kegiatan'] ?? '-' }}
                                </h3>

                                <dl class="mt-2 space-y-1 text-xs">
                                    @if ($keuangan)
                                        <div class="flex gap-2">
                                            <dt
                                                class="w-12 shrink-0 text-[10px] font-semibold uppercase tracking-wide text-gray-400 pt-px">
                                                Unit</dt>
                                            <dd class="text-gray-700 font-medium break-words min-w-0">
                                                {{ $g['unit'] ?? '-' }}</dd>
                                        </div>
                                    @endif
                                    <div class="flex gap-2">
                                        <dt
                                            class="w-12 shrink-0 text-[10px] font-semibold uppercase tracking-wide text-gray-400 pt-px">
                                            PIC</dt>
                                        <dd class="text-gray-700 font-medium break-words min-w-0">{{ $g['pic'] ?: '-' }}
                                        </dd>
                                    </div>
                                    <div class="flex gap-2">
                                        <dt
                                            class="w-12 shrink-0 text-[10px] font-semibold uppercase tracking-wide text-gray-400 pt-px">
                                            MAK</dt>
                                        <dd
                                            class="text-gray-700 font-medium font-mono text-[11px] break-words [overflow-wrap:anywhere] min-w-0">
                                            {{ $g['mak'] ?? '-' }}
                                        </dd>
                                    </div>
                                </dl>

                                {{-- Hapus + Log (per memo PNS / Non PNS). PDF ada di kolom Dokumen --}}
                                <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2">
                                    @if ($g['bisa_hapus'])
                                        <form action="{{ route('memo.destroy', $g['delete_id']) }}" method="POST"
                                            class="delete-memo-form inline-flex m-0" data-label="{{ $g['delete_label'] }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus {{ $g['delete_label'] }}"
                                                class="inline-flex items-center justify-center px-3 py-1.5 text-[11px] font-semibold text-red-500 bg-white hover:bg-red-50 border border-red-400 rounded-md transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                                                {{ $g['delete_text'] }}
                                            </button>
                                        </form>
                                    @endif

                                    @foreach ($g['items'] as $memoLog)
                                        @php $jmlLogBtn = count($logMap[$memoLog['id']] ?? []); @endphp
                                        <div class="inline-flex items-center gap-1.5">
                                            @if (count($g['items']) > 1 && $memoLog['status'])
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $statusClass($memoLog['status']) }}">
                                                    {{ $memoLog['status'] }}
                                                </span>
                                            @endif

                                            <button type="button"
                                                class="js-log-toggle inline-flex items-center justify-center gap-1 px-2.5 py-1.5 text-[11px] font-semibold text-gray-600 bg-white hover:bg-gray-100 border border-gray-300 rounded-md transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                                                data-target="log-{{ $memoLog['id'] }}" aria-expanded="false"
                                                aria-controls="log-{{ $memoLog['id'] }}">
                                                Log
                                                @if ($jmlLogBtn > 0)
                                                    <span class="text-gray-400">({{ $jmlLogBtn }})</span>
                                                @endif
                                            </button>
                                        </div>
                                    @endforeach
                                </div>


                                {{-- Nominatif memo mandiri: tombol PDF per nominatif, tombol tambah, ikon kunci --}}
                                @foreach ($g['items'] as $mNom)
                                    @if (!empty($mNom['mandiri']) && !empty($mNom['perlu_nominatif']))
                                        @php
                                            $jmlNom = (int) ($mNom['nominatif_count'] ?? 0);
                                            // Diisi dari MemoController: [['label' => 'Honorarium', 'url' => '...'], ...]
                                            $nomPdfs = $mNom['nominatif_pdfs'] ?? [];
                                            $nomTerkunci = !in_array($mNom['tahap'] ?? 'draft', ['draft', 'dikembalikan'], true);
                                            $entryId = (int) substr($mNom['id'], 6); // 'entry-' = 6 huruf
                                        @endphp
                                        <div
                                            class="mt-3 rounded-lg border border-dashed border-gray-200 bg-gray-50/60 px-3 py-2.5">
                                            <div class="flex flex-wrap items-center gap-x-2.5 gap-y-2">
                                                <span
                                                    class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">Nominatif</span>

                                                @forelse ($nomPdfs as $np)
                                                    <a href="{{ $np['url'] }}" target="_blank" rel="noopener"
                                                        title="Buka PDF nominatif {{ $np['label'] }}"
                                                        class="inline-flex items-center justify-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-blue-600 bg-white hover:bg-blue-50 border border-blue-500 rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                        </svg>
                                                        PDF{{ count($nomPdfs) > 1 ? ' ' . $np['label'] : '' }}
                                                    </a>
                                                @empty
                                                    @if ($jmlNom > 0)
                                                        {{-- Cadangan: ada nominatif tapi URL PDF belum dikirim dari controller --}}
                                                        <span
                                                            class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200">
                                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                            {{ $jmlNom }} nominatif
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-amber-50 text-amber-700 border-amber-200">
                                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                            Belum ada nominatif
                                                        </span>
                                                    @endif
                                                @endforelse

                                                @if (!empty($mNom['bisa_tambah_nominatif']))
                                                    <a href="{{ route('nominatif.pilih', ['memo' => $entryId]) }}"
                                                        class="inline-flex items-center justify-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-blue-600 bg-white hover:bg-blue-50 border border-blue-500 rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2.2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                        Nominatif
                                                    </a>
                                                @endif

                                                @if ($nomTerkunci)
                                                    <span class="inline-flex items-center gap-1 text-[11px] text-gray-500"
                                                        title="Nominatif terkunci selama memo di Keuangan. Terbuka lagi kalau memo dikembalikan.">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="1.8"
                                                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                        </svg>
                                                        Terkunci
                                                    </span>
                                                @endif
                                            </div>

                                            @if ($jmlNom === 0 && !$nomTerkunci)
                                                <p class="mt-1.5 text-[11px] text-amber-700">
                                                    Memo ini belum bisa dikirim ke Keuangan sebelum ada minimal satu nominatif.
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach

                                @unless ($g['bisa_hapus'])
                                    <p
                                        class="mt-3 text-[11px] text-gray-400 border border-dashed border-gray-200 bg-gray-50/60 rounded-lg px-3 py-2">
                                        Tidak bisa dihapus karena sudah dikirim ke Keuangan, SAKTI, atau SP2D.
                                        Ajukan pengembalian atau gunakan status Batal.
                                    </p>
                                @endunless
                            </div>
                        </div>

                        {{-- Memo per status (PNS / Non PNS) --}}
                        <div class="memo-right space-y-2 min-w-0">
                            @foreach ($g['items'] as $memo)
                                @php
                                    $st = $memo['status_anggaran'] ?? 'outstanding';
                                    $tahap = $memo['tahap'] ?? 'draft';
                                    $legacy = !empty($memo['is_legacy']);
                                    $idx = $urutStatus[$st] ?? null;
                                    $tglSt = $st === 'sakti'
                                        ? ($memo['tgl_sakti'] ?? null)
                                        : ($st === 'sp2d' ? ($memo['tgl_sp2d'] ?? null) : null);
                                    $opsi = $opsiManual($st, $tahap);
                                    $logMemo = $logMap[$memo['id']] ?? [];
                                    $spp = $sppPerMemo->get($memo['id']);

                                    $adaPengajuan = !empty($memo['pengajuan_id']);
                                    $aksiUnit = !empty($memo['bisa_kirim']) || !empty($memo['bisa_ajukan']) || !empty($memo['editable']);
                                    $aksiKeuangan = $keuangan
                                        && ($adaPengajuan || in_array($tahap, ['dikirim', 'lolos_spk', 'sakti', 'otp_ppk', 'ppspm'], true));
                                    $aksiStatus = $keuangan && count($opsi) > 0;
                                    $adaAksi = $adaPengajuan || $aksiUnit || $aksiKeuangan || $aksiStatus;
                                @endphp

                                <div class="rounded-xl bg-gray-50 border border-gray-200 p-3 space-y-3">

                                    {{-- Baris info: nomor | status | nominal | aksi | dokumen --}}
                                    <div class="memo-row">

                                        {{-- Nomor memo --}}
                                        <div class="min-w-0 flex flex-col items-start gap-1.5">
                                            @if ($memo['agenda_id'])
                                                <a href="{{ route('agendas.show', $memo['agenda_id']) }}"
                                                    class="font-semibold text-xs text-gray-800 tabular-nums hover:text-blue-600 hover:underline break-all">
                                                    {{ $memo['nomor_memo'] }}
                                                </a>
                                            @else
                                                <span class="font-semibold text-xs text-gray-800 tabular-nums break-all">
                                                    {{ $memo['nomor_memo'] }}
                                                </span>
                                            @endif

                                            @if ($memo['status'])
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $statusClass($memo['status']) }}">
                                                    {{ $memo['status'] }}
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Progres & status --}}
                                        <div class="min-w-0 flex flex-col items-start gap-2">
                                                                                        @if ($legacy)
                                                                                            <span
                                                                                                class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border bg-gray-100 text-gray-600 border-gray-200"
                                                                                                title="Dibuat sebelum sistem outstanding ada">
                                                                                                Pra-outstanding
                                                                                            </span>
                                                                                        @else
                                                {{-- Badge status anggaran + tanggal --}}
                                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                                    <span
                                                        class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $statusBadge($st) }}">
                                                        <span class="h-1.5 w-1.5 rounded-full {{ $statusDot($st) }}"></span>
                                                        {{ $statusLabel[$st] ?? $st }}
                                                    </span>
                                                    @if ($tglSt)
                                                        <span class="text-[10px] text-gray-400 tabular-nums">
                                                            {{ \Illuminate\Support\Carbon::parse($tglSt)->translatedFormat('d M Y') }}
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Stepper progres 3 tahap dengan label --}}
                                                @if ($idx !== null)
                                                    <div class="w-full max-w-[12rem]" role="img"
                                                        aria-label="Progres: {{ $statusLabel[$st] }}, tahap {{ $idx + 1 }} dari 3">
                                                        <div class="flex gap-1">
                                                            @foreach ([0, 1, 2] as $i)
                                                                <span
                                                                    class="h-1.5 flex-1 rounded-sm {{ $i <= $idx ? $statusBar($st) : 'bg-gray-200' }}"></span>
                                                            @endforeach
                                                        </div>
                                                        <div class="mt-1 grid grid-cols-3 gap-1 text-[9px] leading-none">
                                                            @foreach (['outstanding', 'sakti', 'sp2d'] as $i => $kode)
                                                                <span
                                                                    class="truncate {{ $i === $idx ? 'font-semibold text-gray-800' : ($i < $idx ? 'font-medium text-gray-500' : 'font-medium text-gray-300') }}">
                                                                    {{ $statusLabel[$kode] }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif

                                            {{-- Tahap alur memo --}}
                                            <p
                                                class="inline-flex items-start gap-1.5 text-[11px] font-medium leading-snug {{ $tahapText($tahap) }}">
                                                <span
                                                    class="mt-[5px] h-1.5 w-1.5 shrink-0 rounded-full {{ $tahapDot($tahap) }}"></span>
                                                <span class="min-w-0 break-words">{{ $tahapLabel[$tahap] ?? $tahap }}</span>
                                            </p>

                                            {{-- SPP / SPBy yang diinput saat Input SAKTI --}}
                                            @if ($spp)
                                                <p class="flex items-start gap-1.5 text-[11px] leading-snug">
                                                    <span
                                                        class="shrink-0 rounded border px-1.5 py-px text-[10px] font-semibold {{ ($spp->jenis_dokumen ?? '') === 'SPBy' ? 'bg-orange-50 text-orange-700 border-orange-200' : 'bg-sky-50 text-sky-700 border-sky-200' }}">{{ $spp->jenis_dokumen ?? '-' }}</span>
                                                    <span class="font-mono text-gray-700 break-all">{{ $spp->nomor_spp_spby }}</span>
                                                </p>
                                            @endif

                                            {{-- Berkas fisik: posisi berdiri sendiri, tidak terkait tahap memo --}}
                                            @php
                                                $posisiBerkas = $berkasMap[$memo['id']] ?? '';
                                                $warnaBerkas = match ($posisiBerkas) {
                                                    'Tim Keuangan' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                    'PPSPM' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                    'Unit' => 'bg-gray-50 text-gray-700 border-gray-200',
                                                    default => 'bg-gray-50 text-gray-400 border-gray-200',
                                                };
                                            @endphp
                                            <div class="w-full max-w-[12rem]">
                                                <p class="mb-0.5 text-[9px] font-semibold uppercase tracking-wide text-gray-400">Berkas fisik</p>
                                                @if ($keuangan)
                                                    <select aria-label="Berkas fisik memo {{ $memo['nomor_memo'] }}"
                                                        data-url="{{ route('memo.berkas-fisik', $memo['id']) }}"
                                                        data-awal="{{ $posisiBerkas }}"
                                                        data-memo="{{ $memo['nomor_memo'] }}"
                                                        class="js-berkas w-full min-w-0 rounded-md border py-1 pl-1.5 pr-6 text-[11px] font-semibold cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 {{ $warnaBerkas }}">
                                                        <option value="" disabled @selected($posisiBerkas === '')>Belum diatur</option>
                                                        @foreach (['Tim Keuangan', 'PPSPM', 'Unit'] as $o)
                                                            <option value="{{ $o }}" @selected($o === $posisiBerkas)>{{ $o }}</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <span
                                                        class="inline-flex px-2 py-0.5 rounded-md border text-[11px] font-semibold {{ $warnaBerkas }}">{{ $posisiBerkas !== '' ? $posisiBerkas : 'Belum diatur' }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Nominal --}}
                                        <div class="memo-wide memo-nominal min-w-0">
                                            <span
                                                class="text-xs font-semibold text-gray-700 tabular-nums whitespace-nowrap {{ $st === 'batal' ? 'line-through text-gray-400' : '' }}">
                                                Rp {{ number_format($memo['nominal'] ?? 0, 0, ',', '.') }}
                                            </span>
                                        </div>

                                        {{-- Aksi (selalu dirender supaya kolom Dokumen tetap sejajar) --}}
                                        <div
                                            class="memo-wide memo-aksi min-w-0 space-y-2 {{ $adaAksi ? '' : 'memo-aksi-kosong' }}">
                                            @if ($adaAksi)
                                                @if ($adaPengajuan)
                                                    <p
                                                        class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-2 py-1 break-words">
                                                        Pengajuan pengembalian menunggu keputusan:
                                                        <span class="font-medium">{{ $memo['pengajuan_alasan'] }}</span>
                                                    </p>
                                                @endif

                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    {{-- Unit kerja --}}
                                                    @if (!empty($memo['bisa_kirim']))
                                                        <span class="aksi-kirim">
                                                            <x-aksi-button varian="biru"
                                                                :url="route('memo.kirim', $memo['id'])"
                                                                :konfirmasi="'Kirim memo ' . $memo['nomor_memo'] . ' ke Keuangan? Setelah dikirim, memo tidak bisa diedit.'">
                                                                <svg class="w-3.5 h-3.5 -ml-0.5 mr-1 inline-block" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24"
                                                                    aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="1.8"
                                                                        d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                                                </svg>
                                                                Kirim ke Keuangan
                                                            </x-aksi-button>
                                                        </span>
                                                    @endif
                                                    @if (!empty($memo['bisa_ajukan']))
                                                        <span class="aksi-ajukan">
                                                            <x-aksi-button varian="abu"
                                                                :url="route('memo.ajukan', $memo['id'])" prompt="alasan"
                                                                :wajib="true"
                                                                prompt-label="Alasan pengajuan pengembalian (wajib diisi):">
                                                                <svg class="w-3.5 h-3.5 -ml-0.5 mr-1 inline-block" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24"
                                                                    aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                                </svg>
                                                                Ajukan pengembalian
                                                            </x-aksi-button>
                                                        </span>
                                                    @endif
                                                    @if (!empty($memo['editable']))
                                                        <a href="{{ $memo['edit_url'] }}"
                                                            class="inline-flex items-center justify-center px-3 py-1.5 text-[11px] font-semibold text-gray-600 bg-white hover:bg-gray-100 border border-gray-300 rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                                                            Edit
                                                        </a>
                                                    @endif

                                                    {{-- Keuangan Sesdep --}}
                                                    @if ($keuangan)
                                                        @if ($adaPengajuan)
                                                            <x-aksi-button varian="hijau"
                                                                :url="route('memo.pengajuan.putusan', $memo['pengajuan_id'])"
                                                                :fields="['setuju' => 1]"
                                                                :konfirmasi="'Setujui pengajuan? Memo akan dikembalikan ke unit kerja' . (in_array($tahap, ['sakti', 'otp_ppk'], true) ? ' dan SAKTI dilepas' : '') . '.'">
                                                                Setujui pengajuan
                                                            </x-aksi-button>
                                                            <x-aksi-button varian="merah"
                                                                :url="route('memo.pengajuan.putusan', $memo['pengajuan_id'])"
                                                                :fields="['setuju' => 0]" prompt="catatan" :wajib="true"
                                                                prompt-label="Catatan penolakan pengajuan (wajib diisi):">
                                                                Tolak pengajuan
                                                            </x-aksi-button>
                                                        @endif

                                                        @if ($tahap === 'dikirim' || $tahap === 'sakti')
                                                            @php $gerbang = $tahap === 'dikirim' ? 'K2' : 'K3'; @endphp
                                                            <x-aksi-button varian="hijau"
                                                                :url="route('memo.verifikasi', $memo['id'])"
                                                                :fields="['gerbang' => $gerbang, 'lolos' => 1]"
                                                                :konfirmasi="'Verifikasi ' . $gerbang . ': lengkap dan sesuai?'">
                                                                {{ $gerbang }} lolos
                                                            </x-aksi-button>
                                                            <x-aksi-button varian="merah"
                                                                :url="route('memo.verifikasi', $memo['id'])"
                                                                :fields="['gerbang' => $gerbang, 'lolos' => 0]"
                                                                prompt="alasan" :wajib="true"
                                                                :prompt-label="'Alasan tidak lolos ' . $gerbang . ' (wajib diisi). Memo akan dikembalikan ke unit kerja:'">
                                                                {{ $gerbang }} tidak lolos
                                                            </x-aksi-button>
                                                        @endif

                                                        {{-- Input SAKTI: pilih SPP/SPBy + nomor --}}
                                                        @if ($tahap === 'lolos_spk')
                                                            <x-sakti-nomor-button
                                                                :url="route('memo.input-sakti', $memo['id'])"
                                                                :memo="$memo['nomor_memo']" />
                                                        @endif

                                                        {{-- Koreksi jenis/nomor setelah SAKTI --}}
                                                        @if (in_array($tahap, ['sakti', 'otp_ppk', 'ppspm'], true))
                                                            <x-sakti-nomor-button :ubah="true" label="Ubah nomor"
                                                                :url="route('memo.ubah-nomor-sakti', $memo['id'])"
                                                                :memo="$memo['nomor_memo']"
                                                                :jenis="$spp->jenis_dokumen ?? 'SPP'"
                                                                :nomor="$spp->nomor_spp_spby ?? ''" />
                                                        @endif

                                                        @if ($tahap === 'otp_ppk')
                                                            <x-aksi-button varian="biru"
                                                                :url="route('memo.selesai-otp', $memo['id'])"
                                                                konfirmasi="OTP PPK selesai? Setelah ini memo tidak bisa dikembalikan lagi.">
                                                                Selesai OTP
                                                            </x-aksi-button>
                                                        @endif

                                                        {{-- Ubah status manual (hanya pada tahap yang diizinkan) --}}
                                                        @if (count($opsi) > 0)
                                                            <form method="POST" action="{{ route('memo.status', $memo['id']) }}"
                                                                class="js-status-form m-0" data-maju="{{ $maju[$st] ?? '' }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="alasan" value="">
                                                                <select name="status"
                                                                    aria-label="Ubah status {{ $memo['nomor_memo'] }}"
                                                                    class="js-status-select border border-gray-200 bg-white rounded-md px-2 py-1 text-[11px] text-gray-600 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none cursor-pointer">
                                                                    <option value="">Ubah status…</option>
                                                                    @foreach ($opsi as $o)
                                                                        <option value="{{ $o }}">
                                                                            @if (($maju[$st] ?? null) === $o)
                                                                                Lanjut ke {{ $statusLabel[$o] }}
                                                                            @elseif ($o === 'batal')
                                                                                Batalkan memo
                                                                            @else
                                                                                Kembali ke {{ $statusLabel[$o] }}
                                                                            @endif
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </form>
                                                        @endif
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-[11px] text-gray-300">&mdash;</span>
                                            @endif
                                        </div>

                                        {{-- Dokumen: PDF saja --}}
                                        <div class="memo-wide memo-dokumen min-w-0">
                                            <a href="{{ $memo['pdf_url'] }}" target="_blank" rel="noopener"
                                                title="Buka PDF {{ $memo['status'] ?? '' }}"
                                                class="inline-flex items-center justify-center gap-1 px-2.5 py-1.5 text-[11px] font-semibold text-blue-600 bg-white hover:bg-blue-50 border border-blue-500 rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                                PDF
                                            </a>
                                        </div>
                                    </div>

                                    {{-- Log: tersembunyi sampai tombol Log (kiri bawah) ditekan --}}
                                    <div id="log-{{ $memo['id'] }}" class="hidden border-t border-gray-200 pt-3">
                                        <p
                                            class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 mb-2">
                                            Riwayat</p>
                                        @if (count($logMemo) > 0)
                                            <ol class="space-y-2.5">
                                                @foreach ($logMemo as $l)
                                                    <li class="flex gap-2.5">
                                                        <span
                                                            class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-gray-300"></span>
                                                        <div class="min-w-0 text-[11px] leading-snug">
                                                            <p class="font-semibold text-gray-700 break-words">
                                                                {{ $l['judul'] }}</p>
                                                            @if (filled($l['detail']))
                                                                <p class="text-gray-600 break-words">{{ $l['detail'] }}</p>
                                                            @endif
                                                            <p class="text-gray-400">
                                                                {{ \Illuminate\Support\Carbon::parse($l['waktu'])->translatedFormat('d M Y, H:i') }}
                                                                @if (filled($l['oleh']))
                                                                    · {{ $l['oleh'] }}
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ol>
                                        @else
                                            <p class="text-[11px] text-gray-400">Belum ada riwayat untuk memo ini.</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div class="bg-white rounded-2xl border border-gray-200 px-4 py-14 text-center">
                        <p class="text-sm font-semibold text-gray-800">Belum ada nomor memo</p>
                        <p class="text-xs text-gray-500 mt-1">Klik "Buat Memorandum" untuk membuat yang pertama.</p>
                    </div>
                @endforelse
            </div>

        </div>{{-- /memo-board --}}

        <div id="memo-no-result" class="hidden bg-white rounded-2xl border border-gray-200 px-4 py-14 text-center">
            <p class="text-sm font-semibold text-gray-800">Tidak ada nomor memo yang cocok</p>
            <p class="text-xs text-gray-500 mt-1">Ubah filter atau klik Reset untuk menampilkan semua.</p>
        </div>
    </div>

    @if ($keuangan)
        {{-- Dialog konfirmasi perubahan posisi berkas fisik --}}
        <dialog id="dlg-berkas" aria-labelledby="dlg-berkas-judul"
            class="m-auto w-[min(92vw,26rem)] rounded-2xl border border-gray-200 bg-white p-0 shadow-xl backdrop:bg-gray-900/50">
            <div class="px-5 pt-5">
                <h3 id="dlg-berkas-judul" class="text-base font-bold text-gray-900">Ubah posisi berkas fisik?</h3>
                <dl class="mt-3 space-y-1.5 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Memo</dt>
                        <dd id="dlg-berkas-memo" class="font-semibold text-gray-900 text-right break-all"></dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Dari</dt>
                        <dd id="dlg-berkas-dari" class="font-semibold text-gray-900"></dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Ke</dt>
                        <dd id="dlg-berkas-ke" class="font-semibold text-gray-900"></dd>
                    </div>
                </dl>
                <p class="mt-3 text-xs text-gray-500">Perubahan ini dicatat di Log memo beserta nama dan waktunya.</p>
            </div>
            <div class="mt-4 flex justify-end gap-2 border-t border-gray-100 px-5 py-3">
                <button type="button" id="dlg-berkas-batal"
                    class="rounded-lg border border-gray-300 bg-white px-3.5 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">Batal</button>
                <button type="button" id="dlg-berkas-ok"
                    class="rounded-lg border border-blue-800 bg-blue-700 px-3.5 py-1.5 text-sm font-semibold text-white hover:bg-blue-800 disabled:opacity-60 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-300">Ya, ubah</button>
            </div>
        </dialog>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Setiap form hapus diberi id unik, lalu konfirmasi lewat confirmDelete()
            document.querySelectorAll('.delete-memo-form').forEach(function (form, index) {
                if (!form.id) {
                    form.id = 'delete-memo-form-' + index;
                }

                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const label = form.dataset.label || 'nomor memo ini';
                    confirmDelete(form.id, label);
                });
            });

            // Tombol Log: buka / tutup riwayat memo
            document.querySelectorAll('.js-log-toggle').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const panel = document.getElementById(btn.dataset.target);
                    if (!panel) return;
                    const terbuka = panel.classList.toggle('hidden') === false;
                    btn.setAttribute('aria-expanded', terbuka ? 'true' : 'false');
                });
            });

            // Ubah status manual. Hanya sakti -> sp2d yang tanpa alasan;
            // selain itu wajib isi alasan (divalidasi juga di server).
            document.querySelectorAll('.js-status-select').forEach(function (sel) {
                sel.addEventListener('change', async function () {
                    const form = sel.closest('form');
                    const ke = sel.value;
                    if (!ke) return;

                    let alasan = '';
                    if (form.dataset.maju !== ke) {
                        alasan = await window.appDialog.prompt({
                            judul: 'Ubah status memo',
                            label: 'Alasan perubahan status (wajib diisi):',
                            wajib: true,
                            teksOk: 'Ubah status',
                        });
                        if (alasan === null) {
                            sel.value = '';
                            return;
                        }
                    }

                    form.querySelector('input[name="alasan"]').value = alasan;
                    form.submit();
                });
            });

            // Tombol aksi alur (kirim, ajukan, verifikasi, dst.) ada di komponen aksi-button.
        });
    </script>

    {{-- Toast + Filter instan client-side + Berkas fisik --}}
    <script>
        // ---------- Toast: tetap tampil setelah reload lewat sessionStorage
        (function () {
            function tampilToast(pesan, jenis) {
                const ok = jenis !== 'error';
                const el = document.createElement('div');
                el.setAttribute('role', ok ? 'status' : 'alert');
                el.className = 'fixed top-20 right-4 z-[100] max-w-sm rounded-xl border px-4 py-3 text-sm font-semibold shadow-lg transition-opacity duration-300 '
                    + (ok ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
                        : 'bg-red-50 text-red-700 border-red-200');
                el.textContent = pesan;
                document.body.appendChild(el);
                setTimeout(function () { el.style.opacity = '0'; }, 3500);
                setTimeout(function () { el.remove(); }, 4000);
            }
            window.tampilToast = tampilToast;

            // Tampilkan pesan yang ditinggalkan sebelum reload
            try {
                const raw = sessionStorage.getItem('toast.pending');
                if (raw) {
                    sessionStorage.removeItem('toast.pending');
                    const t = JSON.parse(raw);
                    if (t && t.pesan) tampilToast(t.pesan, t.jenis);
                }
            } catch (e) { }
        })();

        // ---------- Filter instan client-side
        (function () {
            const cariInput = document.getElementById('memo-filter-cari');
            const jenisSelect = document.getElementById('memo-filter-jenis');
            const statusSelect = document.getElementById('memo-filter-status');
            const tahapSelect = document.getElementById('memo-filter-tahap');
            const unitSelect = document.getElementById('memo-filter-unit'); // hanya ada untuk Keuangan
            const picSelect = document.getElementById('memo-filter-pic');
            const dariInput = document.getElementById('memo-filter-dari');
            const sampaiInput = document.getElementById('memo-filter-sampai');
            const resetBtn = document.getElementById('memo-filter-reset');

            const items = Array.from(document.querySelectorAll('.js-memo-item'));
            const noResult = document.getElementById('memo-no-result');
            const countEl = document.getElementById('memo-visible-count');

            if (items.length === 0) return; // belum ada data, tidak perlu filter

            function applyFilter() {
                const cari = cariInput.value.trim().toLowerCase();
                const jenis = jenisSelect.value;
                const status = statusSelect.value;
                const tahap = tahapSelect.value;
                const unit = unitSelect ? unitSelect.value : '';
                const pic = picSelect.value;
                const dari = dariInput.value; // YYYY-MM-DD, bisa dibandingkan sebagai string
                const sampai = sampaiInput.value;

                let visible = 0;

                items.forEach(function (el) {
                    const matchCari = cari === '' || el.dataset.search.includes(cari);
                    const matchJenis = jenis === 'semua' || el.dataset.jenis === jenis;
                    const matchStatus = status === '' || (el.dataset.status || '').split(' ').includes(status);
                    const matchTahap = tahap === '' || (el.dataset.tahap || '').split(' ').includes(tahap);
                    const matchUnit = unit === '' || el.dataset.unit === unit;
                    const matchPic = pic === '' || el.dataset.pic === pic;

                    const tgl = el.dataset.tanggal || '';
                    const matchDari = dari === '' || (tgl !== '' && tgl >= dari);
                    const matchSampai = sampai === '' || (tgl !== '' && tgl <= sampai);

                    const ok = matchCari && matchJenis && matchStatus && matchTahap && matchUnit
                        && matchPic && matchDari && matchSampai;
                    el.style.display = ok ? '' : 'none';

                    if (ok) {
                        visible++;
                        el.querySelector('.js-memo-number').textContent = visible;
                    }
                });

                countEl.textContent = visible;
                noResult.classList.toggle('hidden', visible > 0);
            }

            cariInput.addEventListener('input', applyFilter);
            jenisSelect.addEventListener('change', applyFilter);
            statusSelect.addEventListener('change', applyFilter);
            tahapSelect.addEventListener('change', applyFilter);
            if (unitSelect) unitSelect.addEventListener('change', applyFilter);
            picSelect.addEventListener('change', applyFilter);
            dariInput.addEventListener('change', applyFilter);
            sampaiInput.addEventListener('change', applyFilter);

            resetBtn.addEventListener('click', function () {
                cariInput.value = '';
                jenisSelect.value = 'semua';
                statusSelect.value = '';
                tahapSelect.value = '';
                if (unitSelect) unitSelect.value = '';
                picSelect.value = '';
                dariInput.value = '';
                sampaiInput.value = '';
                applyFilter();
            });
        })();

        // ---------- Berkas fisik: konfirmasi dulu, simpan, lalu toast (dicatat di Log)
        (function () {
            const dlg = document.getElementById('dlg-berkas');
            if (!dlg) return; // dialog hanya dirender untuk Keuangan

            const warna = {
                '': ['bg-gray-50', 'text-gray-400', 'border-gray-200'],
                'Tim Keuangan': ['bg-blue-50', 'text-blue-700', 'border-blue-200'],
                'PPSPM': ['bg-emerald-50', 'text-emerald-700', 'border-emerald-200'],
                'Unit': ['bg-gray-50', 'text-gray-700', 'border-gray-200'],
            };
            const semuaWarna = [].concat.apply([], Object.values(warna));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            const ok = document.getElementById('dlg-berkas-ok');
            let sel = null;

            function warnai(s) {
                s.classList.remove.apply(s.classList, semuaWarna);
                s.classList.add.apply(s.classList, warna[s.value] || warna['']);
            }
            function batal() {
                if (sel) { sel.value = sel.dataset.awal; warnai(sel); }
                sel = null;
            }
            function simpan() {
                const s = sel;
                if (!s) return;
                fetch(s.dataset.url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ posisi: s.value }),
                }).then(function (r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    s.dataset.awal = s.value;
                    try {
                        sessionStorage.setItem('toast.pending', JSON.stringify({
                            pesan: 'Berkas fisik memo ' + (s.dataset.memo || '') + ' dipindah ke ' + s.value + '.',
                            jenis: 'ok',
                        }));
                    } catch (e) { }
                    sel = null;
                    location.reload(); // supaya entri baru langsung muncul di Log
                }).catch(function () {
                    if (dlg.open) dlg.close();
                    batal();
                    window.tampilToast('Gagal menyimpan posisi berkas fisik.', 'error');
                });
            }

            document.querySelectorAll('.js-berkas').forEach(function (s) {
                s.addEventListener('change', function () {
                    sel = s;
                    warnai(s);
                    const dari = s.dataset.awal || 'Belum diatur';
                    if (typeof dlg.showModal !== 'function') {
                        if (window.confirm('Ubah posisi berkas ' + s.dataset.memo + ' dari ' + dari + ' ke ' + s.value + '?')) simpan();
                        else batal();
                        return;
                    }
                    document.getElementById('dlg-berkas-memo').textContent = s.dataset.memo || '-';
                    document.getElementById('dlg-berkas-dari').textContent = dari;
                    document.getElementById('dlg-berkas-ke').textContent = s.value;
                    ok.disabled = false;
                    dlg.showModal();
                });
            });

            ok.addEventListener('click', function () { this.disabled = true; simpan(); });
            document.getElementById('dlg-berkas-batal').addEventListener('click', function () { dlg.close(); batal(); });
            dlg.addEventListener('cancel', batal); // tombol Esc
        })();
    </script>

    <style>
        /*
          Papan daftar = container. Semua keputusan layout memakai lebar papan, bukan lebar layar,
          jadi hasilnya konsisten di zoom 80% - 125% dan sidebar terbuka/tertutup.

          Lebar kolom memo (nomor | status | nominal | aksi | dokumen) dipusatkan di satu variabel,
          dipakai baik oleh header maupun baris, supaya selalu sejajar.
        */
        .memo-board {
            container-type: inline-size;
            container-name: board;
            --memo-grid: 7.25rem 9.5rem 5.25rem minmax(0, 1fr) 3.75rem;
            --memo-gap: .75rem;
        }

        .memo-right {
            container-type: inline-size;
            container-name: right;
        }

        /* Default (sempit): kartu menumpuk, baris memo 2 kolom, header disembunyikan */
        .memo-cols {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem 1.5rem;
        }

        .memo-head {
            display: none;
        }

        .memo-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem 1rem;
            align-items: start;
        }

        .memo-row>.memo-wide {
            grid-column: span 2;
        }

        .memo-nominal {
            text-align: left;
        }

        .memo-aksi-kosong {
            display: none;
        }

        /* Area memo sudah cukup lebar (kartu masih menumpuk): header hanya untuk kolom memo */
        @container board (min-width: 39rem) {
            .memo-head {
                display: block;
                padding: 0 1.25rem;
            }

            .memo-head-kegiatan {
                display: none;
            }

            .memo-head-right {
                display: grid;
                grid-template-columns: var(--memo-grid);
                gap: 0 var(--memo-gap);
                padding: 0 calc(.75rem + 1px);
            }
        }

        /* Papan lebar: kegiatan di kiri, memo di kanan, header lengkap */
        @container board (min-width: 64rem) {
            .memo-cols {
                grid-template-columns: minmax(15rem, 4fr) minmax(0, 7fr);
            }

            .memo-head {
                display: grid;
                grid-template-columns: minmax(15rem, 4fr) minmax(0, 7fr);
                gap: 0 1.5rem;
                padding: 0 1.25rem;
            }

            .memo-head-kegiatan {
                display: block;
            }
        }

        /* Area memo cukup lebar: baris memo jadi 5 kolom (nomor | status | nominal | aksi | dokumen) */
        @container right (min-width: 36rem) {
            .memo-row {
                grid-template-columns: var(--memo-grid);
                gap: .75rem var(--memo-gap);
            }

            .memo-row>.memo-wide {
                grid-column: auto;
            }

            .memo-nominal {
                text-align: right;
            }

            .memo-aksi-kosong {
                display: block;
            }
        }

        /* ============ Warna tombol aksi utama ============ */
        /* Pembungkus tidak mengubah layout; hanya mengganti tampilan tombol di dalam komponen aksi-button */
        .aksi-kirim,
        .aksi-ajukan {
            display: contents;
        }

        /* Kirim ke Keuangan: biru tua bergradasi, solid dan tegas */
        .aksi-kirim button,
        .aksi-kirim a {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            padding: .4rem .9rem !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            color: #fff !important;
            background-image: linear-gradient(to bottom, #2563eb, #1d4ed8) !important;
            background-color: #1d4ed8 !important;
            border: 1px solid #1e40af !important;
            border-radius: .5rem !important;
            box-shadow: 0 1px 2px rgba(30, 64, 175, .35), inset 0 1px 0 rgba(255, 255, 255, .15) !important;
            transition: background .15s, box-shadow .15s, transform .05s;
            cursor: pointer;
        }

        .aksi-kirim button:hover,
        .aksi-kirim a:hover {
            background-image: linear-gradient(to bottom, #1d4ed8, #1e40af) !important;
            box-shadow: 0 2px 6px rgba(30, 64, 175, .4), inset 0 1px 0 rgba(255, 255, 255, .15) !important;
        }

        .aksi-kirim button:active,
        .aksi-kirim a:active {
            transform: translateY(1px);
        }

        .aksi-kirim button:focus-visible,
        .aksi-kirim a:focus-visible {
            outline: 2px solid #93c5fd;
            outline-offset: 2px;
        }

        /* Ajukan pengembalian: outline amber, tegas tapi tidak seagresif tombol merah */
        .aksi-ajukan button,
        .aksi-ajukan a {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            padding: .4rem .9rem !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            color: #b45309 !important;
            background: #fffbeb !important;
            background-image: none !important;
            border: 1px solid #f59e0b !important;
            border-radius: .5rem !important;
            box-shadow: 0 1px 2px rgba(180, 83, 9, .12) !important;
            transition: background .15s, color .15s, border-color .15s;
            cursor: pointer;
        }

        .aksi-ajukan button:hover,
        .aksi-ajukan a:hover {
            background: #fef3c7 !important;
            color: #92400e !important;
            border-color: #d97706 !important;
        }

        .aksi-ajukan button:focus-visible,
        .aksi-ajukan a:focus-visible {
            outline: 2px solid #fcd34d;
            outline-offset: 2px;
        }
    </style>
</x-app-layout>