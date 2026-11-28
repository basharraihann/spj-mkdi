{{--
    Kartu atas bersama untuk tab Daftar, Kalender, dan Jadwal Pegawai.
    Isinya: bar tab + tombol "Input disposisi", baris pencarian, lalu "Filter lanjutan".

    Props
      tampil      daftar | kalender | jadwal   (tab yang aktif)
      q           isi kolom cari
      placeholder teks petunjuk kolom cari
      resetUrl    URL tombol Reset (null = tombol tidak muncul)
      lanjut      true bila ada filter lanjutan yang aktif (panel terbuka + titik biru)
      bolehCatat  tampilkan tombol "Input disposisi"
      action      URL form (default: disposisi.index)
      formId      id untuk <form> (opsional)

    Slot
      (default)   isi filter lanjutan, taruh <x-disposisi.pilih> / <x-disposisi.kolom>
      hidden      <input type="hidden"> tambahan
      tambahan    isi yang selalu tampil di bawah baris cari (di luar filter lanjutan)
      bawah       isi yang menyatu di bawah form dalam kartu yang sama (mis. daftar surat)
--}}
@props([
    'tampil',
    'q' => '',
    'placeholder' => 'Cari…',
    'resetUrl' => null,
    'lanjut' => false,
    'bolehCatat' => false,
    'action' => null,
    'formId' => null,
])

<section {{ $attributes->merge(['class' => 'overflow-clip rounded-2xl border border-slate-200 bg-white']) }}>

    {{-- Tab + tombol input --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 p-2">
        @include('disposisi.kalender._tab', ['tampil' => $tampil, 'menyatu' => true])

        @if ($bolehCatat)
            <a href="{{ route('disposisi.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Input disposisi
            </a>
        @endif
    </div>

    {{-- Pencarian + filter --}}
    <form method="GET" action="{{ $action ?: route('disposisi.index') }}" @if ($formId) id="{{ $formId }}" @endif
        class="space-y-3 p-4 @isset($bawah) border-b border-slate-100 @endisset">

<div hidden>{{ $hidden ?? '' }}</div>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <label for="cari" class="sr-only">Cari disposisi</label>
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
                </svg>
                <input id="cari" type="search" name="q" value="{{ $q }}" maxlength="100" placeholder="{{ $placeholder }}"
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 pl-9 text-sm text-slate-900 placeholder:text-slate-400 shadow-none outline-none transition hover:border-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div class="flex gap-2">
                <button class="inline-flex flex-1 items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200 sm:flex-none">Cari</button>
                @if ($resetUrl)
                    <a href="{{ $resetUrl }}"
                        class="inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 focus:outline-none focus-visible:ring-4 focus-visible:ring-slate-200">Reset</a>
                @endif
            </div>
        </div>

        {{-- Selalu tampil (di luar filter lanjutan) --}}
        {{ $tambahan ?? '' }}

        {{-- Filter lanjutan --}}
        @if (! $slot->isEmpty())
            <details class="group" @if ($lanjut) open @endif>
                <summary class="inline-flex cursor-pointer list-none items-center gap-1.5 text-sm font-medium text-slate-600 hover:text-slate-900">
                    <svg class="h-4 w-4 transition group-open:rotate-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    Filter lanjutan
                    @if ($lanjut)
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                    @endif
                </summary>

                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    {{ $slot }}
                </div>
            </details>
        @endif
    </form>

    {{ $bawah ?? '' }}
</section>