<x-app-layout title="Edit Agenda">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Agenda</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Breadcrumb / context bar --}}
            <div class="flex items-center gap-2 mb-5 px-1 text-sm">
                <a href="{{ route('agendas.index') }}" class="text-gray-400 hover:text-gray-600 transition">Agenda</a>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-300" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-gray-700 font-semibold truncate max-w-xs">{{ $agenda->uraian_kegiatan }}</span>
            </div>

            @include('agendas._form', ['agenda' => $agenda])
        </div>
    </div>
</x-app-layout>