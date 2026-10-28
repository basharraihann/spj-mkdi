@php
    $user = auth()->user();
    $activeTab = $tab ?? 'semua';
    $q = $q ?? '';

    // TU & admin melihat disposisi yang mereka kirim; yang lain melihat yang masuk ke mereka
    $lihatKirim = $user?->isTu() || $user?->isAdmin();

    $adaFilter = $activeTab !== 'semua' || $q !== '';

    // Level tampilan: eselon1 | eselon2 | pegawai (admin & TU memakai tampilan eselon2)
    $level = $user?->isEselon1() ? 'eselon1' : ($user?->isUser() ? 'pegawai' : 'eselon2');

    $kolomPerLevel = [
        'eselon1' => ['no', 'perihal', 'asal', 'penerima', 'instruksi', 'status', 'aksi'],
        'eselon2' => ['no', 'perihal', 'asal', 'instruksi', 'dari', 'penerima', 'status', 'tenggat', 'aksi'],
        'pegawai' => ['no', 'perihal', 'asal', 'instruksi', 'dari', 'status', 'tenggat', 'aksi'],
    ];
    $kolom = $kolomPerLevel[$level];

    // TU/admin melihat disposisi yang mereka kirim sendiri: "Dari" selalu nama sendiri,
    // dan disposisi awal dari TU tidak membawa instruksi, jadi kedua kolom itu dibuang
    if ($lihatKirim) {
        $kolom = array_values(array_diff($kolom, ['dari', 'instruksi']));
    }

    $judulKolom = [
        'no' => 'No',
        'perihal' => 'Perihal surat',
        'asal' => 'Asal surat',
        'instruksi' => 'Instruksi',
        'dari' => 'Dari',
        'penerima' => $lihatKirim ? 'Kepada' : 'Diteruskan ke',
        'status' => 'Status',
        'tenggat' => 'Tenggat',
        'aksi' => 'Aksi',
    ];
    // Lebar kolom (px). Perihal mengambil sisa ruang, minimal 240px.
    $lebarPx = [
        'no' => 36,
        'perihal' => 240,
        'asal' => 104,
        'instruksi' => 132,
        'dari' => 104,
        'penerima' => 112,
        'status' => 140,
        'tenggat' => 92,
        'aksi' => 64,
    ];
    $minLebar = array_sum(array_map(fn ($k) => $lebarPx[$k], $kolom));
    $kolomTengah = ['no', 'status', 'aksi'];


    // Bila tidak ada kolom Tenggat (eselon1), sinyal terlambat/hari ini ditaruh di bawah Status
    $tenggatDiStatus = !in_array('tenggat', $kolom, true);

    $opsiStatus = [
        'semua' => 'Semua status',
        'baru' => 'Baru',
        'dibaca' => 'Perlu tindakan',
        'diproses' => 'Diteruskan',
        'selesai' => 'Selesai',
    ];

    // [label, kelas badge, kelas titik]
    $chipStatus = [
        'baru' => ['Baru', 'bg-blue-50 text-blue-700 ring-blue-600/20', 'bg-blue-500'],
        'dibaca' => ['Perlu tindakan', 'bg-amber-50 text-amber-800 ring-amber-600/20', 'bg-amber-500'],
        'diproses' => ['Diteruskan', 'bg-indigo-50 text-indigo-700 ring-indigo-600/20', 'bg-indigo-500'],
        'selesai' => ['Selesai', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'bg-emerald-500'],
    ];

    $chipSifat = [
        'segera' => 'bg-red-50 text-red-700 ring-red-600/20',
        'rahasia' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
    ];

    // Label di atas nilai untuk tampilan kartu (di bawah 1100px, tabel diganti kartu)
    $cardLabel = "max-[1099px]:border-0 max-[1099px]:p-0 max-[1099px]:text-left max-[1099px]:before:mb-1 max-[1099px]:before:block max-[1099px]:before:content-[attr(data-label)] max-[1099px]:before:text-xs max-[1099px]:before:font-semibold max-[1099px]:before:text-gray-400";

    $subjudul = $lihatKirim
        ? 'Disposisi yang Anda kirim. Pantau perkembangan tindak lanjutnya.'
        : 'Semua lembar disposisi yang masuk ke Anda.';

    $kelasSelect = 'w-full rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-9 text-[15px] text-gray-700 focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition cursor-pointer';
@endphp

<x-app-layout title="Disposisi">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inbox Disposisi Digital</h2>
    </x-slot>

    <div class="py-6">
        <div id="dispo-root" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div id="dispo-card" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden transition-opacity">

                {{-- Judul --}}
                <div class="flex items-start justify-between gap-4 flex-wrap px-4 py-5 sm:px-6">
                    <div class="min-w-0">
                        <h3 class="text-xl font-bold text-gray-800">Disposisi</h3>
                        <p class="mt-0.5 text-[15px] text-gray-500">{{ $subjudul }}</p>
                    </div>
                    <span class="text-[13px] font-semibold text-gray-500 bg-gray-50 border border-gray-200 rounded-full px-3 py-1.5">
                        {{ $disposisi->total() }} disposisi
                    </span>
                </div>

                {{-- Toolbar: pencarian + status (otomatis, tanpa tombol Cari) --}}
                <form id="dispo-form" action="{{ route('disposisi.index') }}" method="GET"
                    class="flex flex-col gap-3 border-y border-gray-100 bg-gray-50/60 p-4 lg:flex-row lg:items-center">
                    <div class="relative flex-1">
                        <label for="dispo-filter-q" class="sr-only">Cari disposisi</label>
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-3.5-3.5" />
                        </svg>
                        <input type="text" id="dispo-filter-q" name="q" value="{{ $q }}" autocomplete="off"
                            placeholder="Cari perihal, agenda, atau asal surat"
                            class="w-full border border-gray-200 rounded-lg bg-white pl-9 pr-3 py-2 text-[15px] focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                    </div>

                    <div class="lg:w-52">
                        <label for="dispo-filter-tab" class="sr-only">Status</label>
                        <select id="dispo-filter-tab" name="tab" class="{{ $kelasSelect }}">
                            @foreach ($opsiStatus as $key => $label)
                                <option value="{{ $key }}" @selected($activeTab === $key)>
                                    {{ $label }} ({{ $jumlah[$key] ?? 0 }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if ($adaFilter)
                        <div class="flex items-center">
                            <a href="{{ route('disposisi.index') }}"
                                data-ajax
                                class="text-[13px] text-gray-500 hover:text-gray-700 transition">Reset</a>
                        </div>
                    @endif
                </form>

                {{-- Tabel di layar >= 1100px (scroll horizontal bila ruang kurang), kartu di bawahnya --}}
                <div class="overflow-x-auto max-[1099px]:overflow-visible">
                <table style="--min-w: {{ $minLebar }}px" class="w-full min-[1100px]:min-w-[var(--min-w)] table-fixed border-collapse text-[15px] max-[1099px]:block">
                    <colgroup class="max-[1099px]:hidden">
                        @foreach ($kolom as $k)
                            <col @if ($k !== 'perihal') style="width: {{ $lebarPx[$k] }}px" @endif>
                        @endforeach
                    </colgroup>
                    <thead class="max-[1099px]:hidden">
                        <tr class="border-y border-gray-300 bg-gray-100 text-xs text-gray-600">
                            @foreach ($kolom as $k)
                                <th class="{{ $loop->last ? '' : 'border-r border-gray-200' }} px-3 py-3 font-semibold {{ in_array($k, $kolomTengah, true) ? 'text-center' : 'text-left' }}">{{ $judulKolom[$k] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 max-[1099px]:block">
                        @forelse ($disposisi as $i => $dispo)
                            @php
                                $surat = $dispo->suratMasuk;
                                $isBaru = $dispo->status === 'baru';
                                $isSelesai = $dispo->status === 'selesai';

                                $tenggat = $dispo->tenggat_waktu;
                                $terlambat = $tenggat && !$isSelesai && $tenggat->copy()->startOfDay()->lt(today());
                                $hariIni = $tenggat && !$isSelesai && $tenggat->copy()->startOfDay()->eq(today());

                                $statusChip = $chipStatus[$dispo->status] ?? [ucfirst((string) $dispo->status), 'bg-slate-50 text-slate-600 ring-slate-500/20', 'bg-slate-400'];
                                $asalSurat = $surat->asal_surat ?? '—';
                                $pengirimNama = $dispo->pengirim->name ?? 'Staf TU';

                                // Instruksi yang diterima dari atasan (disposisi awal dari TU tidak punya induk, jadi kosong)
                                $instruksiDiterima = $dispo->parent_dispo_id ? trim((string) $dispo->instruksi) : '';

                                // Instruksi yang diberikan sendiri saat meneruskan (diambil dari disposisi anak)
                                $instruksiDiberikan = $dispo->children
                                    ->map(fn ($c) => trim((string) $c->instruksi))
                                    ->filter()
                                    ->unique()
                                    ->values();

                                // Pegawai: instruksi yang diterima.
                                // Eselon 1/2: instruksi yang diberikan sendiri; bila belum meneruskan, pakai yang diterima (jika ada).
                                if ($level === 'pegawai' || $instruksiDiberikan->isEmpty()) {
                                    $daftarInstruksi = collect($instruksiDiterima !== '' ? [$instruksiDiterima] : []);
                                } else {
                                    $daftarInstruksi = $instruksiDiberikan;
                                }
                                $instruksi = (string) ($daftarInstruksi->first() ?? '');
                                $instruksiSisa = max($daftarInstruksi->count() - 1, 0);

                                // Penerima: TU/admin = penerima baris ini; selain itu = siapa saja yang diteruskan dari disposisi ini
                                if ($lihatKirim) {
                                    $daftarPenerima = collect([$dispo->penerima->name ?? 'Penerima']);
                                } else {
                                    $daftarPenerima = $dispo->children
                                        ->map(fn ($c) => $c->penerima->name ?? null)
                                        ->filter()
                                        ->unique()
                                        ->values();
                                }
                                $penerimaTampil = $daftarPenerima->first();
                                $penerimaSisa = max($daftarPenerima->count() - 1, 0);
                            @endphp

                            <tr class="group bg-white align-middle transition hover:bg-gray-50
                                max-[1099px]:grid max-[1099px]:grid-cols-2 max-[1099px]:gap-x-4 max-[1099px]:gap-y-3 max-[1099px]:p-4 md:max-[1099px]:grid-cols-4
                                {{ $isBaru ? 'max-[1099px]:border-l-[3px] max-[1099px]:border-l-blue-600' : '' }}">

                                @foreach ($kolom as $k)
                                    @php $bd = $loop->last ? '' : 'border-r border-gray-200'; @endphp

                                    @switch($k)
                                        {{-- No (+ penanda belum dibaca di tepi kiri) --}}
                                        @case('no')
                                            <td class="relative {{ $bd }} px-2 py-3 text-center tabular-nums text-gray-500 max-[1099px]:hidden {{ $isBaru ? 'before:absolute before:inset-y-0 before:left-0 before:w-[3px] before:bg-blue-600' : '' }}">
                                                {{ $disposisi->firstItem() + $i }}
                                            </td>
                                            @break

                                        {{-- Perihal + agenda + sifat + waktu diterima/dikirim --}}
                                        @case('perihal')
                                            <td class="{{ $bd }} px-3 py-3 max-[1099px]:col-span-full max-[1099px]:border-0 max-[1099px]:p-0">
                                                <a href="{{ route('disposisi.show', $dispo->id) }}"
                                                    class="line-clamp-2 break-words hover:text-blue-700 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 {{ $isBaru ? 'font-bold text-gray-900' : 'font-semibold text-gray-800' }}">
                                                    {{ $surat->perihal ?? '-' }}
                                                </a>
                                                <div class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-gray-500">
                                                    <span class="font-mono">{{ $surat->nomor_agenda ?? '-' }}</span>
                                                    @if (isset($chipSifat[$dispo->sifat]))
                                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold capitalize ring-1 ring-inset {{ $chipSifat[$dispo->sifat] }}">{{ $dispo->sifat }}</span>
                                                    @endif
                                                    @if ($dispo->is_tembusan)
                                                        <span class="inline-flex items-center rounded-full bg-sky-50 px-2 py-0.5 text-[11px] font-semibold text-sky-700 ring-1 ring-inset ring-sky-600/20">Tembusan</span>
                                                    @endif
                                                    <span class="text-gray-300" aria-hidden="true">·</span>
                                                    <span title="{{ $dispo->created_at?->format('d M Y, H:i') }}">{{ $lihatKirim ? 'Dikirim' : 'Diterima' }} {{ $dispo->created_at?->diffForHumans() }}</span>
                                                </div>
                                            </td>
                                            @break

                                        {{-- Asal surat --}}
                                        @case('asal')
                                            <td data-label="Asal surat" class="min-w-0 {{ $bd }} px-3 py-3 text-[13px] text-gray-600 max-[1099px]:text-[15px] {{ $cardLabel }}">
                                                <div class="line-clamp-2 break-words" title="{{ $asalSurat }}">{{ $asalSurat }}</div>
                                            </td>
                                            @break

                                        {{-- Instruksi --}}
                                        @case('instruksi')
                                            <td data-label="Instruksi" class="min-w-0 {{ $bd }} px-3 py-3 text-[13px] text-gray-600 max-[1099px]:col-span-full max-[1099px]:text-[15px] {{ $cardLabel }}">
                                                @if ($instruksi !== '')
                                                    <div class="line-clamp-2 break-words" title="{{ $daftarInstruksi->implode(' | ') }}">
                                                        {{ $instruksi }}
                                                        @if ($instruksiSisa > 0)
                                                            <span class="ml-1 inline-flex items-center rounded-full bg-gray-100 px-1.5 py-0.5 text-[11px] font-semibold text-gray-600 ring-1 ring-inset ring-gray-500/10">+{{ $instruksiSisa }}</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-gray-300">—</span>
                                                @endif
                                            </td>
                                            @break

                                        {{-- Dari --}}
                                        @case('dari')
                                            <td data-label="Dari" class="min-w-0 {{ $bd }} px-3 py-3 text-[13px] font-medium text-gray-700 max-[1099px]:text-[15px] {{ $cardLabel }}">
                                                <div class="line-clamp-2 break-words" title="{{ $pengirimNama }}">{{ $pengirimNama }}</div>
                                            </td>
                                            @break

                                        {{-- Penerima dispo --}}
                                        @case('penerima')
                                            <td data-label="{{ $judulKolom['penerima'] }}" class="min-w-0 {{ $bd }} px-3 py-3 font-medium text-gray-700 {{ $cardLabel }}">
                                                @if ($penerimaTampil)
                                                    <div class="line-clamp-2 break-words" title="{{ $daftarPenerima->implode(', ') }}">
                                                        {{ $penerimaTampil }}
                                                        @if ($penerimaSisa > 0)
                                                            <span class="ml-1 inline-flex items-center rounded-full bg-gray-100 px-1.5 py-0.5 text-[11px] font-semibold text-gray-600 ring-1 ring-inset ring-gray-500/10">+{{ $penerimaSisa }}</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-[13px] font-normal text-gray-400">Belum diteruskan</span>
                                                @endif
                                            </td>
                                            @break

                                        {{-- Status (+ sinyal terlambat bila tidak ada kolom Tenggat) --}}
                                        @case('status')
                                            <td data-label="Status" class="{{ $bd }} px-2 py-3 text-center {{ $cardLabel }}">
                                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusChip[1] }}">
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $statusChip[2] }}" aria-hidden="true"></span>
                                                    {{ $statusChip[0] }}
                                                </span>
                                                @if ($tenggatDiStatus)
                                                    @if ($terlambat)
                                                        <span class="mx-auto mt-1 flex w-fit items-center rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-700 ring-1 ring-inset ring-red-600/20 max-[1099px]:mx-0">Terlambat</span>
                                                    @elseif ($hariIni)
                                                        <span class="mx-auto mt-1 flex w-fit items-center rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20 max-[1099px]:mx-0">Hari ini</span>
                                                    @endif
                                                @endif
                                            </td>
                                            @break

                                        {{-- Tenggat --}}
                                        @case('tenggat')
                                            <td data-label="Tenggat" class="{{ $bd }} px-3 py-3 text-[13px] leading-snug max-[1099px]:text-sm {{ $cardLabel }}">
                                                @if ($tenggat)
                                                    <span class="{{ $terlambat ? 'font-semibold text-red-600' : ($hariIni ? 'font-semibold text-amber-600' : 'text-gray-600') }}">
                                                        {{ $tenggat->translatedFormat('d M Y') }}
                                                    </span>
                                                    @if ($terlambat)
                                                        <span class="mt-0.5 flex w-fit items-center rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-700 ring-1 ring-inset ring-red-600/20">Terlambat</span>
                                                    @elseif ($hariIni)
                                                        <span class="mt-0.5 flex w-fit items-center rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">Hari ini</span>
                                                    @endif
                                                @else
                                                    <span class="text-gray-300">—</span>
                                                @endif
                                            </td>
                                            @break

                                        {{-- Aksi --}}
                                        @case('aksi')
                                            <td class="px-2 py-3 max-[1099px]:col-span-full max-[1099px]:p-0 md:max-[1099px]:text-right">
                                                <a href="{{ route('disposisi.show', $dispo->id) }}"
                                                    class="inline-flex w-full items-center justify-center rounded-md border border-blue-500 bg-white px-2 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 max-[1099px]:py-2 md:max-[1099px]:w-auto md:max-[1099px]:px-6">
                                                    Buka
                                                </a>
                                            </td>
                                            @break
                                    @endswitch
                                @endforeach
                            </tr>
                        @empty
                            <tr class="max-[1099px]:block">
                                <td colspan="{{ count($kolom) }}" class="px-5 py-14 text-center text-gray-500 max-[1099px]:block">
                                    <p class="text-[15px] font-semibold text-gray-800">
                                        @if ($adaFilter)
                                            Tidak ada disposisi yang cocok
                                        @elseif ($lihatKirim)
                                            Belum ada disposisi yang Anda kirim
                                        @else
                                            Belum ada disposisi masuk
                                        @endif
                                    </p>
                                    @if ($adaFilter)
                                        <a href="{{ route('disposisi.index') }}"
                                            data-ajax
                                            class="mt-2 inline-block text-[13px] font-semibold text-blue-600 hover:text-blue-700">Tampilkan semua</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>

                {{-- Pagination --}}
                @if ($disposisi->total() > 0)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-t border-gray-200 px-4 py-3 sm:px-6">
                        <p class="text-[13px] text-gray-500">
                            Menampilkan <span class="font-semibold text-gray-700">{{ $disposisi->firstItem() }}–{{ $disposisi->lastItem() }}</span>
                            dari <span class="font-semibold text-gray-700">{{ $disposisi->total() }}</span> disposisi
                        </p>
                        @if ($disposisi->hasPages())
                            <div>{{ $disposisi->links() }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
    (function () {
        const root = document.getElementById('dispo-root');
        if (!root) return;

        let timer = null;
        let controller = null;

        async function muat(url, push = true) {
            controller?.abort();
            controller = new AbortController();

            const card = document.getElementById('dispo-card');
            card.classList.add('opacity-60');

            try {
                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal,
                });
                if (!res.ok) throw new Error('gagal');

                const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
                const baru = doc.getElementById('dispo-card');
                if (!baru) throw new Error('kartu tidak ditemukan');

                // Simpan fokus & isi input supaya ketikan tidak hilang
                const aktif = document.activeElement;
                const fokusQ = aktif && aktif.id === 'dispo-filter-q';
                const nilaiQ = fokusQ ? aktif.value : null;
                const pos = fokusQ ? aktif.selectionStart : null;

                card.replaceWith(baru);

                if (fokusQ) {
                    const inp = document.getElementById('dispo-filter-q');
                    inp.value = nilaiQ;
                    inp.focus();
                    inp.setSelectionRange(pos, pos);
                }

                if (push) history.pushState(null, '', url);
            } catch (e) {
                if (e.name === 'AbortError') return;
                window.location.href = url; // fallback: reload biasa
            }
        }

        function urlDariForm() {
            const form = document.getElementById('dispo-form');
            const p = new URLSearchParams(new FormData(form));
            if (!p.get('q')) p.delete('q');
            if (p.get('tab') === 'semua') p.delete('tab');
            const qs = p.toString();
            return form.action + (qs ? '?' + qs : '');
        }

        // Ketik di pencarian (debounce 400ms)
        root.addEventListener('input', (e) => {
            if (e.target.id !== 'dispo-filter-q') return;
            clearTimeout(timer);
            timer = setTimeout(() => muat(urlDariForm()), 400);
        });

        // Ganti select
        root.addEventListener('change', (e) => {
            if (e.target.matches('#dispo-filter-tab')) {
                clearTimeout(timer);
                muat(urlDariForm());
            }
        });

        // Enter di input pencarian
        root.addEventListener('submit', (e) => {
            if (e.target.id !== 'dispo-form') return;
            e.preventDefault();
            clearTimeout(timer);
            muat(urlDariForm());
        });

        // Pagination & link reset
        root.addEventListener('click', (e) => {
            const a = e.target.closest('a[data-ajax], nav[role="navigation"] a[href]');
            if (!a || e.metaKey || e.ctrlKey || e.shiftKey) return;
            e.preventDefault();
            muat(a.href);
        });

        // Tombol back/forward
        window.addEventListener('popstate', () => muat(location.href, false));
    })();
    </script>
</x-app-layout>