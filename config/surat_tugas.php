<?php

return [
    'instansi' => 'KEMENTERIAN KOORDINATOR BIDANG PANGAN',
    'sub_instansi' => 'REPUBLIK INDONESIA',
    'alamat' => 'Graha Mandiri, Jl. Imam Bonjol No. 61, Jakarta Pusat 10310',
    'email' => 'kemenkopangan@kemenkopangan.go.id',

    // Taruh file logo Garuda di public/images/logo-kemenko.png
    'logo' => 'images/logo.png',

    'kode_klasifikasi' => 'KA.00.04',
    'tempat' => 'Jakarta',
    'kepada_default' => 'Para Pejabat/Staf yang tercantum pada lampiran Surat Tugas ini.',

    'penandatangan' => [
        'jabatan' => 'Kepala Biro Manajemen Kinerja, Data dan Informasi',
        'nama' => 'Achmad Murman',
        'nip' => null,
    ],

    // Template ST 2026 (format: lampiran, tanpa_lampiran, an_sesdep)
    'nama_deputi' => 'Deputi Bidang Koordinasi Usaha Pangan dan Pertanian',
    'tembusan_default' => ['Deputi Bidang Koordinasi Usaha Pangan dan Pertanian'],
    'tembusan_an_sesdep' => ['Sekretaris Kementerian Koordinator'],

    'untuk_default' => [
        'Selesai melaksanakan tugas agar menyampaikan laporan tertulis sesuai dengan ketentuan yang berlaku;',
        'Biaya yang diperlukan untuk kegiatan tersebut dibebankan pada Anggaran Kementerian Koordinator Bidang Pangan.',
    ],
];
