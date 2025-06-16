<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\SbuController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PerjalananDinasController;
use App\Http\Controllers\DokumenPerjalananDinasController;
use App\Http\Controllers\LaporanPerjalananDinasPegawaiController;
use App\Http\Controllers\Verifikasi\VerifikasiPerjalananDinasController;
use App\Http\Controllers\Persetujuan\PersetujuanPerjalananDinasController;
use App\Http\Controllers\Auth\RegisterController; // Jika Anda menggunakannya

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Route untuk halaman utama/landing page
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('home');
    }
    return view('welcome');
})->name('welcome');

// --- Rute Autentikasi ---
Route::middleware('guest')->group(function () {
    // Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    // Route::post('register', [RegisterController::class, 'register']);
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});
Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
// --- Akhir Rute Autentikasi ---


// --- SEMUA ROUTE YANG MEMERLUKAN AUTENTIKASI MASUK KE GRUP INI ---
Route::middleware(['auth'])->group(function () {

    // Dashboard Utama
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // --- Rute Operator untuk Perjalanan Dinas ---
    Route::prefix('operator/perjalanan-dinas')
        ->name('operator.perjalanan-dinas.')
        ->middleware('role:operator|superadmin')
        ->group(function () {
            Route::get('/', [PerjalananDinasController::class, 'index'])->name('index');
            Route::get('/create', [PerjalananDinasController::class, 'create'])->name('create');
            Route::post('/', [PerjalananDinasController::class, 'store'])->name('store');
            Route::get('/{perjalananDinas}', [PerjalananDinasController::class, 'show'])->name('show');
            Route::get('/{perjalananDinas}/edit', [PerjalananDinasController::class, 'edit'])->name('edit');
            Route::put('/{perjalananDinas}', [PerjalananDinasController::class, 'update'])->name('update');
            Route::delete('/{perjalananDinas}', [PerjalananDinasController::class, 'destroy'])->name('destroy');
        });

    // --- Rute Verifikator untuk Perjalanan Dinas ---
    // --- Rute Verifikator ---
    // --- Rute Verifikator ---
    Route::prefix('verifikator')
        ->middleware('role:verifikator|superadmin')
        ->group(function () {
            // Grup untuk verifikasi SPT awal
            Route::prefix('perjalanan-dinas')
                ->name('verifikator.perjalanan-dinas.') // Hanya untuk SPT
                ->group(function () {
                    Route::get('/', [VerifikasiPerjalananDinasController::class, 'index'])->name('index');
                    Route::get('/{perjalananDinas}', [VerifikasiPerjalananDinasController::class, 'show'])->name('show');
                    Route::post('/{perjalananDinas}/process', [VerifikasiPerjalananDinasController::class, 'processVerification'])->name('process');
                });

            // Grup BARU untuk verifikasi Laporan Perjalanan Dinas
            Route::prefix('laporan-perjadin')
                ->name('verifikator.laporan-perjadin.') // Nama route baru yang lebih pendek
                ->group(function () {
                    Route::get('/', [VerifikasiPerjalananDinasController::class, 'indexLaporan'])->name('index'); // Sekarang menjadi verifikator.laporan-perjadin.index
                    Route::get('/{laporan}', [VerifikasiPerjalananDinasController::class, 'showLaporan'])->name('show');
                    Route::post('/{laporan}/process', [VerifikasiPerjalananDinasController::class, 'processLaporan'])->name('process');
                });
        });
    // --- Akhir Rute Verifikator ---
    // ...
});
// --- Rute Atasan untuk Perjalanan Dinas ---
// Grup middleware auth di dalam grup auth lain dihapus karena tidak perlu
Route::prefix('atasan/perjalanan-dinas')
    ->name('atasan.perjalanan-dinas.')
    ->middleware('role:atasan|kepala dinas|superadmin') // Kepala Dinas juga bisa jadi atasan
    ->group(function () {
        Route::get('/', [PersetujuanPerjalananDinasController::class, 'index'])->name('index');
        Route::get('/{perjalananDinas}', [PersetujuanPerjalananDinasController::class, 'show'])->name('show');
        Route::post('/{perjalananDinas}/process', [PersetujuanPerjalananDinasController::class, 'processApproval'])->name('process');
    });

// --- Rute Download Dokumen Perjalanan Dinas ---
Route::prefix('dokumen-perjalanan-dinas')->name('dokumen.')->group(function () {
    Route::get('/', [DokumenPerjalananDinasController::class, 'index'])->name('index');
    Route::get('/spt/{perjalananDinas}/download/{format?}', [DokumenPerjalananDinasController::class, 'downloadSPT'])->name('spt.download');
    Route::get('/sppd/{perjalananDinas}/download/{format?}', [DokumenPerjalananDinasController::class, 'downloadSPPD'])->name('sppd.download');
    // Route untuk "from scratch" bisa Anda hapus jika sudah menggunakan TemplateProcessor untuk Word
    // Route::get('/spt/{perjalananDinas}/download-from-scratch', [DokumenPerjalananDinasController::class, 'downloadSPTDariNol'])->name('spt.download.fromscratch');
    // Route::get('/sppd/{perjalananDinas}/download-from-scratch', [DokumenPerjalananDinasController::class, 'downloadSPPDDariNol'])->name('sppd.download.fromscratch');
});

// --- Rute Laporan SPT ---
Route::prefix('laporan')
    ->name('laporan.')
    ->middleware('role:superadmin|operator|atasan|verifikator|kepala dinas')
    ->group(function () {
        Route::get('/spt', [DokumenPerjalananDinasController::class, 'laporanSPT'])->name('spt.index');
        Route::get('/spt/data', [DokumenPerjalananDinasController::class, 'dataTableSPT'])->name('spt.data');
    });


// --- Rute Administrasi ---
Route::prefix('admin')->name('admin.')->group(function () {
    // Manajemen User (memerlukan permission 'manage users')
    Route::resource('users', UserController::class)->middleware('can:manage users');

    // Manajemen SBU (hanya superadmin atau yang punya permission 'manage sbu')
    Route::middleware('can:manage sbu') // Atau 'role:superadmin' jika lebih sederhana
        ->prefix('sbu')
        ->name('sbu.')
        ->group(function () {
            // Route resource untuk CRUD SBU
            // Jika SbuController Anda menggunakan 'SbuItem $sbuItem', maka gunakan parameters()
            Route::resource('/', SbuController::class)->except(['show'])->parameters(['' => 'sbuItem']);
            // Jika SbuController Anda menggunakan 'SbuItem $sbu', maka:
            // Route::resource('/', SbuController::class)->except(['show']);

            // Route untuk Import SBU
            Route::get('/import', [SbuController::class, 'showImportForm'])->name('import.form');
            Route::post('/import', [SbuController::class, 'importSbu'])->name('import.process');
            Route::get('/download-template', [SbuController::class, 'downloadSbuTemplate'])->name('download.template');
        });
});
// --- Akhir Rute Administrasi ---

// --- Rute Laporan Perjalanan Dinas oleh Pegawai ---
Route::prefix('pegawai/laporan-perjalanan-dinas')
    ->name('pegawai.laporan-perjadin.')
    // Middleware role 'pegawai' atau role lain yang berhak membuat laporan
    // Otorisasi lebih detail akan dilakukan di controller (memastikan user adalah pelaksana)
    ->group(function () {
        Route::get('/', [LaporanPerjalananDinasPegawaiController::class, 'index'])->name('index');
        // Menggunakan perjalananDinas ID untuk konsistensi URL dengan daftar perjalanan
        Route::get('/{perjalananDinas}/buat-atau-edit', [LaporanPerjalananDinasPegawaiController::class, 'createOrEdit'])->name('createOrEdit');
        Route::post('/{perjalananDinas}/simpan', [LaporanPerjalananDinasPegawaiController::class, 'storeOrUpdate'])->name('storeOrUpdate');
        Route::get('/{perjalananDinas}/lihat', [LaporanPerjalananDinasPegawaiController::class, 'showLaporan'])->name('show');
        // Menggunakan LaporanPerjalananDinas ID untuk submit
        Route::patch('/{laporan}/submit', [LaporanPerjalananDinasPegawaiController::class, 'submitLaporan'])->name('submit');
    });
