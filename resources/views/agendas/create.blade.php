<x-app-layout title="Create Agenda">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Agenda Baru</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Step indicator --}}
            <div class="flex items-center gap-3 mb-5 px-1">
                <div class="flex items-center gap-2">
                    <span
                        class="h-6 w-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">1</span>
                    <span class="text-sm font-semibold text-gray-700">Peserta &amp; Detail Agenda</span>
                </div>
                <div class="flex-1 h-px bg-gray-200"></div>
                <div class="flex items-center gap-2">
                    <span
                        class="h-6 w-6 rounded-full bg-gray-100 text-gray-400 text-xs font-bold flex items-center justify-center">2</span>
                    <span class="text-sm font-medium text-gray-400">Rincian Biaya</span>
                </div>
            </div>

            @include('agendas._form', ['agenda' => null])
        </div>
    </div>
</x-app-layout>