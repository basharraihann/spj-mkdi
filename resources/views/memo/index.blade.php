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
        $tahapBadge = fn($t) => match ($t) {
            'dikirim' => 'bg-blue-50 text-blue-700 border-blue-200',
            'lolos_spk' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'sakti' => 'bg-sky-50 text-sky-700 border-sky-200',
            'otp_ppk' => 'bg-violet-50 text-violet-700 border-violet-200',
            'ppspm' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'dikembalikan' => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-gray-50 text-gray-600 border-gray-200',
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
        $logMap = $logMap ?? [];

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

        // Opsi filter unit (hanya dipakai Keuangan)
        $unitOptions = $groups->pluck('unit')->filter(fn($u) => filled($u) && $u !== '-')->unique()->sort()->values();

        $statusClass = fn($status) => $status === 'PNS'
            ? 'bg-blue-50 text-blue-700 border-blue-200'
            : 'bg-orange-50 text-orange-700 border-orange-200';

        // ---- Kelas Tailwind yang dipakai berulang (satu tempat, biar gampang diubah) ----
        $kelasLabel = 'text-xs font-semibold text-gray-600';
        $kelasField = 'w-full min-w-0 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition';
        $kelasBadge = 'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border';
        $kelasDt = 'w-12 shrink-0 text-[10px] font-semibold uppercase tracking-wide text-gray-400 pt-px';
        // Tombol kecil (PDF, Log, Edit)
        $kelasBtn = 'inline-flex items-center justify-center gap-1 px-2.5 py-1.5 text-[11px] font-semibold bg-white border rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500';
        $kelasBtnPdf = $kelasBtn . ' text-blue-600 hover:bg-blue-50 border-blue-500';
        $kelasBtnAbu = $kelasBtn . ' text-gray-600 hover:bg-gray-100 border-gray-300';
    @endphp

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-5">

        @if (session('error') || $errors->any())
            <div class="rounded-xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm space-y-1">
                @if (session('error'))
                    <p>{{ session('error') }}</p>
                @endif
                @foreach ($errors->all() as $pesanError)
                    <p>{{ $pesanError }}</p>
                @endforeach
            </div>
        @endif

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
                <a href="{{ route('memo.create') }}"
                    class="inline-flex w-full sm:w-auto shrink-0 items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm shadow-md shadow-blue-600/20 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Memorandum
                </a>
            </div>

            {{-- Filter instan, tanpa reload. Kolom grid rata dan menyesuaikan lebar layar (lihat .mm-filter di spj-ui.css). --}}
            <div class="mm-filter">
                <div class="mm-filter-cari flex flex-col gap-1 min-w-0">
                    <label for="memo-filter-cari" class="{{ $kelasLabel }}">Cari</label>
                    <input type="text" id="memo-filter-cari" autocomplete="off"
                        placeholder="Nomor memo, uraian, PIC, atau unit..." class="{{ $kelasField }}">
                </div>

                <div class="flex flex-col gap-1 min-w-0">
                    <label for="memo-filter-jenis" class="{{ $kelasLabel }}">Jenis</label>
                    <select id="memo-filter-jenis" class="{{ $kelasField }}">
                        <option value="semua" selected>Semua Jenis</option>
                        <option value="perdin">Perjalanan Dinas</option>
                        @foreach (\App\Models\MemoEntry::JENIS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1 min-w-0">
                    <label for="memo-filter-status" class="{{ $kelasLabel }}">Status</label>
                    <select id="memo-filter-status" class="{{ $kelasField }}">
                        <option value="">Semua Status</option>
                        @foreach ($statusLabel as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1 min-w-0">
                    <label for="memo-filter-tahap" class="{{ $kelasLabel }}">Tahap</label>
                    <select id="memo-filter-tahap" class="{{ $kelasField }}">
                        <option value="">Semua Tahap</option>
                        @foreach ($tahapLabel as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($keuangan)
                    <div class="flex flex-col gap-1 min-w-0">
                        <label for="memo-filter-unit" class="{{ $kelasLabel }}">Unit</label>
                        <select id="memo-filter-unit" class="{{ $kelasField }}">
                            <option value="">Semua Unit</option>
                            @foreach ($unitOptions as $namaUnit)
                                <option value="{{ $namaUnit }}">{{ $namaUnit }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex flex-col gap-1 min-w-0">
                    <label for="memo-filter-pic" class="{{ $kelasLabel }}">PIC</label>
                    <select id="memo-filter-pic" class="{{ $kelasField }}">
                        <option value="">Semua PIC</option>
                        @foreach ($picOptions as $namaPic)
                            <option value="{{ $namaPic }}">{{ $namaPic }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1 min-w-0">
                    <label for="memo-filter-dari" class="{{ $kelasLabel }}">Dari tanggal</label>
                    <input type="date" id="memo-filter-dari" class="{{ $kelasField }}">
                </div>

                <div class="flex flex-col gap-1 min-w-0">
                    <label for="memo-filter-sampai" class="{{ $kelasLabel }}">Sampai tanggal</label>
                    <input type="date" id="memo-filter-sampai" class="{{ $kelasField }}">
                </div>

                <button type="button" id="memo-filter-reset"
                    class="mm-filter-reset text-sm text-gray-500 hover:text-gray-700 transition cursor-pointer">
                    Reset
                </button>
            </div>
        </div>

        {{-- ============ DAFTAR: satu kartu per kegiatan ============ --}}
        {{--
            Tata letak memakai CONTAINER QUERY (lihat <style> di akhir file), bukan breakpoint layar.
            Yang diukur adalah lebar area daftar itu sendiri, jadi hasilnya sama di Chrome, Edge,
            zoom berapa pun, dan sidebar terbuka atau tertutup.
              - papan >= 70rem : kartu 2 kolom (kegiatan | memo) + header kolom
              - kolom memo >= 42rem : baris memo 4 kolom (nomor | status | nominal | aksi)
              - selain itu : semua menumpuk
        --}}
        <div class="mm-board">

            {{-- Header kolom: hanya tampil saat papan cukup lebar --}}
            <div class="mm-head text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-3">
                <div class="pl-10">Kegiatan</div>
                <div class="mm-head-right">
                    <div>Nomor Memo</div>
                    <div>Status</div>
                    <div class="text-right">Nominal</div>
                    <div>Aksi</div>
                </div>
            </div>

            <div id="memo-list" class="space-y-3">
                @forelse ($groups as $g)
                    <article
                        class="js-memo-item mm-cols bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5"
                        data-search="{{ $g['search_blob'] }}" data-jenis="{{ $g['jenis'] }}" data-pic="{{ $g['pic'] }}"
                        data-unit="{{ $g['unit'] ?? '' }}" data-status="{{ $g['status_blob'] }}"
                        data-tahap="{{ $g['tahap_blob'] }}" data-tanggal="{{ $g['tanggal_iso'] }}">

                        {{-- ===== Kiri: info kegiatan + hapus / PDF / log ===== --}}
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
                                            <dt class="{{ $kelasDt }}">Unit</dt>
                                            <dd class="text-gray-700 font-medium break-words min-w-0">{{ $g['unit'] ?? '-' }}</dd>
                                        </div>
                                    @endif
                                    <div class="flex gap-2">
                                        <dt class="{{ $kelasDt }}">PIC</dt>
                                        <dd class="text-gray-700 font-medium break-words min-w-0">{{ $g['pic'] ?: '-' }}</dd>
                                    </div>
                                    <div class="flex gap-2">
                                        <dt class="{{ $kelasDt }}">MAK</dt>
                                        <dd class="text-gray-700 font-medium font-mono text-[11px] break-words [overflow-wrap:anywhere] min-w-0">
                                            {{ $g['mak'] ?? '-' }}
                                        </dd>
                                    </div>
                                </dl>

                                {{-- Hapus + PDF + Log (satu grup per memo PNS / Non PNS) --}}
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

                                    @foreach ($g['items'] as $memoDok)
                                        @php $jmlLog = count($logMap[$memoDok['id']] ?? []); @endphp

                                        <div class="inline-flex items-center gap-1.5">
                                            @if (count($g['items']) > 1 && $memoDok['status'])
                                                <span class="{{ $kelasBadge }} {{ $statusClass($memoDok['status']) }}">
                                                    {{ $memoDok['status'] }}
                                                </span>
                                            @endif

                                            <a href="{{ $memoDok['pdf_url'] }}" target="_blank" rel="noopener"
                                                title="Buka PDF {{ $memoDok['status'] ?? '' }}"
                                                class="{{ $kelasBtnPdf }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                    aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                                PDF
                                            </a>

                                            <button type="button" class="js-log-toggle cursor-pointer {{ $kelasBtnAbu }}"
                                                data-target="log-{{ $memoDok['id'] }}" aria-expanded="false"
                                                aria-controls="log-{{ $memoDok['id'] }}">
                                                Log
                                                @if ($jmlLog > 0)
                                                    <span class="text-gray-400">({{ $jmlLog }})</span>
                                                @endif
                                            </button>
                                        </div>
                                    @endforeach
                                </div>

                                @unless ($g['bisa_hapus'])
                                    <p class="mt-3 text-[11px] text-gray-400 border border-dashed border-gray-200 bg-gray-50/60 rounded-lg px-3 py-2">
                                        Tidak bisa dihapus karena sudah dikirim ke Keuangan, SAKTI, atau SP2D.
                                        Ajukan pengembalian atau gunakan status Batal.
                                    </p>
                                @endunless
                            </div>
                        </div>

                        {{-- ===== Kanan: memo per status (PNS / Non PNS) ===== --}}
                        <div class="mm-right space-y-2 min-w-0">
                            @foreach ($g['items'] as $memo)
                                @php
                                    $st = $memo['status_anggaran'] ?? 'outstanding';
                                    $tahap = $memo['tahap'] ?? 'draft';
                                    $idx = $urutStatus[$st] ?? null;
                                    $tglSt = $st === 'sakti'
                                        ? ($memo['tgl_sakti'] ?? null)
                                        : ($st === 'sp2d' ? ($memo['tgl_sp2d'] ?? null) : null);
                                    $opsi = $opsiManual($st, $tahap);
                                    $logMemo = $logMap[$memo['id']] ?? [];

                                    $adaPengajuan = !empty($memo['pengajuan_id']);
                                    $aksiUnit = !empty($memo['bisa_kirim']) || !empty($memo['bisa_ajukan']) || !empty($memo['editable']);
                                    $aksiKeuangan = $keuangan
                                        && ($adaPengajuan || in_array($tahap, ['dikirim', 'lolos_spk', 'sakti', 'otp_ppk'], true));
                                    $aksiStatus = $keuangan && count($opsi) > 0;
                                    $adaAksi = $adaPengajuan || $aksiUnit || $aksiKeuangan || $aksiStatus;
                                @endphp

                                <div class="rounded-xl bg-gray-50 border border-gray-200 p-3 space-y-3">

                                    {{-- Baris info: nomor | status | nominal | aksi --}}
                                    <div class="mm-row">

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
                                                <span class="{{ $kelasBadge }} {{ $statusClass($memo['status']) }}">
                                                    {{ $memo['status'] }}
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Progres & status --}}
                                        <div class="min-w-0 flex flex-col items-start gap-1.5">
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                                <span class="{{ $kelasBadge }} {{ $statusBadge($st) }}">
                                                    {{ $statusLabel[$st] ?? $st }}
                                                </span>

                                                @if ($idx !== null)
                                                    <span class="flex items-center gap-0.5" role="img"
                                                        aria-label="Progres: {{ $statusLabel[$st] }}, tahap {{ $idx + 1 }} dari 3">
                                                        @foreach ([0, 1, 2] as $i)
                                                            <span
                                                                class="h-1 w-5 rounded-full {{ $i <= $idx ? $statusBar($st) : 'bg-gray-200' }}"></span>
                                                        @endforeach
                                                    </span>
                                                @endif

                                                @if ($tglSt)
                                                    <span class="text-[10px] text-gray-400">
                                                        {{ \Illuminate\Support\Carbon::parse($tglSt)->translatedFormat('d M Y') }}
                                                    </span>
                                                @endif
                                            </div>

                                            <span class="{{ $kelasBadge }} {{ $tahapBadge($tahap) }}">
                                                {{ $tahapLabel[$tahap] ?? $tahap }}
                                            </span>
                                        </div>

                                        {{-- Nominal --}}
                                        <div class="mm-wide mm-nominal min-w-0">
                                            <span
                                                class="text-xs font-semibold text-gray-700 tabular-nums whitespace-nowrap {{ $st === 'batal' ? 'line-through text-gray-400' : '' }}">
                                                Rp {{ number_format($memo['nominal'] ?? 0, 0, ',', '.') }}
                                            </span>
                                        </div>

                                        {{-- Aksi --}}
                                        @if ($adaAksi)
                                            <div class="mm-wide min-w-0 space-y-2">
                                                @if ($adaPengajuan)
                                                    <p class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-2 py-1 break-words">
                                                        Pengajuan pengembalian menunggu keputusan:
                                                        <span class="font-medium">{{ $memo['pengajuan_alasan'] }}</span>
                                                    </p>
                                                @endif

                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    {{-- Unit kerja --}}
                                                    @if (!empty($memo['bisa_kirim']))
                                                        <x-aksi-button varian="biru"
                                                            :url="route('memo.kirim', $memo['id'])"
                                                            :konfirmasi="'Kirim memo ' . $memo['nomor_memo'] . ' ke Keuangan? Setelah dikirim, memo tidak bisa diedit.'">
                                                            Kirim ke Keuangan
                                                        </x-aksi-button>
                                                    @endif
                                                    @if (!empty($memo['bisa_ajukan']))
                                                        <x-aksi-button varian="abu"
                                                            :url="route('memo.ajukan', $memo['id'])"
                                                            prompt="alasan" :wajib="true"
                                                            prompt-label="Alasan pengajuan pengembalian (wajib diisi):">
                                                            Ajukan pengembalian
                                                        </x-aksi-button>
                                                    @endif
                                                    @if (!empty($memo['editable']))
                                                        <a href="{{ $memo['edit_url'] }}" class="{{ $kelasBtnAbu }}">
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
                                                                :fields="['setuju' => 0]"
                                                                prompt="catatan" :wajib="true"
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

                                                        @if ($tahap === 'lolos_spk')
                                                            <x-aksi-button varian="biru"
                                                                :url="route('memo.input-sakti', $memo['id'])"
                                                                :konfirmasi="'Catat memo ' . $memo['nomor_memo'] . ' sudah diinput di SAKTI?'">
                                                                Input SAKTI
                                                            </x-aksi-button>
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
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Log: tersembunyi sampai tombol Log ditekan --}}
                                    <div id="log-{{ $memo['id'] }}" class="hidden border-t border-gray-200 pt-3">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 mb-2">Riwayat</p>
                                        @if (count($logMemo) > 0)
                                            <ol class="space-y-2.5">
                                                @foreach ($logMemo as $l)
                                                    <li class="flex gap-2.5">
                                                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-gray-300"></span>
                                                        <div class="min-w-0 text-[11px] leading-snug">
                                                            <p class="font-semibold text-gray-700 break-words">{{ $l['judul'] }}</p>
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
        </div>{{-- /mm-board --}}

        <div id="memo-no-result" class="hidden bg-white rounded-2xl border border-gray-200 px-4 py-14 text-center">
            <p class="text-sm font-semibold text-gray-800">Tidak ada nomor memo yang cocok</p>
            <p class="text-xs text-gray-500 mt-1">Ubah filter atau klik Reset untuk menampilkan semua.</p>
        </div>
    </div>

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

    {{-- Filter instan client-side --}}
    <script>
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
    </script>

    <style>
        /* ===== Daftar memo: tata letak berdasarkan lebar papan (container query), bukan lebar layar ===== */
        .mm-board { container-type: inline-size; container-name: board; }
        .mm-right { container-type: inline-size; container-name: right; }

        /* Default (sempit): kartu menumpuk, baris memo 2 kolom, header kolom disembunyikan */
        .mm-cols { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem 1.5rem; }
        .mm-head { display: none; }
        .mm-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem 1rem; align-items: start; }
        .mm-row > .mm-wide { grid-column: span 2; }
        .mm-nominal { text-align: left; }

        /* Papan lebar: kegiatan di kiri, memo di kanan, header kolom muncul */
        @container board (min-width: 70rem) {
            .mm-cols { grid-template-columns: minmax(0, 1fr) minmax(0, 2fr); }
            .mm-head { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 2fr); gap: 0 1.5rem; padding: 0 1.25rem; }
            .mm-head-right { display: grid; grid-template-columns: 8rem 11rem 6rem minmax(0, 1fr); gap: 0 1rem; padding: 0 calc(.75rem + 1px); }
        }

        /* Kolom memo cukup lebar: baris memo jadi 4 kolom (nomor | status | nominal | aksi) */
        @container right (min-width: 42rem) {
            .mm-row { grid-template-columns: 8rem 11rem 6rem minmax(0, 1fr); }
            .mm-row > .mm-wide { grid-column: auto; }
            .mm-nominal { text-align: right; }
        }
    </style>
</x-app-layout>