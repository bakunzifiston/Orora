<?php

use App\Http\Controllers\Api\RwandaLocationController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\FeedInventoryController;
use App\Http\Controllers\FeedingController;
use App\Http\Controllers\FeedingModuleController;
use App\Http\Controllers\FeedingScheduleController;
use App\Http\Controllers\FeedSupplierController;
use App\Http\Controllers\FeedTypeController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\LivestockController;
use App\Http\Controllers\MortalityController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\VetVisitController;
use App\Http\Controllers\VaccinationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('api/rwanda')->name('api.rwanda.')->group(function () {
        Route::get('provinces', [RwandaLocationController::class, 'provinces'])->name('provinces');
        Route::get('districts', [RwandaLocationController::class, 'districts'])->name('districts');
        Route::get('sectors', [RwandaLocationController::class, 'sectors'])->name('sectors');
        Route::get('cells', [RwandaLocationController::class, 'cells'])->name('cells');
        Route::get('villages', [RwandaLocationController::class, 'villages'])->name('villages');
    });

    Route::resource('farms', FarmController::class)->except(['show']);
    Route::resource('livestock', LivestockController::class)->except(['show']);
    Route::resource('animals', AnimalController::class)->except(['show']);
    Route::prefix('health')->name('health.')->group(function () {
        Route::get('/', [HealthController::class, 'overview'])->name('overview');
        Route::redirect('/index', '/health');
        Route::get('/vaccinations', [HealthController::class, 'vaccinations'])->name('vaccinations');
        Route::resource('vaccinations', VaccinationController::class)->except(['show', 'index']);
        Route::get('/treatments', [HealthController::class, 'treatments'])->name('treatments');
        Route::resource('treatments', TreatmentController::class)->except(['show', 'index']);
        Route::get('/vet-visits', [HealthController::class, 'vetVisits'])->name('vet-visits');
        Route::resource('vet-visits', VetVisitController::class)
            ->except(['show', 'index'])
            ->parameters(['vet-visits' => 'vetVisit']);
        Route::get('/mortality', [HealthController::class, 'mortality'])->name('mortality');
        Route::resource('mortalities', MortalityController::class)->except(['show', 'index']);
        Route::get('/timeline', [HealthController::class, 'timeline'])->name('timeline');

        Route::resource('records', HealthRecordController::class)->except(['show', 'index']);
    });
    Route::prefix('feeding')->name('feeding.')->group(function () {
        Route::get('/', [FeedingModuleController::class, 'overview'])->name('overview');
        Route::redirect('/index', '/feeding');
        Route::get('/feed-types', [FeedTypeController::class, 'index'])->name('feed-types');
        Route::resource('feed-types', FeedTypeController::class)->except(['show', 'index'])->parameters(['feed-types' => 'feedType']);
        Route::get('/inventory', [FeedInventoryController::class, 'index'])->name('inventory');
        Route::resource('inventory', FeedInventoryController::class)->except(['show', 'index'])->parameters(['inventory' => 'feedInventory']);
        Route::post('/inventory/{feedInventory}/movements', [FeedInventoryController::class, 'storeMovement'])->name('inventory.movements.store');
        Route::get('/records', [FeedingController::class, 'index'])->name('records');
        Route::resource('records', FeedingController::class)->except(['show', 'index'])->parameters(['records' => 'feeding']);
        Route::get('/suppliers', [FeedSupplierController::class, 'index'])->name('suppliers');
        Route::resource('suppliers', FeedSupplierController::class)->except(['show', 'index']);
        Route::get('/schedules', [FeedingScheduleController::class, 'index'])->name('schedules');
        Route::resource('schedules', FeedingScheduleController::class)->except(['show', 'index']);
    });
    Route::redirect('/feedings', '/feeding/records');
    Route::redirect('/feedings/create', '/feeding/records/create');
    Route::resource('certificates', CertificateController::class)->except(['show']);
    Route::resource('movements', MovementController::class)->except(['show']);
    Route::resource('sales', SaleController::class)->except(['show']);
});
