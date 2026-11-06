<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Sementara semua user yang login boleh. Nanti persempit ke admin sesdep / admin anggaran.
        Gate::define('kelola-anggaran', fn($user) => true);
    }
}