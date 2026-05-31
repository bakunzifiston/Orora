<?php

use App\Http\Controllers\AnimalController;
use App\Http\Controllers\BirthRecordController;
use App\Http\Controllers\BreedingModuleController;
use App\Http\Controllers\BreedingRecordController;
use App\Http\Controllers\PregnancyCheckController;
use App\Http\Controllers\Api\RwandaLocationController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseModuleController;
use App\Http\Controllers\ExpenseVendorController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\FeedingController;
use App\Http\Controllers\FeedingModuleController;
use App\Http\Controllers\FeedingScheduleController;
use App\Http\Controllers\FeedInventoryController;
use App\Http\Controllers\FeedSupplierController;
use App\Http\Controllers\FeedTypeController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\LivestockController;
use App\Http\Controllers\MilkModuleController;
use App\Http\Controllers\MilkRecordController;
use App\Http\Controllers\MilkSaleController;
use App\Http\Controllers\MilkSessionController;
use App\Http\Controllers\MilkStorageController;
use App\Http\Controllers\MortalityController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\VaccinationController;
use App\Http\Controllers\VetVisitController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('api/rwanda')->name('api.rwanda.')->group(function () {
        Route::get('provinces', [RwandaLocationController::class, 'provinces'])->name('provinces');
        Route::get('districts', [RwandaLocationController::class, 'districts'])->name('districts');
        Route::get('sectors', [RwandaLocationController::class, 'sectors'])->name('sectors');
        Route::get('cells', [RwandaLocationController::class, 'cells'])->name('cells');
        Route::get('villages', [RwandaLocationController::class, 'villages'])->name('villages');
    });

    Route::resource('farms', FarmController::class);
    Route::resource('livestock', LivestockController::class);
    Route::resource('animals', AnimalController::class);
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
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', [ExpenseModuleController::class, 'overview'])->name('overview');
        Route::redirect('/index', '/expenses');
        Route::get('/categories', [ExpenseCategoryController::class, 'index'])->name('categories');
        Route::resource('categories', ExpenseCategoryController::class)->except(['show', 'index'])->parameters(['categories' => 'category']);
        Route::get('/vendors', [ExpenseVendorController::class, 'index'])->name('vendors');
        Route::resource('vendors', ExpenseVendorController::class)->except(['show', 'index']);
        Route::get('/records', [ExpenseController::class, 'index'])->name('records');
        Route::resource('records', ExpenseController::class)->except(['show', 'index'])->parameters(['records' => 'expense']);
    });
    Route::prefix('milk')->name('milk.')->group(function () {
        Route::get('/', [MilkModuleController::class, 'overview'])->name('overview');
        Route::redirect('/index', '/milk');
        Route::get('/sessions', [MilkSessionController::class, 'index'])->name('sessions');
        Route::resource('sessions', MilkSessionController::class)->except(['show', 'index'])->parameters(['sessions' => 'milkSession']);
        Route::post('/sessions/{milkSession}/complete', [MilkSessionController::class, 'complete'])->name('sessions.complete');
        Route::post('/sessions/{milkSession}/cancel', [MilkSessionController::class, 'cancel'])->name('sessions.cancel');
        Route::post('/sessions/{milkSession}/records', [MilkRecordController::class, 'store'])->name('sessions.records.store');
        Route::post('/sessions/{milkSession}/records/bulk', [MilkRecordController::class, 'bulkStore'])->name('sessions.records.bulk');
        Route::put('/records/{milkRecord}', [MilkRecordController::class, 'update'])->name('records.update');
        Route::delete('/records/{milkRecord}', [MilkRecordController::class, 'destroy'])->name('records.destroy');
        Route::get('/storage', [MilkStorageController::class, 'index'])->name('storage');
        Route::resource('storage', MilkStorageController::class)->except(['show', 'index'])->parameters(['storage' => 'milkStorage']);
        Route::get('/sales', [MilkSaleController::class, 'index'])->name('sales');
        Route::resource('sales', MilkSaleController::class)->except(['show', 'index'])->parameters(['sales' => 'milkSale']);
        Route::post('/sales/{milkSale}/confirm', [MilkSaleController::class, 'confirm'])->name('sales.confirm');
        Route::post('/sales/{milkSale}/items', [MilkSaleController::class, 'storeItem'])->name('sales.items.store');
        Route::post('/sales/{milkSale}/payments', [MilkSaleController::class, 'storePayment'])->name('sales.payments.store');
    });
    Route::prefix('breeding')->name('breeding.')->group(function () {
        Route::get('/', [BreedingModuleController::class, 'overview'])->name('overview');
        Route::redirect('/index', '/breeding');
        Route::get('/records', [BreedingRecordController::class, 'index'])->name('records');
        Route::resource('records', BreedingRecordController::class)->except(['show', 'index'])->parameters(['records' => 'breedingRecord']);
        Route::get('/checks', [PregnancyCheckController::class, 'index'])->name('checks');
        Route::get('/checks/create', [PregnancyCheckController::class, 'create'])->name('checks.create');
        Route::post('/checks', [PregnancyCheckController::class, 'store'])->name('checks.store');
        Route::get('/births', [BirthRecordController::class, 'index'])->name('births');
        Route::get('/births/create', [BirthRecordController::class, 'create'])->name('births.create');
        Route::post('/births', [BirthRecordController::class, 'store'])->name('births.store');
        Route::get('/births/{birthRecord}/edit', [BirthRecordController::class, 'edit'])->name('births.edit');
        Route::put('/births/{birthRecord}/offspring/{offspring}', [BirthRecordController::class, 'updateOffspring'])->name('births.offspring.update');
        Route::post('/births/{birthRecord}/offspring/{offspring}/register', [BirthRecordController::class, 'registerOffspring'])->name('births.offspring.register');
    });
    Route::resource('certificates', CertificateController::class)->except(['show']);
    Route::resource('movements', MovementController::class)->except(['show']);
    Route::resource('sales', SaleController::class)->except(['show']);
});
