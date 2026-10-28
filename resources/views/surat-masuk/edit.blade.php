<x-app-layout title="Edit Surat Masuk">
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <nav class="mb-1 flex flex-wrap items-center gap-x-2 text-xs text-gray-500" aria-label="Breadcrumb">
                    <a href="{{ route('surat-masuk.index') }}" class="transition hover:text-blue-600">Surat Masuk</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('surat-masuk.show', $suratMasuk->id) }}"
                        class="break-all transition hover:text-blue-600">{{ $suratMasuk->nomor_agenda }}</a>
                    <span aria-hidden="true">/</span>
                    <span class="font-medium text-gray-800">Edit</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Edit Data Surat Masuk') }}
                </h2>
            </div>
            <a href="{{ route('surat-masuk.show', $suratMasuk->id) }}"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
                Kembali
            </a>
        </div>
    </x-slot>

    @php
        $inputClass =
            'mt-1.5 block w-full rounded-lg border-gray-200 bg-white text-sm text-gray-800 placeholder:text-gray-400 shadow-sm transition focus:border-blue-400 focus:ring-1 focus:ring-blue-400';
        $inputError = 'border-red-300 focus:border-red-400 focus:ring-red-400';

        $status = strtolower($suratMasuk->status ?? 'baru');
        $statusLabel = ['baru' => 'Baru', 'proses' => 'Proses', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'];
        $statusClass = match ($status) {
            'proses' => 'bg-amber-50 text-amber-700 border-amber-200',
            'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'ditolak' => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-blue-50 text-blue-700 border-blue-200',
        };

        $nomorSuratLama = $suratMasuk->nomor_surat === '-' ? '' : $suratMasuk->nomor_surat;
    @endphp

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-5">

        {{-- Ringkasan kesalahan validasi --}}
        @if ($errors->any())
            <div role="alert" class="rounded-2xl border border-red-200 bg-red-50 p-4 sm:p-5">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 8v4m0 4h.01" />
                    </svg>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-red-800">Perubahan belum tersimpan. Perbaiki isian berikut:</p>
                        <ul class="mt-1.5 list-disc list-inside space-y-0.5 text-xs text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form id="form-edit-surat" action="{{ route('surat-masuk.update', $suratMasuk->id) }}" method="POST"
            class="grid grid-cols-1 rounded-2xl border border-gray-200 bg-white shadow-sm lg:grid-cols-[minmax(0,1fr)_22rem]">
            @csrf
            @method('PUT')

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
                            <input type="text" id="asal_surat" name="asal_surat"
                                value="{{ old('asal_surat', $suratMasuk->asal_surat) }}" required autocomplete="off"
                                class="{{ $inputClass }} {{ $errors->has('asal_surat') ? $inputError : '' }}">
                            @error('asal_surat')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nomor_surat" class="block text-sm font-medium text-gray-700">
                                Nomor surat dari pengirim
                                <span class="text-xs font-normal text-gray-400">(opsional)</span>
                            </label>
                            <input type="text" id="nomor_surat" name="nomor_surat"
                                value="{{ old('nomor_surat', $nomorSuratLama) }}" autocomplete="off"
                                placeholder="Contoh: B-123/KEMENTAN/2026"
                                class="{{ $inputClass }} font-mono {{ $errors->has('nomor_surat') ? $inputError : '' }}">
                            @error('nomor_surat')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="tanggal_surat" class="block text-sm font-medium text-gray-700">
                                Tanggal surat <span class="text-red-600">*</span>
                            </label>
                            <input type="date" id="tanggal_surat" name="tanggal_surat"
                                value="{{ old('tanggal_surat', $suratMasuk->tanggal_surat?->format('Y-m-d')) }}"
                                required
                                class="{{ $inputClass }} {{ $errors->has('tanggal_surat') ? $inputError : '' }}">
                            @error('tanggal_surat')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tanggal_diterima" class="block text-sm font-medium text-gray-700">
                                Tanggal diterima <span class="text-red-600">*</span>
                            </label>
                            <input type="date" id="tanggal_diterima" name="tanggal_diterima"
                                value="{{ old('tanggal_diterima', $suratMasuk->tanggal_diterima?->format('Y-m-d')) }}"
                                required
                                class="{{ $inputClass }} {{ $errors->has('tanggal_diterima') ? $inputError : '' }}">
                            @error('tanggal_diterima')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="perihal" class="block text-sm font-medium text-gray-700">
                            Perihal <span class="text-red-600">*</span>
                        </label>
                        <textarea id="perihal" name="perihal" rows="4" required
                            class="{{ $inputClass }} {{ $errors->has('perihal') ? $inputError : '' }}">{{ old('perihal', $suratMasuk->perihal) }}</textarea>
                        @error('perihal')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="drive_link" class="block text-sm font-medium text-gray-700">
                            Tautan berkas di Google Drive
                            <span class="text-xs font-normal text-gray-400">(PDF, Word, Excel, atau PPT)</span>
                        </label>
                        <input type="url" id="drive_link" name="drive_link"
                            value="{{ old('drive_link', $suratMasuk->drive_link) }}"
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

            {{-- ============ KANAN: NOMOR AGENDA, STATUS, AKSI ============ --}}
            <aside
                class="min-w-0 rounded-b-2xl border-t border-gray-100 bg-gray-50/70 lg:rounded-b-none lg:rounded-r-2xl lg:border-l lg:border-t-0">
                <div class="space-y-6 p-5 sm:p-8 lg:sticky lg:top-6">

                    {{-- Nomor agenda --}}
                    <div>
                        <label for="nomor_agenda" class="block text-xs font-semibold text-gray-500">
                            Nomor agenda <span class="text-red-600">*</span>
                        </label>
                        <div class="mt-2 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
                            <input type="text" id="nomor_agenda" name="nomor_agenda" required autocomplete="off"
                                value="{{ old('nomor_agenda', $suratMasuk->nomor_agenda) }}"
                                class="block w-full rounded-lg border-blue-100 bg-white font-mono text-sm font-semibold text-gray-900 shadow-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 {{ $errors->has('nomor_agenda') ? $inputError : '' }}">
                            <p class="mt-2 text-xs text-gray-500">
                                Nomor dibuat otomatis saat surat dicatat. Ubah hanya bila ada kesalahan pencatatan.
                            </p>
                            @error('nomor_agenda')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Status saat ini (hanya info) --}}
                    <div>
                        <p class="text-xs font-semibold text-gray-500">Status saat ini</p>
                        <div class="mt-2">
                            <span
                                class="inline-flex items-center whitespace-nowrap rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $statusClass }}">
                                {{ $statusLabel[$status] ?? ucfirst($status) }}
                            </span>
                        </div>
                    </div>

                    {{-- Aksi --}}
                    <div class="flex flex-col gap-2 border-t border-gray-200 pt-6">
                        <button type="submit" id="btn-simpan"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 cursor-pointer">
                            <span id="btn-simpan-label">Simpan perubahan</span>
                        </button>
                        <a href="{{ route('surat-masuk.show', $suratMasuk->id) }}"
                            class="inline-flex w-full items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                            Batal
                        </a>
                    </div>
                </div>
            </aside>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Cegah klik ganda saat menyimpan
            const form = document.getElementById('form-edit-surat');
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