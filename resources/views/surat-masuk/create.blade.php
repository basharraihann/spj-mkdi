<x-app-layout title="Input Surat Masuk">
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Input Surat Masuk Baru') }}
            </h2>
            <a href="{{ route('surat-masuk.index') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
                Kembali
            </a>
        </div>
    </x-slot>

    @php
        $punyaUnit = (bool) auth()->user()?->unit_id;
        $tanpaTujuan = $punyaUnit && !$target;

        $inputClass =
            'mt-1.5 block w-full rounded-lg border-gray-200 bg-white text-sm text-gray-800 placeholder:text-gray-400 shadow-sm transition focus:border-blue-400 focus:ring-1 focus:ring-blue-400';
        $inputError = 'border-red-300 focus:border-red-400 focus:ring-red-400';
    @endphp

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-5">

        {{-- Ringkasan kesalahan validasi --}}
        @if ($errors->any())
            <div role="alert" class="rounded-2xl border border-red-200 bg-red-50 p-4 sm:p-5">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 8v4m0 4h.01" />
                    </svg>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-red-800">Surat belum tersimpan. Perbaiki isian berikut:</p>
                        <ul class="mt-1.5 list-disc list-inside space-y-0.5 text-xs text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Satu kartu: isian di kiri, panel tujuan + nomor agenda + aksi di kanan --}}
        <form id="form-surat-masuk" action="{{ route('surat-masuk.store') }}" method="POST"
            class="grid grid-cols-1 rounded-2xl border border-gray-200 bg-white shadow-sm lg:grid-cols-[minmax(0,1fr)_22rem]">
            @csrf

            {{-- ============ KIRI: DATA SURAT ============ --}}
            <div class="min-w-0 p-5 sm:p-8">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-800">Data surat</h3>
                    <p class="mt-0.5 text-sm text-gray-500">Isian bertanda <span class="text-red-600">*</span> wajib
                        diisi.</p>
                </div>

                <div class="space-y-5">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label for="asal_surat" class="block text-sm font-medium text-gray-700">
                                Asal instansi / pengirim <span class="text-red-600">*</span>
                            </label>
                            <input type="text" id="asal_surat" name="asal_surat" value="{{ old('asal_surat') }}"
                                required autocomplete="off" placeholder="Contoh: Kementerian Pertanian"
                                class="{{ $inputClass }} {{ $errors->has('asal_surat') ? $inputError : '' }}">
                            @error('asal_surat')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tanggal_surat" class="block text-sm font-medium text-gray-700">
                                Tanggal surat <span class="text-red-600">*</span>
                            </label>
                            <input type="date" id="tanggal_surat" name="tanggal_surat"
                                value="{{ old('tanggal_surat', date('Y-m-d')) }}" required
                                class="{{ $inputClass }} {{ $errors->has('tanggal_surat') ? $inputError : '' }}">
                            @error('tanggal_surat')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="nomor_surat" class="block text-sm font-medium text-gray-700">
                            Nomor surat dari pengirim
                            <span class="text-xs font-normal text-gray-400">(opsional)</span>
                        </label>
                        <input type="text" id="nomor_surat" name="nomor_surat" value="{{ old('nomor_surat') }}"
                            autocomplete="off" placeholder="Contoh: B-123/KEMENTAN/2026"
                            class="{{ $inputClass }} {{ $errors->has('nomor_surat') ? $inputError : '' }}">
                        <p class="mt-1 text-xs text-gray-400">Kosongkan bila surat fisik tidak memiliki nomor resmi.</p>
                        @error('nomor_surat')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="perihal" class="block text-sm font-medium text-gray-700">
                            Perihal <span class="text-red-600">*</span>
                        </label>
                        <textarea id="perihal" name="perihal" rows="4" required placeholder="Tulis ringkasan isi surat"
                            class="{{ $inputClass }} {{ $errors->has('perihal') ? $inputError : '' }}">{{ old('perihal') }}</textarea>
                        @error('perihal')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="drive_link" class="block text-sm font-medium text-gray-700">
                            Tautan berkas di Google Drive
                            <span class="text-xs font-normal text-gray-400">(PDF, Word, Excel, atau PPT)</span>
                        </label>
                        <input type="url" id="drive_link" name="drive_link" value="{{ old('drive_link') }}"
                            placeholder="https://drive.google.com/file/d/..."
                            class="{{ $inputClass }} {{ $errors->has('drive_link') ? $inputError : '' }}">
                        <p class="mt-1 text-xs text-gray-400">
                            Atur akses tautan ke "Siapa saja yang memiliki link" agar penerima bisa membukanya.
                        </p>
                        @error('drive_link')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ============ KANAN: TUJUAN, NOMOR AGENDA, AKSI ============ --}}
            <aside
                class="min-w-0 rounded-b-2xl border-t border-gray-100 bg-gray-50/70 lg:rounded-b-none lg:rounded-r-2xl lg:border-l lg:border-t-0">
                <div class="space-y-6 p-5 sm:p-8 lg:sticky lg:top-6">

                    {{-- Nomor agenda --}}
                    <div>
                        <p class="text-xs font-semibold text-gray-500">Nomor agenda</p>
                        <div class="mt-2 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
                            <p class="text-xl font-bold text-gray-900 tabular-nums break-all">
                                {{ $nomorAgendaBerikutnya ?? '-' }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Nomor final ditetapkan saat surat disimpan.
                            </p>
                        </div>
                    </div>

                    {{-- Tujuan surat --}}
                    <div>
                        <p class="text-xs font-semibold text-gray-500">Tujuan surat</p>
                        <div class="mt-2 rounded-xl border border-gray-200 bg-white px-4 py-3">
                            @if ($punyaUnit)
                                @if ($target)
                                    <p class="text-xs text-gray-500">Diteruskan ke</p>
                                    <p class="mt-0.5 text-sm font-semibold text-gray-900 break-words">{{ $target->name }}</p>
                                    @if ($unitNama)
                                        <p class="mt-0.5 text-xs text-gray-500 break-words">{{ $unitNama }}</p>
                                    @endif
                                @else
                                    <p class="text-sm font-medium text-red-700">
                                        Belum ada pejabat terdaftar di unit Anda. Hubungi admin untuk menambahkannya.
                                    </p>
                                @endif
                            @else
                                <label for="unit_id" class="block text-xs text-gray-500">
                                    Unit tujuan <span class="text-red-600">*</span>
                                </label>
                                <select id="unit_id" name="unit_id" required
                                    class="{{ $inputClass }} {{ $errors->has('unit_id') ? $inputError : '' }}">
                                    <option value="">Pilih unit</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>
                                            {{ $unit->kode }} - {{ $unit->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                    </div>

                    {{-- Aksi --}}
                    <div class="flex flex-col gap-2 border-t border-gray-200 pt-6">
                        <button type="submit" id="btn-simpan" @disabled($tanpaTujuan)
                            class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 cursor-pointer">
                            <span id="btn-simpan-label">Simpan dan teruskan</span>
                        </button>
                        <a href="{{ route('surat-masuk.index') }}"
                            class="inline-flex w-full items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                            Batal
                        </a>
                        @if ($tanpaTujuan)
                            <p class="mt-1 text-xs text-red-600">Tombol simpan nonaktif karena unit Anda belum memiliki
                                pejabat penerima.</p>
                        @endif
                    </div>
                </div>
            </aside>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Cegah klik ganda agar satu surat tidak memakai dua nomor agenda
            const form = document.getElementById('form-surat-masuk');
            const tombol = document.getElementById('btn-simpan');
            const label = document.getElementById('btn-simpan-label');

            if (!form || !tombol) return;

            form.addEventListener('submit', function () {
                if (form.dataset.submitting === '1') return;
                form.dataset.submitting = '1';

                setTimeout(function () {
                    tombol.disabled = true;
                    label.textContent = 'Menyimpan...';
                }, 0);
            });
        });
    </script>
</x-app-layout>