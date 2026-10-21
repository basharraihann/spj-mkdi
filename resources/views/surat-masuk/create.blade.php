<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Input Surat Masuk Baru') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <form action="{{ route('surat-masuk.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nomor Agenda / Disposisi</label>
                        <input type="text" name="nomor_agenda" required placeholder="Contoh: AG-001/2026"
                            class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nomor Surat Physical/Asal</label>
                        <input type="text" name="nomor_surat" required placeholder="Contoh: B-123/KEMENTAN/2026"
                            class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Asal Instansi / Pengirim</label>
                        <input type="text" name="asal_surat" required placeholder="Contoh: Kementerian Pertanian"
                            class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tanggal Surat</label>
                        <input type="date" name="tanggal_surat" required
                            class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Perihal / Ringkasan Isi Surat</label>
                    <textarea name="perihal" rows="3" required placeholder="Jelaskan ringkasan isi surat..."
                        class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Link Google Drive Berkas Surat (PDF/Word/Excel/PPT)</label>
                    <input type="url" name="drive_link" placeholder="https://drive.google.com/file/d/..."
                        class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-slate-500 mt-1">Pastikan akses link Google Drive sudah diatur ke 'Anyone with link / Siapa saja yang memiliki link'.</p>
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <a href="{{ route('surat-masuk.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition">Batal</a>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-sm transition">Simpan & Teruskan ke Deputi ➔</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>