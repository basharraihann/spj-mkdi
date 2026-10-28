<x-app-layout title="Surat Masuk">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitoring Surat Masuk') }}
        </h2>
    </x-slot>

    @php
        $user = auth()->user();
        $isAdmin = (bool) $user?->isAdmin();
        $canManage = $user?->isTu()
            || $isAdmin
            || in_array($user->role ?? '', ['admin', 'tu', 'tu-d2', 'admin-d2'], true);

        $statusCounts = collect($statusCounts ?? []);
        $totalSemua = $statusCounts->sum();
        $statusAktif = request('status');
        $keyword = request('search');
        $adaFilter = filled($statusAktif) || filled($keyword);

        $statusLabel = ['baru' => 'Baru', 'proses' => 'Proses', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'];

        $statusClass = fn($s) => match ($s) {
            'proses' => 'bg-amber-50 text-amber-700 border-amber-200',
            'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'ditolak' => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-blue-50 text-blue-700 border-blue-200',
        };

        $chipUrl = fn($status) => route('surat-masuk.index', array_filter([
            'search' => $keyword,
            'status' => $status,
        ]));
    @endphp

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-5">


        {{-- ============ BAGIAN ATAS: judul + nomor agenda berikutnya ============ --}}
        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-4 sm:p-6 space-y-5">

            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="min-w-0">
                    <h3 class="text-lg font-bold text-gray-800">Surat Masuk</h3>
                    <p class="text-sm text-gray-500">Diurutkan dari yang terbaru.</p>
                </div>
                <span
                    class="text-xs font-semibold text-gray-500 bg-gray-50 border border-gray-100 rounded-full px-3 py-1.5">
                    {{ $suratMasuk->total() }} dari {{ $totalSemua }} surat masuk
                </span>
            </div>

            @if ($canManage)
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl bg-blue-50 border border-blue-100 px-4 py-3">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-blue-700">Nomor agenda berikutnya</p>
                        <p class="text-xl font-bold text-gray-900 tabular-nums break-all">
                            {{ $nomorAgendaBerikutnya ?? '-' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">Nomor final ditetapkan saat surat masuk disimpan.</p>
                    </div>

                    <a href="{{ route('surat-masuk.create') }}"
                        class="inline-flex w-full sm:w-auto shrink-0 items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm shadow-md shadow-blue-600/20 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Input Surat Masuk
                    </a>
                </div>
            @endif
        </div>

        {{-- ============ DAFTAR: satu kartu, toolbar di atas, baris di bawah ============ --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

            {{-- Toolbar: pencarian + chip status --}}
            <div
                class="flex flex-col gap-3 border-b border-gray-100 bg-gray-50/60 p-4 lg:flex-row lg:items-center lg:justify-between">
                <form method="GET" action="{{ route('surat-masuk.index') }}" class="flex w-full gap-2 lg:max-w-md">
                    @if (filled($statusAktif))
                        <input type="hidden" name="status" value="{{ $statusAktif }}">
                    @endif
                    <div class="relative flex-1">
                        <label for="surat-cari" class="sr-only">Cari surat</label>
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-3.5-3.5" />
                        </svg>
                        <input type="text" id="surat-cari" name="search" value="{{ $keyword }}" autocomplete="off"
                            placeholder="Cari perihal, asal, nomor surat, atau agenda"
                            class="w-full border border-gray-200 rounded-lg bg-white pl-9 pr-3 py-2 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition">
                    </div>
                    <button type="submit"
                        class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-900 cursor-pointer">
                        Cari
                    </button>
                </form>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ $chipUrl(null) }}"
                        class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ blank($statusAktif) ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                        Semua <span class="tabular-nums opacity-70">{{ $totalSemua }}</span>
                    </a>

                    @foreach ($statusCounts as $key => $jumlah)
                        <a href="{{ $chipUrl($key) }}"
                            class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $statusAktif === $key ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                            {{ $statusLabel[$key] ?? ucfirst($key) }} <span
                                class="tabular-nums opacity-70">{{ $jumlah }}</span>
                        </a>
                    @endforeach

                    @if ($adaFilter)
                        <a href="{{ route('surat-masuk.index') }}"
                            class="px-1 py-1.5 text-xs text-gray-500 hover:text-gray-700 transition">Reset</a>
                    @endif
                </div>
            </div>

            {{-- Judul kolom --}}
            <div
                class="hidden lg:grid grid-cols-[9.5rem_minmax(0,1fr)_12rem_6rem_9rem] gap-4 border-b border-gray-100 px-5 py-2.5 text-[11px] font-semibold text-gray-400">
                <div>Agenda</div>
                <div>Surat</div>
                <div>Posisi saat ini</div>
                <div>Status</div>
                <div class="text-right">Aksi</div>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse ($suratMasuk as $surat)
                    @php
                        $status = strtolower($surat->status ?? 'baru');
                        $posisi = $surat->disposisi->sortByDesc('id')->first()?->penerima?->name;
                        $tglTerima = $surat->tanggal_diterima ?? $surat->created_at;
                        $tglTerima = $tglTerima ? \Illuminate\Support\Carbon::parse($tglTerima)->translatedFormat('d M Y') : '-';
                        $tglSurat = $surat->tanggal_surat
                            ? \Illuminate\Support\Carbon::parse($surat->tanggal_surat)->translatedFormat('d M Y')
                            : null;
                        $nomorSurat = filled($surat->nomor_surat) && $surat->nomor_surat !== '-' ? $surat->nomor_surat : null;
                        $bisaUbah = $canManage && ($isAdmin || $status === 'baru');
                    @endphp

                    <div
                        class="grid gap-x-4 gap-y-3 px-4 py-4 sm:px-5 lg:grid-cols-[9.5rem_minmax(0,1fr)_12rem_6rem_9rem] lg:items-start transition hover:bg-gray-50/70">

                        {{-- Agenda --}}
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 tabular-nums break-all">{{ $surat->nomor_agenda }}
                            </p>
                            <p class="mt-0.5 text-xs text-gray-500">Diterima {{ $tglTerima }}</p>
                        </div>

                        {{-- Surat --}}
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800 leading-snug break-words">{{ $surat->perihal }}
                            </p>
                            <p class="mt-1 text-xs text-gray-600">
                                Dari <span class="font-medium text-gray-800">{{ $surat->asal_surat }}</span>
                            </p>
                            @if ($nomorSurat || $tglSurat)
                                <p class="mt-0.5 text-xs text-gray-500 break-all">
                                    @if ($nomorSurat) No. <span
                                    class="font-mono">{{ $nomorSurat }}</span>@endif{{ $nomorSurat && $tglSurat ? ',' : '' }}
                                    @if ($tglSurat) tgl {{ $tglSurat }} @endif
                                </p>
                            @endif
                            @if ($surat->drive_link)
                                <a href="{{ $surat->drive_link }}" target="_blank" rel="noopener"
                                    class="mt-1 inline-block text-xs font-medium text-blue-600 hover:underline">
                                    Buka berkas
                                </a>
                            @endif
                        </div>

                        {{-- Posisi --}}
                        <div class="min-w-0 text-xs">
                            <span class="lg:hidden text-gray-400">Posisi: </span>
                            @if ($posisi)
                                <span class="font-semibold text-gray-800 break-words">{{ $posisi }}</span>
                            @else
                                <span class="font-medium text-amber-600">Belum didisposisi</span>
                            @endif
                        </div>

                        {{-- Status --}}
                        <div>
                            <span
                                class="inline-flex items-center whitespace-nowrap px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $statusClass($status) }}">
                                {{ $statusLabel[$status] ?? ucfirst($status) }}
                            </span>
                        </div>

                        {{-- Aksi --}}
                        <div class="flex items-center gap-1.5 lg:justify-end">
                            <a href="{{ route('surat-masuk.show', $surat->id) }}"
                                class="inline-flex items-center justify-center px-3 py-1.5 text-[11px] font-semibold text-blue-600 bg-white hover:bg-blue-50 border border-blue-500 rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                                Detail
                            </a>

                            @if ($bisaUbah)
                                <a href="{{ route('surat-masuk.edit', $surat->id) }}"
                                    class="inline-flex items-center justify-center px-3 py-1.5 text-[11px] font-semibold text-gray-600 bg-white hover:bg-gray-100 border border-gray-300 rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                                    Edit
                                </a>

                                <form action="{{ route('surat-masuk.destroy', $surat->id) }}" method="POST"
                                    class="delete-surat-form inline-flex m-0"
                                    data-label="surat masuk ini beserta seluruh rantai disposisinya">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus surat masuk" aria-label="Hapus surat masuk"
                                        class="inline-flex h-7 w-7 items-center justify-center text-red-500 bg-white hover:bg-red-50 border border-red-300 rounded-md transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-14 text-center">
                        @if ($adaFilter)
                            <p class="text-sm font-semibold text-gray-800">Tidak ada surat masuk yang cocok</p>
                            <a href="{{ route('surat-masuk.index') }}"
                                class="mt-2 inline-block text-xs font-semibold text-blue-600 hover:text-blue-700">
                                Tampilkan semua
                            </a>
                        @else
                            <p class="text-sm font-semibold text-gray-800">Belum ada surat masuk</p>
                            @if ($canManage)
                                <p class="text-xs text-gray-500 mt-1">Klik "Input Surat Masuk" untuk mencatat yang pertama.</p>
                            @endif
                        @endif
                    </div>
                @endforelse
            </div>

            @if ($suratMasuk->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">
                    {{ $suratMasuk->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Konfirmasi hapus lewat confirmDelete() bila ada di layout, jika tidak pakai confirm bawaan browser
            document.querySelectorAll('.delete-surat-form').forEach(function (form, index) {
                if (!form.id) {
                    form.id = 'delete-surat-form-' + index;
                }

                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const label = form.dataset.label || 'surat masuk ini';

                    if (typeof confirmDelete === 'function') {
                        confirmDelete(form.id, label);
                    } else if (confirm('Yakin ingin menghapus ' + label + '?')) {
                        form.submit();
                    }
                });
            });
        });
    </script>
</x-app-layout>