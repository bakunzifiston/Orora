<?php

use App\Http\Controllers\Marketplace\AboutController;
use App\Http\Controllers\Marketplace\ContactController;
use App\Http\Controllers\Marketplace\MarketplaceHomeController;
use App\Http\Controllers\Marketplace\ShopController;
use App\Http\Controllers\Marketplace\TraceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public marketplace (ororafarm.com)
|--------------------------------------------------------------------------
*/

Route::get('/', [MarketplaceHomeController::class, 'index'])->name('marketplace.home');
Route::get('/about', [AboutController::class, 'index'])->name('marketplace.about');

Route::get('/shop', [ShopController::class, 'index'])->name('marketplace.shop');
Route::middleware('auth')->group(function () {
    Route::get('/shop/create', [ShopController::class, 'create'])->name('marketplace.shop.create');
    Route::post('/shop', [ShopController::class, 'store'])->name('marketplace.shop.store');
    Route::get('/shop/{listing}/edit', [ShopController::class, 'edit'])->name('marketplace.shop.edit');
    Route::put('/shop/{listing}', [ShopController::class, 'update'])->name('marketplace.shop.update');
    Route::delete('/shop/{listing}', [ShopController::class, 'destroy'])->name('marketplace.shop.destroy');
});
Route::get('/shop/{listing}', [ShopController::class, 'show'])->name('marketplace.shop.show');
Route::post('/shop/{listing}/inquiry', [ShopController::class, 'inquiry'])->name('marketplace.shop.inquiry');

Route::redirect('/learning', '/')->name('marketplace.learning');
Route::match(['get', 'post'], '/learning/subscribe', fn () => redirect('/'))->name('marketplace.learning.subscribe');
Route::redirect('/learning/category/{category}', '/')->name('marketplace.learning.category');
Route::redirect('/learning/{post}', '/')->name('marketplace.learning.show');
Route::get('/contact', [ContactController::class, 'index'])->name('marketplace.contact');
Route::post('/contact', [ContactController::class, 'store'])->name('marketplace.contact.store');

Route::middleware('throttle:30,1')->group(function () {
    Route::get('/trace', [TraceController::class, 'index'])->name('marketplace.trace');
    Route::post('/trace', [TraceController::class, 'lookup'])->name('marketplace.trace.lookup');
    Route::get('/trace/{animal}', [TraceController::class, 'show'])->name('marketplace.trace.show')->whereNumber('animal');
    Route::get('/trace/{animal}/pdf', [TraceController::class, 'pdf'])->name('marketplace.trace.pdf')->whereNumber('animal');
});
