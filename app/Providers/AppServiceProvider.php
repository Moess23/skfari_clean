<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Aimeos\Shop\Base\Support;


class AppServiceProvider extends ServiceProvider
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
    public function boot()
    {
        Gate::define('admin', function ($user, $class, $roles) {
            if (isset($user->superuser) && $user->superuser) {
                return true;
            }
            return app(Support::class)->checkUserGroup($user, $roles);
        });
    }
}
