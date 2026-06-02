<?php

namespace App\Providers;

use App\Auth\TenantAwareUserProvider;
use App\Http\Middleware\InitializeTenancyForAssets;
use Illuminate\Support\Facades\Auth;
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
        Auth::provider('tenant_eloquent', function ($app, array $config) {
            return new TenantAwareUserProvider($app['hash'], $config['model']);
        });

        TenantAssetsController::$tenancyMiddleware = InitializeTenancyForAssets::class;

        Route::middleware('web')
            ->get('/tenancy/assets/{path?}', [TenantAssetsController::class, 'asset'])
            ->where('path', '(.*)')
            ->name('stancl.tenancy.asset');
    }
}
