<x-app-layout title="Ubah Surat">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Ubah Surat</h2>
    </x-slot>

    @include('disposisi._form', ['surat' => $surat])
</x-app-layout>