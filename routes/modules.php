<?php

use App\Http\Controllers\AnimalController;
use App\Http\Controllers\AbattoirDispatchController;
use App\Http\Controllers\BirthRecordController;
use App\Http\Controllers\BreedingModuleController;
use App\Http\Controllers\BreedingRecordController;
use App\Http\Controllers\PregnancyCheckController;
use App\Http\Controllers\Api\RwandaLocationController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CustomerAddressController;
use App\Http\Controllers\CustomerCommunicationController;
use App\Http\Controllers\CustomerContactController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerCreditController;
use App\Http\Controllers\CustomerModuleController;
use App\Http\Controllers\EmployeeAddressController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\EmployeeEmergencyContactController;
use App\Http\Controllers\EmployeeFarmAssignmentController;
use App\Http\Controllers\EmployeeModuleController;
use App\Http\Controllers\EmployeePayrollController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseModuleController;
use App\Http\Controllers\ExpenseVendorController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\FinanceModuleController;
use App\Http\Controllers\FeedCalculatorController;
use App\Http\Controllers\FeedingController;
use App\Http\Controllers\FeedingModuleController;
use App\Http\Controllers\FeedingScheduleController;
use App\Http\Controllers\FeedInventoryController;
use App\Http\Controllers\FeedSupplierController;
use App\Http\Controllers\FeedTypeController;
use App\Http\Controllers\DiseaseRecordController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\LivestockController;
use App\Http\Controllers\MilkModuleController;
use App\Http\Controllers\MilkRecordController;
use App\Http\Controllers\MilkSessionController;
use App\Http\Controllers\MilkStorageController;
use App\Http\Controllers\MortalityController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\SalesModuleController;
use App\Http\Controllers\SaleTransactionController;
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
        Route::get('/disease', [HealthController::class, 'disease'])->name('disease');
        Route::resource('disease', DiseaseRecordController::class)
            ->except(['show', 'index'])
            ->parameters(['disease' => 'diseaseRecord']);
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
        Route::prefix('calculator')->name('calculator.')->group(function () {
            Route::get('/', [FeedCalculatorController::class, 'index'])->name('index');
            Route::post('/calculate', [FeedCalculatorController::class, 'calculate'])->name('calculate');
            Route::get('/livestock', [FeedCalculatorController::class, 'getLivestock'])->name('livestock');
            Route::get('/animals', [FeedCalculatorController::class, 'getAnimals'])->name('animals');
        });
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
        Route::redirect('/sales', '/sales/transactions?type=milk_sale');
        Route::redirect('/sales/create', '/sales/transactions/create?type=milk_sale');
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
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SalesModuleController::class, 'overview'])->name('overview');
        Route::redirect('/index', '/sales');
        Route::redirect('/buyers', '/customers/directory');
        Route::get('/transactions', [SaleTransactionController::class, 'index'])->name('transactions');
        Route::get('/transactions/create', [SaleTransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions', [SaleTransactionController::class, 'store'])->name('transactions.store');
        Route::get('/transactions/{transaction}', [SaleTransactionController::class, 'show'])->name('transactions.show');
        Route::put('/transactions/{transaction}', [SaleTransactionController::class, 'update'])->name('transactions.update');
        Route::delete('/transactions/{transaction}', [SaleTransactionController::class, 'destroy'])->name('transactions.destroy');
        Route::post('/transactions/{transaction}/items', [SaleTransactionController::class, 'storeItem'])->name('transactions.items.store');
        Route::post('/transactions/{transaction}/payments', [SaleTransactionController::class, 'storePayment'])->name('transactions.payments.store');
        Route::post('/transactions/{transaction}/confirm', [SaleTransactionController::class, 'confirm'])->name('transactions.confirm');
        Route::post('/transactions/{transaction}/complete', [SaleTransactionController::class, 'complete'])->name('transactions.complete');
        Route::post('/transactions/{transaction}/cancel', [SaleTransactionController::class, 'cancel'])->name('transactions.cancel');
        Route::get('/abattoir', [AbattoirDispatchController::class, 'index'])->name('abattoir');
        Route::get('/abattoir/create', [AbattoirDispatchController::class, 'create'])->name('abattoir.create');
        Route::post('/abattoir', [AbattoirDispatchController::class, 'store'])->name('abattoir.store');
        Route::get('/abattoir/{abattoirDispatch}', [AbattoirDispatchController::class, 'show'])->name('abattoir.show');
        Route::post('/abattoir/{abattoirDispatch}/returns', [AbattoirDispatchController::class, 'storeReturn'])->name('abattoir.returns.store');
    });
    Route::redirect('/sales-legacy', '/sales/transactions');

    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerModuleController::class, 'overview'])->name('overview');
        Route::get('/directory', [CustomerController::class, 'directory'])->name('directory');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/communications', [CustomerCommunicationController::class, 'index'])->name('communications');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
        Route::post('/{customer}/contacts', [CustomerContactController::class, 'store'])->name('contacts.store');
        Route::put('/{customer}/contacts/{contact}', [CustomerContactController::class, 'update'])->name('contacts.update');
        Route::delete('/{customer}/contacts/{contact}', [CustomerContactController::class, 'destroy'])->name('contacts.destroy');
        Route::post('/{customer}/addresses', [CustomerAddressController::class, 'store'])->name('addresses.store');
        Route::delete('/{customer}/addresses/{address}', [CustomerAddressController::class, 'destroy'])->name('addresses.destroy');
        Route::post('/{customer}/communications', [CustomerCommunicationController::class, 'store'])->name('communications.store');
        Route::put('/{customer}/credit', [CustomerCreditController::class, 'update'])->name('credit.update');
    });

    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [FinanceModuleController::class, 'overview'])->name('overview');
        Route::get('/transactions', [FinanceModuleController::class, 'transactions'])->name('transactions');
        Route::get('/reports/profit-loss', [FinanceModuleController::class, 'profitLoss'])->name('reports.profit_loss');
        Route::get('/reports/cash-flow', [FinanceModuleController::class, 'cashFlow'])->name('reports.cash_flow');
    });

    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', [EmployeeModuleController::class, 'overview'])->name('overview');
        Route::get('/directory', [EmployeeController::class, 'directory'])->name('directory');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
        Route::post('/{employee}/addresses', [EmployeeAddressController::class, 'store'])->name('addresses.store');
        Route::delete('/{employee}/addresses/{address}', [EmployeeAddressController::class, 'destroy'])->name('addresses.destroy');
        Route::post('/{employee}/emergency-contacts', [EmployeeEmergencyContactController::class, 'store'])->name('emergency_contacts.store');
        Route::delete('/{employee}/emergency-contacts/{emergencyContact}', [EmployeeEmergencyContactController::class, 'destroy'])->name('emergency_contacts.destroy');
        Route::post('/{employee}/farm-assignments', [EmployeeFarmAssignmentController::class, 'store'])->name('farm_assignments.store');
        Route::delete('/{employee}/farm-assignments/{employeeFarmAssignment}', [EmployeeFarmAssignmentController::class, 'destroy'])->name('farm_assignments.destroy');
        Route::put('/{employee}/payroll', [EmployeePayrollController::class, 'update'])->name('payroll.update');
        Route::post('/{employee}/documents', [EmployeeDocumentController::class, 'store'])->name('documents.store');
        Route::delete('/{employee}/documents/{document}', [EmployeeDocumentController::class, 'destroy'])->name('documents.destroy');
    });
});
