{{--
Tab halaman Disposisi.
Variabel: $tampil = 'daftar' | 'hari-ini' | 'kalender' | 'jadwal'

--}}
@php
    $tampil = $tampil ?? 'daftar';
    $pengelola = \App\Models\DisposisiSurat::bolehKelola(auth()->user());
    $baruMasuk = \App\Models\Disposisi::jumlahBaru(auth()->id());

    $tabs = array_filter([
        $pengelola ? ['daftar', 'Daftar', route('disposisi.index'), 'M4 6h16M4 12h16M4 18h16', 0] : null,
        ['hari-ini', 'Hari Ini', route('disposisi.index', ['tampil' => 'hari-ini']), 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', $baruMasuk],
        ['kalender', 'Kalender', route('disposisi.index', ['tampil' => 'kalender']), 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 0],
        ['jadwal', 'Jadwal Pegawai', route('disposisi.index', ['tampil' => 'jadwal']), 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 0],
    ]);
@endphp

<nav class="flex w-full gap-0.5 overflow-x-auto rounded-xl bg-slate-100 p-1 sm:inline-flex sm:w-auto"
    aria-label="Tampilan disposisi">
    @foreach ($tabs as [$kunci, $nama, $url, $ikon, $angka])
        <a href="{{ $url }}" @if ($tampil === $kunci) aria-current="page" @endif @class([
            'inline-flex flex-1 flex-none items-center justify-center gap-2 whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold transition focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100 sm:flex-none',
            'bg-blue-600 text-white shadow-sm' => $tampil === $kunci,
            'text-slate-600 hover:text-slate-900' => $tampil !== $kunci,
        ])>
            <svg class="h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}" />
            </svg>
            {{ $nama }}
            @if ($angka > 0)
                <span
                    class="grid h-5 min-w-5 place-items-center rounded-full bg-red-500 px-1.5 text-[11px] font-semibold leading-none text-white">{{ $angka }}</span>
            @endif
        </a>
    @endforeach
</nav>