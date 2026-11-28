<?php

namespace App\Notifications;

use App\Models\Disposisi;
use App\Models\DisposisiSurat;
use Illuminate\Notifications\Notification;

class DisposisiBaru extends Notification
{
    public function __construct(public Disposisi $disposisi)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        // Disposisi pertama dari TU ke Eselon II tidak punya instruksi:
        // itu permohonan disposisi. Yang sudah berisi instruksi adalah penunjukan ke bawahan.
        $permohonan = empty($this->disposisi->instruksi);

        // Pengelola (TU Unit, Eselon II, admin) membuka tab Daftar;
        // akun biasa membuka tab Jadwal Pegawai (agenda miliknya).
        $tab = DisposisiSurat::bolehKelola($notifiable) ? 'daftar' : 'jadwal';

        return [
            'judul' => $permohonan ? 'Permohonan disposisi baru' : 'Penunjukan disposisi',
            'pesan' => $this->disposisi->surat?->perihal ?? 'Surat',
            'surat_id' => $this->disposisi->surat_id,
            'url' => route('disposisi.index', [
                'tampil' => $tab,
                'buka' => $this->disposisi->surat_id,
            ], false),
        ];
    }
}