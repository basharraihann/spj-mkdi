{{-- Dipakai bersama oleh create.blade.php dan edit.blade.php --}}
@php
    $pegawai = $pegawai ?? null;
    $unitKerjaList = $unitKerjaList ?? collect();
@endphp

{{-- Section: Data Utama --}}
<div class="space-y-4">
    <div class="flex items-center gap-2">
        <span class="h-6 w-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </span>
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Data Utama</h4>
    </div>

    <div>
        <label class="flex items-center gap-1 text-xs font-semibold text-gray-500 mb-1.5">
            Nama <span class="text-red-400">*</span>
        </label>
        <input type="text" name="nama" required value="{{ old('nama', $pegawai->nama ?? '') }}"
            placeholder="Contoh: Achmad Murman"
            class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1.5">
            Nama + Gelar
            <span class="text-gray-300 font-normal normal-case">(opsional, untuk keperluan TTD formal)</span>
        </label>
        <input type="text" name="nama_gelar" value="{{ old('nama_gelar', $pegawai->nama_gelar ?? '') }}"
            placeholder="Contoh: Achmad Murman, S.T., M.T., M.Sc."
            class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                NIP <span class="text-gray-300 font-normal normal-case">(opsional)</span>
            </label>
            <input type="text" name="nip" value="{{ old('nip', $pegawai->nip ?? '') }}"
                placeholder="Contoh: 197706232005021002"
                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm font-mono tracking-tight focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300 placeholder:font-sans">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                Golongan <span class="text-gray-300 font-normal normal-case">(opsional)</span>
            </label>
            <input type="text" name="golongan" value="{{ old('golongan', $pegawai->golongan ?? '') }}"
                placeholder="Contoh: IV/b"
                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
        </div>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1.5">
            Pangkat <span class="text-gray-300 font-normal normal-case">(opsional)</span>
        </label>
        <input type="text" name="pangkat" value="{{ old('pangkat', $pegawai->pangkat ?? '') }}"
            placeholder="Contoh: Pembina Tk I"
            class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
    </div>

    <div>
        <label class="flex items-center gap-1 text-xs font-semibold text-gray-500 mb-1.5">
            Jabatan <span class="text-red-400">*</span>
        </label>
        <input type="text" name="jabatan" required value="{{ old('jabatan', $pegawai->jabatan ?? '') }}"
            placeholder="Contoh: Analis Kebijakan Ahli Madya"
            class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
    </div>

    <div>
        <label class="flex items-center gap-1 text-xs font-semibold text-gray-500 mb-1.5">
            Unit Kerja <span class="text-red-400">*</span>
        </label>
        <input type="text" name="unit_kerja" list="unit-kerja-options" required
            value="{{ old('unit_kerja', $pegawai->unit_kerja ?? '') }}"
            placeholder="Contoh: Biro Manajemen Kinerja, Data dan Informasi"
            class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition placeholder:text-gray-300">
        <datalist id="unit-kerja-options">
            @foreach ($unitKerjaList as $unit)
                <option value="{{ $unit }}"></option>
            @endforeach
        </datalist>
        <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            Ketik nama unit baru, atau pilih dari saran unit yang sudah ada.
        </p>
    </div>
</div>

<div class="h-px bg-gray-100"></div>

{{-- Section: Kepegawaian & Peran --}}
<div class="space-y-4">
    <div class="flex items-center gap-2">
        <span class="h-6 w-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.75h-.152c-3.196 0-6.1-1.248-8.25-3.286z" />
            </svg>
        </span>
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Kepegawaian &amp; Peran</h4>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="flex items-center gap-1 text-xs font-semibold text-gray-500 mb-1.5">
                Status Kepegawaian <span class="text-red-400">*</span>
            </label>
            <div class="relative">
                <select name="status_kepegawaian" required
                    class="w-full appearance-none border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white">
                    <option value="">— Pilih —</option>
                    @foreach (\App\Models\Pegawai::STATUS_KEPEGAWAIAN as $value => $label)
                        <option value="{{ $value }}" {{ old('status_kepegawaian', $pegawai->status_kepegawaian ?? '') === $value ? 'selected' : '' }}>
                            {{ $label }}
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

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                Peran Penandatangan <span class="text-gray-300 font-normal normal-case">(opsional)</span>
            </label>
            <div class="relative">
                <select name="role_penandatangan"
                    class="w-full appearance-none border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none transition bg-white">
                    <option value="">— Bukan penandatangan default —</option>
                    @foreach (\App\Models\Pegawai::ROLE_PENANDATANGAN as $value => $label)
                        <option value="{{ $value }}" {{ old('role_penandatangan', $pegawai->role_penandatangan ?? '') === $value ? 'selected' : '' }}>
                            {{ $label }}
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
    </div>

    <p class="text-xs text-gray-400 flex items-start gap-1.5 bg-gray-50 border border-gray-100 rounded-lg px-3 py-2.5">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
        </svg>
        <span>
            <strong class="text-gray-500">Status Kepegawaian</strong> dipakai buat misahin biaya ASN vs Non-ASN di
            rekap agenda. <strong class="text-gray-500">Peran Penandatangan</strong> menandai kalau pegawai ini biasa
            jadi PPK/Bendahara/dst di dropdown form agenda.
        </span>
    </p>
</div>