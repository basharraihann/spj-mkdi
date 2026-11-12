<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Admin boleh semua. Gate dicek lewat Gate::before supaya
        // admin tidak pernah ditolak, walau Gate baru ditambah nanti.
        Gate::before(fn(User $user) => $user->isAdmin() ? true : null);

        // Ubah status memo (SAKTI/SP2D/batal) + aksi massal di Monitoring Memo,
        // plus aksi alur Keuangan Sesdep (K2, K3, Input SAKTI, Selesai OTP, putusan pengajuan).
        Gate::define('kelola-anggaran', fn(User $user) => $user->isTu());

        // Membuat, mengedit, menghapus, mengirim memo, dan mengajukan pengembalian.
        // Hanya operator unit: Keuangan Sesdep (TU) hanya memverifikasi dan memproses.
        // Admin tetap lolos lewat Gate::before.
        Gate::define('buat-memo', fn(User $user) => !$user->isTu());

        // Upload file Realisasi Anggaran + pemetaan RO -> Unit.
        Gate::define('upload-anggaran', fn(User $user) => $user->isTu());

        // Menu dan halaman Pegawai.
        Gate::define('kelola-pegawai', fn(User $user) => $user->isTu());
    }
}