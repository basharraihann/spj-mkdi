@php
    $list = $byParent->get($parentId, collect());

    $warnaStatus = [
        'baru' => 'bg-blue-50 text-blue-600 border border-blue-200',
        'dibaca' => 'bg-amber-50 text-amber-700 border border-amber-200',
        'diproses' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
        'selesai' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    ];
@endphp

@if ($list->isNotEmpty())
    <ul class="space-y-3 pl-4 border-l-2 border-slate-200">
        @foreach ($list as $item)
            <li class="space-y-2">
                <div class="p-3.5 bg-slate-50/80 rounded-2xl border border-slate-200/80 text-xs shadow-sm">
                    <div class="flex items-center justify-between gap-2 font-semibold text-slate-800 mb-1.5">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-slate-700 font-medium">{{ $item->pengirim->name ?? 'Sistem' }}</span>
                            <svg class="h-3.5 w-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                            <span class="text-blue-600 font-semibold">{{ $item->penerima->name ?? '-' }}</span>

                            @if ($item->is_tembusan)
                                <span
                                    class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase bg-violet-50 text-violet-700 border border-violet-200">Tembusan</span>
                            @endif
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold tracking-wide uppercase {{ $warnaStatus[$item->status] ?? 'bg-slate-100 text-slate-600' }}">
                                {{ $item->status }}
                            </span>
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold tracking-wide uppercase shadow-2xs
                                        {{ $item->sifat === 'segera' ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-slate-200/70 text-slate-700' }}">
                                {{ $item->sifat }}
                            </span>
                        </div>
                    </div>

                    @php
                        $petunjukItem = $item->petunjuk ?? [];
                    @endphp

                    @if (!empty($petunjukItem))
                        <div class="flex flex-wrap gap-1.5 mb-2">
                            @foreach ($petunjukItem as $p)
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-medium">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    {{ $p }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @if ($item->instruksi && $item->instruksi !== implode(', ', $petunjukItem))
                        <p class="text-slate-600 leading-relaxed whitespace-pre-line pl-0.5">{{ $item->instruksi }}</p>
                    @endif

                    @if ($item->tenggat_waktu)
                        <p class="mt-1.5 text-[11px] text-slate-400">Tenggat: {{ $item->tenggat_waktu->format('d M Y') }}</p>
                    @endif
                </div>

                {{-- Rekursi untuk child disposisi --}}
                @include('disposisi.partials.tree-item', ['parentId' => $item->id, 'byParent' => $byParent])
            </li>
        @endforeach
    </ul>
@endif