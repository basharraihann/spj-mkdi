<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AgendaPdfController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])
    ->controller(DashboardController::class)
    ->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/dashboard/kalender', 'kalender')->name('dashboard.kalender');
    });

/*
|--------------------------------------------------------------------------
| Halaman yang butuh login
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Profile
    Route::controller(ProfileController::class)
        ->prefix('profile')
        ->name('profile.')
        ->group(function () {
            Route::get('/', 'edit')->name('edit');
            Route::patch('/', 'update')->name('update');
            Route::delete('/', 'destroy')->name('destroy');
        });

    // Agenda: peserta & dokumen
    Route::prefix('agendas/{agenda}')
        ->name('agendas.')
        ->group(function () {

            Route::controller(AgendaController::class)->group(function () {
                Route::get('peserta', 'pesertaForm')->name('peserta');
                Route::post('peserta', 'pesertaStore')->name('peserta.store');

                Route::get('dokumen', 'dokumenForm')->name('dokumen');
                Route::post('dokumen', 'dokumenStore')->name('dokumen.store');
                Route::post('dokumen/bulk', 'dokumenStoreAll')->name('dokumen.store-all');
                Route::delete('dokumen/{dokumen}', 'dokumenDestroy')->name('dokumen.destroy');
            });

            // Generate PDF (semua lewat AgendaPdfController)
            Route::controller(AgendaPdfController::class)->group(function () {
                Route::get('spd/{pegawai}', 'generateSpd')->name('spd');
                Route::get('nominatif/{status}', 'generateNominatif')->name('nominatif');
                Route::get('memorandum/{status}/pdf', 'memorandumPdf')->name('memorandum.pdf');
                Route::get('rincian-biaya/{pegawai}', 'generateRincianBiaya')->name('rincian-biaya');
                Route::get('pengeluaran-riil/{pegawai}', 'generatePengeluaranRiil')->name('pengeluaran-riil');
                Route::get('pegawai/{pegawai}/merge-pdf', 'generateMergedPdf')->name('merge-pdf');
            });
        });

    Route::resource('agendas', AgendaController::class);

    // Pegawai
    Route::post('pegawais/reorder', [PegawaiController::class, 'reorder'])->name('pegawais.reorder');
    Route::resource('pegawais', PegawaiController::class);

    // Rekap nomor memo (PNS & Non PNS, dari seluruh agenda)
    Route::controller(MemoController::class)
        ->prefix('nomor-memo')
        ->name('memo.')
        ->group(function () {
            Route::get('berikutnya', 'nomorBerikutnya')->name('nomor-berikutnya');
            Route::get('create', 'create')->name('create');
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
        });

    // User Manajemen (Khusus Admin)
    Route::middleware('admin')->resource('users', UserController::class);
});

require __DIR__ . '/auth.php';