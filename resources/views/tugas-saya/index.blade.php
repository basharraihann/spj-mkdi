<x-app-layout>
    @php
        $tabs = [
            'menunggu' => 'Belum diisi',
            'selesai' => 'Sudah dikirim',
            'ditutup' => 'Ditutup',
            'semua' => 'Semua',
        ];
    @endphp

    <div class="py-8 bg-slate-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Header Halaman --}}
            <div class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <div class="min-w-0 space-y-1">
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold tracking-tight text-slate-900">Tugas Saya</h2>
                    </div>
                    <p class="max-w-3xl text-xs sm:text-sm text-slate-500 leading-relaxed">
                        Daftar laporan hasil surat tugas yang harus Anda isi. Cukup satu peserta yang mengirim laporan;
                        setelah itu tugas peserta lain otomatis ditutup.
                    </p>
                </div>

                <div class="shrink-0 self-start sm:self-center">
                    @if (($jumlah['menunggu'] ?? 0) > 0)
                        <div class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50/80 px-3.5 py-1.5 text-xs font-semibold text-rose-700 shadow-sm">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                            </span>
                            {{ $jumlah['menunggu'] }} laporan perlu diisi
                        </div>
                    @else
                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50/80 px-3.5 py-1.5 text-xs font-semibold text-emerald-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Semua laporan selesai
                        </div>
                    @endif
                </div>
            </div>

            {{-- Filter Tab Status --}}
            <nav class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Filter status tugas">
                <div class="flex w-max gap-2 rounded-xl bg-slate-200/60 p-1 sm:w-auto sm:flex-wrap">
                    @foreach ($tabs as $key => $tabLabel)
                        @php $aktif = $status === $key; @endphp
                        <a href="{{ route('tugas-saya.index', ['status' => $key]) }}"
                            @if ($aktif) aria-current="page" @endif
                            class="inline-flex items-center gap-2 whitespace-nowrap rounded-lg px-4 py-2 text-xs sm:text-sm font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600
                                {{ $aktif ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                            {{ $tabLabel }}
                            <span class="rounded-md px-2 py-0.5 text-[11px] font-bold tabular-nums transition-colors {{ $aktif ? 'bg-slate-100 text-slate-900' : 'bg-slate-200/80 text-slate-600' }}">
                                {{ $jumlah[$key] }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </nav>

            {{-- Daftar Kartu Tugas --}}
            <div class="space-y-3.5">
                @forelse ($tugas as $t)
                    @php
                        $st = $t->suratTugas;
                        $laporan = $st->laporan;

                        // Status yang ditampilkan ke pengguna
                        if ($t->status === \App\Models\LaporanStTugas::STATUS_SELESAI) {
                            $statusLabel = 'Sudah dikirim';
                            $badge = 'border-emerald-200 bg-emerald-50 text-emerald-700';
                            $aksen = 'border-l-emerald-500';
                        } elseif ($t->status === \App\Models\LaporanStTugas::STATUS_DITUTUP) {
                            $statusLabel = 'Ditutup';
                            $badge = 'border-slate-200 bg-slate-100 text-slate-600';
                            $aksen = 'border-l-slate-400';
                        } elseif ($laporan && $laporan->status === 'draf') {
                            $statusLabel = 'Draf';
                            $badge = 'border-amber-200 bg-amber-50 text-amber-700';
                            $aksen = 'border-l-amber-400';
                        } else {
                            $statusLabel = 'Belum diisi';
                            $badge = 'border-rose-200 bg-rose-50 text-rose-700';
                            $aksen = 'border-l-rose-500';
                        }

                        // Judul singkat: tujuan, kalau kosong ambil dari "untuk"
                        $judul = $st->tujuan;
                        if (!$judul && is_array($st->untuk) && isset($st->untuk[0]) && is_string($st->untuk[0])) {
                            $judul = $st->untuk[0];
                        }
                    @endphp

                    <article
                        class="group flex flex-col gap-4 rounded-xl border border-slate-200/80 border-l-[5px] {{ $aksen }} bg-white p-4 shadow-sm transition-all duration-200 hover:shadow-md sm:flex-row sm:items-center sm:justify-between sm:p-5">
                        <div class="min-w-0 flex-1 space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('surat-tugas.show', $st) }}"
                                    class="break-all text-sm sm:text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors focus:outline-none focus-visible:underline">
                                    {{ $st->nomor_tampil }}
                                </a>
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold tracking-wide uppercase {{ $badge }}">
                                    {{ $statusLabel }}
                                </span>
                                @if ($st->tusi)
                                    <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50/80 px-2.5 py-0.5 text-[11px] font-semibold text-blue-700">
                                        {{ $st->tusi->nama }}
                                    </span>
                                @endif
                            </div>

                            @if ($judul)
                                <p class="line-clamp-2 text-xs sm:text-sm text-slate-600 font-normal leading-relaxed">
                                    {{ $judul }}
                                </p>
                            @endif

                            <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 pt-0.5 text-xs font-medium text-slate-500">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="h-4 w-4 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="4.5" width="18" height="16" rx="2" />
                                        <path d="M3 9.5h18M8 2.5v4M16 2.5v4" />
                                    </svg>
                                    {{ $st->rentang_tanggal ?? 'Tanggal belum diisi' }}
                                </span>
                                @if ($st->lokasi)
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11Z" />
                                            <circle cx="12" cy="10" r="2.5" />
                                        </svg>
                                        {{ $st->lokasi }}
                                    </span>
                                @endif
                            </div>

                            @if ($t->status === \App\Models\LaporanStTugas::STATUS_DITUTUP)
                                <div class="flex items-center gap-1.5 text-xs font-medium italic text-slate-500 pt-1">
                                    <svg class="h-3.5 w-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Laporan sudah dikirim oleh peserta lain.
                                </div>
                            @endif
                        </div>

                        <div class="shrink-0 pt-2 sm:pt-0">
                            @if ($t->status === \App\Models\LaporanStTugas::STATUS_MENUNGGU)
                                <a href="{{ route('surat-tugas.show', $st) }}"
                                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 sm:w-auto">
                                    <span>{{ $statusLabel === 'Draf' ? 'Lanjutkan' : 'Isi Laporan' }}</span>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            @else
                                <a href="{{ route('surat-tugas.show', $st) }}"
                                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 sm:w-auto">
                                    Lihat
                                </a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300/80 bg-white px-6 py-14 text-center shadow-sm">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100/80 text-slate-400">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="5" y="3.5" width="14" height="17" rx="2" />
                                <path d="M9 12.5l2 2 4-4.5M9 7h6" />
                            </svg>
                        </div>
                        <p class="mt-4 text-sm font-bold text-slate-800">
                            @if ($status === 'menunggu')
                                Tidak ada laporan yang perlu Anda isi saat ini.
                            @else
                                Belum ada tugas pada tab ini.
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">
                            Tugas baru akan muncul di sini saat Anda ditunjuk sebagai peserta surat tugas.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Paginasi --}}
            <div class="pt-2">
                {{ $tugas->links() }}
            </div>
        </div>
    </div>
</x-app-layout>