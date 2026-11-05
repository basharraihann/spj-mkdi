<x-app-layout title="Edit Memo">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-600/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Nomor Memo</h2>
        </div>
    </x-slot>

    <div class="py-8 bg-gradient-to-b from-gray-50/70 to-white min-h-full">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('memo._form', ['memo' => $memo])
        </div>
    </div>
</x-app-layout>