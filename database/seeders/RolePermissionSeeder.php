<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat Permissions (sesuaikan dengan kebutuhan Anda)
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'view data penting']);
        // ... tambahkan permission lain

        // Buat Roles
        $superadmin = Role::create(['name' => 'superadmin']);
        $atasan = Role::create(['name' => 'atasan']);
        $verifikator = Role::create(['name' => 'verifikator']);
        $pegawai = Role::create(['name' => 'pegawai']);
        $operator = Role::create(['name' => 'operator']);
        $kepala_dinas = Role::create(['name' => 'kepala dinas']);

        // Beri permission ke roles (contoh)
        // Superadmin akan dapat semua via Gate
        $atasan->givePermissionTo('view data penting');
        // ... tambahkan assignment permission lain

        // Buat User Super Admin Contoh
        $adminUser = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'nama' => 'Super Admin',
                'nip' => '000000000000000000', // Pastikan unik atau null jika tidak ada
                'password' => Hash::make('password'), // Ganti dengan password aman
                'jabatan' => 'Administrator Sistem',
                'gol' => 'IV/e'
            ]
        );
        $adminUser->assignRole($superadmin);

        // Buat User Pegawai Contoh (untuk tes login NIP)
        $pegawaiUser = User::firstOrCreate(
            ['email' => 'pegawai@example.com'],
            [
                'nama' => 'Pegawai Contoh',
                'nip' => '123456789012345678', // NIP unik untuk tes
                'password' => Hash::make('password'),
                'jabatan' => 'Staf Pelaksana',
                'gol' => 'III/a'
            ]
        );
        $pegawaiUser->assignRole($pegawai);

        // Buat User Kepala Dinas Contoh (untuk tes login NIP)
        $kadisUser = User::firstOrCreate(
            ['email' => 'kadis@example.com'],
            [
                'nama' => 'Kepala Dinas Contoh',
                'nip' => '987654321098765432', // NIP unik untuk tes
                'password' => Hash::make('password'),
                'jabatan' => 'Kepala Dinas',
                'gol' => 'IV/c'
            ]
        );
        $kadisUser->assignRole($kepala_dinas);
    }
}