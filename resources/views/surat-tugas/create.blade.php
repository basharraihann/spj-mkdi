<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Surat Tugas</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('surat-tugas.store') }}">
                @csrf
                @include('surat-tugas._form')
            </form>
        </div>
    </div>
</x-app-layout>