<?php

namespace App\Providers;

use App\Auth\TenantAwareUserProvider;
use App\Http\Middleware\InitializeTenancyForAssets;
use App\Support\AppUrl;
use App\Support\TenancyMode;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;
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
        if (TenancyMode::usesSingleDatabase()) {
            $this->loadMigrationsFrom(database_path('migrations/tenant'));
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensureValidConsoleRequest();

        Auth::provider('tenant_eloquent', function ($app, array $config) {
            return new TenantAwareUserProvider($app['hash'], $config['model']);
        });

        Authenticate::redirectUsing(function (Request $request) {
            return route('login');
        });

        RedirectIfAuthenticated::redirectUsing(function (Request $request) {
            if ($request->is('admin', 'admin/*')) {
                return '/admin';
            }

            return route('dashboard');
        });

        TenantAssetsController::$tenancyMiddleware = InitializeTenancyForAssets::class;

        Route::middleware('web')
            ->get('/tenancy/assets/{path?}', [TenantAssetsController::class, 'asset'])
            ->where('path', '(.*)')
            ->name('stancl.tenancy.asset');
    }

    /**
     * Artisan on some hosts builds a request with no valid scheme; tenancy bootstrapping
     * then fails when code parses the request URI (e.g. during tenants:migrate).
     */
    protected function ensureValidConsoleRequest(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        /** @var Request $request */
        $request = $this->app->make('request');

        try {
            $request->uri();
        } catch (\Throwable) {
            $this->app->instance('request', Request::create(AppUrl::base().'/', 'GET'));
        }
    }
}
