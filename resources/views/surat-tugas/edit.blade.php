<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
            <div
                class="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Edit Draf Surat Tugas</h2>
                    <p class="mt-0.5 text-xs text-gray-500">Ubah data di bawah ini, lalu klik Simpan. Nomor surat
                        terkunci setelah disimpan.</p>
                </div>
                <a href="{{ route('surat-tugas.index') }}"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50">
                    Kembali ke daftar
                </a>
            </div>

            <form method="POST" action="{{ route('surat-tugas.update', $st) }}">
                @csrf
                @method('PUT')
                @include('surat-tugas._form')
            </form>
        </div>
    </div>
</x-app-layout>