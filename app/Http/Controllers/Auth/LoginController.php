<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
// use App\Providers\RouteServiceProvider; // Bisa digunakan jika ada
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected string $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        $loginValue = $request->input('login_identifier');
        $password = $request->input('password');
        $remember = $request->filled('remember');

        // Coba login dengan email
        if (Auth::attempt(['email' => $loginValue, 'password' => $password], $remember)) {
            $request->session()->regenerate();
            return redirect()->intended($this->redirectTo);
        }

        // Jika gagal dengan email, coba login dengan NIP
        // hanya untuk role 'pegawai' dan 'kepala dinas'
        $userByNip = User::where('nip', $loginValue)->first();

        if ($userByNip && $userByNip->hasAnyRole(['pegawai', 'kepala dinas'])) {
            if (Auth::attempt(['nip' => $loginValue, 'password' => $password], $remember)) {
                $request->session()->regenerate();
                return redirect()->intended($this->redirectTo);
            }
        }

        // Jika semua upaya login gagal
        return $this->sendFailedLoginResponse($request, $userByNip);
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'login_identifier' => 'required|string',
            'password' => 'required|string',
        ],[
            'login_identifier.required' => 'Kolom Email atau NIP wajib diisi.',
            'password.required' => 'Kolom Password wajib diisi.',
        ]);
    }

    protected function sendFailedLoginResponse(Request $request, ?User $userByNip)
    {
        $errors = [];
        $loginValue = $request->input('login_identifier');

        // Cek apakah input berupa NIP dan user ada tapi role tidak diizinkan
        // Asumsi NIP itu numerik dan panjang tertentu (sesuaikan)
        $isPotentiallyNip = is_numeric(str_replace(' ', '', $loginValue)) && strlen(str_replace(' ', '', $loginValue)) >= 5;

        if ($isPotentiallyNip && $userByNip && !$userByNip->hasAnyRole(['pegawai', 'kepala dinas'])) {
             $errors['login_identifier'] = 'Login dengan NIP tersebut tidak diizinkan untuk role Anda.';
        } else {
            // Error umum jika kredensial tidak cocok
            $errors['login_identifier'] = 'Email/NIP atau Password salah.';
        }

        throw ValidationException::withMessages($errors);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login'); // Redirect ke halaman utama setelah logout
    }
}