<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AgendaPdfController;
use App\Http\Controllers\AnggaranController;
use App\Http\Controllers\AnggaranPemetaanController;
use App\Http\Controllers\BerkasFisikController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\KalenderDisposisiController;
use App\Http\Controllers\MakOptionController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\MemoNomorSettingController;
use App\Http\Controllers\MemoStatusController;
use App\Http\Controllers\MemoTahapController;
use App\Http\Controllers\MonitoringMemoController;
use App\Http\Controllers\NominatifController;
use App\Http\Controllers\NominatifCustomController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UnduhMassalController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

/*
|--------------------------------------------------------------------------
| Dashboard: semua role
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])
    ->controller(DashboardController::class)
    ->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        // Kalender agenda SPJ: hanya role yang melihat blok kalender di dashboard
        Route::get('/dashboard/kalender', 'kalender')
            ->middleware('role:user,admin,super_admin')
            ->name('dashboard.kalender');
    });

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile: semua role
    |--------------------------------------------------------------------------
    */
    Route::controller(ProfileController::class)
        ->prefix('profile')
        ->name('profile.')
        ->group(function () {
            Route::get('/', 'edit')->name('edit');
            Route::patch('/', 'update')->name('update');
            Route::delete('/', 'destroy')->name('destroy');
        });

    /*
    |--------------------------------------------------------------------------
    | Unduh massal: semua role
    | Keuangan: lintas unit. Unit kerja: hanya unitnya sendiri (dicek di controller).
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:user,tu_unit,tu_sekretariat,admin,super_admin')
        ->controller(UnduhMassalController::class)
        ->prefix('unduh-massal')
        ->name('unduh.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'unduh')->name('zip');
            Route::get('excel', 'excel')->name('excel');
        });

    /*
    |--------------------------------------------------------------------------
    | Agenda (SPJ): role user dan admin
    | (prefix 'agendas/{agenda}' dan name 'agendas.' otomatis ditambahkan ke semua
    |  route di grup ini, jadi path/nama di dalam grup JANGAN mengulang 'agendas')
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:user,admin,super_admin')->group(function () {

        Route::prefix('agendas/{agenda}')
            ->name('agendas.')
            ->group(function () {

                Route::controller(AgendaController::class)->group(function () {
                    // Step 2: rincian biaya
                    Route::get('peserta', 'pesertaForm')->name('peserta');
                    Route::post('peserta', 'pesertaStore')->name('peserta.store');

                    // Step 3: anggaran (MAK) & nomor memo
                    Route::get('anggaran', 'anggaranForm')->name('anggaran');
                    Route::post('anggaran', 'anggaranStore')->name('anggaran.store');

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
    });

    /*
    |--------------------------------------------------------------------------
    | Pegawai: role tu dan admin (role tu_unit, tu_sekretariat, admin, super_admin)
    | Dropdown pegawai di form agenda/memo memakai model langsung, bukan route ini.
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:tu_unit,tu_sekretariat,admin,super_admin')->group(function () {
        Route::post('pegawais/reorder', [PegawaiController::class, 'reorder'])->name('pegawais.reorder');
        Route::resource('pegawais', PegawaiController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Nomor MAK: role tu dan admin
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:tu_unit,tu_sekretariat,admin,super_admin')->group(function () {
        Route::resource('mak-options', MakOptionController::class)
            ->except(['show', 'create', 'edit']);
    });

    /*
    |--------------------------------------------------------------------------
    | Rekap nomor memo (PNS & Non PNS, dari seluruh agenda)
    | Lihat daftar dan PDF: semua user login.
    | Buat, edit, hapus: operator unit dan admin (role user, admin, super_admin).
    |--------------------------------------------------------------------------
    */
    Route::controller(MemoController::class)
        ->prefix('nomor-memo')
        ->name('memo.')
        ->group(function () {
            Route::middleware('role:user,tu_unit,tu_sekretariat,admin,super_admin')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('{memo}/pdf', 'pdf')->name('pdf');
            });

            Route::middleware('role:user,admin,super_admin')->group(function () {
                Route::get('berikutnya', 'nomorBerikutnya')->name('nomor-berikutnya');
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{memo}/edit', 'edit')->name('edit');
                Route::put('{memo}', 'update')->name('update');
                Route::delete('{memo}', 'destroy')->name('destroy');
            });
        });

    /*
    |--------------------------------------------------------------------------
    | Pengaturan nomor memo (format nomor & nomor awal): admin saja
    | Path sengaja 'pengaturan/nomor-memo' supaya tidak bentrok dengan
    | 'nomor-memo/{memo}'.
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,super_admin')
        ->controller(MemoNomorSettingController::class)
        ->prefix('pengaturan/nomor-memo')
        ->name('memo.nomor-setting')
        ->group(function () {
            Route::get('/', 'edit');
            Route::put('/', 'update')->name('.update');
        });

    /*
    |--------------------------------------------------------------------------
    | Approval memo: TU Sekretariat dan admin (role tu_sekretariat, admin, super_admin)
    | Role tu_sekretariat, admin, super_admin (sama dengan $user->canApproveMemo()).
    | {memo} = entry-12, agenda-3-pns, agenda-3-non_pns
    |--------------------------------------------------------------------------
    */
    Route::patch('nomor-memo/{memo}/status', [MemoStatusController::class, 'update'])
        ->middleware('role:tu_sekretariat,admin,super_admin')
        ->where('memo', 'entry-\d+|agenda-\d+-(?:pns|non_pns)')
        ->name('memo.status');

    // Alur tahap memo
    Route::controller(MemoTahapController::class)
        ->prefix('nomor-memo')
        ->name('memo.')
        ->group(function () {
            $pola = 'entry-\d+|agenda-\d+-(?:pns|non_pns)';

            // Operator unit (akses unit dicek di controller lewat scope unit)
            Route::middleware('role:user,admin,super_admin')->group(function () use ($pola) {
                Route::post('{memo}/kirim', 'kirim')->where('memo', $pola)->name('kirim');
                Route::post('{memo}/ajukan-kembali', 'ajukan')->where('memo', $pola)->name('ajukan');
            });

            // Keuangan Sesdep / TU Sekretariat
            Route::middleware('role:tu_sekretariat,admin,super_admin')->group(function () use ($pola) {
                Route::post('pengajuan/{pengajuan}/putusan', 'putusan')->whereNumber('pengajuan')->name('pengajuan.putusan');
                Route::post('{memo}/verifikasi', 'verifikasi')->where('memo', $pola)->name('verifikasi');
                Route::post('{memo}/input-sakti', 'inputSakti')->where('memo', $pola)->name('input-sakti');
                Route::post('{memo}/ubah-nomor-sakti', 'ubahNomorSakti')->where('memo', $pola)->name('ubah-nomor-sakti');
                Route::post('{memo}/selesai-otp', 'selesaiOtp')->where('memo', $pola)->name('selesai-otp');
                Route::post('{memo}/berkas-fisik', [BerkasFisikController::class, 'simpan'])
                    ->where('memo', $pola)
                    ->name('berkas-fisik');
                Route::post('{memo}/tarik-kembali', 'tarikKembali')
                    ->where('memo', $pola)
                    ->name('tarik-kembali');
            });
        });

    /*
    |--------------------------------------------------------------------------
    | Monitoring memo
    | Lihat: semua role. Aksi massal (approval): role tu_sekretariat, admin, super_admin.
    |--------------------------------------------------------------------------
    */
    Route::get('monitoring-memo', [MonitoringMemoController::class, 'index'])
        ->middleware('role:user,tu_unit,tu_sekretariat,admin,super_admin')
        ->name('monitoring.index');
    Route::post('monitoring-memo/status-massal', [MonitoringMemoController::class, 'statusMassal'])
        ->middleware('role:tu_sekretariat,admin,super_admin')
        ->name('monitoring.status-massal');

    /*
    |--------------------------------------------------------------------------
    | Nominatif (dari agenda + nominatif mandiri): role user dan admin
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:user,admin,super_admin')
        ->prefix('nominatif')
        ->name('nominatif.')
        ->group(function () {

            // Nominatif custom (harus di atas route {nominatif})
            Route::controller(NominatifCustomController::class)->group(function () {
                Route::get('custom/create', 'create')->name('custom.create');
                Route::post('custom', 'store')->name('custom.store');
                Route::get('{nominatif}/custom/edit', 'edit')->name('custom.edit');
                Route::put('{nominatif}/custom', 'update')->name('custom.update');
                Route::get('{nominatif}/custom/pdf', 'pdf')->name('custom.pdf');
            });

            // Nominatif honorarium
            Route::controller(NominatifController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('pilih', 'pilih')->name('pilih');
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{nominatif}/edit', 'edit')->name('edit');
                Route::put('{nominatif}', 'update')->name('update');
                Route::delete('{nominatif}', 'destroy')->name('destroy');
                Route::get('{nominatif}/pdf', 'pdf')->name('pdf');
            });
        });

    /*
    |--------------------------------------------------------------------------
    | Realisasi anggaran
    | - lihat dan export: semua role
    | - upload: admin saja
    | - pemetaan RO -> Unit: admin saja
    |--------------------------------------------------------------------------
    */
    Route::controller(AnggaranController::class)
        ->prefix('anggaran')
        ->name('anggaran.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('data', 'data')->name('data');
            Route::get('export', 'export')->name('export');

            Route::middleware('role:admin,super_admin')->group(function () {
                Route::get('upload', 'uploadForm')->name('upload');
                Route::post('upload', 'uploadStore')->name('upload.store');
                Route::get('upload/{upload}/file', 'download')->name('upload.file');
            });
        });

    Route::middleware('role:admin,super_admin')
        ->controller(AnggaranPemetaanController::class)
        ->prefix('anggaran/pemetaan')
        ->name('anggaran.pemetaan')
        ->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store')->name('.store');
            Route::put('{pemetaan}', 'update')->name('.update');
            Route::delete('{pemetaan}', 'destroy')->name('.destroy');
        });

    /*
    |--------------------------------------------------------------------------
    | Disposisi (Persuratan)
    | - menu Disposisi (input, semua disposisi, teruskan, hapus): TU Unit, Eselon II (+ admin)
    | - detail, berkas, lembar PDF, feedback, selesaikan: semua akun yang terlibat (dicek di controller)
    |--------------------------------------------------------------------------
    */
    Route::controller(DisposisiController::class)
        ->prefix('disposisi')
        ->name('disposisi.')
        ->group(function () {
            Route::middleware('role:tu_unit,eselon2,admin,super_admin')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('surat/create', 'create')->name('create');
                Route::post('surat', 'store')->name('store');
                Route::delete('surat/{surat}', 'destroy')->whereNumber('surat')->name('destroy');
                Route::post('{disposisi}/teruskan', 'teruskan')->whereNumber('disposisi')->name('teruskan');
            });

            Route::get('surat/{surat}', 'show')->whereNumber('surat')->name('show');
            Route::get('surat/{surat}/berkas', 'berkas')->whereNumber('surat')->name('berkas');
            Route::get('surat/{surat}/lembar', 'lembar')->whereNumber('surat')->name('lembar');
            Route::post('surat/{surat}/feedback', 'feedback')->whereNumber('surat')->name('feedback');

            Route::post('{disposisi}/selesai', 'selesai')->whereNumber('disposisi')->name('selesai');
            Route::get('{disposisi}/laporan-berkas', 'berkasLaporan')->whereNumber('disposisi')->name('laporan-berkas');
        });

    /*
    |--------------------------------------------------------------------------
    | Kalender Disposisi: semua akun
    | + hubungkan Google Calendar per akun (notifikasi disposisi di Google Calendar)
    |--------------------------------------------------------------------------
    */
    Route::controller(KalenderDisposisiController::class)
        ->prefix('kalender')
        ->name('kalender.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('events', 'events')->name('events');
            Route::get('surat/{surat}', 'detail')->whereNumber('surat')->name('detail');

            Route::get('google/hubungkan', 'googleHubungkan')->name('google.hubungkan');
            Route::get('google/callback', 'googleCallback')->name('google.callback');
            Route::post('google/sinkron', 'googleSinkron')->name('google.sinkron');
            Route::post('google/putuskan', 'googlePutuskan')->name('google.putuskan');
        });

    /*
    |--------------------------------------------------------------------------
    | User manajemen: admin saja
    | (route show dihapus karena UserController tidak punya method show())
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,super_admin')
        ->resource('users', UserController::class)
        ->except('show');
});

require __DIR__ . '/auth.php';