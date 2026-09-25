{{--
    Komponen tombol Hapus global.
    Pakai ini di SEMUA halaman yang butuh tombol hapus (Pegawai, Nomor Memo, dll)
    supaya konfirmasinya konsisten (pakai SweetAlert2, bukan confirm() bawaan browser).

    Simpan file ini di: resources/views/components/delete-button.blade.php

    Cara pakai di Blade manapun:

    <x-delete-button
        :action="route('pegawais.destroy', $pegawai)"
        :label="'pegawai ' . $pegawai->nama"
        :id="'pegawai-' . $pegawai->id" />

    Props:
    - action (wajib)  : URL tujuan form delete, contoh route('pegawais.destroy', $pegawai)
    - label  (opsional): nama item yang muncul di teks konfirmasi, contoh "pegawai Raka Panji Wibowo"
    - id     (opsional): id unik untuk form ini, wajib unik per baris kalau ada banyak tombol di 1 halaman
--}}

@props([
    'action',
    'label' => 'data ini',
    'id' => null,
])

@php
    $formId = 'delete-form-' . ($id ?? uniqid());
@endphp

<form id="{{ $formId }}" action="{{ $action }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<button
    type="button"
    onclick="confirmDelete('{{ $formId }}', @js($label))"
    {{ $attributes->merge(['class' => 'text-red-600 hover:text-red-800 hover:underline inline-flex items-center gap-1']) }}
>
    {{ $slot->isEmpty() ? 'Hapus' : $slot }}
</button>
