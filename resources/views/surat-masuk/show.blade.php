<x-app-layout title="Detail Surat Masuk">
    @php
        $user = auth()->user();
        $isAdmin = (bool) $user?->isAdmin();
        $canManage = $user?->isTu()
            || $isAdmin
            || in_array($user->role ?? '', ['admin', 'tu', 'tu-d2', 'admin-d2'], true);

        $status = strtolower($suratMasuk->status ?? 'baru');
        $bisaUbah = $canManage && ($isAdmin || $status === 'baru');

        $statusLabel = ['baru' => 'Baru', 'proses' => 'Proses', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'];
        $statusClass = fn($s) => match (strtolower((string) $s)) {
            'proses' => 'bg-amber-50 text-amber-700 border-amber-200',
            'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'ditolak' => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-blue-50 text-blue-700 border-blue-200',
        };

        $fmt = fn($tgl, $format = 'd M Y') => $tgl
            ? \Illuminate\Support\Carbon::parse($tgl)->translatedFormat($format)
            : '-';

        $nomorSurat = filled($suratMasuk->nomor_surat) && $suratMasuk->nomor_surat !== '-'
            ? $suratMasuk->nomor_surat
            : null;
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <nav class="mb-1 flex flex-wrap items-center gap-x-2 text-xs text-gray-500" aria-label="Breadcrumb">
                    <a href="{{ route('surat-masuk.index') }}" class="transition hover:text-blue-600">Surat Masuk</a>
                    <span aria-hidden="true">/</span>
                    <span class="font-medium text-gray-800">Detail</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight break-all">
                    {{ $suratMasuk->nomor_agenda }}
                </h2>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('surat-masuk.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                    Kembali
                </a>

                @if ($bisaUbah)
                    <a href="{{ route('surat-masuk.edit', $suratMasuk->id) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                            <path d="M17.5 3.5a2.1 2.1 0 013 3L12 15l-4 1 1-4 8.5-8.5z" />
                        </svg>
                        Edit surat
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-5">

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

            {{-- ============ KIRI: INFORMASI SURAT ============ --}}
            <section class="min-w-0 rounded-2xl border border-gray-200 bg-white shadow-sm lg:col-span-1">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/60 px-5 py-4">
                    <h3 class="text-sm font-bold text-gray-800">Informasi surat</h3>
                    <span
                        class="inline-flex items-center whitespace-nowrap rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $statusClass($status) }}">
                        {{ $statusLabel[$status] ?? ucfirst($status) }}
                    </span>
                </div>

                <div class="space-y-5 p-5">
                    <div>
                        <p class="text-xs font-medium text-gray-400">Perihal</p>
                        <p class="mt-1 text-sm font-semibold leading-relaxed text-gray-900 break-words">
                            {{ $suratMasuk->perihal }}</p>
                    </div>

                    <dl class="grid grid-cols-2 gap-x-4 gap-y-4 border-t border-gray-100 pt-5 text-sm">
                        <div class="min-w-0">
                            <dt class="text-xs font-medium text-gray-400">Nomor surat</dt>
                            <dd class="mt-1 font-mono text-xs text-gray-700 break-all">
                                {{ $nomorSurat ?? 'Tidak ada nomor' }}</dd>
                        </div>
                        <div class="min-w-0">
                            <dt class="text-xs font-medium text-gray-400">Asal surat</dt>
                            <dd class="mt-1 font-medium text-gray-700 break-words">{{ $suratMasuk->asal_surat }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400">Tanggal surat</dt>
                            <dd class="mt-1 font-medium text-gray-700">{{ $fmt($suratMasuk->tanggal_surat) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400">Tanggal diterima</dt>
                            <dd class="mt-1 font-medium text-gray-700">{{ $fmt($suratMasuk->tanggal_diterima) }}</dd>
                        </div>
                    </dl>

                    <div class="border-t border-gray-100 pt-5 text-sm">
                        <p class="text-xs font-medium text-gray-400">Dicatat oleh</p>
                        <p class="mt-1 font-medium text-gray-700">
                            {{ $suratMasuk->createdBy?->name ?? 'Staf TU Persuratan' }}</p>
                    </div>

                    @if ($suratMasuk->drive_link)
                        <a href="{{ $suratMasuk->drive_link }}" target="_blank" rel="noopener"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-gray-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-800 focus-visible:ring-offset-2">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6v6M20 4L10 14" />
                            </svg>
                            Buka berkas asli
                        </a>
                    @endif
                </div>
            </section>

            {{-- ============ KANAN: PRATINJAU BERKAS ============ --}}
            <section class="flex min-w-0 flex-col rounded-2xl border border-gray-200 bg-white shadow-sm lg:col-span-2">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/60 px-5 py-4">
                    <h3 class="text-sm font-bold text-gray-800">Pratinjau berkas</h3>
                    <span class="text-xs text-gray-400">Google Drive</span>
                </div>

                <div class="flex-1 p-4">
                    <div
                        class="h-[420px] w-full overflow-hidden rounded-xl border border-gray-200 bg-gray-100 sm:h-[520px] lg:h-full lg:min-h-[520px]">
                        @if ($suratMasuk->embed_drive_url)
                            <iframe src="{{ $suratMasuk->embed_drive_url }}" title="Pratinjau berkas surat"
                                class="h-full w-full border-0" loading="lazy" allow="autoplay"></iframe>
                        @else
                            <div class="flex h-full flex-col items-center justify-center gap-2 p-6 text-center">
                                <svg class="h-10 w-10 text-gray-300" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true">
                                    <path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z" />
                                    <path d="M14 3v5h5M9 13h6M9 17h4" />
                                </svg>
                                <p class="text-sm font-semibold text-gray-700">Belum ada berkas digital</p>
                                <p class="text-xs text-gray-500">
                                    @if ($bisaUbah)
                                        Tambahkan tautan Google Drive lewat tombol Edit surat.
                                    @else
                                        Surat ini tidak memiliki tautan berkas.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        </div>

        {{-- ============ RIWAYAT DISPOSISI ============ --}}
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/60 px-5 py-4">
                <h3 class="text-sm font-bold text-gray-800">Riwayat disposisi</h3>
                <span class="text-xs text-gray-400">{{ $suratMasuk->disposisi->count() }} disposisi</span>
            </div>

            <div class="p-5 sm:p-6">
                @if ($suratMasuk->disposisi->isEmpty())
                    <div class="py-8 text-center">
                        <p class="text-sm font-semibold text-gray-800">Belum ada disposisi</p>
                        <p class="mt-1 text-xs text-gray-500">Disposisi akan muncul di sini setelah surat diteruskan.</p>
                    </div>
                @else
                    <ol class="relative space-y-5 border-l-2 border-blue-100 pl-6">
                        @foreach ($suratMasuk->disposisi as $dispo)
                            <li class="relative">
                                <span
                                    class="absolute -left-[33px] top-4 h-4 w-4 rounded-full border-4 border-white bg-blue-600 shadow-sm"
                                    aria-hidden="true"></span>

                                <div class="space-y-3 rounded-xl border border-gray-200 bg-gray-50/70 p-4">
                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                        <p
                                            class="flex flex-wrap items-center gap-x-2 text-sm font-semibold text-gray-900">
                                            <span class="text-blue-600">{{ $dispo->pengirim?->name ?? 'Pengirim' }}</span>
                                            <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" aria-hidden="true">
                                                <path d="M5 12h14M13 6l6 6-6 6" />
                                            </svg>
                                            <span>{{ $dispo->penerima?->name ?? 'Penerima' }}</span>
                                        </p>
                                        <time class="text-xs tabular-nums text-gray-400">
                                            {{ $fmt($dispo->created_at, 'd M Y, H:i') }} WIB
                                        </time>
                                    </div>

                                    @if (filled($dispo->instruksi))
                                        <p
                                            class="rounded-lg border border-gray-100 bg-white p-3 text-sm leading-relaxed text-gray-600 break-words">
                                            {{ $dispo->instruksi }}
                                        </p>
                                    @endif

                                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if (filled($dispo->sifat))
                                                <span>Sifat: <strong
                                                        class="font-semibold capitalize text-gray-700">{{ $dispo->sifat }}</strong></span>
                                            @endif
                                            @if ($dispo->perlu_st)
                                                <span
                                                    class="rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Perlu
                                                    ST</span>
                                            @endif
                                        </div>
                                        <span
                                            class="inline-flex items-center whitespace-nowrap rounded-full border px-2.5 py-1 text-[11px] font-semibold capitalize {{ $statusClass($dispo->status) }}">
                                            {{ $dispo->status }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>
    </div>
</x-app-layout>