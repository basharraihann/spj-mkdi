<x-app-layout title="Input Disposisi">
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">Input disposisi</h2>
                <p class="text-sm text-slate-500 mt-0.5">Catat surat masuk dan teruskan ke Eselon II.</p>
            </div>
            <a href="{{ route('disposisi.index', ['tab' => 'surat']) }}"
                class="hidden sm:inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 hover:text-slate-900">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Kembali ke daftar
            </a>
        </div>
    </x-slot>

    @include('disposisi._form', ['surat' => null, 'es2' => $es2])
</x-app-layout>