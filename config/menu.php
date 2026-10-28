<?php

return [
    'akses' => [
        'admin' => '*',
        'tu' => [
            'surat_masuk',
            'surat_keluar',
            'pegawais',
            'mak_options',
            'surat_tugas',
        ],
        'eselon1' => ['disposisi', 'monitoring'],
        'eselon2' => ['disposisi', 'monitoring', 'anggaran', 'surat_tugas'],
        'user' => ['dashboard', 'disposisi', 'tugas_saya', 'agendas', 'memo', 'nominatif', 'anggaran'],
    ],

    'beranda' => [
        'admin' => 'dashboard',
        'tu' => 'surat-masuk.index',
        'eselon1' => 'disposisi.index',
        'eselon2' => 'disposisi.index',
        'user' => 'tugas-saya.index',
    ],
];