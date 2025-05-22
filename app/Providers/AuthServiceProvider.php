<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Implicitly grant "Super Admin" role all permissions
        // This works granting all abilities to a user that has the "superadmin" role
        Gate::before(function ($user, $ability) {
            // Pastikan nama role 'superadmin' ditulis dengan benar (case-sensitive)
            // dan sesuai dengan yang ada di database Anda.
            if ($user->hasRole('superadmin')) {
                return true; // Super Admin bisa melakukan apa saja
            }
            return null; // Biarkan pengecekan permission/policy lain berjalan untuk role lain
        });
    }
}
