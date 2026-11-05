<x-app-layout title="Create Memo">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-600/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Nomor Memo</h2>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-gray-50/70 to-white min-h-full">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('memo._form')
        </div>
    </div>
</x-app-layout>