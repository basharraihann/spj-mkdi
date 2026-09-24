<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-600/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Nomor Memo</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div
                    class="flex gap-2.5 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl mb-5 animate-[fadeIn_.2s_ease-out]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div
                class="flex gap-2.5 bg-blue-50/70 border border-blue-100 text-blue-700 text-xs sm:text-sm px-4 py-3 rounded-xl mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <p>Nomor memo di sini gak nempel ke agenda perjalanan dinas manapun — cocok buat keperluan lain yang
                    tetap butuh nomor urut memo resmi.</p>
            </div>

            <form action="{{ route('memo.store') }}" method="POST"
                class="bg-white shadow-sm hover:shadow-md rounded-2xl border border-gray-100 p-5 sm:p-8 space-y-6 transition-shadow duration-300">
                @csrf

                <!-- Nomor Memo -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">
                        Nomor Memo <span class="text-red-400">*</span>
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" id="nomor-memo-input" name="nomor_memo"
                            value="{{ old('nomor_memo', $nomorBerikutnya['nomor']) }}" required
                            placeholder="325/LS.D1.PPK/KU.00/09/2026"
                            class="flex-1 min-w-0 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-mono tracking-tight focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        <button type="button" id="btn-ambil-memo"
                            class="flex-shrink-0 inline-flex items-center justify-center gap-1.5 border border-blue-200 text-blue-600 hover:bg-blue-50 active:scale-[0.97] font-semibold px-3.5 py-2.5 rounded-xl text-xs transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" id="icon-ambil-memo"
                                class="h-3.5 w-3.5 transition-transform" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span id="label-ambil-memo">Ambil Nomor Memo</span>
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">
                        Format ekor (setelah angka) niru nomor terakhir yang dipakai — boleh diubah manual kalau
                        beda.
                    </p>
                </div>

                <!-- Tanggal -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">
                        Tanggal Memo <span class="text-red-400">*</span>
                    </label>
                    <input type="date" name="tanggal_memo" value="{{ old('tanggal_memo', now()->toDateString()) }}"
                        required
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                </div>

                <!-- Uraian -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">Nama/Uraian
                        Kegiatan</label>
                    <input type="text" name="uraian_kegiatan" value="{{ old('uraian_kegiatan') }}"
                        placeholder="Keperluan memo ini"
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition placeholder:text-gray-300">
                </div>

                <!-- PIC -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">PIC</label>
                    <div class="relative">
                        <select name="pic_id"
                            class="w-full appearance-none bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            <option value="">— Pilih —</option>
                            @foreach ($pegawaiList as $p)
                                <option value="{{ $p->id }}" @selected(old('pic_id') == $p->id)>
                                    {{ $p->nama_gelar ?? $p->nama }}
                                </option>
                            @endforeach
                        </select>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- MAK & Nominal -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">MAK</label>
                        <input type="text" name="mak" value="{{ old('mak') }}" placeholder="7458.ABR.006.075.EE.524119"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-mono focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition placeholder:text-gray-300">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">Nominal</label>
                        <div class="relative">
                            <span
                                class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400 pointer-events-none">Rp</span>
                            <input type="number" name="nominal" value="{{ old('nominal') }}" min="0" placeholder="0"
                                class="w-full border border-gray-200 rounded-xl pl-9 pr-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition placeholder:text-gray-300">
                        </div>
                    </div>
                </div>

                <!-- Keterangan -->
                <div>
                    <label
                        class="block text-xs font-semibold text-gray-500 mb-1.5 tracking-wide uppercase">Keterangan</label>
                    <textarea name="keterangan" rows="3"
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition resize-none">{{ old('keterangan') }}</textarea>
                </div>

                <!-- Actions -->
                <div
                    class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 pt-2 border-t border-gray-50 pt-5">
                    <a href="{{ route('memo.index') }}"
                        class="w-full sm:w-auto text-center text-sm text-gray-400 hover:text-gray-600 transition">
                        &larr; Batal
                    </a>
                    <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-semibold px-5 py-2.5 rounded-xl text-sm shadow-md shadow-blue-600/20 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Nomor Memo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinning {
            animation: spin 0.8s linear infinite;
        }
    </style>

    <script>
        (function () {
            const btn = document.getElementById('btn-ambil-memo');
            const input = document.getElementById('nomor-memo-input');
            const icon = document.getElementById('icon-ambil-memo');
            const label = document.getElementById('label-ambil-memo');
            if (!btn || !input) return;

            btn.addEventListener('click', async function () {
                const teksAsli = label.textContent;
                btn.disabled = true;
                label.textContent = 'Mengambil...';
                icon.classList.add('spinning');
                try {
                    const res = await fetch('{{ route('memo.nomor-berikutnya') }}');
                    const data = await res.json();
                    input.value = data.nomor;
                    input.classList.add('ring-2', 'ring-green-200', 'border-green-300');
                    setTimeout(() => input.classList.remove('ring-2', 'ring-green-200', 'border-green-300'), 900);
                } catch (e) {
                    alert('Gagal mengambil nomor memo. Coba lagi.');
                } finally {
                    btn.disabled = false;
                    label.textContent = teksAsli;
                    icon.classList.remove('spinning');
                }
            });
        })();
    </script>
</x-app-layout>