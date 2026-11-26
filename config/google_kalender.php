<?php

/*
|--------------------------------------------------------------------------
| Integrasi Google Calendar (modul Disposisi) - lewat Google Apps Script
|--------------------------------------------------------------------------
| Isi di .env:
|   GOOGLE_SCRIPT_URL=https://script.google.com/macros/s/xxxx/exec
|   GOOGLE_SCRIPT_RAHASIA=teks-acak-yang-sama-dengan-RAHASIA-di-script
|   GOOGLE_AKUN_PUSAT=basharraihann@gmail.com        (hanya label di halaman admin)
|   GOOGLE_EMAIL_UJI=uji1@gmail.com,uji2@gmail.com   (kosongkan saat produksi)
|
| GOOGLE_EMAIL_UJI diisi  = undangan HANYA dikirim ke email di daftar ini (mode uji).
| GOOGLE_EMAIL_UJI kosong = undangan dikirim ke semua penerima disposisi.
| GOOGLE_SCRIPT_URL kosong = integrasi nonaktif, fitur lain tetap jalan.
*/
return [
    'script_url' => env('GOOGLE_SCRIPT_URL'),
    'script_rahasia' => env('GOOGLE_SCRIPT_RAHASIA'),

    'akun_pusat' => env('GOOGLE_AKUN_PUSAT'),
    'email_uji' => env('GOOGLE_EMAIL_UJI'),

    'timezone' => env('GOOGLE_CALENDAR_TIMEZONE', 'Asia/Jakarta'),
];