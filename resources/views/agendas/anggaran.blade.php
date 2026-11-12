<x-app-layout title="Anggaran & Memo">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight break-words">
            Anggaran & Memo — {{ $agenda->uraian_kegiatan }}
        </h2>
    </x-slot>

    @php
        $rp = fn($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');

        // Status kosong dianggap PNS, sama seperti halaman show agenda.
        $pesertaStatus = $agenda->pegawai->map(fn($p) => $p->status_kepegawaian ?? 'PNS');
        $jmlPns = $pesertaStatus->filter(fn($s) => $s === 'PNS')->count();
        $jmlNonPns = $pesertaStatus->filter(fn($s) => $s === 'Non PNS')->count();
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Step indicator --}}
            <div class="flex items-center gap-3 px-1">
                <div class="flex items-center gap-2">
                    <span
                        class="h-6 w-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    <span class="hidden sm:inline text-sm font-medium text-gray-500">Detail Agenda & Peserta</span>
                </div>
                <div class="flex-1 h-px bg-gray-200"></div>
                <div class="flex items-center gap-2">
                    <span
                        class="h-6 w-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    <span class="hidden sm:inline text-sm font-medium text-gray-500">Rincian Biaya</span>
                </div>
                <div class="flex-1 h-px bg-gray-200"></div>
                <div class="flex items-center gap-2">
                    <span
                        class="h-6 w-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">3</span>
                    <span class="text-sm font-semibold text-gray-700">Anggaran & Memo</span>
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl px-4 sm:px-5 py-3 text-xs text-red-700">
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach (collect($errors->all())->unique() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('agendas.anggaran.store', $agenda) }}" method="POST" id="anggaran-form">
                @csrf

                {{-- Mobile: ringkasan tampil di atas (urutan DOM). Desktop: ringkasan jadi kolom kanan yang menempel. --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

                    {{-- ===== Ringkasan (kanan di desktop) ===== --}}
                    <aside class="lg:col-start-3 lg:row-start-1 lg:sticky lg:top-6 space-y-4">

                        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-5">
                            <h3 class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Agenda</h3>
                            <p class="mt-1.5 text-sm font-semibold text-gray-800 leading-snug break-words">
                                {{ $agenda->uraian_kegiatan }}</p>

                            <dl class="mt-4 space-y-2.5 text-xs">
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="text-gray-400 flex-shrink-0">Tujuan</dt>
                                    <dd class="text-gray-700 text-right font-medium">{{ $agenda->tujuan }}</dd>
                                </div>
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="text-gray-400 flex-shrink-0">Tanggal</dt>
                                    <dd class="text-gray-700 text-right font-medium">
                                        {{ $agenda->tanggal_mulai->translatedFormat('d M Y') }} –
                                        {{ $agenda->tanggal_selesai->translatedFormat('d M Y') }}
                                    </dd>
                                </div>
                                @if ($agenda->nomor_st)
                                    <div class="flex items-start justify-between gap-3">
                                        <dt class="text-gray-400 flex-shrink-0">No. ST</dt>
                                        <dd class="text-gray-700 text-right font-medium break-all">{{ $agenda->nomor_st }}
                                        </dd>
                                    </div>
                                @endif
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="text-gray-400 flex-shrink-0">Peserta</dt>
                                    <dd class="text-right">
                                        <span
                                            class="inline-flex items-center rounded-full bg-blue-50 text-blue-700 px-2 py-0.5 font-semibold">{{ $jmlPns }}
                                            PNS</span>
                                        @if ($jmlNonPns > 0)
                                            <span
                                                class="inline-flex items-center rounded-full bg-amber-50 text-amber-700 px-2 py-0.5 font-semibold ml-1">{{ $jmlNonPns }}
                                                Non PNS</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div class="bg-blue-600 text-white shadow-sm rounded-2xl p-5">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-blue-100">Total biaya
                                agenda</div>
                            <div class="mt-1 text-2xl font-bold tabular-nums">{{ $rp($totalBiaya) }}</div>

                            <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                                <div class="rounded-xl bg-white/10 px-3 py-2">
                                    <div class="text-blue-100">Biaya PNS</div>
                                    <div class="mt-0.5 font-semibold tabular-nums">{{ $rp($totalAsn) }}</div>
                                </div>
                                <div class="rounded-xl bg-white/10 px-3 py-2">
                                    <div class="text-blue-100">Biaya Non PNS</div>
                                    <div class="mt-0.5 font-semibold tabular-nums">{{ $rp($totalNonAsn) }}</div>
                                </div>
                            </div>

                            <a href="{{ route('agendas.peserta', $agenda) }}"
                                class="mt-4 inline-flex items-center gap-1 text-xs font-medium text-blue-100 hover:text-white transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                </svg>
                                Edit rincian biaya
                            </a>
                        </div>
                    </aside>

                    {{-- ===== Form (kiri di desktop) ===== --}}
                    <div class="lg:col-span-2 lg:col-start-1 lg:row-start-1 space-y-6 min-w-0">

                        @include('agendas.partials.administrasi-anggaran', ['agenda' => $agenda])

                        {{-- Action bar --}}
                        <div class="sticky bottom-4 z-20">
                            <div
                                class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white/90 backdrop-blur px-4 py-3 shadow-lg">
                                <a href="{{ route('agendas.show', $agenda) }}"
                                    class="text-sm text-gray-500 hover:text-gray-700 transition">Batal</a>
                                <button type="submit" id="btn-simpan-anggaran"
                                    class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-lg shadow-lg shadow-blue-600/25 transition disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Simpan Anggaran & Memo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>