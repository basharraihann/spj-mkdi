<x-app-layout title="Edit Surat Tugas">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Surat Tugas</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if ($errors->any())
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert">
                    <span class="font-bold">Gagal menyimpan perubahan! Periksa isian berikut:</span>
                    <ul class="mt-1.5 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('surat-tugas.update', $st) }}" method="POST">
                @csrf
                @method('PUT')
                @include('surat-tugas._form')
            </form>
        </div>
    </div>
</x-app-layout>