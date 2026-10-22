@php
    $list = $byParent->get($parentId, collect());
@endphp

@if ($list->isNotEmpty())
    <ul class="space-y-3 pl-4 border-l-2 border-slate-200">
        @foreach ($list as $item)
            <li class="space-y-2">
                <div class="p-3.5 bg-slate-50/80 rounded-2xl border border-slate-200/80 text-xs shadow-sm">
                    <div class="flex items-center justify-between gap-2 font-semibold text-slate-800 mb-1.5">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-slate-700 font-medium">{{ $item->pengirim->name ?? 'Sistem' }}</span>
                            <svg class="h-3.5 w-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                            <span class="text-blue-600 font-semibold">{{ $item->penerima->name ?? '-' }}</span>
                        </div>

                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold tracking-wide uppercase shadow-2xs
                            {{ $item->sifat === 'segera' ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-slate-200/70 text-slate-700' }}">
                            {{ $item->sifat }}
                        </span>
                    </div>

                    <p class="text-slate-600 leading-relaxed whitespace-pre-line pl-0.5">{{ $item->instruksi }}</p>

                    @if ($item->perlu_st)
                        <div class="mt-2.5 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-[11px] font-semibold border border-indigo-200/80 shadow-2xs">
                            <svg class="h-3.5 w-3.5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span>Perlu Penerbitan Surat Tugas (ST)</span>
                        </div>
                    @endif
                </div>

                {{-- Rekursi untuk child disposisi --}}
                @include('disposisi.partials.tree-item', ['parentId' => $item->id, 'byParent' => $byParent])
            </li>
        @endforeach
    </ul>
@endif