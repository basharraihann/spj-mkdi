<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    {{ __('Inbox Disposisi Digital') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar lembar disposisi masuk yang memerlukan tindak lanjut Anda.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

        {{-- ===== 1. Bilah Filter Tab Status & Form Pencarian ===== --}}
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            
            {{-- Navigation Tabs Filter --}}
            <div class="flex items-center gap-1 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
                @php
                    $activeTab = $tab ?? 'semua';
                    $tabs = [
                        'semua' => ['label' => 'Semua', 'count' => $jumlah['semua'] ?? 0],
                        'baru' => ['label' => 'Baru', 'count' => $jumlah['baru'] ?? 0],
                        'dibaca' => ['label' => 'Dibaca', 'count' => $jumlah['dibaca'] ?? 0],
                        'diproses' => ['label' => 'Diproses', 'count' => $jumlah['diproses'] ?? 0],
                    ];
                @endphp

                @foreach ($tabs as $key => $t)
                    <a href="{{ route('disposisi.index', array_merge(request()->query(), ['tab' => $key])) }}"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ $activeTab === $key ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <span>{{ $t['label'] }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $activeTab === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                            {{ $t['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- Form Search Query --}}
            <form action="{{ route('disposisi.index') }}" method="GET" class="relative min-w-[240px] sm:min-w-[280px]">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="relative">
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari perihal, agenda, atau asal surat..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50 border-slate-200 rounded-xl text-xs focus:border-blue-500 focus:ring-blue-500 focus:bg-white transition">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                </div>
            </form>
        </div>

        {{-- ===== 2. Container List Disposisi ===== --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="divide-y divide-slate-100">
                @forelse($disposisi ?? [] as $dispo)
                    @php
                        $isBaru = $dispo->status === 'baru';
                    @endphp

                    <a href="{{ route('disposisi.show', $dispo->id) }}" 
                        class="p-4 sm:p-5 block hover:bg-slate-50/80 transition relative group border-l-4 {{ $isBaru ? 'border-l-blue-600 bg-blue-50/20' : 'border-l-transparent' }}">
                        
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 sm:gap-4">
                            
                            {{-- Info Disposisi Left Side --}}
                            <div class="flex items-start gap-3.5">
                                {{-- Avatar Pengirim --}}
                                <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 text-slate-600 font-bold text-xs uppercase shadow-2xs group-hover:bg-blue-50 group-hover:text-blue-600 group-hover:border-blue-200 transition">
                                    {{ substr($dispo->pengirim->name ?? 'TU', 0, 2) }}
                                </div>

                                <div class="space-y-1">
                                    {{-- Agenda & Badges --}}
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono text-[11px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200/60">
                                            {{ $dispo->suratMasuk->nomor_agenda ?? '-' }}
                                        </span>

                                        {{-- Badge Sifat --}}
                                        <span class="px-2 py-0.5 text-[10px] uppercase tracking-wider font-bold rounded-md border {{ $dispo->sifat === 'segera' ? 'bg-red-50 text-red-600 border-red-200/80' : 'bg-slate-100 text-slate-600 border-slate-200/60' }}">
                                            {{ $dispo->sifat }}
                                        </span>

                                        {{-- Badge Status Disposisi --}}
                                        @if ($isBaru)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold rounded-md bg-blue-100 text-blue-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                                Baru
                                            </span>
                                        @elseif($dispo->status === 'dibaca')
                                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md bg-amber-50 text-amber-700 border border-amber-200/60">
                                                Dibaca
                                            </span>
                                        @elseif($dispo->status === 'diproses')
                                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                                Diteruskan
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Perihal Surat Masuk --}}
                                    <h4 class="text-sm sm:text-base font-semibold {{ $isBaru ? 'text-slate-900 font-bold' : 'text-slate-800' }} group-hover:text-blue-600 transition leading-snug">
                                        {{ $dispo->suratMasuk->perihal ?? '-' }}
                                    </h4>

                                    {{-- Pengirim & Instruksi --}}
                                    <div class="text-xs text-slate-500 space-y-0.5 pt-0.5">
                                        <p class="flex items-center gap-1.5">
                                            <span class="font-medium text-slate-700">Dari: {{ $dispo->pengirim->name ?? 'Staf TU' }}</span>
                                            @if($dispo->suratMasuk->asal_surat)
                                                <span class="text-slate-300">•</span>
                                                <span class="text-slate-400">Asal: {{ $dispo->suratMasuk->asal_surat }}</span>
                                            @endif
                                        </p>
                                        <p class="text-slate-600 italic line-clamp-1">
                                            Catatan: "{{ Str::limit($dispo->instruksi, 110) }}"
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Time Ago Right Side --}}
                            <div class="flex items-center sm:flex-col sm:items-end justify-between shrink-0 pt-2 sm:pt-0 border-t sm:border-0 border-slate-100">
                                <span class="text-[11px] text-slate-400 font-medium">
                                    {{ $dispo->created_at ? $dispo->created_at->diffForHumans() : '' }}
                                </span>
                                <span class="text-xs text-blue-600 font-semibold group-hover:translate-x-0.5 transition hidden sm:inline-flex items-center gap-1 mt-2">
                                    <span>Buka Detail</span>
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </span>
                            </div>

                        </div>
                    </a>
                @empty
                    <div class="p-12 text-center space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Tidak ada disposisi</h4>
                            <p class="text-xs text-slate-400 mt-1">Belum ada lembar disposisi masuk pada kategori atau hasil pencarian ini.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- ===== 3. Pagination Links ===== --}}
            @if(method_exists($disposisi ?? collect(), 'hasPages') && $disposisi->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $disposisi->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>