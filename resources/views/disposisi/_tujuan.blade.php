{{--
    Pilih penerima + instruksi + catatan + batas waktu.
    Dipakai di form catat surat (create) dan form teruskan (show).
    Variabel: $penerima (collection id, nama, role, unit), $idForm (string unik per form)
--}}
@php
    $idForm = $idForm ?? 'tujuan';
    $dipilih = collect(old('kepada', []))->map(fn($v) => (int) $v)->all();
    $instruksiLama = old('instruksi', []);
    $perUnit = collect($penerima)->groupBy('unit');
@endphp

<div class="space-y-4">
    {{-- Penerima --}}
    <div x-data="{ q: '', n: {{ count($dipilih) }} }">
        <div class="flex items-center justify-between gap-2 mb-1.5">
            <label class="block text-xs font-semibold text-gray-600">
                Kepada <span class="text-red-400">*</span>
                <span class="font-normal text-gray-400">(boleh lebih dari satu)</span>
            </label>
            <span class="text-[11px] font-medium text-blue-700 bg-blue-50 rounded-full px-2 py-0.5"
                x-text="n + ' dipilih'"></span>
        </div>
        <input type="search" x-model="q" placeholder="Cari nama, unit, atau jabatan..."
            class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm mb-2 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none">
        <div class="max-h-64 overflow-y-auto rounded-xl border border-gray-200 divide-y divide-gray-100"
            @change="n = $el.querySelectorAll('input[type=checkbox]:checked').length">
            @forelse ($perUnit as $unit => $daftar)
                <div data-cari="{{ strtolower($unit . ' ' . $daftar->pluck('nama')->implode(' ') . ' ' . $daftar->pluck('role')->implode(' ')) }}"
                    x-show="$el.dataset.cari.includes(q.toLowerCase())">
                    <p class="sticky top-0 bg-gray-50 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                        {{ $unit }}</p>
                    @foreach ($daftar as $p)
                        <label class="flex items-center gap-3 px-3 py-2 text-sm hover:bg-blue-50/50 cursor-pointer"
                            data-cari="{{ strtolower($p['nama'] . ' ' . $p['role'] . ' ' . $unit) }}"
                            x-show="$el.dataset.cari.includes(q.toLowerCase())">
                            <input type="checkbox" name="kepada[]" value="{{ $p['id'] }}" @checked(in_array($p['id'], $dipilih, true))
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="flex-1 text-gray-800">{{ $p['nama'] }}</span>
                            <span class="text-[11px] text-gray-400">{{ $p['role'] }}</span>
                        </label>
                    @endforeach
                </div>
            @empty
                <p class="px-3 py-6 text-center text-sm text-gray-400">Belum ada user lain.</p>
            @endforelse
        </div>
        @error('kepada')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
        @error('kepada.*')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Instruksi --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Instruksi <span class="text-red-400">*</span></label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach (\App\Models\Disposisi::INSTRUKSI as $key => $label)
                <label class="flex items-center gap-2.5 rounded-xl border border-gray-200 px-3 py-2 text-sm cursor-pointer hover:border-blue-300 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/60">
                    <input type="checkbox" name="instruksi[]" value="{{ $key }}" @checked(in_array($key, $instruksiLama, true))
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-gray-700">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('instruksi')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="sm:col-span-2">
            <label for="{{ $idForm }}-catatan" class="block text-xs font-semibold text-gray-600 mb-1.5">Catatan</label>
            <textarea id="{{ $idForm }}-catatan" name="catatan" rows="2" placeholder="Arahan tambahan (opsional)"
                class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none">{{ old('catatan') }}</textarea>
            @error('catatan')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="{{ $idForm }}-batas" class="block text-xs font-semibold text-gray-600 mb-1.5">Batas waktu</label>
            <input id="{{ $idForm }}-batas" type="date" name="batas_waktu" value="{{ old('batas_waktu') }}"
                class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none">
            @error('batas_waktu')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
