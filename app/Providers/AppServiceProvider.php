<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SupabaseAuth; // <-- ADD THIS

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(SupabaseAuth::class, function () {
            return new SupabaseAuth();
        });
    }

    public function boot(): void
    {
        //
    }
}
