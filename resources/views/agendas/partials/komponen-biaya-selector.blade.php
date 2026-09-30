{{--
Selector "Komponen Biaya" + "Jenis Uang Harian" (versi simpel).
Letak file: resources/views/agendas/partials/komponen-biaya-selector.blade.php
Dipanggil dari agendas/peserta.blade.php dengan ['agenda' => $agenda].

Kontrak dengan halaman induk TIDAK berubah:
- name input: komponen_biaya[] dan jenis_uang_harian[] (value sama)
- id: kb-selector, kb-uh, kb-count, kb-clear
- event 'change' tetap di-dispatch, jadi JS di peserta.blade.php tidak perlu diubah.
--}}
@php
    $kbGroups = [
        'Transportasi' => [
            'icon' => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12',
            'items' => [
                'tiket' => 'Tiket',
                'dukungan_transportasi' => 'Dukungan Transportasi',
                'transportasi_darat' => 'Transportasi Darat',
                'transportasi_lokal' => 'Transportasi Lokal',
                'peng_riil' => 'Peng. Riil',
            ],
        ],
        'Akomodasi' => [
            'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
            'items' => [
                'hotel' => 'Hotel',
                'penginapan_30' => 'Penginapan 30%',
            ],
        ],
        'Uang Harian & Lainnya' => [
            'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
            'items' => [
                'lumpsum' => 'Uang Harian (Lumpsum)',
                'representatif' => 'Representatif',
                'belanja_bahan' => 'Belanja Bahan',
                'honor_narsum' => 'Honor Narsum',
            ],
        ],
    ];

    $uhOptions = [
        'uh_biasa' => 'UH Biasa',
        'uh_biasa_60' => 'UH Biasa 60%',
        'uh_fullday' => 'UH Fullday',
        'uh_fullboard' => 'UH FullBoard',
    ];

    $kbSelected = (array) old('komponen_biaya', $agenda->komponen_biaya ?? []);
    $kbSelectedUh = (array) old('jenis_uang_harian', $agenda->jenis_uang_harian ?? []);
@endphp

<style>
    .kb-chip {
        position: relative;
        display: inline-block;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }

    .kb-chip input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .kb-chip-body {
        display: inline-flex;
        align-items: center;
        gap: .375rem;
        min-height: 2.25rem;
        padding: .375rem .875rem;
        border: 1px solid #e5e7eb;
        border-radius: 999px;
        background: #fff;
        color: #4b5563;
        font-size: .875rem;
        line-height: 1.2;
        transition: background-color .15s, border-color .15s, color .15s;
    }

    .kb-chip:hover .kb-chip-body {
        border-color: #9ca3af;
    }

    .kb-chip input:focus-visible+.kb-chip-body {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }

    .kb-chip input:checked+.kb-chip-body {
        background: #eff6ff;
        border-color: #2563eb;
        color: #1d4ed8;
        font-weight: 500;
    }

    .kb-check {
        display: none;
        width: .875rem;
        height: .875rem;
        flex: none;
    }

    .kb-chip input:checked+.kb-chip-body .kb-check {
        display: block;
    }

    /* Jenis UH hanya relevan kalau Lumpsum dicentang */
    #kb-uh .kb-uh-chips {
        transition: opacity .2s;
    }

    #kb-uh.kb-off .kb-uh-chips {
        opacity: .5;
    }

    #kb-uh .kb-uh-hint {
        display: none;
    }

    #kb-uh.kb-off .kb-uh-hint {
        display: block;
    }

    @media (prefers-reduced-motion: reduce) {

        .kb-chip-body,
        #kb-uh .kb-uh-chips {
            transition: none;
        }
    }
</style>

<div id="kb-selector" class="bg-white shadow-sm rounded-2xl border border-gray-100 p-4 sm:p-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-3 mb-5">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">Komponen Biaya</h3>
                <span id="kb-count"
                    class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-xs font-medium">
                    0 dipilih
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">
                Pilih komponen yang berlaku. Kolom di tabel peserta menyesuaikan.
            </p>
        </div>

        <button type="button" id="kb-clear"
            class="text-sm text-gray-500 hover:text-red-600 px-2 py-1 rounded-lg transition">
            Kosongkan
        </button>
    </div>

    @error('komponen_biaya')
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs px-3 py-2">
            {{ $message }}
        </div>
    @enderror

    {{-- Grup komponen --}}
    <div class="space-y-5">
        @foreach ($kbGroups as $title => $group)
            <div>
                <div class="flex items-center gap-2 mb-2 text-sm font-medium text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $group['icon'] }}" />
                    </svg>
                    {{ $title }}
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($group['items'] as $value => $label)
                        <label class="kb-chip">
                            <input type="checkbox" name="komponen_biaya[]" value="{{ $value }}" @checked(in_array($value, $kbSelected))>
                            <span class="kb-chip-body">
                                <svg class="kb-check" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Jenis Uang Harian --}}
    <div id="kb-uh" class="mt-6 pt-5 border-t border-gray-100">
        <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1 mb-2">
            <h4 class="flex items-center gap-2 text-sm font-medium text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Jenis Uang Harian
            </h4>
            <span class="kb-uh-hint text-xs text-amber-600">
                Aktif setelah "Uang Harian (Lumpsum)" dipilih. Memilih jenis di sini akan mencentangnya otomatis.
            </span>
        </div>

        <div class="kb-uh-chips flex flex-wrap gap-2">
            @foreach ($uhOptions as $value => $label)
                <label class="kb-chip">
                    <input type="checkbox" name="jenis_uang_harian[]" value="{{ $value }}" @checked(in_array($value, $kbSelectedUh))>
                    <span class="kb-chip-body">
                        <svg class="kb-check" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $label }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>
</div>

<script>
    (function () {
        const root = document.getElementById('kb-selector');
        if (!root) return;

        const komponen = root.querySelectorAll('input[name="komponen_biaya[]"]');
        const uh = root.querySelectorAll('input[name="jenis_uang_harian[]"]');
        const lumpsum = root.querySelector('input[name="komponen_biaya[]"][value="lumpsum"]');
        const uhBox = document.getElementById('kb-uh');
        const countEl = document.getElementById('kb-count');

        function refresh() {
            const n = root.querySelectorAll('input[name="komponen_biaya[]"]:checked').length;
            countEl.textContent = n + ' dipilih';
            if (uhBox && lumpsum) uhBox.classList.toggle('kb-off', !lumpsum.checked);
        }

        komponen.forEach(function (cb) {
            cb.addEventListener('change', refresh);
        });

        // Memilih jenis UH otomatis mencentang "Uang Harian (Lumpsum)"
        uh.forEach(function (cb) {
            cb.addEventListener('change', function () {
                if (cb.checked && lumpsum && !lumpsum.checked) {
                    lumpsum.checked = true;
                    lumpsum.dispatchEvent(new Event('change', { bubbles: true }));
                }
                refresh();
            });
        });

        // Kosongkan semua komponen (kolom di tabel ikut tersembunyi lewat event change)
        document.getElementById('kb-clear').addEventListener('click', function () {
            komponen.forEach(function (cb) {
                if (cb.checked) {
                    cb.checked = false;
                    cb.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
            refresh();
        });

        refresh();
    })();
</script>