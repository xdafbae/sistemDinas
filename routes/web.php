<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;    // Pastikan controller ini ada jika Anda membuat auth manual
use App\Http\Controllers\Auth\RegisterController;  // Pastikan controller ini ada jika Anda membuat auth manual
use App\Http\Controllers\HomeController;           // Controller untuk dashboard/home
use App\Http\Controllers\PerjalananDinasController;           // Controller untuk dashboard/home
use App\Http\Controllers\Verifikasi\VerifikasiPerjalananDinasController;           // Controller untuk dashboard/home
use App\Http\Controllers\Persetujuan\PersetujuanPerjalananDinasController;          // Controller untuk dashboard/home
use App\Http\Controllers\DokumenPerjalananDinasController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Route untuk halaman utama/landing page (bisa diakses siapa saja)
Route::get('/', function () {
    // Jika user sudah login, arahkan ke home, jika belum, tampilkan welcome page
    if (auth()->check()) {
        return redirect()->route('home');
    }
    return view('welcome'); // Pastikan view 'welcome.blade.php' ada
})->name('welcome');


// --- Rute Autentikasi Manual ---
Route::middleware('guest')->group(function () {
    // Aktifkan jika Anda memiliki fitur registrasi publik
    // Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    // Route::post('register', [RegisterController::class, 'register']);

    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
// --- Akhir Rute Autentikasi Manual ---


// --- Rute yang Memerlukan Autentikasi (Umum) ---
Route::middleware(['auth'])->group(function () {
    // Route untuk halaman home/dashboard setelah login
    // URL akan menjadi /home
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Route lain yang bisa diakses semua user terautentikasi bisa ditaruh di sini
    // Contoh: Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    // --- Rute Operator untuk Perjalanan Dinas ---
    Route::prefix('operator/perjalanan-dinas')
        ->name('operator.perjalanan-dinas.')
        ->middleware('role:operator|superadmin') // Pastikan role 'operator' ada dan di-assign
        ->group(function () {
            Route::get('/', [PerjalananDinasController::class, 'index'])->name('index');
            Route::get('/create', [PerjalananDinasController::class, 'create'])->name('create');
            Route::post('/', [PerjalananDinasController::class, 'store'])->name('store');
            Route::get('/{perjalananDinas}', [PerjalananDinasController::class, 'show'])->name('show'); // Route model binding
            Route::get('/{perjalananDinas}/edit', [PerjalananDinasController::class, 'edit'])->name('edit');
            Route::put('/{perjalananDinas}', [PerjalananDinasController::class, 'update'])->name('update');
            Route::delete('/{perjalananDinas}', [PerjalananDinasController::class, 'destroy'])->name('destroy');

            // Opsional: Jika Anda membuat endpoint AJAX terpisah untuk hitung estimasi
            // Route::post('/hitung-estimasi', [PerjalananDinasController::class, 'hitungEstimasiAjax'])->name('hitung-estimasi-ajax');
        });
    // --- Akhir Rute Operator ---

    Route::prefix('verifikator/perjalanan-dinas')
        ->name('verifikator.perjalanan-dinas.')
        ->middleware('role:verifikator|superadmin') // Pastikan role 'verifikator' ada
        ->group(function () {
            Route::get('/', [VerifikasiPerjalananDinasController::class, 'index'])->name('index');
            Route::get('/{perjalananDinas}', [VerifikasiPerjalananDinasController::class, 'show'])->name('show'); // Route model binding
            Route::post('/{perjalananDinas}/process', [VerifikasiPerjalananDinasController::class, 'processVerification'])->name('process');
        });

    Route::middleware(['auth'])->group(function () {
        // ...

        // --- Rute Atasan untuk Perjalanan Dinas ---
        Route::prefix('atasan/perjalanan-dinas')
            ->name('atasan.perjalanan-dinas.')
            ->middleware(['auth', 'role:atasan']) // TAMBAHKAN |superadmin
            ->group(function () {
                Route::get('/', [PersetujuanPerjalananDinasController::class, 'index'])->name('index');
                Route::get('/{perjalananDinas}', [PersetujuanPerjalananDinasController::class, 'show'])->name('show');
                Route::post('/{perjalananDinas}/process', [PersetujuanPerjalananDinasController::class, 'processApproval'])->name('process');
            });
        // --- Akhir Rute Atasan ---
    });

    Route::prefix('dokumen-perjalanan-dinas')->name('dokumen.')->group(function () {
        Route::get('/', [DokumenPerjalananDinasController::class, 'index'])->name('index');
        // Tambahkan parameter {format?} opsional dengan default 'pdf'
        Route::get('/spt/{perjalananDinas}/download/{format?}', [DokumenPerjalananDinasController::class, 'downloadSPT'])->name('spt.download');
        Route::get('/sppd/{perjalananDinas}/download/{format?}', [DokumenPerjalananDinasController::class, 'downloadSPPD'])->name('sppd.download');
        Route::get('/spt/{perjalananDinas}/download-from-scratch', [DokumenPerjalananDinasController::class, 'downloadSPTDariNol'])->name('spt.download.fromscratch');
        Route::get('/sppd/{perjalananDinas}/download-from-scratch', [DokumenPerjalananDinasController::class, 'downloadSPPDDariNol'])->name('sppd.download.fromscratch');
    });
});
// --- Akhir Rute yang Memerlukan Autentikasi (Umum) ---


// --- Rute Administrasi (Memerlukan Autentikasi DAN Permission Spesifik) ---
Route::middleware(['auth', 'can:manage users']) // Pertama cek login, lalu cek permission
    ->prefix('admin')                         // URL: /admin/...
    ->name('admin.')                          // Nama route: admin....
    ->group(function () {

        Route::resource('users', UserController::class);
    });
// --- Akhir Rute Administrasi ---