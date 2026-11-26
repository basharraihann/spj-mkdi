{{-- Kartu status Google Calendar (lewat Apps Script). Variabel: $google (aktif, akun), $ringkas (bool, versi kecil)
--}}
@php
    $ringkas = $ringkas ?? false;
    $admin = (bool) auth()->user()?->isAdmin();
    $aktif = $google['aktif'];
    $status = \App\Services\GoogleKalenderService::status();
    $mengujiSaja = filled(config('google_kalender.email_uji'));
@endphp

<div class="kd-card {{ $ringkas ? 'p-4 sm:flex sm:items-center sm:justify-between sm:gap-4' : 'p-4 sm:p-5' }}">
    <div class="flex items-start gap-3">
        <div
            class="h-9 w-9 shrink-0 rounded-xl flex items-center justify-center {{ $aktif && !$status['error'] ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-gray-900">Google Calendar</p>

            @if ($admin)
                @if ($aktif)
                    <p class="text-xs text-gray-500 break-words">
                        Aktif lewat Apps Script
                        @if (filled(config('google_kalender.akun_pusat')))
                            (akun pusat: <b class="text-gray-700">{{ config('google_kalender.akun_pusat') }}</b>)
                        @endif
                    </p>
                    @if ($status['terakhir_sinkron'])
                        <p class="text-[11px] text-gray-400">Sinkron {{ $status['terakhir_sinkron']->diffForHumans() }}</p>
                    @endif
                    @if ($status['error'])
                        <p class="mt-1 text-xs text-red-600">{{ $status['error'] }}</p>
                    @endif
                @else
                    <p class="text-xs text-gray-500">Isi GOOGLE_SCRIPT_URL dan GOOGLE_SCRIPT_RAHASIA di .env untuk mengaktifkan.
                    </p>
                @endif
                @if ($mengujiSaja)
                    <p class="mt-1 text-[11px] text-amber-600">Mode uji: undangan hanya dikirim ke email di GOOGLE_EMAIL_UJI.
                    </p>
                @endif
            @else
                @if ($aktif)
                    <p class="text-xs text-gray-500 break-words">Disposisi untuk Anda dikirim sebagai undangan Google Calendar
                        ke <b class="text-gray-700">{{ auth()->user()->email }}</b>. Tidak perlu menghubungkan apa pun.</p>
                @else
                    <p class="text-xs text-gray-500">Belum diaktifkan admin. Kalender di aplikasi tetap bisa dipakai.</p>
                @endif
            @endif
        </div>
    </div>

    @if ($admin && $aktif)
        <div class="{{ $ringkas ? 'mt-3 sm:mt-0 sm:shrink-0' : 'mt-3' }} flex flex-wrap gap-2">
            <form method="POST" action="{{ route('kalender.google.sinkron') }}" class="flex-1">
                @csrf
                <button class="kd-btn-soft w-full">Sinkronkan</button>
            </form>
        </div>
    @endif
</div>