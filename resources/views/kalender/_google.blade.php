{{-- Kartu status Google Calendar. Variabel: $google (aktif, akun), $ringkas (bool, versi kecil) --}}
@php
    $akun = $google['akun'];
    $ringkas = $ringkas ?? false;
@endphp

<div class="kd-card {{ $ringkas ? 'p-4 sm:flex sm:items-center sm:justify-between sm:gap-4' : 'p-4 sm:p-5' }}">
    <div class="flex items-start gap-3">
        <div class="h-9 w-9 shrink-0 rounded-xl flex items-center justify-center {{ $akun && !$akun->error_terakhir ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-gray-900">Google Calendar</p>
            @if ($akun)
                <p class="text-xs text-gray-500 break-words">Terhubung ke <b class="text-gray-700">{{ $akun->google_email ?? 'akun Google' }}</b></p>
                @if ($akun->terakhir_sinkron)
                    <p class="text-[11px] text-gray-400">Sinkron {{ $akun->terakhir_sinkron->diffForHumans() }}</p>
                @endif
                @if ($akun->error_terakhir)
                    <p class="mt-1 text-xs text-red-600">{{ $akun->error_terakhir }}</p>
                @endif
            @elseif ($google['aktif'])
                <p class="text-xs text-gray-500">Disposisi yang masuk muncul di Google Calendar Anda, lengkap dengan notifikasi di HP.</p>
            @else
                <p class="text-xs text-gray-500">Belum diaktifkan admin. Kalender di aplikasi tetap bisa dipakai.</p>
            @endif
        </div>
    </div>

    <div class="{{ $ringkas ? 'mt-3 sm:mt-0 sm:shrink-0' : 'mt-3' }} flex flex-wrap gap-2">
        @if ($akun)
            <form method="POST" action="{{ route('kalender.google.sinkron') }}" class="flex-1">
                @csrf
                <button class="kd-btn-soft w-full">Sinkronkan</button>
            </form>
            @if ($akun->error_terakhir)
                <a href="{{ route('kalender.google.hubungkan') }}" class="kd-btn-primary flex-1">Hubungkan ulang</a>
            @endif
            <form method="POST" action="{{ route('kalender.google.putuskan') }}"
                onsubmit="return confirm('Putuskan Google Calendar? Disposisi baru tidak dikirim ke Google lagi.')">
                @csrf
                <button class="kd-btn-ghost text-red-600 hover:bg-red-50">Putuskan</button>
            </form>
        @elseif ($google['aktif'])
            <a href="{{ route('kalender.google.hubungkan') }}" class="kd-btn-outline {{ $ringkas ? 'w-full sm:w-auto' : 'w-full' }}">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2zm0 16H5V10h14v10z" />
                </svg>
                Hubungkan Google Calendar
            </a>
        @endif
    </div>
</div>
