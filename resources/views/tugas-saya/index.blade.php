<x-app-layout>
    @php
        $tabs = [
            'menunggu' => 'Belum diisi',
            'selesai' => 'Sudah dikirim',
            'ditutup' => 'Ditutup',
            'semua' => 'Semua',
        ];
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            {{-- Kepala halaman --}}
            <div
                class="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="min-w-0">
                    <h2 class="text-xl font-semibold text-gray-900">Tugas Saya</h2>
                    <p class="mt-0.5 max-w-2xl text-xs text-gray-500">
                        Daftar laporan hasil surat tugas yang harus Anda isi. Cukup satu peserta yang mengirim laporan;
                        setelah itu tugas peserta lain otomatis ditutup.
                    </p>
                </div>

                @if (($jumlah['menunggu'] ?? 0) > 0)
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700">
                        <span class="h-2 w-2 rounded-full bg-red-500"></span>
                        {{ $jumlah['menunggu'] }} laporan perlu diisi
                    </div>
                @else
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700">
                        <span class="h-2 w-2 rounded-full bg-green-500"></span>
                        Semua laporan sudah beres
                    </div>
                @endif
            </div>

            {{-- Tab status: bisa digeser horizontal di layar kecil --}}
            <nav class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Filter status tugas">
                <div class="flex w-max gap-2 sm:w-auto sm:flex-wrap">
                    @foreach ($tabs as $key => $tabLabel)
                        @php $aktif = $status === $key; @endphp
                        <a href="{{ route('tugas-saya.index', ['status' => $key]) }}"
                            @if ($aktif) aria-current="page" @endif
                            class="inline-flex items-center gap-2 whitespace-nowrap rounded-full border px-4 py-1.5 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2
                                {{ $aktif ? 'border-slate-800 bg-slate-800 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                            {{ $tabLabel }}
                            <span
                                class="rounded-full px-2 py-0.5 text-xs tabular-nums {{ $aktif ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                                {{ $jumlah[$key] }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </nav>

            {{-- Daftar tugas --}}
            <div class="space-y-3">
                @forelse ($tugas as $t)
                    @php
                        $st = $t->suratTugas;
                        $laporan = $st->laporan;

                        // Status yang ditampilkan ke pengguna
                        if ($t->status === \App\Models\LaporanStTugas::STATUS_SELESAI) {
                            $statusLabel = 'Sudah dikirim';
                            $badge = 'border-green-200 bg-green-50 text-green-700';
                            $aksen = 'border-l-green-500';
                        } elseif ($t->status === \App\Models\LaporanStTugas::STATUS_DITUTUP) {
                            $statusLabel = 'Ditutup';
                            $badge = 'border-slate-200 bg-slate-100 text-slate-600';
                            $aksen = 'border-l-slate-300';
                        } elseif ($laporan && $laporan->status === 'draf') {
                            $statusLabel = 'Draf';
                            $badge = 'border-amber-200 bg-amber-50 text-amber-700';
                            $aksen = 'border-l-amber-400';
                        } else {
                            $statusLabel = 'Belum diisi';
                            $badge = 'border-red-200 bg-red-50 text-red-700';
                            $aksen = 'border-l-red-500';
                        }

                        // Judul singkat: tujuan, kalau kosong ambil dari "untuk"
                        $judul = $st->tujuan;
                        if (!$judul && is_array($st->untuk) && isset($st->untuk[0]) && is_string($st->untuk[0])) {
                            $judul = $st->untuk[0];
                        }
                    @endphp

                    <article
                        class="flex flex-col gap-4 rounded-xl border border-l-4 border-slate-200 {{ $aksen }} bg-white p-4 shadow-sm transition hover:shadow-md sm:flex-row sm:items-center sm:justify-between sm:p-5">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('surat-tugas.show', $st) }}"
                                    class="break-all text-sm font-semibold text-slate-900 hover:text-blue-700 hover:underline focus:outline-none focus-visible:underline sm:text-base">
                                    {{ $st->nomor_tampil }}
                                </a>
                                <span
                                    class="rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $badge }}">{{ $statusLabel }}</span>
                                @if ($st->tusi)
                                    {{-- Kolom nama tusi diasumsikan "nama" --}}
                                    <span
                                        class="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                        {{ $st->tusi->nama }}
                                    </span>
                                @endif
                            </div>

                            @if ($judul)
                                <p class="mt-1.5 line-clamp-2 text-sm text-slate-600">{{ $judul }}</p>
                            @endif

                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
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
                                <p class="mt-2 text-xs italic text-slate-500">Laporan sudah dikirim oleh peserta lain.
                                </p>
                            @endif
                        </div>

                        <div class="shrink-0">
                            @if ($t->status === \App\Models\LaporanStTugas::STATUS_MENUNGGU)
                                <a href="{{ route('surat-tugas.show', $st) }}"
                                    class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 sm:w-auto">
                                    {{ $statusLabel === 'Draf' ? 'Lanjutkan' : 'Isi Laporan' }}
                                </a>
                            @else
                                <a href="{{ route('surat-tugas.show', $st) }}"
                                    class="inline-flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 sm:w-auto">
                                    Lihat
                                </a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div
                        class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center shadow-sm">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                            <svg class="h-6 w-6 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <rect x="5" y="3.5" width="14" height="17" rx="2" />
                                <path d="M9 12.5l2 2 4-4.5M9 7h6" />
                            </svg>
                        </div>
                        <p class="mt-3 text-sm font-medium text-slate-700">
                            @if ($status === 'menunggu')
                                Tidak ada laporan yang perlu Anda isi saat ini.
                            @else
                                Belum ada tugas pada tab ini.
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-slate-500">Tugas baru akan muncul di sini saat Anda ditunjuk sebagai
                            peserta surat tugas.</p>
                    </div>
                @endforelse
            </div>

            <div>
                {{ $tugas->links() }}
            </div>
        </div>
    </div>
</x-app-layout>