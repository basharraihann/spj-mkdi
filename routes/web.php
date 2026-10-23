<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AgendaPdfController;
use App\Http\Controllers\AnggaranController;
use App\Http\Controllers\AnggaranPemetaanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\LaporanStController;
use App\Http\Controllers\MakOptionController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\NominatifController;
use App\Http\Controllers\NominatifCustomController;
use App\Http\Controllers\NotaDinasController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\SuratTugasController;
use App\Http\Controllers\TugasSayaController;
use App\Http\Controllers\TusiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPegawaiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = Auth::user();
    if ($user && in_array($user->role, ['deputi-d2', 'asdep-d2'], true)) {
        return redirect()->route('surat-masuk.index');
    }
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])
    ->controller(DashboardController::class)
    ->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/dashboard/kalender', 'kalender')->name('dashboard.kalender');
    });

Route::middleware('auth')->group(function () {

    Route::controller(ProfileController::class)
        ->prefix('profile')
        ->name('profile.')
        ->group(function () {
            Route::get('/', 'edit')->name('edit');
            Route::patch('/', 'update')->name('update');
            Route::delete('/', 'destroy')->name('destroy');
        });

    // ==========================================
    // MODUL PERSURATAN & DISPOSISI BERJENJANG
    // ==========================================
    Route::resource('surat-masuk', SuratMasukController::class);

    Route::prefix('disposisi')
        ->name('disposisi.')
        ->controller(DisposisiController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{disposisi}', 'show')->name('show');
            Route::post('{disposisi}', 'store')->name('store');
        });

    Route::prefix('nota-dinas')
        ->name('nota-dinas.')
        ->controller(NotaDinasController::class)
        ->group(function () {
            Route::get('create/{disposisi}', 'create')->name('create');
            Route::post('{disposisi}', 'store')->name('store');
            Route::get('{notaDinas}', 'show')->name('show');
        });

    // ==========================================
    // MODUL SPJ & AGENDA EXISTING
    // ==========================================
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

    Route::post('pegawais/reorder', [PegawaiController::class, 'reorder'])->name('pegawais.reorder');
    Route::resource('pegawais', PegawaiController::class);

    Route::controller(MemoController::class)
        ->prefix('nomor-memo')
        ->name('memo.')
        ->group(function () {
            Route::get('berikutnya', 'nomorBerikutnya')->name('nomor-berikutnya');
            Route::get('create', 'create')->name('create');
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('{memo}/edit', 'edit')->name('edit');
            Route::put('{memo}', 'update')->name('update');
            Route::delete('{memo}', 'destroy')->name('destroy');
            Route::get('{memo}/pdf', 'pdf')->name('pdf');
        });

    Route::prefix('nominatif')
        ->name('nominatif.')
        ->group(function () {

            Route::controller(NominatifCustomController::class)->group(function () {
                Route::get('custom/create', 'create')->name('custom.create');
                Route::post('custom', 'store')->name('custom.store');
                Route::get('{nominatif}/custom/edit', 'edit')->name('custom.edit');
                Route::put('{nominatif}/custom', 'update')->name('custom.update');
                Route::get('{nominatif}/custom/pdf', 'pdf')->name('custom.pdf');
            });

            Route::controller(NominatifController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{nominatif}/edit', 'edit')->name('edit');
                Route::put('{nominatif}', 'update')->name('update');
                Route::delete('{nominatif}', 'destroy')->name('destroy');
                Route::get('{nominatif}/pdf', 'pdf')->name('pdf');
            });
        });

    Route::prefix('surat-tugas')
        ->name('surat-tugas.')
        ->controller(SuratTugasController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');

            Route::post('counter', 'setCounter')->middleware('admin')->name('counter');

            Route::post('nomor-berikutnya', 'nomorBerikutnya')->name('nomor-berikutnya');

            Route::get('{suratTugas}', 'show')->name('show');
            Route::get('{suratTugas}/edit', 'edit')->name('edit');
            Route::put('{suratTugas}', 'update')->name('update');
            Route::delete('{suratTugas}', 'destroy')->name('destroy');

            Route::patch('{suratTugas}/tusi', 'updateTusi')->name('tusi');

            Route::post('{suratTugas}/terbitkan', 'terbitkan')->name('terbitkan');

            Route::post('{suratTugas}/laporan', [LaporanStController::class, 'simpan'])->name('laporan.simpan');

            Route::get('{suratTugas}/laporan/berkas', [LaporanStController::class, 'berkas'])->name('laporan.berkas');

            Route::get('{suratTugas}/laporan/materi/{filename}', [LaporanStController::class, 'berkasMateri'])->name('laporan.materi');

            Route::get('{suratTugas}/pdf', 'pdf')->name('pdf');
            Route::get('{suratTugas}/data', 'data')->name('data');
        });

    Route::get('tugas-saya', [TugasSayaController::class, 'index'])->name('tugas-saya.index');

    Route::middleware('admin')
        ->resource('tusi', TusiController::class)
        ->except(['show', 'create', 'edit'])
        ->parameters(['tusi' => 'tusi']);

    Route::middleware(['auth'])->group(function () {
        Route::resource('mak-options', MakOptionController::class)
            ->except(['show', 'create', 'edit']);
    });

    Route::controller(AnggaranController::class)
        ->prefix('anggaran')
        ->name('anggaran.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('data', 'data')->name('data');
            Route::get('export', 'export')->name('export');

            Route::middleware('admin')->group(function () {
                Route::get('upload', 'uploadForm')->name('upload');
                Route::post('upload', 'uploadStore')->name('upload.store');

                Route::get('pemetaan', [AnggaranPemetaanController::class, 'index'])->name('pemetaan');
                Route::post('pemetaan', [AnggaranPemetaanController::class, 'store'])->name('pemetaan.store');
                Route::put('pemetaan/{pemetaan}', [AnggaranPemetaanController::class, 'update'])->name('pemetaan.update');
                Route::delete('pemetaan/{pemetaan}', [AnggaranPemetaanController::class, 'destroy'])->name('pemetaan.destroy');
            });
        });

    Route::middleware('admin')
        ->prefix('pemetaan-akun')
        ->name('pemetaan-akun.')
        ->controller(UserPegawaiController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('{user}', 'update')->name('update');
        });

    Route::middleware('admin')->resource('users', UserController::class);
});

require __DIR__ . '/auth.php';