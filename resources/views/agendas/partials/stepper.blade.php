{{--
Stepper 3 tahap pembuatan agenda.
Simpan di: resources/views/agendas/partials/stepper.blade.php

Pakai:
Tahap 1 (create): @include('agendas.partials.stepper', ['step' => 1])
Tahap 1 (edit) : @include('agendas.partials.stepper', ['step' => 1, 'agenda' => $agenda])
Tahap 2 : @include('agendas.partials.stepper', ['step' => 2, 'agenda' => $agenda])
Tahap 3 : @include('agendas.partials.stepper', ['step' => 3, 'agenda' => $agenda])

Tahap yang sudah lewat otomatis hijau + bisa diklik (kalau $agenda ada).
--}}
@php
    $step = $step ?? 1;
    $agenda = $agenda ?? null;

    $steps = [
        1 => ['label' => 'Detail Agenda & Peserta', 'hint' => 'Tujuan, tanggal, peserta', 'route' => 'agendas.edit'],
        2 => ['label' => 'Rincian Biaya', 'hint' => 'Biaya tiap peserta', 'route' => 'agendas.peserta'],
        3 => ['label' => 'Anggaran & Memo', 'hint' => 'MAK dan nomor memo', 'route' => 'agendas.anggaran'],
    ];
    $total = count($steps);
    $persen = (int) round((($step - 1) / ($total - 1)) * 100);
@endphp

<nav aria-label="Langkah pengisian"
    class="relative overflow-hidden bg-white rounded-2xl border border-gray-100 shadow-sm px-4 sm:px-6 py-4 sm:py-5 mb-6">

    {{-- Progress tipis di tepi atas kartu --}}
    <div class="absolute inset-x-0 top-0 h-1 bg-gray-100" aria-hidden="true">
        <div class="h-full rounded-r-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all duration-500"
            style="width: {{ $persen === 0 ? 8 : $persen }}%"></div>
    </div>

    {{-- HP: keterangan singkat posisi sekarang --}}
    <p class="sm:hidden text-[11px] font-semibold uppercase tracking-wider text-blue-600 mb-3">
        Langkah {{ $step }} dari {{ $total }}
    </p>

    <ol class="flex items-center gap-3 sm:gap-4">
        @foreach ($steps as $n => $s)
            @php
                $done = $n < $step;
                $current = $n === $step;
                $href = $done && $agenda ? route($s['route'], $agenda) : null;

                if ($current) {
                    $dotClass = 'bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-600/30 ring-4 ring-blue-100';
                } elseif ($done) {
                    $dotClass = 'bg-emerald-500 text-white shadow-sm shadow-emerald-500/30';
                } else {
                    $dotClass = 'bg-white text-gray-400 border-2 border-gray-200';
                }

                $titleClass = $current ? 'text-gray-900 font-semibold' : ($done ? 'text-gray-700 font-medium' : 'text-gray-400 font-medium');
                $tag = $href ? 'a' : 'div';
            @endphp

            <li class="flex items-center gap-3 sm:gap-4 {{ $loop->last ? '' : 'flex-1' }}" @if ($current)
            aria-current="step" @endif>

                <{{ $tag }} @if ($href) href="{{ $href }}" title="Kembali ke {{ $s['label'] }}" @endif
                    class="group flex items-center gap-3 flex-shrink-0">
                    <span
                        class="h-10 w-10 rounded-full text-sm font-bold flex items-center justify-center flex-shrink-0 transition {{ $dotClass }} {{ $href ? 'group-hover:scale-105' : '' }}">
                        @if ($done)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" style="height:18px;width:18px"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        @else
                            {{ $n }}
                        @endif
                    </span>

                    {{-- HP: hanya label tahap aktif. Desktop: semua label + keterangan --}}
                    <span class="leading-tight {{ $current ? '' : 'hidden sm:block' }}">
                        <span
                            class="hidden sm:block text-[10px] uppercase tracking-wider {{ $current ? 'text-blue-600 font-semibold' : 'text-gray-400' }}">
                            Langkah {{ $n }}
                        </span>
                        <span
                            class="block text-sm {{ $titleClass }} {{ $href ? 'group-hover:text-blue-600 transition' : '' }}">
                            {{ $s['label'] }}
                        </span>
                        <span class="hidden lg:block text-xs text-gray-400 mt-0.5">{{ $s['hint'] }}</span>
                    </span>
                </{{ $tag }}>

                @unless ($loop->last)
                    <span class="flex-1 h-0.5 rounded-full min-w-[1.5rem] {{ $done ? 'bg-emerald-400' : 'bg-gray-200' }}"
                        aria-hidden="true"></span>
                @endunless
            </li>
        @endforeach
    </ol>
</nav>