<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\WasteManagementService;

class WasteManagementServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(WasteManagementService::class, function ($app) {
            return new WasteManagementService();
        });
    }

    public function boot()
    {
        //
    }
}
