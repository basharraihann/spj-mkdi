<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            {{-- Kepala halaman: ukuran dan gaya sama dengan halaman daftar --}}
            <div
                class="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Buat Surat Tugas</h2>
                    <p class="mt-0.5 text-xs text-gray-500">Lengkapi data di bawah ini. Nomor surat terkunci setelah
                        disimpan.</p>
                </div>
                <a href="{{ route('surat-tugas.index') }}"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50">
                    Kembali ke daftar
                </a>
            </div>

            <form method="POST" action="{{ route('surat-tugas.store') }}">
                @csrf
                @include('surat-tugas._form')
            </form>
        </div>
    </div>
</x-app-layout>