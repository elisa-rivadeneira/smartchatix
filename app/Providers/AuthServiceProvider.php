<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define el Gate 'isAdmin'
        Gate::define('isAdmin', function ($user) {
            return $user->is_admin; // Cambia según tu lógica de administrador
        });
    }
}