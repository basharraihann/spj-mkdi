<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Edit Surat / Dokumen Keluar') }}
            </h2>
            <a href="{{ route('surat-keluar.show', $suratKeluar->id) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs font-medium space-y-1">
                <p class="font-bold text-sm mb-1">Gagal menyimpan perubahan! Periksa kembali inputan berikut:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
            <form action="{{ route('surat-keluar.update', $suratKeluar->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @include('surat-keluar._form', ['surat' => $suratKeluar])

                <div class="pt-4 flex justify-end gap-3 border-t border-slate-200">
                    <a href="{{ route('surat-keluar.show', $suratKeluar->id) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">Batal</a>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-md transition">Simpan Perubahan &#10132;</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
