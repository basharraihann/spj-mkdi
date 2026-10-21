<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Buat Nota Dinas / Laporan Pengerjaan') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <form action="{{ route('nota-dinas.store', $disposisi->id ?? 1) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">Perihal Nota Dinas</label>
                    <input type="text" name="perihal" required placeholder="Laporan hasil pelaksanaan tugas..."
                        class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Ringkasan Hasil Pengerjaan</label>
                    <textarea name="ringkasan_hasil" rows="5" required placeholder="Jelaskan ringkasan hasil kegiatan/tugas yang telah dilakukan..."
                        class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Link Google Drive Berkas Laporan Hasil</label>
                    <input type="url" name="drive_link_hasil" placeholder="https://drive.google.com/file/d/..."
                        class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-sm transition">Kirim Nota Dinas ➔</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>