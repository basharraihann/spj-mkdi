<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Detail & Disposisi Surat') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- PANEL KIRI: DETAIL & DOCUMENT PREVIEW -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 block">Surat Masuk</span>
                        <strong class="text-slate-800">{{ $disposisi->suratMasuk->nomor_surat ?? '-' }}</strong>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Perihal</span>
                        <strong class="text-slate-800">{{ $disposisi->suratMasuk->perihal ?? '-' }}</strong>
                    </div>
                </div>

                @if(!empty($disposisi->suratMasuk->drive_link))
                    <div class="pt-2">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-semibold text-slate-600">📄 Preview Berkas Surat (PDF/Word/Excel/PPT)</span>
                            <a href="{{ $disposisi->suratMasuk->drive_link }}" target="_blank" class="text-xs text-blue-600 hover:underline">Buka Tab Baru ↗</a>
                        </div>
                        <div class="w-full h-96 bg-slate-100 rounded-xl overflow-hidden border border-slate-200">
                            @php
                                $embedUrl = $disposisi->suratMasuk->drive_link;
                                if (preg_match('/(?:d\/|id=)([\w-]+)/', $embedUrl, $m)) {
                                    $embedUrl = "https://drive.google.com/file/d/" . $m[1] . "/preview";
                                }
                            @endphp
                            <iframe src="{{ $embedUrl }}" class="w-full h-full border-0"></iframe>
                        </div>
                    </div>
                @endif
            </div>

            <!-- POHON HIERARKI DISPOSISI -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="font-semibold text-slate-800 text-sm mb-4">POHON HIERARKI DISPOSISI</h3>
                <div class="space-y-3 border-l-2 border-blue-500 pl-4 ml-2">
                    <div class="text-sm">
                        <strong class="text-slate-900">{{ $disposisi->pengirim->name ?? 'Staf TU' }}</strong>
                        <p class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-lg mt-1 border border-slate-100">{{ $disposisi->instruksi }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANEL KANAN: FORM DISPOSISI LANJUTAN -->
        <div class="lg:col-span-5">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm sticky top-6">
                <h3 class="font-semibold text-slate-800 text-sm mb-4">FORM DISPOSISI LANJUTAN</h3>
                <form action="{{ route('disposisi.store', $disposisi->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Instruksi / Catatan Tugas</label>
                        <textarea name="instruksi" rows="4" required class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Tulis instruksi penugasan..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Tenggat Waktu</label>
                            <input type="date" name="tenggat_waktu" class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Sifat</label>
                            <select name="sifat" class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="biasa">Biasa</option>
                                <option value="segera">Segera</option>
                                <option value="rahasia">Rahasia</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-sm transition">Kirim Disposisi ➔</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>