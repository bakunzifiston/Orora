<?php

namespace App\Providers;

use App\Http\Middleware\InitializeTenancyForAssets;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Controllers\TenantAssetsController;

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
    public function boot(): void
    {
        TenantAssetsController::$tenancyMiddleware = InitializeTenancyForAssets::class;

        Route::middleware('web')
            ->get('/tenancy/assets/{path?}', [TenantAssetsController::class, 'asset'])
            ->where('path', '(.*)')
            ->name('stancl.tenancy.asset');
    }
}
