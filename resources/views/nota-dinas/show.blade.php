<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Detail Nota Dinas') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div>
                <span class="text-xs text-slate-400">Perihal</span>
                <h3 class="text-lg font-bold text-slate-800">{{ $notaDinas->perihal ?? 'Laporan Hasil Pengerjaan Disposisi' }}</h3>
            </div>

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-xs font-semibold text-slate-500 block mb-1">Ringkasan Hasil:</span>
                <p class="text-sm text-slate-700 leading-relaxed">{{ $notaDinas->ringkasan_hasil ?? '-' }}</p>
            </div>

            @if(!empty($notaDinas->drive_link_hasil))
                <div class="pt-2">
                    <a href="{{ $notaDinas->drive_link_hasil }}" target="_blank" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl inline-block shadow-sm transition">
                        📄 Buka Berkas Laporan Hasil di Google Drive ↗
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>