<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;    // Pastikan controller ini ada jika Anda membuat auth manual
use App\Http\Controllers\Auth\RegisterController;  // Pastikan controller ini ada jika Anda membuat auth manual
use App\Http\Controllers\HomeController;           // Controller untuk dashboard/home

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