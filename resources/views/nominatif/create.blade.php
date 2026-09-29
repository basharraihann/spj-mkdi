<x-app-layout title="Buat Nominatif">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-600/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Nominatif Narasumber</h2>
        </div>
    </x-slot>

    @include('nominatif._form', [
        'action' => route('nominatif.store'),
        'method' => 'POST',
        'submitLabel' => 'Simpan Nominatif',
    ])
</x-app-layout>