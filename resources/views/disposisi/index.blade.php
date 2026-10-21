<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Inbox Disposisi Digital') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <p class="text-sm text-slate-500">Disposisi yang ditujukan kepada Anda. Yang belum dibuka tampil tebal.</p>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($disposisi ?? [] as $dispo)
                    <a href="{{ route('disposisi.show', $dispo->id) }}" class="p-4 block hover:bg-slate-50 transition {{ $dispo->status === 'baru' ? 'bg-blue-50/30 font-semibold' : '' }}">
                        <div class="flex items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-mono text-slate-500">{{ $dispo->suratMasuk->nomor_agenda ?? '-' }}</span>
                                    <span class="px-2 py-0.5 text-[10px] uppercase tracking-wide font-bold rounded {{ $dispo->sifat === 'segera' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $dispo->sifat }}
                                    </span>
                                </div>
                                <h4 class="text-slate-900 text-base">{{ $dispo->suratMasuk->perihal ?? '-' }}</h4>
                                <p class="text-xs text-slate-500">Dari: {{ $dispo->pengirim->name ?? 'Staf TU' }} | Catatan: {{ Str::limit($dispo->instruksi, 100) }}</p>
                            </div>
                            <span class="text-xs text-slate-400 shrink-0">{{ $dispo->created_at ? $dispo->created_at->diffForHumans() : '' }}</span>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-slate-400">Belum ada disposisi masuk pada tab ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>