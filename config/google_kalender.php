<?php

/*
|--------------------------------------------------------------------------
| Integrasi Google Calendar (modul Disposisi)
|--------------------------------------------------------------------------
| Isi di .env:
|   GOOGLE_CLIENT_ID=xxxx.apps.googleusercontent.com
|   GOOGLE_CLIENT_SECRET=xxxx
|   GOOGLE_REDIRECT_URI=https://domain-anda/kalender/google/callback
| Kosong = tombol "Hubungkan Google Calendar" dinonaktifkan, fitur lain tetap jalan.
*/
return [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),

    'timezone' => env('GOOGLE_CALENDAR_TIMEZONE', 'Asia/Jakarta'),

    // Pengingat (menit sebelum acara)
    'pengingat_berjam' => [1440, 60],   // acara berjam: H-1 dan 1 jam sebelum
    'pengingat_seharian' => [960],      // acara seharian: jam 08.00 H-1

    'scopes' => [
        'openid',
        'email',
        'https://www.googleapis.com/auth/calendar.events',
    ],
];
