@php
    $komponenOptions = [
        'tiket' => 'Tiket',
        'dukungan_transportasi' => 'Dukungan Transportasi',
        'transportasi_darat' => 'Transportasi Darat',
        'transportasi_lokal' => 'Transportasi Lokal',
        'peng_riil' => 'Peng. Riil',
        'hotel' => 'Hotel',
        'penginapan_30' => 'Penginapan 30%',
        'lumpsum' => 'Uang Harian (Lumpsum)',
        'representatif' => 'Representatif',
        'belanja_bahan' => 'Belanja Bahan',
        'honor_narsum' => 'Honor Narsum',
    ];

    $uhOptions = [
        'uh_biasa' => 'UH Biasa',
        'uh_biasa_60' => 'UH Biasa 60%',
        'uh_fullday' => 'UH Fullday',
        'uh_fullboard' => 'UH FullBoard',
    ];

    $selectedKomponen = old('komponen_biaya', $agenda->komponen_biaya ?? []);
    $selectedUh = old('jenis_uang_harian', $agenda->jenis_uang_harian ?? []);
@endphp

<div class="bg-white shadow-sm rounded-xl border border-gray-100 p-5 space-y-5">
    <div>
        <h3 class="font-semibold text-gray-800 mb-3">Komponen Biaya</h3>
        <div class="flex flex-wrap gap-2">
            @foreach ($komponenOptions as $key => $label)
                <label class="chip-option">
                    <input type="checkbox" name="komponen_biaya[]" value="{{ $key }}" class="sr-only peer"
                        @checked(in_array($key, $selectedKomponen)) @if ($key === 'lumpsum') data-toggles-uh="1" @endif>
                    <span class="chip-label">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('komponen_biaya')
            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
        @enderror
    </div>

    <div id="jenis-uh-section" class="{{ in_array('lumpsum', $selectedKomponen) ? '' : 'hidden' }}">
        <h3 class="font-semibold text-gray-800 mb-3">Jenis Uang Harian</h3>
        <div class="flex flex-wrap gap-2">
            @foreach ($uhOptions as $key => $label)
                <label class="chip-option">
                    <input type="checkbox" name="jenis_uang_harian[]" value="{{ $key }}" class="sr-only peer"
                        @checked(in_array($key, $selectedUh))>
                    <span class="chip-label">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('jenis_uang_harian')
            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
        @enderror
    </div>
</div>

<style>
    .chip-option .chip-label {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.9rem;
        border-radius: 9999px;
        border: 1px solid #d1d5db;
        font-size: 0.8rem;
        font-weight: 500;
        color: #374151;
        cursor: pointer;
        transition: all 0.15s ease;
        user-select: none;
    }

    .chip-option .chip-label:hover {
        border-color: #9ca3af;
    }

    .chip-option input:checked+.chip-label {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }
</style>

<script>
    // Munculin/sembunyiin section "Jenis Uang Harian" sesuai centang "Uang Harian (Lumpsum)"
    document.querySelectorAll('input[data-toggles-uh="1"]').forEach(function (cb) {
        cb.addEventListener('change', function () {
            document.getElementById('jenis-uh-section').classList.toggle('hidden', !cb.checked);
        });
    });
</script>