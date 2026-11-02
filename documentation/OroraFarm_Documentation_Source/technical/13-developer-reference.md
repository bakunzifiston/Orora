# Developer Reference

Generated and verified against the codebase on 2026-11-02 (`php artisan route:list --json`: 375 routes; Laravel 13.11.2; PHP ^8.3).

## Project layout

| Path | Contents |
|---|---|
| `app/Http/Controllers` | web controllers (+ `Auth`, `Central` = platform admin, `Marketplace` = public site, `Api`, `Concerns` view-data traits) |
| `app/Http/Requests` | FormRequests (`Auth`, `Central`, `Marketplace`, `Concerns`) |
| `app/Http/Middleware` | tenant/species/locale/permission middleware |
| `app/Models` | Eloquent models (`Central/` for central-connection models, `Concerns/` for tenancy traits) |
| `app/Services` | business logic (sub-folders `Feeding`, `Finance`, `Milk`, `Marketplace`, `Species`, `Import`, `Export`, `ImportExport`, `Releases`) |
| `app/Observers`, `app/Notifications`, `app/Providers`, `app/Auth`, `app/Tenancy`, `app/Support` | observers, notifications, service providers, tenant-aware user provider, cPanel DB manager stubs, helpers |
| `config/` | `modules.php` (navigation and all enumerations), `species.php`, `workspace_access.php`, `finance.php`, `feed_calculator.php`, `record_views.php`, `releases.php`, `tenancy.php`, `localization.php`, `branding.php`, `marketplace.php`, `rwanda_map.php`, `admin.php`, `demo.php` |
| `routes/` | `web.php` (entry), `modules.php` (workspace modules, included inside `auth` group), `marketplace.php`, `tenant.php` (host-based tenancy, only when `TENANCY_DOMAIN_ROUTES=true`), `console.php` |
| `database/migrations` | central + tenant-tagging migrations; `database/migrations/tenant/` application schema (loaded into the same DB in single-database mode) |
| `lang/en`, `lang/rw`, `lang/*.json` | translations (`__()` keys are English sentences) |
| `resources/views`, `resources/js`, `resources/css` | Blade UI, page scripts, styles (Vite inputs listed in `vite.config.js`) |

## Routes by module

Method column omits `HEAD`. Middleware column omits `web` (applied to all) and shows: `tenant` = `InitializeDefaultTenant`, `species` = `EnsureSpeciesModule`, `module.access` = permission check, `workspace.admin`, `auth:admin`. Actions are relative to `App\Http\Controllers`.

### Auth, profile, dashboard, notifications, utilities

18 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/api/rwanda/cells` | `api.rwanda.cells` | `Api\RwandaLocationController@cells` | tenant, auth |
| GET | `/api/rwanda/districts` | `api.rwanda.districts` | `Api\RwandaLocationController@districts` | tenant, auth |
| GET | `/api/rwanda/provinces` | `api.rwanda.provinces` | `Api\RwandaLocationController@provinces` | tenant, auth |
| GET | `/api/rwanda/sectors` | `api.rwanda.sectors` | `Api\RwandaLocationController@sectors` | tenant, auth |
| GET | `/api/rwanda/villages` | `api.rwanda.villages` | `Api\RwandaLocationController@villages` | tenant, auth |
| GET | `/dashboard` | `dashboard` | `DashboardController` | tenant, auth, module.access |
| GET | `/locale/{locale}` | `locale.switch` | `LocaleController` | tenant |
| GET | `/login` | `login` | `Auth\LoginController@create` | tenant, guest |
| POST | `/login` | `login.store` | `Auth\LoginController@store` | tenant, guest |
| POST | `/logout` | `logout` | `Auth\LoginController@destroy` | tenant, auth |
| GET | `/notifications` | `notifications.index` | `NotificationController@index` | tenant, auth |
| POST | `/notifications/read-all` | `notifications.read-all` | `NotificationController@readAll` | tenant, auth |
| GET | `/notifications/{notification}/open` | `notifications.open` | `NotificationController@open` | tenant, auth |
| GET | `/profile` | `profile.edit` | `ProfileController@edit` | tenant, auth, module.access |
| PUT | `/profile` | `profile.update` | `ProfileController@update` | tenant, auth, module.access |
| GET | `/register` | `register` | `Auth\RegisterController@create` | tenant, guest |
| POST | `/register` | `register.store` | `Auth\RegisterController@store` | tenant, guest |
| GET | `/whats-new` | `whats-new.index` | `WhatsNewController@index` | tenant, auth |

### Workspace users and access

7 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/workspace/users` | `workspace.users.index` | `WorkspaceUserController@index` | tenant, auth, workspace.admin |
| POST | `/workspace/users` | `workspace.users.store` | `WorkspaceUserController@store` | tenant, auth, workspace.admin |
| GET | `/workspace/users/create` | `workspace.users.create` | `WorkspaceUserController@create` | tenant, auth, workspace.admin |
| PUT | `/workspace/users/{user}` | `workspace.users.update` | `WorkspaceUserController@update` | tenant, auth, workspace.admin |
| POST | `/workspace/users/{user}/activate` | `workspace.users.activate` | `WorkspaceUserController@activate` | tenant, auth, workspace.admin |
| POST | `/workspace/users/{user}/deactivate` | `workspace.users.deactivate` | `WorkspaceUserController@deactivate` | tenant, auth, workspace.admin |
| GET | `/workspace/users/{user}/edit` | `workspace.users.edit` | `WorkspaceUserController@edit` | tenant, auth, workspace.admin |

### Animals

16 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/animals` | `animals.index` | `AnimalController@index` | tenant, auth, module.access, species |
| POST | `/animals` | `animals.store` | `AnimalController@store` | tenant, auth, module.access, species |
| GET | `/animals/create` | `animals.create` | `AnimalController@create` | tenant, auth, module.access, species |
| GET | `/animals/export` | `animals.export` | `AnimalImportExportController@export` | tenant, auth, module.access, species |
| GET | `/animals/import` | `animals.import` | `AnimalImportExportController@create` | tenant, auth, module.access, species |
| POST | `/animals/import` | `animals.import.store` | `AnimalImportExportController@store` | tenant, auth, module.access, species |
| POST | `/animals/import/cancel` | `animals.import.cancel` | `AnimalImportExportController@cancel` | tenant, auth, module.access, species |
| POST | `/animals/import/confirm` | `animals.import.confirm` | `AnimalImportExportController@confirm` | tenant, auth, module.access, species |
| GET | `/animals/import/confirm-replace` | `animals.import.confirm-replace` | `AnimalImportExportController@confirmReplace` | tenant, auth, module.access, species |
| POST | `/animals/import/execute-replace` | `animals.import.execute-replace` | `AnimalImportExportController@executeReplace` | tenant, auth, module.access, species |
| GET | `/animals/import/preview` | `animals.import.preview` | `AnimalImportExportController@preview` | tenant, auth, module.access, species |
| GET | `/animals/import/template` | `animals.import.template` | `AnimalImportExportController@template` | tenant, auth, module.access, species |
| DELETE | `/animals/{animal}` | `animals.destroy` | `AnimalController@destroy` | tenant, auth, module.access, species |
| GET | `/animals/{animal}` | `animals.show` | `AnimalController@show` | tenant, auth, module.access, species |
| PUT, PATCH | `/animals/{animal}` | `animals.update` | `AnimalController@update` | tenant, auth, module.access, species |
| GET | `/animals/{animal}/edit` | `animals.edit` | `AnimalController@edit` | tenant, auth, module.access, species |

### Breeding

19 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/breeding` | `breeding.overview` | `BreedingModuleController@overview` | tenant, auth, module.access, species |
| GET | `/breeding/births` | `breeding.births` | `BirthRecordController@index` | tenant, auth, module.access, species |
| POST | `/breeding/births` | `breeding.births.store` | `BirthRecordController@store` | tenant, auth, module.access, species |
| GET | `/breeding/births/create` | `breeding.births.create` | `BirthRecordController@create` | tenant, auth, module.access, species |
| GET | `/breeding/births/{birthRecord}/edit` | `breeding.births.edit` | `BirthRecordController@edit` | tenant, auth, module.access, species |
| PUT | `/breeding/births/{birthRecord}/offspring/{offspring}` | `breeding.births.offspring.update` | `BirthRecordController@updateOffspring` | tenant, auth, module.access, species |
| POST | `/breeding/births/{birthRecord}/offspring/{offspring}/register` | `breeding.births.offspring.register` | `BirthRecordController@registerOffspring` | tenant, auth, module.access, species |
| GET | `/breeding/births/{record}` | `breeding.births.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |
| GET | `/breeding/checks` | `breeding.checks` | `PregnancyCheckController@index` | tenant, auth, module.access, species |
| POST | `/breeding/checks` | `breeding.checks.store` | `PregnancyCheckController@store` | tenant, auth, module.access, species |
| GET | `/breeding/checks/create` | `breeding.checks.create` | `PregnancyCheckController@create` | tenant, auth, module.access, species |
| ANY | `/breeding/index` | `breeding.` | `Illuminate\Routing\RedirectController` | tenant, auth, module.access, species |
| GET | `/breeding/records` | `breeding.records` | `BreedingRecordController@index` | tenant, auth, module.access, species |
| POST | `/breeding/records` | `breeding.records.store` | `BreedingRecordController@store` | tenant, auth, module.access, species |
| GET | `/breeding/records/create` | `breeding.records.create` | `BreedingRecordController@create` | tenant, auth, module.access, species |
| DELETE | `/breeding/records/{breedingRecord}` | `breeding.records.destroy` | `BreedingRecordController@destroy` | tenant, auth, module.access, species |
| PUT, PATCH | `/breeding/records/{breedingRecord}` | `breeding.records.update` | `BreedingRecordController@update` | tenant, auth, module.access, species |
| GET | `/breeding/records/{breedingRecord}/edit` | `breeding.records.edit` | `BreedingRecordController@edit` | tenant, auth, module.access, species |
| GET | `/breeding/records/{record}` | `breeding.records.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |

### Certificates

7 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/certificates` | `certificates.index` | `CertificateController@index` | tenant, auth, module.access |
| POST | `/certificates` | `certificates.store` | `CertificateController@store` | tenant, auth, module.access |
| GET | `/certificates/create` | `certificates.create` | `CertificateController@create` | tenant, auth, module.access |
| DELETE | `/certificates/{certificate}` | `certificates.destroy` | `CertificateController@destroy` | tenant, auth, module.access |
| PUT, PATCH | `/certificates/{certificate}` | `certificates.update` | `CertificateController@update` | tenant, auth, module.access |
| GET | `/certificates/{certificate}/edit` | `certificates.edit` | `CertificateController@edit` | tenant, auth, module.access |
| GET | `/certificates/{record}` | `certificates.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |

### Customers

20 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/customers` | `customers.overview` | `CustomerModuleController@overview` | tenant, auth, module.access |
| POST | `/customers` | `customers.store` | `CustomerController@store` | tenant, auth, module.access |
| GET | `/customers/communications` | `customers.communications` | `CustomerCommunicationController@index` | tenant, auth, module.access |
| GET | `/customers/create` | `customers.create` | `CustomerController@create` | tenant, auth, module.access |
| GET | `/customers/directory` | `customers.directory` | `CustomerController@directory` | tenant, auth, module.access |
| GET | `/customers/export` | `customers.export` | `CustomerImportExportController@export` | tenant, auth, module.access |
| GET | `/customers/import` | `customers.import` | `CustomerImportExportController@create` | tenant, auth, module.access |
| POST | `/customers/import` | `customers.import.store` | `CustomerImportExportController@store` | tenant, auth, module.access |
| GET | `/customers/import/template` | `customers.import.template` | `CustomerImportExportController@template` | tenant, auth, module.access |
| DELETE | `/customers/{customer}` | `customers.destroy` | `CustomerController@destroy` | tenant, auth, module.access |
| GET | `/customers/{customer}` | `customers.show` | `CustomerController@show` | tenant, auth, module.access |
| PUT | `/customers/{customer}` | `customers.update` | `CustomerController@update` | tenant, auth, module.access |
| POST | `/customers/{customer}/addresses` | `customers.addresses.store` | `CustomerAddressController@store` | tenant, auth, module.access |
| DELETE | `/customers/{customer}/addresses/{address}` | `customers.addresses.destroy` | `CustomerAddressController@destroy` | tenant, auth, module.access |
| POST | `/customers/{customer}/communications` | `customers.communications.store` | `CustomerCommunicationController@store` | tenant, auth, module.access |
| POST | `/customers/{customer}/contacts` | `customers.contacts.store` | `CustomerContactController@store` | tenant, auth, module.access |
| DELETE | `/customers/{customer}/contacts/{contact}` | `customers.contacts.destroy` | `CustomerContactController@destroy` | tenant, auth, module.access |
| PUT | `/customers/{customer}/contacts/{contact}` | `customers.contacts.update` | `CustomerContactController@update` | tenant, auth, module.access |
| PUT | `/customers/{customer}/credit` | `customers.credit.update` | `CustomerCreditController@update` | tenant, auth, module.access |
| GET | `/customers/{customer}/edit` | `customers.edit` | `CustomerController@edit` | tenant, auth, module.access |

### Eggs (poultry)

8 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/eggs` | `eggs.overview` | `EggModuleController@overview` | tenant, auth, module.access, species |
| GET | `/eggs/collections` | `eggs.collections` | `EggCollectionController@index` | tenant, auth, module.access, species |
| POST | `/eggs/collections` | `eggs.collections.store` | `EggCollectionController@store` | tenant, auth, module.access, species |
| GET | `/eggs/collections/create` | `eggs.collections.create` | `EggCollectionController@create` | tenant, auth, module.access, species |
| DELETE | `/eggs/collections/{eggCollection}` | `eggs.collections.destroy` | `EggCollectionController@destroy` | tenant, auth, module.access, species |
| PUT | `/eggs/collections/{eggCollection}` | `eggs.collections.update` | `EggCollectionController@update` | tenant, auth, module.access, species |
| GET | `/eggs/collections/{eggCollection}/edit` | `eggs.collections.edit` | `EggCollectionController@edit` | tenant, auth, module.access, species |
| GET | `/eggs/collections/{record}` | `eggs.collections.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |

### Employees

21 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/employees` | `employees.overview` | `EmployeeModuleController@overview` | tenant, auth, module.access |
| POST | `/employees` | `employees.store` | `EmployeeController@store` | tenant, auth, module.access |
| GET | `/employees/create` | `employees.create` | `EmployeeController@create` | tenant, auth, module.access |
| GET | `/employees/directory` | `employees.directory` | `EmployeeController@directory` | tenant, auth, module.access |
| GET | `/employees/export` | `employees.export` | `EmployeeImportExportController@export` | tenant, auth, module.access |
| GET | `/employees/import` | `employees.import` | `EmployeeImportExportController@create` | tenant, auth, module.access |
| POST | `/employees/import` | `employees.import.store` | `EmployeeImportExportController@store` | tenant, auth, module.access |
| GET | `/employees/import/template` | `employees.import.template` | `EmployeeImportExportController@template` | tenant, auth, module.access |
| DELETE | `/employees/{employee}` | `employees.destroy` | `EmployeeController@destroy` | tenant, auth, module.access |
| GET | `/employees/{employee}` | `employees.show` | `EmployeeController@show` | tenant, auth, module.access |
| PUT | `/employees/{employee}` | `employees.update` | `EmployeeController@update` | tenant, auth, module.access |
| POST | `/employees/{employee}/addresses` | `employees.addresses.store` | `EmployeeAddressController@store` | tenant, auth, module.access |
| DELETE | `/employees/{employee}/addresses/{address}` | `employees.addresses.destroy` | `EmployeeAddressController@destroy` | tenant, auth, module.access |
| POST | `/employees/{employee}/documents` | `employees.documents.store` | `EmployeeDocumentController@store` | tenant, auth, module.access |
| DELETE | `/employees/{employee}/documents/{document}` | `employees.documents.destroy` | `EmployeeDocumentController@destroy` | tenant, auth, module.access |
| GET | `/employees/{employee}/edit` | `employees.edit` | `EmployeeController@edit` | tenant, auth, module.access |
| POST | `/employees/{employee}/emergency-contacts` | `employees.emergency_contacts.store` | `EmployeeEmergencyContactController@store` | tenant, auth, module.access |
| DELETE | `/employees/{employee}/emergency-contacts/{emergencyContact}` | `employees.emergency_contacts.destroy` | `EmployeeEmergencyContactController@destroy` | tenant, auth, module.access |
| POST | `/employees/{employee}/farm-assignments` | `employees.farm_assignments.store` | `EmployeeFarmAssignmentController@store` | tenant, auth, module.access |
| DELETE | `/employees/{employee}/farm-assignments/{employeeFarmAssignment}` | `employees.farm_assignments.destroy` | `EmployeeFarmAssignmentController@destroy` | tenant, auth, module.access |
| PUT | `/employees/{employee}/payroll` | `employees.payroll.update` | `EmployeePayrollController@update` | tenant, auth, module.access |

### Expenses

23 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/expenses` | `expenses.overview` | `ExpenseModuleController@overview` | tenant, auth, module.access |
| GET | `/expenses/categories` | `expenses.categories` | `ExpenseCategoryController@index` | tenant, auth, module.access |
| POST | `/expenses/categories` | `expenses.categories.store` | `ExpenseCategoryController@store` | tenant, auth, module.access |
| GET | `/expenses/categories/create` | `expenses.categories.create` | `ExpenseCategoryController@create` | tenant, auth, module.access |
| DELETE | `/expenses/categories/{category}` | `expenses.categories.destroy` | `ExpenseCategoryController@destroy` | tenant, auth, module.access |
| PUT, PATCH | `/expenses/categories/{category}` | `expenses.categories.update` | `ExpenseCategoryController@update` | tenant, auth, module.access |
| GET | `/expenses/categories/{category}/edit` | `expenses.categories.edit` | `ExpenseCategoryController@edit` | tenant, auth, module.access |
| GET | `/expenses/categories/{record}` | `expenses.categories.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |
| ANY | `/expenses/index` | `expenses.` | `Illuminate\Routing\RedirectController` | tenant, auth, module.access |
| GET | `/expenses/records` | `expenses.records` | `ExpenseController@index` | tenant, auth, module.access |
| POST | `/expenses/records` | `expenses.records.store` | `ExpenseController@store` | tenant, auth, module.access |
| GET | `/expenses/records/create` | `expenses.records.create` | `ExpenseController@create` | tenant, auth, module.access |
| DELETE | `/expenses/records/{expense}` | `expenses.records.destroy` | `ExpenseController@destroy` | tenant, auth, module.access |
| PUT, PATCH | `/expenses/records/{expense}` | `expenses.records.update` | `ExpenseController@update` | tenant, auth, module.access |
| GET | `/expenses/records/{expense}/edit` | `expenses.records.edit` | `ExpenseController@edit` | tenant, auth, module.access |
| GET | `/expenses/records/{record}` | `expenses.records.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |
| GET | `/expenses/vendors` | `expenses.vendors` | `ExpenseVendorController@index` | tenant, auth, module.access |
| POST | `/expenses/vendors` | `expenses.vendors.store` | `ExpenseVendorController@store` | tenant, auth, module.access |
| GET | `/expenses/vendors/create` | `expenses.vendors.create` | `ExpenseVendorController@create` | tenant, auth, module.access |
| GET | `/expenses/vendors/{record}` | `expenses.vendors.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |
| DELETE | `/expenses/vendors/{vendor}` | `expenses.vendors.destroy` | `ExpenseVendorController@destroy` | tenant, auth, module.access |
| PUT, PATCH | `/expenses/vendors/{vendor}` | `expenses.vendors.update` | `ExpenseVendorController@update` | tenant, auth, module.access |
| GET | `/expenses/vendors/{vendor}/edit` | `expenses.vendors.edit` | `ExpenseVendorController@edit` | tenant, auth, module.access |

### Farms

7 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/farms` | `farms.index` | `FarmController@index` | tenant, auth, module.access |
| POST | `/farms` | `farms.store` | `FarmController@store` | tenant, auth, module.access |
| GET | `/farms/create` | `farms.create` | `FarmController@create` | tenant, auth, module.access |
| DELETE | `/farms/{farm}` | `farms.destroy` | `FarmController@destroy` | tenant, auth, module.access |
| GET | `/farms/{farm}` | `farms.show` | `FarmController@show` | tenant, auth, module.access |
| PUT, PATCH | `/farms/{farm}` | `farms.update` | `FarmController@update` | tenant, auth, module.access |
| GET | `/farms/{farm}/edit` | `farms.edit` | `FarmController@edit` | tenant, auth, module.access |

### Feeding

35 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/feeding` | `feeding.overview` | `FeedingModuleController@overview` | tenant, auth, module.access |
| GET | `/feeding/calculator` | `feeding.calculator.index` | `FeedCalculatorController@index` | tenant, auth, module.access |
| GET | `/feeding/calculator/animals` | `feeding.calculator.animals` | `FeedCalculatorController@getAnimals` | tenant, auth, module.access |
| POST | `/feeding/calculator/calculate` | `feeding.calculator.calculate` | `FeedCalculatorController@calculate` | tenant, auth, module.access |
| GET | `/feeding/calculator/livestock` | `feeding.calculator.livestock` | `FeedCalculatorController@getLivestock` | tenant, auth, module.access |
| GET | `/feeding/feed-types` | `feeding.feed-types` | `FeedTypeController@index` | tenant, auth, module.access |
| POST | `/feeding/feed-types` | `feeding.feed-types.store` | `FeedTypeController@store` | tenant, auth, module.access |
| GET | `/feeding/feed-types/create` | `feeding.feed-types.create` | `FeedTypeController@create` | tenant, auth, module.access |
| DELETE | `/feeding/feed-types/{feedType}` | `feeding.feed-types.destroy` | `FeedTypeController@destroy` | tenant, auth, module.access |
| PUT, PATCH | `/feeding/feed-types/{feedType}` | `feeding.feed-types.update` | `FeedTypeController@update` | tenant, auth, module.access |
| GET | `/feeding/feed-types/{feedType}/edit` | `feeding.feed-types.edit` | `FeedTypeController@edit` | tenant, auth, module.access |
| GET | `/feeding/feed-types/{record}` | `feeding.feed-types.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |
| ANY | `/feeding/index` | `feeding.` | `Illuminate\Routing\RedirectController` | tenant, auth, module.access |
| GET | `/feeding/inventory` | `feeding.inventory` | `FeedInventoryController@index` | tenant, auth, module.access |
| POST | `/feeding/inventory` | `feeding.inventory.store` | `FeedInventoryController@store` | tenant, auth, module.access |
| GET | `/feeding/inventory/create` | `feeding.inventory.create` | `FeedInventoryController@create` | tenant, auth, module.access |
| DELETE | `/feeding/inventory/{feedInventory}` | `feeding.inventory.destroy` | `FeedInventoryController@destroy` | tenant, auth, module.access |
| PUT, PATCH | `/feeding/inventory/{feedInventory}` | `feeding.inventory.update` | `FeedInventoryController@update` | tenant, auth, module.access |
| GET | `/feeding/inventory/{feedInventory}/edit` | `feeding.inventory.edit` | `FeedInventoryController@edit` | tenant, auth, module.access |
| POST | `/feeding/inventory/{feedInventory}/movements` | `feeding.inventory.movements.store` | `FeedInventoryController@storeMovement` | tenant, auth, module.access |
| GET | `/feeding/inventory/{record}` | `feeding.inventory.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |
| GET | `/feeding/records` | `feeding.records` | `FeedingController@index` | tenant, auth, module.access |
| POST | `/feeding/records` | `feeding.records.store` | `FeedingController@store` | tenant, auth, module.access |
| GET | `/feeding/records/create` | `feeding.records.create` | `FeedingController@create` | tenant, auth, module.access |
| DELETE | `/feeding/records/{feeding}` | `feeding.records.destroy` | `FeedingController@destroy` | tenant, auth, module.access |
| PUT, PATCH | `/feeding/records/{feeding}` | `feeding.records.update` | `FeedingController@update` | tenant, auth, module.access |
| GET | `/feeding/records/{feeding}/edit` | `feeding.records.edit` | `FeedingController@edit` | tenant, auth, module.access |
| GET | `/feeding/records/{record}` | `feeding.records.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |
| GET | `/feeding/schedules` | `feeding.schedules` | `FeedingScheduleController@index` | tenant, auth, module.access |
| POST | `/feeding/schedules` | `feeding.schedules.store` | `FeedingScheduleController@store` | tenant, auth, module.access |
| GET | `/feeding/schedules/create` | `feeding.schedules.create` | `FeedingScheduleController@create` | tenant, auth, module.access |
| GET | `/feeding/schedules/{record}` | `feeding.schedules.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |
| DELETE | `/feeding/schedules/{schedule}` | `feeding.schedules.destroy` | `FeedingScheduleController@destroy` | tenant, auth, module.access |
| PUT, PATCH | `/feeding/schedules/{schedule}` | `feeding.schedules.update` | `FeedingScheduleController@update` | tenant, auth, module.access |
| GET | `/feeding/schedules/{schedule}/edit` | `feeding.schedules.edit` | `FeedingScheduleController@edit` | tenant, auth, module.access |

### Finance

4 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/finance` | `finance.overview` | `FinanceModuleController@overview` | tenant, auth, module.access |
| GET | `/finance/reports/cash-flow` | `finance.reports.cash_flow` | `FinanceModuleController@cashFlow` | tenant, auth, module.access |
| GET | `/finance/reports/profit-loss` | `finance.reports.profit_loss` | `FinanceModuleController@profitLoss` | tenant, auth, module.access |
| GET | `/finance/transactions` | `finance.transactions` | `FinanceModuleController@transactions` | tenant, auth, module.access |

### Flocks (poultry)

7 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/flocks` | `flocks.index` | `FlockController@index` | tenant, auth, module.access, species |
| POST | `/flocks` | `flocks.store` | `FlockController@store` | tenant, auth, module.access, species |
| GET | `/flocks/create` | `flocks.create` | `FlockController@create` | tenant, auth, module.access, species |
| DELETE | `/flocks/{flock}` | `flocks.destroy` | `FlockController@destroy` | tenant, auth, module.access, species |
| GET | `/flocks/{flock}` | `flocks.show` | `FlockController@show` | tenant, auth, module.access, species |
| PUT, PATCH | `/flocks/{flock}` | `flocks.update` | `FlockController@update` | tenant, auth, module.access, species |
| GET | `/flocks/{flock}/edit` | `flocks.edit` | `FlockController@edit` | tenant, auth, module.access, species |

### Health

58 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/health` | `health.overview` | `HealthController@overview` | tenant, auth, module.access |
| GET | `/health/dewormings` | `health.dewormings` | `HealthController@dewormings` | tenant, auth, module.access |
| POST | `/health/dewormings` | `health.dewormings.store` | `DewormingController@store` | tenant, auth, module.access |
| GET | `/health/dewormings/create` | `health.dewormings.create` | `DewormingController@create` | tenant, auth, module.access |
| DELETE | `/health/dewormings/{deworming}` | `health.dewormings.destroy` | `DewormingController@destroy` | tenant, auth, module.access |
| PUT, PATCH | `/health/dewormings/{deworming}` | `health.dewormings.update` | `DewormingController@update` | tenant, auth, module.access |
| GET | `/health/dewormings/{deworming}/edit` | `health.dewormings.edit` | `DewormingController@edit` | tenant, auth, module.access |
| GET | `/health/dewormings/{record}` | `health.dewormings.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |
| GET | `/health/disease` | `health.disease` | `HealthController@disease` | tenant, auth, module.access |
| POST | `/health/disease` | `health.disease.store` | `DiseaseRecordController@store` | tenant, auth, module.access |
| GET | `/health/disease/create` | `health.disease.create` | `DiseaseRecordController@create` | tenant, auth, module.access |
| DELETE | `/health/disease/{diseaseRecord}` | `health.disease.destroy` | `DiseaseRecordController@destroy` | tenant, auth, module.access |
| PUT, PATCH | `/health/disease/{diseaseRecord}` | `health.disease.update` | `DiseaseRecordController@update` | tenant, auth, module.access |
| GET | `/health/disease/{diseaseRecord}/edit` | `health.disease.edit` | `DiseaseRecordController@edit` | tenant, auth, module.access |
| GET | `/health/disease/{record}` | `health.disease.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |
| ANY | `/health/index` | `health.` | `Illuminate\Routing\RedirectController` | tenant, auth, module.access |
| GET | `/health/medicines` | `health.medicines` | `MedicineController@index` | tenant, auth, module.access |
| POST | `/health/medicines` | `health.medicines.store` | `MedicineController@store` | tenant, auth, module.access |
| GET | `/health/medicines/create` | `health.medicines.create` | `MedicineController@create` | tenant, auth, module.access |
| DELETE | `/health/medicines/{medicine}` | `health.medicines.destroy` | `MedicineController@destroy` | tenant, auth, module.access |
| GET | `/health/medicines/{medicine}` | `health.medicines.show` | `MedicineController@show` | tenant, auth, module.access |
| PUT, PATCH | `/health/medicines/{medicine}` | `health.medicines.update` | `MedicineController@update` | tenant, auth, module.access |
| GET | `/health/medicines/{medicine}/edit` | `health.medicines.edit` | `MedicineController@edit` | tenant, auth, module.access |
| POST | `/health/medicines/{medicine}/movements` | `health.medicines.movements.store` | `MedicineController@storeMovement` | tenant, auth, module.access |
| POST | `/health/mortalities` | `health.mortalities.store` | `MortalityController@store` | tenant, auth, module.access |
| GET | `/health/mortalities/create` | `health.mortalities.create` | `MortalityController@create` | tenant, auth, module.access |
| DELETE | `/health/mortalities/{mortality}` | `health.mortalities.destroy` | `MortalityController@destroy` | tenant, auth, module.access |
| PUT, PATCH | `/health/mortalities/{mortality}` | `health.mortalities.update` | `MortalityController@update` | tenant, auth, module.access |
| GET | `/health/mortalities/{mortality}/edit` | `health.mortalities.edit` | `MortalityController@edit` | tenant, auth, module.access |
| GET | `/health/mortalities/{record}` | `health.mortalities.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |
| GET | `/health/mortality` | `health.mortality` | `HealthController@mortality` | tenant, auth, module.access |
| POST | `/health/records` | `health.records.store` | `HealthRecordController@store` | tenant, auth, module.access |
| GET | `/health/records/create` | `health.records.create` | `HealthRecordController@create` | tenant, auth, module.access |
| DELETE | `/health/records/{record}` | `health.records.destroy` | `HealthRecordController@destroy` | tenant, auth, module.access |
| PUT, PATCH | `/health/records/{record}` | `health.records.update` | `HealthRecordController@update` | tenant, auth, module.access |
| GET | `/health/records/{record}/edit` | `health.records.edit` | `HealthRecordController@edit` | tenant, auth, module.access |
| GET | `/health/timeline` | `health.timeline` | `HealthController@timeline` | tenant, auth, module.access |
| GET | `/health/treatments` | `health.treatments` | `HealthController@treatments` | tenant, auth, module.access |
| POST | `/health/treatments` | `health.treatments.store` | `TreatmentController@store` | tenant, auth, module.access |
| GET | `/health/treatments/create` | `health.treatments.create` | `TreatmentController@create` | tenant, auth, module.access |
| GET | `/health/treatments/{record}` | `health.treatments.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |
| DELETE | `/health/treatments/{treatment}` | `health.treatments.destroy` | `TreatmentController@destroy` | tenant, auth, module.access |
| PUT, PATCH | `/health/treatments/{treatment}` | `health.treatments.update` | `TreatmentController@update` | tenant, auth, module.access |
| GET | `/health/treatments/{treatment}/edit` | `health.treatments.edit` | `TreatmentController@edit` | tenant, auth, module.access |
| GET | `/health/vaccinations` | `health.vaccinations` | `HealthController@vaccinations` | tenant, auth, module.access |
| POST | `/health/vaccinations` | `health.vaccinations.store` | `VaccinationController@store` | tenant, auth, module.access |
| GET | `/health/vaccinations/create` | `health.vaccinations.create` | `VaccinationController@create` | tenant, auth, module.access |
| GET | `/health/vaccinations/{record}` | `health.vaccinations.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |
| DELETE | `/health/vaccinations/{vaccination}` | `health.vaccinations.destroy` | `VaccinationController@destroy` | tenant, auth, module.access |
| PUT, PATCH | `/health/vaccinations/{vaccination}` | `health.vaccinations.update` | `VaccinationController@update` | tenant, auth, module.access |
| GET | `/health/vaccinations/{vaccination}/edit` | `health.vaccinations.edit` | `VaccinationController@edit` | tenant, auth, module.access |
| GET | `/health/vet-visits` | `health.vet-visits` | `HealthController@vetVisits` | tenant, auth, module.access |
| POST | `/health/vet-visits` | `health.vet-visits.store` | `VetVisitController@store` | tenant, auth, module.access |
| GET | `/health/vet-visits/create` | `health.vet-visits.create` | `VetVisitController@create` | tenant, auth, module.access |
| GET | `/health/vet-visits/{record}` | `health.vet-visits.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |
| DELETE | `/health/vet-visits/{vetVisit}` | `health.vet-visits.destroy` | `VetVisitController@destroy` | tenant, auth, module.access |
| PUT, PATCH | `/health/vet-visits/{vetVisit}` | `health.vet-visits.update` | `VetVisitController@update` | tenant, auth, module.access |
| GET | `/health/vet-visits/{vetVisit}/edit` | `health.vet-visits.edit` | `VetVisitController@edit` | tenant, auth, module.access |

### Livestock groups

7 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/livestock` | `livestock.index` | `LivestockController@index` | tenant, auth, module.access |
| POST | `/livestock` | `livestock.store` | `LivestockController@store` | tenant, auth, module.access |
| GET | `/livestock/create` | `livestock.create` | `LivestockController@create` | tenant, auth, module.access |
| DELETE | `/livestock/{livestock}` | `livestock.destroy` | `LivestockController@destroy` | tenant, auth, module.access |
| GET | `/livestock/{livestock}` | `livestock.show` | `LivestockController@show` | tenant, auth, module.access |
| PUT, PATCH | `/livestock/{livestock}` | `livestock.update` | `LivestockController@update` | tenant, auth, module.access |
| GET | `/livestock/{livestock}/edit` | `livestock.edit` | `LivestockController@edit` | tenant, auth, module.access |

### Milk

31 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/milk` | `milk.overview` | `MilkModuleController@overview` | tenant, auth, module.access, species |
| GET | `/milk/distribution` | `milk.distribution` | `MilkDistributionController@index` | tenant, auth, module.access, species |
| POST | `/milk/distribution` | `milk.distribution.store` | `MilkDistributionController@store` | tenant, auth, module.access, species |
| GET | `/milk/distribution/create` | `milk.distribution.create` | `MilkDistributionController@create` | tenant, auth, module.access, species |
| DELETE | `/milk/distribution/{milkDistribution}` | `milk.distribution.destroy` | `MilkDistributionController@destroy` | tenant, auth, module.access, species |
| PUT, PATCH | `/milk/distribution/{milkDistribution}` | `milk.distribution.update` | `MilkDistributionController@update` | tenant, auth, module.access, species |
| GET | `/milk/distribution/{milkDistribution}/edit` | `milk.distribution.edit` | `MilkDistributionController@edit` | tenant, auth, module.access, species |
| GET | `/milk/distribution/{record}` | `milk.distribution.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |
| ANY | `/milk/index` | `milk.` | `Illuminate\Routing\RedirectController` | tenant, auth, module.access, species |
| DELETE | `/milk/records/{milkRecord}` | `milk.records.destroy` | `MilkRecordController@destroy` | tenant, auth, module.access, species |
| PUT | `/milk/records/{milkRecord}` | `milk.records.update` | `MilkRecordController@update` | tenant, auth, module.access, species |
| ANY | `/milk/sales` | `milk.` | `Illuminate\Routing\RedirectController` | tenant, auth, module.access, species |
| ANY | `/milk/sales/create` | `milk.` | `Illuminate\Routing\RedirectController` | tenant, auth, module.access, species |
| GET | `/milk/sessions` | `milk.sessions` | `MilkSessionController@index` | tenant, auth, module.access, species |
| POST | `/milk/sessions` | `milk.sessions.store` | `MilkSessionController@store` | tenant, auth, module.access, species |
| GET | `/milk/sessions/create` | `milk.sessions.create` | `MilkSessionController@create` | tenant, auth, module.access, species |
| DELETE | `/milk/sessions/{milkSession}` | `milk.sessions.destroy` | `MilkSessionController@destroy` | tenant, auth, module.access, species |
| PUT, PATCH | `/milk/sessions/{milkSession}` | `milk.sessions.update` | `MilkSessionController@update` | tenant, auth, module.access, species |
| POST | `/milk/sessions/{milkSession}/cancel` | `milk.sessions.cancel` | `MilkSessionController@cancel` | tenant, auth, module.access, species |
| POST | `/milk/sessions/{milkSession}/complete` | `milk.sessions.complete` | `MilkSessionController@complete` | tenant, auth, module.access, species |
| GET | `/milk/sessions/{milkSession}/edit` | `milk.sessions.edit` | `MilkSessionController@edit` | tenant, auth, module.access, species |
| POST | `/milk/sessions/{milkSession}/records` | `milk.sessions.records.store` | `MilkRecordController@store` | tenant, auth, module.access, species |
| POST | `/milk/sessions/{milkSession}/records/bulk` | `milk.sessions.records.bulk` | `MilkRecordController@bulkStore` | tenant, auth, module.access, species |
| GET | `/milk/sessions/{record}` | `milk.sessions.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |
| GET | `/milk/storage` | `milk.storage` | `MilkStorageController@index` | tenant, auth, module.access, species |
| POST | `/milk/storage` | `milk.storage.store` | `MilkStorageController@store` | tenant, auth, module.access, species |
| GET | `/milk/storage/create` | `milk.storage.create` | `MilkStorageController@create` | tenant, auth, module.access, species |
| DELETE | `/milk/storage/{milkStorage}` | `milk.storage.destroy` | `MilkStorageController@destroy` | tenant, auth, module.access, species |
| PUT, PATCH | `/milk/storage/{milkStorage}` | `milk.storage.update` | `MilkStorageController@update` | tenant, auth, module.access, species |
| GET | `/milk/storage/{milkStorage}/edit` | `milk.storage.edit` | `MilkStorageController@edit` | tenant, auth, module.access, species |
| GET | `/milk/storage/{record}` | `milk.storage.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |

### Movements

7 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/movements` | `movements.index` | `MovementController@index` | tenant, auth, module.access |
| POST | `/movements` | `movements.store` | `MovementController@store` | tenant, auth, module.access |
| GET | `/movements/create` | `movements.create` | `MovementController@create` | tenant, auth, module.access |
| DELETE | `/movements/{movement}` | `movements.destroy` | `MovementController@destroy` | tenant, auth, module.access |
| PUT, PATCH | `/movements/{movement}` | `movements.update` | `MovementController@update` | tenant, auth, module.access |
| GET | `/movements/{movement}/edit` | `movements.edit` | `MovementController@edit` | tenant, auth, module.access |
| GET | `/movements/{record}` | `movements.show` | `RecordDetailsController@show` | tenant, auth, module.access, species |

### Sales

19 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/sales` | `sales.overview` | `SalesModuleController@overview` | tenant, auth, module.access |
| GET | `/sales/abattoir` | `sales.abattoir` | `AbattoirDispatchController@index` | tenant, auth, module.access, species |
| POST | `/sales/abattoir` | `sales.abattoir.store` | `AbattoirDispatchController@store` | tenant, auth, module.access, species |
| GET | `/sales/abattoir/create` | `sales.abattoir.create` | `AbattoirDispatchController@create` | tenant, auth, module.access, species |
| GET | `/sales/abattoir/{abattoirDispatch}` | `sales.abattoir.show` | `AbattoirDispatchController@show` | tenant, auth, module.access, species |
| POST | `/sales/abattoir/{abattoirDispatch}/returns` | `sales.abattoir.returns.store` | `AbattoirDispatchController@storeReturn` | tenant, auth, module.access, species |
| ANY | `/sales/buyers` | `sales.` | `Illuminate\Routing\RedirectController` | tenant, auth, module.access |
| ANY | `/sales/index` | `sales.` | `Illuminate\Routing\RedirectController` | tenant, auth, module.access |
| GET | `/sales/transactions` | `sales.transactions` | `SaleTransactionController@index` | tenant, auth, module.access |
| POST | `/sales/transactions` | `sales.transactions.store` | `SaleTransactionController@store` | tenant, auth, module.access |
| GET | `/sales/transactions/create` | `sales.transactions.create` | `SaleTransactionController@create` | tenant, auth, module.access |
| DELETE | `/sales/transactions/{transaction}` | `sales.transactions.destroy` | `SaleTransactionController@destroy` | tenant, auth, module.access |
| GET | `/sales/transactions/{transaction}` | `sales.transactions.show` | `SaleTransactionController@show` | tenant, auth, module.access |
| PUT | `/sales/transactions/{transaction}` | `sales.transactions.update` | `SaleTransactionController@update` | tenant, auth, module.access |
| POST | `/sales/transactions/{transaction}/cancel` | `sales.transactions.cancel` | `SaleTransactionController@cancel` | tenant, auth, module.access |
| POST | `/sales/transactions/{transaction}/complete` | `sales.transactions.complete` | `SaleTransactionController@complete` | tenant, auth, module.access |
| POST | `/sales/transactions/{transaction}/confirm` | `sales.transactions.confirm` | `SaleTransactionController@confirm` | tenant, auth, module.access |
| POST | `/sales/transactions/{transaction}/items` | `sales.transactions.items.store` | `SaleTransactionController@storeItem` | tenant, auth, module.access |
| POST | `/sales/transactions/{transaction}/payments` | `sales.transactions.payments.store` | `SaleTransactionController@storePayment` | tenant, auth, module.access |

### Public marketplace and traceability

22 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/` | `marketplace.home` | `Marketplace\MarketplaceHomeController@index` | - |
| GET | `/about` | `marketplace.about` | `Marketplace\AboutController@index` | - |
| GET | `/contact` | `marketplace.contact` | `Marketplace\ContactController@index` | - |
| POST | `/contact` | `marketplace.contact.store` | `Marketplace\ContactController@store` | - |
| ANY | `/learning` | `marketplace.learning` | `Illuminate\Routing\RedirectController` | - |
| ANY | `/learning/category/{category}` | `marketplace.learning.category` | `Illuminate\Routing\RedirectController` | - |
| GET, POST | `/learning/subscribe` | `marketplace.learning.subscribe` | `Closure` | - |
| ANY | `/learning/{post}` | `marketplace.learning.show` | `Illuminate\Routing\RedirectController` | - |
| GET | `/shop` | `marketplace.shop` | `Marketplace\ShopController@index` | - |
| POST | `/shop` | `marketplace.shop.store` | `Marketplace\ShopController@store` | auth |
| GET | `/shop/create` | `marketplace.shop.create` | `Marketplace\ShopController@create` | auth |
| PATCH | `/shop/inquiries/{inquiry}` | `marketplace.shop.inquiries.update` | `Marketplace\ShopController@updateInquiry` | auth |
| GET | `/shop/my-listings` | `marketplace.shop.mine` | `Marketplace\ShopController@mine` | auth |
| DELETE | `/shop/{listing}` | `marketplace.shop.destroy` | `Marketplace\ShopController@destroy` | auth |
| GET | `/shop/{listing}` | `marketplace.shop.show` | `Marketplace\ShopController@show` | - |
| PUT | `/shop/{listing}` | `marketplace.shop.update` | `Marketplace\ShopController@update` | auth |
| GET | `/shop/{listing}/edit` | `marketplace.shop.edit` | `Marketplace\ShopController@edit` | auth |
| POST | `/shop/{listing}/inquiry` | `marketplace.shop.inquiry` | `Marketplace\ShopController@inquiry` | - |
| GET | `/trace` | `marketplace.trace` | `Marketplace\TraceController@index` | throttle:30,1 |
| POST | `/trace` | `marketplace.trace.lookup` | `Marketplace\TraceController@lookup` | throttle:30,1 |
| GET | `/trace/{animal}` | `marketplace.trace.show` | `Marketplace\TraceController@show` | throttle:30,1 |
| GET | `/trace/{animal}/pdf` | `marketplace.trace.pdf` | `Marketplace\TraceController@pdf` | throttle:30,1 |

### Platform admin (/admin)

32 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| GET | `/admin` | `central.dashboard` | `Central\AdminDashboardController@index` | auth:admin, forget-tenant |
| GET | `/admin/api/rwanda/districts` | `central.api.rwanda.districts` | `Api\RwandaLocationController@districts` | auth:admin, forget-tenant |
| GET | `/admin/contact-messages` | `central.contact-messages.index` | `Central\ContactMessageController@index` | auth:admin, forget-tenant |
| PATCH | `/admin/contact-messages/{contactMessage}` | `central.contact-messages.update` | `Central\ContactMessageController@update` | auth:admin, forget-tenant |
| GET | `/admin/ecommerce` | `central.ecommerce.index` | `Central\EcommerceAdminController@index` | auth:admin, forget-tenant |
| PATCH | `/admin/ecommerce/inquiries/{inquiry}` | `central.ecommerce.inquiries.update` | `Central\EcommerceAdminController@updateInquiry` | auth:admin, forget-tenant |
| POST | `/admin/ecommerce/listings` | `central.ecommerce.listings.store` | `Central\EcommerceAdminController@store` | auth:admin, forget-tenant |
| GET | `/admin/ecommerce/listings/create` | `central.ecommerce.listings.create` | `Central\EcommerceAdminController@create` | auth:admin, forget-tenant |
| GET | `/admin/ecommerce/listings/{listing}` | `central.ecommerce.listings.show` | `Central\EcommerceAdminController@show` | auth:admin, forget-tenant |
| PUT | `/admin/ecommerce/listings/{listing}` | `central.ecommerce.listings.update` | `Central\EcommerceAdminController@update` | auth:admin, forget-tenant |
| GET | `/admin/ecommerce/listings/{listing}/edit` | `central.ecommerce.listings.edit` | `Central\EcommerceAdminController@edit` | auth:admin, forget-tenant |
| PATCH | `/admin/ecommerce/listings/{listing}/status` | `central.ecommerce.listings.status` | `Central\EcommerceAdminController@updateStatus` | auth:admin, forget-tenant |
| GET | `/admin/farms` | `central.farms.index` | `Central\UserDirectoryController@index` | auth:admin, forget-tenant |
| GET | `/admin/farms/{farm}` | `central.farms.show` | `Central\UserDirectoryController@show` | auth:admin, forget-tenant |
| ANY | `/admin/login` | `central.login` | `Illuminate\Routing\RedirectController` | - |
| POST | `/admin/logout` | `central.logout` | `Central\Auth\AdminLoginController@destroy` | auth:admin, forget-tenant |
| ANY | `/admin/marketplace` | `central.marketplace.index` | `Illuminate\Routing\RedirectController` | auth:admin, forget-tenant |
| GET | `/admin/releases` | `central.releases.index` | `Central\PlatformReleaseController@index` | auth:admin, forget-tenant |
| POST | `/admin/releases` | `central.releases.store` | `Central\PlatformReleaseController@store` | auth:admin, forget-tenant |
| GET | `/admin/releases/create` | `central.releases.create` | `Central\PlatformReleaseController@create` | auth:admin, forget-tenant |
| DELETE | `/admin/releases/{release}` | `central.releases.destroy` | `Central\PlatformReleaseController@destroy` | auth:admin, forget-tenant |
| PUT, PATCH | `/admin/releases/{release}` | `central.releases.update` | `Central\PlatformReleaseController@update` | auth:admin, forget-tenant |
| GET | `/admin/releases/{release}/edit` | `central.releases.edit` | `Central\PlatformReleaseController@edit` | auth:admin, forget-tenant |
| POST | `/admin/releases/{release}/publish` | `central.releases.publish` | `Central\PlatformReleaseController@publish` | auth:admin, forget-tenant |
| GET | `/admin/tenants` | `central.tenants.index` | `Central\TenantController@index` | auth:admin, forget-tenant |
| POST | `/admin/tenants` | `central.tenants.store` | `Central\TenantController@store` | auth:admin, forget-tenant |
| GET | `/admin/tenants/create` | `central.tenants.create` | `Central\TenantController@create` | auth:admin, forget-tenant |
| DELETE | `/admin/tenants/{tenant}` | `central.tenants.destroy` | `Central\TenantController@destroy` | auth:admin, forget-tenant |
| GET | `/admin/users` | `central.accounts.index` | `Central\PlatformUserController@index` | auth:admin, forget-tenant |
| GET | `/admin/users/farms/{farm}` | `central.users.farms.redirect` | `Closure` | auth:admin, forget-tenant |
| DELETE | `/admin/users/{user}` | `central.accounts.destroy` | `Central\PlatformUserController@destroy` | auth:admin, forget-tenant |
| GET | `/admin/users/{user}` | `central.accounts.show` | `Central\PlatformUserController@show` | auth:admin, forget-tenant |

### Framework / infrastructure

7 routes.

| Method | URI | Name | Action | Middleware |
|---|---|---|---|---|
| ANY | `/feedings` | `-` | `Illuminate\Routing\RedirectController` | tenant, auth, module.access |
| ANY | `/feedings/create` | `-` | `Illuminate\Routing\RedirectController` | tenant, auth, module.access |
| ANY | `/sales-legacy` | `-` | `Illuminate\Routing\RedirectController` | tenant, auth, module.access |
| GET | `/storage/{path}` | `storage.local` | `Closure` | - |
| PUT | `/storage/{path}` | `storage.local.upload` | `Closure` | - |
| GET | `/tenancy/assets/{path?}` | `stancl.tenancy.asset` | `Stancl\Tenancy\Controllers\TenantAssetsController@asset` | tenancy-assets |
| GET | `/up` | `-` | `Closure` | - |

> **Note:** Routes whose Action is `RecordDetailsController@show` are generated from `config/record_views.php` (read-only record detail pages `/{section}/{record}`). Several `RedirectController` routes are legacy redirects (e.g. `/milk/sales`, `/learning*`).

## Controllers catalogue

### Workspace controllers

| Controller | Purpose |
|---|---|
| `DashboardController` | main dashboard; resolves period/farm filters and calls `DashboardAnalyticsService` |
| `ProfileController` | edit own name, email, password |
| `LocaleController` | switch language (`en`/`rw`) |
| `NotificationController`, `WhatsNewController` | in-app notifications and release notes |
| `WorkspaceUserController` | workspace users, roles, module permission matrix, (de)activation |
| `FarmController`, `LivestockController` | farms and livestock groups (CRUD) |
| `AnimalController`, `AnimalImportExportController` | animals CRUD; CSV/XLSX import with preview, CSV export/template |
| `FlockController` | poultry flocks CRUD |
| `EggModuleController`, `EggCollectionController` | egg overview, egg collections |
| `HealthController`, `HealthRecordController`, `VaccinationController`, `TreatmentController`, `DewormingController`, `VetVisitController`, `DiseaseRecordController`, `MortalityController`, `MedicineController` | health overview and each health record type; medicine catalogue and stock movements |
| `FeedingModuleController`, `FeedingController`, `FeedingScheduleController`, `FeedInventoryController`, `FeedTypeController`, `FeedCalculatorController` | feeding overview, records, schedules, inventory and movements, feed types, calculator |
| `MilkModuleController`, `MilkSessionController`, `MilkRecordController`, `MilkStorageController`, `MilkDistributionController` | milk overview, sessions, per-animal records, storage tanks, non-sale distribution |
| `BreedingModuleController`, `BreedingRecordController`, `PregnancyCheckController`, `BirthRecordController` | breeding overview, breeding records, pregnancy checks, births and offspring registration |
| `CertificateController`, `MovementController` | certificates; stock movement records |
| `SalesModuleController`, `SaleTransactionController`, `AbattoirDispatchController` | sales overview, unified sale transactions (items, payments, confirm/complete/cancel), abattoir dispatch and returns |
| `CustomerModuleController`, `CustomerController`, `CustomerAddressController`, `CustomerContactController`, `CustomerCommunicationController`, `CustomerCreditController`, `CustomerImportExportController` | customer directory and sub-resources, credit settings, CSV import/export |
| `ExpenseModuleController`, `ExpenseController`, `ExpenseCategoryController`, `ExpenseVendorController` | expenses overview, records, categories, vendors |
| `FinanceModuleController` | finance overview, transaction list, P&L, cash flow |
| `EmployeeModuleController`, `EmployeeController`, `EmployeeAddressController`, `EmployeeDocumentController`, `EmployeeEmergencyContactController`, `EmployeeFarmAssignmentController`, `EmployeePayrollController`, `EmployeeImportExportController` | employees and sub-resources, payroll records, CSV import/export |
| `RecordDetailsController` | generic read-only detail page for records listed in `config/record_views.php` |
| `Auth\LoginController`, `Auth\RegisterController` | login (both guards), logout, registration (creates tenant + admin user) |
| `Api\RwandaLocationController` | JSON: provinces, districts, sectors, cells, villages |

### Platform admin controllers (`Central\*`, guard `admin`)

| Controller | Purpose |
|---|---|
| `AdminDashboardController` | platform statistics, charts, farm map (580 lines) |
| `PlatformUserController` | list/show/delete platform workspace accounts (purge) |
| `UserDirectoryController` | cross-tenant farm directory |
| `TenantController` | list/create/delete tenants |
| `EcommerceAdminController`, `MarketplaceAdminController` | marketplace listings and inquiries moderation |
| `PlatformReleaseController` | release notes CRUD and publish |
| `ContactMessageController` | contact form messages |
| `Auth\AdminLoginController` | admin logout (login is the shared `/login`) |

### Public marketplace controllers

`MarketplaceHomeController`, `AboutController`, `ShopController` (public listing browse, inquiry; authenticated listing management), `ContactController`, `LearningController` (routes currently redirect to `/`), `TraceController` (animal traceability lookup and PDF).

## Services catalogue

| Service | One-line purpose |
|---|---|
| `TenantContext` | static holder of the active tenant id (single-database tenancy) |
| `TenantAccountService` | tenant resolution from session/cookies/email; registration provisioning |
| `WorkspaceAccessService` | roles, module permission checks, route-to-module mapping, navigation filtering |
| `Species\SpeciesProfile`, `Species\ActiveFarmContext` | per-farm species profile; active-farm session context |
| `FlockService` | flock counts and ledger (`FlockEvent`) |
| `EggCollectionService` | egg collection creation and codes |
| `MedicineInventoryService`, `FeedInventoryService` | locked stock movements, consumption restore/sync |
| `Feeding\FeedingScheduleService`, `Feeding\FeedCalculatorService` | schedule next-due logic; ration recommendations |
| `DiseaseRecordService` | disease record handling helpers |
| `BreedingService`, `BreedingReminderService` | breeding lifecycle; pregnancy-check due logic |
| `MilkSessionService`, `MilkStorageService`, `Milk\MilkDistributionService`, `Milk\MilkCostPerLitreService`, `MilkOverviewAnalyticsService` | milk workflow, stock, distribution, cost per litre, analytics |
| `MilkSaleService` | legacy milk sale workflow (unused) |
| `SaleTransactionService` | unified sale workflow |
| `CustomerService`, `EmployeeService` | customer/employee creation, credit sync, logs |
| `ExpenseService` | linked expense sync and context builders |
| `Finance\FinancePostingService`, `FinanceAccountResolver`, `FinancePeriodService`, `FinanceReportService` | ledger posting/reversal, account mapping, periods, reports |
| `DashboardAnalyticsService`, `HealthOverviewAnalyticsService` | dashboard and health overview data |
| `Import\*CsvImporter`, `ImportExport\*CsvSchema`, `Export\*CsvExporter` | CSV/XLSX import and export |
| `Marketplace\*` | listings, status changes, home content, learning posts, animal trace, DB readiness helpers |
| `Releases\ReleasePublisher` | publish release notes and fan-out notifications |
| `RwandaLocationService` | administrative divisions from a JSON file, cached lookups |
| `PlatformUserWorkspaceService` | purge a tenant workspace (admin) |
| `Admin*Service` | platform dashboard statistics, filters, farm map, user directory filters |

## Middleware

| Alias / class | Purpose |
|---|---|
| `InitializeDefaultTenant` (global web) | resolve tenant for central-domain requests; skips `/admin` |
| `SetLocale` (global web) | apply session locale |
| `ResolveActiveFarm` (global web) | maintain `active_farm_id` |
| `module.access` -> `EnsureModuleAccess` | permission check |
| `workspace.admin` -> `EnsureWorkspaceAdmin` | admin-only routes |
| `EnsureSpeciesModule` (class) | species profile gate |
| `ForgetTenantForCentralAdmin` | clears tenant context for `/admin` |
| `InitializeTenancyForAssets` | tenant asset route (stancl) |

## Models

Tenant-scoped (`TenantModel`, table in parentheses where not the plural of the name):

- **Farm and herd**: `Farm`, `FarmMember`, `Livestock` (`livestock`), `Animal`, `Flock`, `FlockEvent`, `Offspring` (`offspring`)
- **Health**: `HealthRecord`, `Vaccination`, `Treatment`, `Deworming`, `VetVisit`, `DiseaseRecord`, `Mortality` (`mortalities`), `Medicine`, `MedicineMovement`
- **Feeding**: `FeedType`, `FeedSupplier`, `FeedInventory` (`feed_inventories`), `FeedInventoryMovement`, `Feeding`, `FeedingSchedule`
- **Production**: `EggCollection`, `MilkSession`, `MilkRecord`, `MilkStorage` (`milk_storage`), `MilkStorageMovement`, `MilkDistribution`
- **Breeding**: `BreedingRecord`, `BreedingLog`, `PregnancyCheck`, `BirthRecord`
- **Movement/certificates/abattoir**: `Movement`, `Certificate`, `AbattoirDispatch`, `AbattoirDispatchAnimal`, `AbattoirReturn`
- **Sales**: `SaleTransaction`, `SaleItem`, `SalePayment`, `SaleLog`, `SaleDocument`; legacy: `Sale`, `MilkSale`, `MilkSaleItem`, `MilkSalePayment`, `MilkSaleLog`
- **Customers** (`Customer` soft-deletes): `Customer`, `CustomerAddress`, `CustomerCommunication`, `CustomerContact`, `CustomerCredit` (`customer_credit`), `CustomerDocument`, `CustomerLog`, `CustomerProfile`
- **Employees** (`Employee` soft-deletes): `Employee`, `EmployeeAddress`, `EmployeeDocument`, `EmployeeEmergencyContact`, `EmployeeFarmAssignment`, `EmployeeLog`, `EmployeePayroll` (`employee_payroll`), `EmployeeProfile`
- **Expenses/finance**: `Expense`, `ExpenseVendor`, `FinancePeriod`, `FinanceTransaction`, `FinanceTransactionLine`, `FinanceTransactionLog`; shared catalogs (`BelongsToTenantWithSystemCatalog`): `ExpenseCategory`, `FinanceAccount`
- **Identity**: `User` (tenant-scoped), `Tenant`, `TenantAccount` (central, unscoped), `WorkspaceModulePermission` (keyed by user)
- **Central connection (`App\Models\Central`)**: `AdminUser`, `ContactMessage`, `LearningCategory`, `LearningPost`, `MarketplaceCategory`, `MarketplaceListing`, `MarketplaceInquiry`, `MarketplaceListingStatusChange`, `PlatformRelease`

Full column-level schema is in chapter 4.

## Artisan commands

Custom commands (`app/Console/Commands`):

| Signature | Options | Description |
|---|---|---|
| `orora:install` | none | `migrate --force`, verify required tables/columns/files, create super admin from env if set |
| `orora:migrate` | none | `migrate --force` |
| `orora:seed-demo` | `--fresh` | seed demo tenant via `TenantDemoSeeder`; `--fresh` deletes the existing demo tenant first |
| `orora:super-admin` | `--name=`, `--email=`, `--password=` (min 8) | create or update a super admin (falls back to `SUPER_ADMIN_*` env, then prompts) |
| `orora:super-admin-status` | none | list admin accounts and readiness |
| `orora:learning-cleanup` | none | remove placeholder learning videos/posts |
| `rwanda:download-locations` | none | download `database/data/rwanda_locations.json` |
| `finance:backfill` | `--tenant=` (declared, **ignored**) | post ledger entries for existing completed sales, paid expenses and payments |

Provided by packages: `tenants:list`, `tenants:migrate`, `tenants:migrate-fresh`, `tenants:rollback`, `tenants:run`, `tenants:seed` (stancl; relevant to database-per-tenant mode only), plus all Laravel commands (`migrate`, `route:list`, `pail`, ...).

## Scheduled jobs

None are defined (`routes/console.php` only registers `inspire`). See chapter 10.

## APIs and integration points

- **No REST/JSON API** for external clients: `routes/` has no `api.php`, no token authentication (no Sanctum/Passport). All routes are session-based HTML except:
  - `GET /api/rwanda/{provinces|districts|sectors|cells|villages}` (authenticated, JSON) and `GET /admin/api/rwanda/districts`.
  - `POST /notifications/read-all` returns JSON when the client expects JSON.
  - `GET /up` health check.
- **Public endpoints**: marketplace pages, `/trace` lookups (with PDF), contact form and shop inquiries.
- **External services**: GitHub raw content (one-time location JSON download), Bunny Fonts (build time). No payment, SMS or e-mail provider is integrated; `MAIL_MAILER=log`.
- **Libraries**: DomPDF (trace report PDF), PhpSpreadsheet (XLSX import), Chart.js (dashboards).
- **cPanel**: `app/Tenancy/CpanelMysqlDatabaseManager` and `SharedHostingBlockedDatabaseManager` support database-per-tenant creation (`TENANCY_CPANEL_*`); not used in the default single-database mode.
- The repository also contains `OroraFarm-Mobile-App-System-Analysis.pdf`; no mobile API exists in the code.

## Testing

- Location: `tests/Feature` (17 files, about 100 tests), helpers in `tests/Support` and base classes `tests/TestCase.php`, `tests/TenantTestCase.php`. `phpunit.xml` defines suites `Unit` (directory `tests/Unit` **does not exist**) and `Feature`.
- Covered areas: auth and dashboard, admin platform users, animal CSV import, animal/farm workflow, animal sale vs health, feed inventory/stock vendor, feeding records/schedules, milk distribution, poultry workflow, sales species types, marketplace admin connection, platform release notifications, record view pages, dashboard zero-KPI.
- Not covered (no test files): finance posting and reports, sale transaction lifecycle, milk sessions/storage, breeding, medicine inventory, workspace permissions/`module.access`, tenant isolation, security flows.
- `phpunit.xml` sets `DB_CONNECTION=sqlite` (`:memory:`), but `TenantTestCase` **overrides** this: it requires a reachable MySQL database named by `TEST_DB_DATABASE` (default `orora_test`), runs `migrate:fresh --force` once per process against it, and skips tests with a message if MySQL is unavailable. Never point `TEST_DB_DATABASE` at a real database.
- Run:

```bash
php artisan config:clear
php artisan test                 # or: composer test
php artisan test --filter=MilkDistributionTest
vendor/bin/phpunit tests/Feature/PoultryWorkflowTest.php
```

- Static style: `laravel/pint` is installed (`vendor/bin/pint`); there is no CI configuration in the repository.

## Coding conventions (observed)

- Thin controllers delegating to services for stock, finance and lifecycle logic; FormRequests for validation where present, inline `validate()` in a few controllers (movements, certificates, abattoir).
- Business rule violations are raised as `InvalidArgumentException` by services and converted to validation errors by controllers.
- Stock changes always go through a `recordMovement` service method with `lockForUpdate` and a movement ledger (`balance_after`).
- Codes follow `PREFIX-YYYYMMDD-NNNN` (`MS`, `MR`, `MK`, `MD`, `MLS`, `EGS`, `MET`, `SL`, `ABD`, `FLK`, `EGG`, `BR`, `PC`, `BIR`, `OFF`, `FT`, `CUS-NNNN`).
- Enumerations (statuses, types, labels) live in `config/modules.php`, never in the database; add new values there and in the matching FormRequest `Rule::in`.
- Views: pages are `modules/{module}/...`; controller traits (`HealthSectionViews`, `SalesSectionViews`, ...) provide shared view data and `ProvidesModuleNavigation` the module nav.
- All UI strings use `__()` with the English sentence as the key; Kinyarwanda in `lang/rw.json`.
- Every model must extend `TenantModel` (or use a tenancy trait) so the `tenant_id` scope and auto-fill apply; every new table needs a `tenant_id` column.
- Adding a module: route group in `routes/modules.php`, entry in `config/modules.php` navigation, key in `config/workspace_access.php` `modules` and `route_modules`, and (if species-specific) in `config/species.php`.
