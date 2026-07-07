<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Api\RwandaLocationController;
use App\Http\Controllers\Central\AdminDashboardController;
use App\Http\Controllers\Central\Auth\AdminLoginController;
use App\Http\Controllers\Central\ContactMessageController;
use App\Http\Controllers\Central\MarketplaceAdminController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\UserDirectoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\ForgetTenantForCentralAdmin;
use App\Http\Middleware\InitializeDefaultTenant;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Main app (session-based tenant) — login, register, modules
|--------------------------------------------------------------------------
|
| By default (TENANCY_DOMAIN_ROUTES=false) these routes apply on any host,
| e.g. ororafarm.com. Set TENANCY_DOMAIN_ROUTES=true to scope them to
| central_domains only and use routes/tenant.php for subdomain tenants.
|
*/

$registerMarketplaceRoutes = function (): void {
    require __DIR__.'/marketplace.php';
};

$registerAppRoutes = function (): void {
    Route::middleware(InitializeDefaultTenant::class)->group(function () {
        Route::middleware('guest')->group(function () {
            Route::get('/login', [LoginController::class, 'create'])->name('login');
            Route::post('/login', [LoginController::class, 'store'])->name('login.store');
            Route::get('/register', [RegisterController::class, 'create'])->name('register');
            Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
        });

        Route::middleware('auth')->group(function () {
            Route::get('/dashboard', DashboardController::class)->name('dashboard');
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

            require __DIR__.'/modules.php';
        });
    });

    Route::prefix('admin')->name('central.')->group(function () {
        Route::redirect('/login', '/login')->name('login');

        Route::middleware(['auth:admin', ForgetTenantForCentralAdmin::class])->group(function () {
            Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
            Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');

            Route::get('/users', [UserDirectoryController::class, 'index'])->name('users.index');
            Route::get('/users/farms/{farm}', [UserDirectoryController::class, 'show'])->name('users.show');

            Route::prefix('api/rwanda')->name('api.rwanda.')->group(function () {
                Route::get('districts', [RwandaLocationController::class, 'districts'])->name('districts');
            });

            Route::resource('tenants', TenantController::class)
                ->only(['index', 'create', 'store', 'destroy'])
                ->names([
                    'index' => 'tenants.index',
                    'create' => 'tenants.create',
                    'store' => 'tenants.store',
                    'destroy' => 'tenants.destroy',
                ]);

            Route::get('/marketplace', [MarketplaceAdminController::class, 'index'])->name('marketplace.index');
            Route::get('/contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
            Route::patch('/contact-messages/{contactMessage}', [ContactMessageController::class, 'update'])->name('contact-messages.update');
        });
    });
};

if (config('tenancy.enable_domain_routes', false)) {
    foreach (config('tenancy.central_domains', ['127.0.0.1', 'localhost']) as $domain) {
        Route::domain($domain)->group(function () use ($registerMarketplaceRoutes, $registerAppRoutes) {
            $registerMarketplaceRoutes();
            $registerAppRoutes();
        });
    }
} else {
    $registerMarketplaceRoutes();
    $registerAppRoutes();
}
