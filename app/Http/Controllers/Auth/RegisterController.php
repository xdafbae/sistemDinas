<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
// use App\Providers\RouteServiceProvider; // Bisa digunakan jika ada

class RegisterController extends Controller
{
    // Redirect ke mana setelah registrasi berhasil dan login
    // Anda bisa mengganti '/home' dengan route tujuan Anda
    protected string $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        // Otomatis login user setelah registrasi
        Auth::login($user);

        return redirect($this->redirectTo);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'nama' => ['required', 'string', 'max:255'],
            'gol' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'nip' => ['nullable', 'string', 'max:50', 'unique:users,nip'], // NIP unik jika diisi
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], // 'confirmed' butuh field password_confirmation
        ]);
    }

    protected function create(array $data): User
    {
        $user = User::create([
            'nama' => $data['nama'],
            'gol' => $data['gol'] ?? null,
            'jabatan' => $data['jabatan'] ?? null,
            'nip' => $data['nip'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Assign role default 'pegawai' jika ada. Sesuaikan jika perlu.
        if (Role::where('name', 'pegawai')->exists()) {
            $user->assignRole('pegawai');
        }

        return $user;
    }
}