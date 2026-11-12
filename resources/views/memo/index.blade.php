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

        // Kelas tombol aksi alur
        $btnBase = 'inline-flex items-center justify-center px-2.5 py-1.5 xl:py-1 text-[11px] font-semibold rounded-md border transition cursor-pointer focus:outline-none focus-visible:ring-2';
        $btnBiru = $btnBase . ' text-white bg-blue-600 hover:bg-blue-700 border-blue-600 focus-visible:ring-blue-500';
        $btnHijau = $btnBase . ' text-white bg-emerald-600 hover:bg-emerald-700 border-emerald-600 focus-visible:ring-emerald-500';
        $btnMerah = $btnBase . ' text-red-600 bg-white hover:bg-red-50 border-red-400 focus-visible:ring-red-500';
        $btnAbu = $btnBase . ' text-gray-600 bg-white hover:bg-gray-100 border-gray-300 focus-visible:ring-blue-500';

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
                // Hapus hanya boleh kalau semua memo di grup masih outstanding/batal
                // DAN belum dikirim ke Keuangan (draft / dikembalikan).
                'bisa_hapus' => $statuses->every(fn($s) => in_array($s, ['outstanding', 'batal'], true))
                    && $tahaps->every(fn($t) => in_array($t, ['draft', 'dikembalikan'], true)),
            ];
        });

        $statusClass = fn($status) => $status === 'PNS'
            ? 'bg-blue-50 text-blue-700 border-blue-200'
            : 'bg-orange-50 text-orange-700 border-orange-200';

        $bisaBuat = auth()->user()->can('buat-memo');
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

            {{-- Filter instan, tanpa reload --}}
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="memo-filter-cari" class="text-xs font-semibold text-gray-600">Cari</label>
                    <input type="text" id="memo-filter-cari" autocomplete="off"
                        placeholder="Nomor memo, uraian, atau PIC..."
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
        <div class="space-y-3">

            {{-- Judul kolom (hanya layar lebar, sejajar dengan isi kartu) --}}
            <div
                class="hidden xl:grid grid-cols-[minmax(0,1fr)_44rem] gap-6 px-5 text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                <div>Kegiatan</div>
                <div class="grid grid-cols-[9rem_minmax(0,1fr)_7rem_5rem] gap-x-3 px-3">
                    <div>Nomor Memo</div>
                    <div>Progres &amp; Status</div>
                    <div class="text-right">Nominal</div>
                    <div class="text-right">Aksi</div>
                </div>
            </div>

            <div id="memo-list" class="space-y-3">
                @forelse ($groups as $g)
                    <article
                        class="js-memo-item bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5 grid gap-4 xl:grid-cols-[minmax(0,1fr)_44rem] xl:gap-6"
                        data-search="{{ $g['search_blob'] }}" data-jenis="{{ $g['jenis'] }}" data-pic="{{ $g['pic'] }}"
                        data-status="{{ $g['status_blob'] }}" data-tanggal="{{ $g['tanggal_iso'] }}">

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
                                    <div class="flex gap-2">
                                        <dt class="w-12 shrink-0 text-[10px] font-semibold uppercase tracking-wide text-gray-400 pt-px">PIC</dt>
                                        <dd class="text-gray-700 font-medium break-words min-w-0">{{ $g['pic'] ?: '-' }}</dd>
                                    </div>
                                    <div class="flex gap-2">
                                        <dt class="w-12 shrink-0 text-[10px] font-semibold uppercase tracking-wide text-gray-400 pt-px">MAK</dt>
                                        <dd class="text-gray-700 font-medium font-mono text-xs break-all min-w-0">
                                            {{ $g['mak'] ?? '-' }}
                                        </dd>
                                    </div>
                                </dl>

                                @if ($g['bisa_hapus'])
                                    <form action="{{ route('memo.destroy', $g['delete_id']) }}" method="POST"
                                        class="delete-memo-form inline-flex mt-3 m-0" data-label="{{ $g['delete_label'] }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus {{ $g['delete_label'] }}"
                                            class="inline-flex items-center justify-center px-3 py-2 xl:py-1 text-[11px] font-semibold text-red-500 bg-white hover:bg-red-50 border border-red-400 rounded-md transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                                            {{ $g['delete_text'] }}
                                        </button>
                                    </form>
                                @else
                                    <p class="mt-3 text-[11px] text-gray-400 border border-dashed border-gray-200 bg-gray-50/60 rounded-lg px-3 py-2">
                                        Tidak bisa dihapus karena sudah dikirim ke Keuangan, SAKTI, atau SP2D.
                                        Ajukan pengembalian atau gunakan status Batal.
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Nomor memo per status (PNS / Non PNS) --}}
                        <div class="space-y-2 min-w-0 xl:pt-0">
                            @foreach ($g['items'] as $memo)
                                @php
                                    $st = $memo['status_anggaran'] ?? 'outstanding';
                                    $tahap = $memo['tahap'] ?? 'draft';
                                    $idx = $urutStatus[$st] ?? null;
                                    $tglSt = $st === 'sakti'
                                        ? ($memo['tgl_sakti'] ?? null)
                                        : ($st === 'sp2d' ? ($memo['tgl_sp2d'] ?? null) : null);
                                    $opsi = $opsiManual($st, $tahap);
                                @endphp
                                <div
                                    class="rounded-xl bg-gray-50 border border-gray-200 p-3 grid grid-cols-2 gap-x-3 gap-y-3 xl:grid-cols-[9rem_minmax(0,1fr)_7rem_5rem] xl:items-start">

                                    {{-- Kolom 1: Nomor memo --}}
                                    <div class="order-1 xl:order-none min-w-0 flex flex-col items-start gap-1.5">
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

                                    {{-- Kolom 2: Progres & status --}}
                                    <div class="order-3 col-span-2 xl:order-none xl:col-span-1 min-w-0 flex flex-col items-start gap-1.5">

                                        {{-- Status anggaran + progres 3 segmen --}}
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $statusBadge($st) }}">
                                                {{ $statusLabel[$st] ?? $st }}
                                            </span>

                                            @if ($idx !== null)
                                                <span class="flex items-center gap-0.5"
                                                    role="img"
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

                                        {{-- Tahap alur --}}
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $tahapBadge($tahap) }}">
                                            {{ $tahapLabel[$tahap] ?? $tahap }}
                                        </span>

                                        @if (!empty($memo['pengajuan_id']))
                                            <p class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-2 py-1 break-words">
                                                Pengajuan pengembalian menunggu keputusan:
                                                <span class="font-medium">{{ $memo['pengajuan_alasan'] }}</span>
                                            </p>
                                        @endif

                                        {{-- Aksi alur: unit kerja --}}
                                        @if (!empty($memo['bisa_kirim']) || !empty($memo['bisa_ajukan']))
                                            <div class="flex flex-wrap gap-1.5">
                                                @if (!empty($memo['bisa_kirim']))
                                                    <button type="button" class="js-aksi {{ $btnBiru }}"
                                                        data-url="{{ route('memo.kirim', $memo['id']) }}"
                                                        data-konfirmasi="Kirim memo {{ $memo['nomor_memo'] }} ke Keuangan? Setelah dikirim, memo tidak bisa diedit.">
                                                        Kirim ke Keuangan
                                                    </button>
                                                @endif
                                                @if (!empty($memo['bisa_ajukan']))
                                                    <button type="button" class="js-aksi {{ $btnAbu }}"
                                                        data-url="{{ route('memo.ajukan', $memo['id']) }}"
                                                        data-prompt="alasan" data-prompt-wajib="1"
                                                        data-prompt-label="Alasan pengajuan pengembalian (wajib diisi):">
                                                        Ajukan pengembalian
                                                    </button>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Aksi alur: Keuangan Sesdep --}}
                                        @can('kelola-anggaran')
                                            @php
                                                $adaAksiKeuangan = !empty($memo['pengajuan_id'])
                                                    || in_array($tahap, ['dikirim', 'lolos_spk', 'sakti', 'otp_ppk'], true);
                                            @endphp
                                            @if ($adaAksiKeuangan)
                                                <div class="flex flex-wrap gap-1.5">
                                                    @if (!empty($memo['pengajuan_id']))
                                                        <button type="button" class="js-aksi {{ $btnHijau }}"
                                                            data-url="{{ route('memo.pengajuan.putusan', $memo['pengajuan_id']) }}"
                                                            data-fields="{{ json_encode(['setuju' => 1]) }}"
                                                            data-konfirmasi="Setujui pengajuan? Memo akan dikembalikan ke unit kerja{{ in_array($tahap, ['sakti', 'otp_ppk'], true) ? ' dan SAKTI dilepas' : '' }}.">
                                                            Setujui pengajuan
                                                        </button>
                                                        <button type="button" class="js-aksi {{ $btnMerah }}"
                                                            data-url="{{ route('memo.pengajuan.putusan', $memo['pengajuan_id']) }}"
                                                            data-fields="{{ json_encode(['setuju' => 0]) }}"
                                                            data-prompt="catatan" data-prompt-wajib="1"
                                                            data-prompt-label="Catatan penolakan pengajuan (wajib diisi):">
                                                            Tolak pengajuan
                                                        </button>
                                                    @endif

                                                    @if ($tahap === 'dikirim' || $tahap === 'sakti')
                                                        @php $gerbang = $tahap === 'dikirim' ? 'K2' : 'K3'; @endphp
                                                        <button type="button" class="js-aksi {{ $btnHijau }}"
                                                            data-url="{{ route('memo.verifikasi', $memo['id']) }}"
                                                            data-fields="{{ json_encode(['gerbang' => $gerbang, 'lolos' => 1]) }}"
                                                            data-konfirmasi="Verifikasi {{ $gerbang }}: lengkap dan sesuai?">
                                                            {{ $gerbang }} lolos
                                                        </button>
                                                        <button type="button" class="js-aksi {{ $btnMerah }}"
                                                            data-url="{{ route('memo.verifikasi', $memo['id']) }}"
                                                            data-fields="{{ json_encode(['gerbang' => $gerbang, 'lolos' => 0]) }}"
                                                            data-prompt="alasan" data-prompt-wajib="1"
                                                            data-prompt-label="Alasan tidak lolos {{ $gerbang }} (wajib diisi). Memo akan dikembalikan ke unit kerja:">
                                                            {{ $gerbang }} tidak lolos
                                                        </button>
                                                    @endif

                                                    @if ($tahap === 'lolos_spk')
                                                        <button type="button" class="js-aksi {{ $btnBiru }}"
                                                            data-url="{{ route('memo.input-sakti', $memo['id']) }}"
                                                            data-konfirmasi="Catat memo {{ $memo['nomor_memo'] }} sudah diinput di SAKTI?">
                                                            Input SAKTI
                                                        </button>
                                                    @endif

                                                    @if ($tahap === 'otp_ppk')
                                                        <button type="button" class="js-aksi {{ $btnBiru }}"
                                                            data-url="{{ route('memo.selesai-otp', $memo['id']) }}"
                                                            data-konfirmasi="OTP PPK selesai? Setelah ini memo tidak bisa dikembalikan lagi.">
                                                            Selesai OTP
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif
                                        @endcan

                                        {{-- Ubah status manual (hanya yang berwenang, hanya pada tahap yang diizinkan) --}}
                                        @can('kelola-anggaran')
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
                                        @endcan
                                    </div>

                                    {{-- Kolom 3: Nominal --}}
                                    <div class="order-2 xl:order-none text-right min-w-0">
                                        <span
                                            class="text-xs font-semibold text-gray-700 tabular-nums whitespace-nowrap {{ $st === 'batal' ? 'line-through text-gray-400' : '' }}">
                                            Rp {{ number_format($memo['nominal'] ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    {{-- Kolom 4: Aksi (PDF / Edit) --}}
                                    <div class="order-4 col-span-2 xl:order-none xl:col-span-1 flex items-center gap-1.5 xl:flex-col xl:items-stretch">
                                        <a href="{{ $memo['pdf_url'] }}" target="_blank" rel="noopener"
                                            title="Buka PDF {{ $memo['status'] ?? '' }}"
                                            class="inline-flex flex-1 xl:flex-none items-center justify-center gap-1 px-2.5 py-2 xl:py-1 text-[11px] font-semibold text-blue-600 bg-white hover:bg-blue-50 border border-blue-500 rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            PDF
                                        </a>

                                        @if ($memo['editable'])
                                            <a href="{{ $memo['edit_url'] }}"
                                                class="inline-flex flex-1 xl:flex-none items-center justify-center px-3 py-2 xl:py-1 text-[11px] font-semibold text-gray-600 bg-white hover:bg-gray-100 border border-gray-300 rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                                                Edit
                                            </a>
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

            <div id="memo-no-result" class="hidden bg-white rounded-2xl border border-gray-200 px-4 py-14 text-center">
                <p class="text-sm font-semibold text-gray-800">Tidak ada nomor memo yang cocok</p>
                <p class="text-xs text-gray-500 mt-1">Ubah filter atau klik Reset untuk menampilkan semua.</p>
            </div>
        </div>
    </div>

    {{-- Form kosong yang dipakai semua tombol aksi alur (diisi lewat JS) --}}
    <form id="form-aksi" method="POST" class="hidden">
        @csrf
    </form>

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

            // Aksi alur (kirim, ajukan, verifikasi, input SAKTI, selesai OTP, putusan pengajuan).
            // data-url: tujuan POST. data-fields: JSON field tetap.
            // data-prompt: nama field yang diisi dari dialog; data-prompt-wajib="1" kalau wajib.
            const formAksi = document.getElementById('form-aksi');

            // Warna tombol OK di dialog mengikuti warna tombol pemicunya
            function varianTombol(btn) {
                if (btn.className.includes('bg-emerald-600')) return 'hijau';
                if (btn.className.includes('text-red-600')) return 'merah';
                return 'biru';
            }

            document.querySelectorAll('.js-aksi').forEach(function (btn) {
                btn.addEventListener('click', async function () {
                    const judul = btn.textContent.trim();
                    const varian = varianTombol(btn);
                    const fields = JSON.parse(btn.dataset.fields || '{}');

                    if (btn.dataset.konfirmasi) {
                        const ok = await window.appDialog.confirm({
                            judul: judul,
                            pesan: btn.dataset.konfirmasi,
                            varian: varian,
                            teksOk: judul,
                        });
                        if (!ok) return;
                    }

                    if (btn.dataset.prompt) {
                        const nilai = await window.appDialog.prompt({
                            judul: judul,
                            label: btn.dataset.promptLabel || 'Keterangan:',
                            wajib: btn.dataset.promptWajib === '1',
                            varian: varian,
                            teksOk: judul,
                        });
                        if (nilai === null) return;
                        fields[btn.dataset.prompt] = nilai;
                    }

                    formAksi.querySelectorAll('input[data-dinamis]').forEach(function (el) { el.remove(); });
                    Object.keys(fields).forEach(function (nama) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = nama;
                        input.value = fields[nama];
                        input.setAttribute('data-dinamis', '1');
                        formAksi.appendChild(input);
                    });

                    formAksi.action = btn.dataset.url;
                    document.querySelectorAll('.js-aksi').forEach(function (b) { b.disabled = true; });
                    formAksi.submit();
                });
            });
        });
    </script>

    {{-- Filter instan client-side --}}
    <script>
        (function () {
            const cariInput = document.getElementById('memo-filter-cari');
            const jenisSelect = document.getElementById('memo-filter-jenis');
            const statusSelect = document.getElementById('memo-filter-status');
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
                const pic = picSelect.value;
                const dari = dariInput.value; // YYYY-MM-DD, bisa dibandingkan sebagai string
                const sampai = sampaiInput.value;

                let visible = 0;

                items.forEach(function (el) {
                    const matchCari = cari === '' || el.dataset.search.includes(cari);
                    const matchJenis = jenis === 'semua' || el.dataset.jenis === jenis;
                    const matchStatus = status === '' || (el.dataset.status || '').split(' ').includes(status);
                    const matchPic = pic === '' || el.dataset.pic === pic;

                    const tgl = el.dataset.tanggal || '';
                    const matchDari = dari === '' || (tgl !== '' && tgl >= dari);
                    const matchSampai = sampai === '' || (tgl !== '' && tgl <= sampai);

                    const ok = matchCari && matchJenis && matchStatus && matchPic && matchDari && matchSampai;
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
            picSelect.addEventListener('change', applyFilter);
            dariInput.addEventListener('change', applyFilter);
            sampaiInput.addEventListener('change', applyFilter);

            resetBtn.addEventListener('click', function () {
                cariInput.value = '';
                jenisSelect.value = 'semua';
                statusSelect.value = '';
                picSelect.value = '';
                dariInput.value = '';
                sampaiInput.value = '';
                applyFilter();
            });
        })();
    </script>
</x-app-layout>