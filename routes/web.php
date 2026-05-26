<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\InitializeDefaultTenant;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central domain — login landing + admin
|--------------------------------------------------------------------------
*/

$centralDomains = config('tenancy.central_domains', ['127.0.0.1', 'localhost']);

foreach ($centralDomains as $domain) {
    Route::domain($domain)->group(function () {
        Route::middleware(InitializeDefaultTenant::class)->group(function () {
            Route::middleware('guest')->group(function () {
                Route::get('/', [LoginController::class, 'create'])->name('login');
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
            Route::get('/', function () {
                return redirect()->route('central.tenants.index');
            })->name('home');

            Route::resource('tenants', TenantController::class)
                ->only(['index', 'create', 'store', 'destroy'])
                ->names([
                    'index' => 'tenants.index',
                    'create' => 'tenants.create',
                    'store' => 'tenants.store',
                    'destroy' => 'tenants.destroy',
                ]);
        });
    });
}
