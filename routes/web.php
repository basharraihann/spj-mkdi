<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AgendaPdfController;
use App\Http\Controllers\AnggaranController;
use App\Http\Controllers\AnggaranPemetaanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MakOptionController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\MemoStatusController;
use App\Http\Controllers\MemoTahapController;
use App\Http\Controllers\MonitoringMemoController;
use App\Http\Controllers\NominatifController;
use App\Http\Controllers\NominatifCustomController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])
    ->controller(DashboardController::class)
    ->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/dashboard/kalender', 'kalender')->name('dashboard.kalender');
    });

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

    // Agenda: peserta, anggaran & dokumen
    // (prefix 'agendas/{agenda}' dan name 'agendas.' otomatis ditambahkan ke semua route di grup ini,
    //  jadi path/nama di dalam grup JANGAN mengulang 'agendas')
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

    // Pegawai: hanya TU dan admin (Gate kelola-pegawai).
    // Dropdown pegawai di form agenda/memo memakai model langsung, bukan route ini.
    Route::middleware('can:kelola-pegawai')->group(function () {
        Route::post('pegawais/reorder', [PegawaiController::class, 'reorder'])->name('pegawais.reorder');
        Route::resource('pegawais', PegawaiController::class);
    });

    // Rekap nomor memo (PNS & Non PNS, dari seluruh agenda).
    // Lihat daftar dan PDF: semua user login.
    // Buat, edit, hapus: operator unit dan admin (Gate buat-memo), bukan Keuangan Sesdep.
    Route::controller(MemoController::class)
        ->prefix('nomor-memo')
        ->name('memo.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{memo}/pdf', 'pdf')->name('pdf');

            Route::middleware('can:buat-memo')->group(function () {
                Route::get('berikutnya', 'nomorBerikutnya')->name('nomor-berikutnya');
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{memo}/edit', 'edit')->name('edit');
                Route::put('{memo}', 'update')->name('update');
                Route::delete('{memo}', 'destroy')->name('destroy');
            });
        });

    // Ubah status anggaran memo (outstanding -> sakti -> sp2d / batal).
    // Hanya TU (dan admin), diatur lewat Gate kelola-anggaran di AppServiceProvider.
    // {memo} memakai id yang sama dengan di daftar memo: entry-12, agenda-3-pns, agenda-3-non_pns
    Route::patch('nomor-memo/{memo}/status', [MemoStatusController::class, 'update'])
        ->middleware('can:kelola-anggaran')
        ->where('memo', 'entry-\d+|agenda-\d+-(?:pns|non_pns)')
        ->name('memo.status');

    // Alur tahap memo. {memo} = entry-12, agenda-3-pns, agenda-3-non_pns
    Route::controller(MemoTahapController::class)
        ->prefix('nomor-memo')
        ->name('memo.')
        ->group(function () {
            $pola = 'entry-\d+|agenda-\d+-(?:pns|non_pns)';

            // Operator unit (akses unit dicek di controller lewat scope unit)
            Route::middleware('can:buat-memo')->group(function () use ($pola) {
                Route::post('{memo}/kirim', 'kirim')->where('memo', $pola)->name('kirim');
                Route::post('{memo}/ajukan-kembali', 'ajukan')->where('memo', $pola)->name('ajukan');
            });

            // Keuangan Sesdep
            Route::middleware('can:kelola-anggaran')->group(function () use ($pola) {
                Route::post('pengajuan/{pengajuan}/putusan', 'putusan')->whereNumber('pengajuan')->name('pengajuan.putusan');
                Route::post('{memo}/verifikasi', 'verifikasi')->where('memo', $pola)->name('verifikasi');
                Route::post('{memo}/input-sakti', 'inputSakti')->where('memo', $pola)->name('input-sakti');
                Route::post('{memo}/selesai-otp', 'selesaiOtp')->where('memo', $pola)->name('selesai-otp');
            });
        });

    // Monitoring memo & anggaran (lihat: semua user login; aksi massal: Gate kelola-anggaran)
    Route::get('monitoring-memo', [MonitoringMemoController::class, 'index'])->name('monitoring.index');
    Route::post('monitoring-memo/status-massal', [MonitoringMemoController::class, 'statusMassal'])
        ->middleware('can:kelola-anggaran')
        ->name('monitoring.status-massal');

    // Rekap nominatif (dari agenda + nominatif mandiri tanpa agenda)
    Route::prefix('nominatif')
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
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{nominatif}/edit', 'edit')->name('edit');
                Route::put('{nominatif}', 'update')->name('update');
                Route::delete('{nominatif}', 'destroy')->name('destroy');
                Route::get('{nominatif}/pdf', 'pdf')->name('pdf');
            });
        });

    Route::resource('mak-options', MakOptionController::class)
        ->except(['show', 'create', 'edit']);

    // Monitor anggaran
    // - lihat: semua user login
    // - upload: TU (dan admin), lewat Gate upload-anggaran
    // - pemetaan RO -> Unit: TU (dan admin), lewat Gate upload-anggaran
    Route::controller(AnggaranController::class)
        ->prefix('anggaran')
        ->name('anggaran.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('data', 'data')->name('data');
            Route::get('export', 'export')->name('export');

            Route::middleware('can:upload-anggaran')->group(function () {
                Route::get('upload', 'uploadForm')->name('upload');
                Route::post('upload', 'uploadStore')->name('upload.store');
                Route::get('upload/{upload}/file', 'download')->name('upload.file');
            });

            // Pemetaan RO -> Unit (Deputi/Biro -> Asdep): TU dan admin
            Route::middleware('can:upload-anggaran')->group(function () {
                Route::get('pemetaan', [AnggaranPemetaanController::class, 'index'])->name('pemetaan');
                Route::post('pemetaan', [AnggaranPemetaanController::class, 'store'])->name('pemetaan.store');
                Route::put('pemetaan/{pemetaan}', [AnggaranPemetaanController::class, 'update'])->name('pemetaan.update');
                Route::delete('pemetaan/{pemetaan}', [AnggaranPemetaanController::class, 'destroy'])->name('pemetaan.destroy');
            });
        });

    // User Manajemen (Khusus Admin). Route show dihapus karena
    // UserController tidak punya method show().
    Route::middleware('admin')
        ->resource('users', UserController::class)
        ->except('show');
});

require __DIR__ . '/auth.php';