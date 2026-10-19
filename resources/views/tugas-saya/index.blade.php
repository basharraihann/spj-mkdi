<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tugas Saya') }}
        </h2>
    </x-slot>

    @php
        $tabs = [
            'menunggu' => 'Belum diisi',
            'selesai' => 'Sudah dikirim',
            'ditutup' => 'Ditutup',
            'semua' => 'Semua',
        ];
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <p class="text-sm text-gray-600 px-4 sm:px-0">
                Daftar laporan hasil surat tugas yang harus Anda isi. Cukup satu peserta yang mengirim laporan;
                setelah itu tugas peserta lain otomatis ditutup.
            </p>

            {{-- Tab status --}}
            <div class="flex flex-wrap gap-2 px-4 sm:px-0">
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('tugas-saya.index', ['status' => $key]) }}"
                        class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-medium border transition
                            {{ $status === $key ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                        {{ $label }}
                        <span
                            class="rounded-full px-2 text-xs {{ $status === $key ? 'bg-white/20' : 'bg-slate-100 text-slate-600' }}">
                            {{ $jumlah[$key] }}
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- Daftar tugas --}}
            <div class="bg-white shadow-sm sm:rounded-2xl divide-y divide-slate-100">
                @forelse ($tugas as $t)
                    @php
                        $st = $t->suratTugas;
                        $laporan = $st->laporan;

                        // Status yang ditampilkan ke pengguna
                        if ($t->status === \App\Models\LaporanStTugas::STATUS_SELESAI) {
                            $label = 'Sudah dikirim';
                            $badge = 'bg-green-100 text-green-700';
                        } elseif ($t->status === \App\Models\LaporanStTugas::STATUS_DITUTUP) {
                            $label = 'Ditutup';
                            $badge = 'bg-gray-100 text-gray-600';
                        } elseif ($laporan && $laporan->status === 'draf') {
                            $label = 'Draf';
                            $badge = 'bg-amber-100 text-amber-700';
                        } else {
                            $label = 'Belum diisi';
                            $badge = 'bg-red-100 text-red-700';
                        }

                        // Judul singkat: tujuan, kalau kosong ambil dari "untuk"
                        $judul = $st->tujuan;
                        if (!$judul && is_array($st->untuk) && isset($st->untuk[0]) && is_string($st->untuk[0])) {
                            $judul = $st->untuk[0];
                        }
                    @endphp

                    <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('surat-tugas.show', $st) }}"
                                    class="font-semibold text-slate-800 hover:text-blue-700">
                                    {{ $st->nomor_tampil }}
                                </a>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">{{ $label }}</span>
                                @if ($st->tusi)
                                    {{-- Kolom nama tusi diasumsikan "nama" --}}
                                    <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs text-blue-700">
                                        {{ $st->tusi->nama }}
                                    </span>
                                @endif
                            </div>

                            @if ($judul)
                                <p class="mt-1 text-sm text-slate-600 line-clamp-2">{{ $judul }}</p>
                            @endif

                            <p class="mt-1 text-xs text-slate-500">
                                {{ $st->rentang_tanggal ?? 'Tanggal belum diisi' }}
                                @if ($st->lokasi)
                                    &middot; {{ $st->lokasi }}
                                @endif
                            </p>

                            @if ($t->status === \App\Models\LaporanStTugas::STATUS_DITUTUP)
                                <p class="mt-1 text-xs text-slate-500">Laporan sudah dikirim oleh peserta lain.</p>
                            @endif
                        </div>

                        <div class="shrink-0">
                            @if ($t->status === \App\Models\LaporanStTugas::STATUS_MENUNGGU)
                                <a href="{{ route('surat-tugas.show', $st) }}"
                                    class="inline-flex items-center rounded-xl bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                                    {{ $label === 'Draf' ? 'Lanjutkan' : 'Isi Laporan' }}
                                </a>
                            @else
                                <a href="{{ route('surat-tugas.show', $st) }}"
                                    class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                                    Lihat
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center text-sm text-slate-500">
                        @if ($status === 'menunggu')
                            Tidak ada laporan yang perlu Anda isi saat ini.
                        @else
                            Belum ada tugas pada tab ini.
                        @endif
                    </div>
                @endforelse
            </div>

            <div class="px-4 sm:px-0">
                {{ $tugas->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
