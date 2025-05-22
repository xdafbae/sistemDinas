<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Jika menggunakan Sanctum
use Spatie\Permission\Traits\HasRoles; // Import trait Spatie


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles; // Tambahkan HasRoles

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'gol',
        'jabatan',
        'nomor_hp',
        'nip',
        'email',
        'password',
        'aktif'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    // app/Models/User.php
    protected $casts = [
        'email_verified_at' => 'datetime',
        'aktif' => 'boolean', // <-- TAMBAHKAN INI
        'password' => 'hashed', // Jika menggunakan Laravel 9+
    ];
}
