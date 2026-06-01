<?php

/**
 * Authenticated GET smoke test for tenant routes.
 * Usage: php scripts/smoke-routes.php
 */

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Route;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = Tenant::find('demo');
if (! $tenant) {
    fwrite(STDERR, "Demo tenant not found.\n");
    exit(1);
}

tenancy()->initialize($tenant);

$user = User::query()->first();
if (! $user) {
    fwrite(STDERR, "No user in tenant demo DB.\n");
    exit(1);
}

auth()->login($user);

$ids = [
    'farm' => \App\Models\Farm::query()->value('id'),
    'livestock' => \App\Models\Livestock::query()->value('id'),
    'animal' => \App\Models\Animal::query()->value('id'),
    'breedingRecord' => \App\Models\BreedingRecord::query()->value('id'),
    'birthRecord' => \App\Models\BirthRecord::query()->value('id'),
    'offspring' => \App\Models\Offspring::query()->value('id'),
    'transaction' => \App\Models\SaleTransaction::query()->value('id'),
    'customer' => \App\Models\Customer::query()->value('id'),
    'contact' => \App\Models\CustomerContact::query()->value('id'),
    'address' => \App\Models\CustomerAddress::query()->value('id'),
    'expense' => \App\Models\Expense::query()->value('id'),
    'category' => \App\Models\ExpenseCategory::query()->value('id'),
    'feedType' => \App\Models\FeedType::query()->value('id'),
    'feedInventory' => \App\Models\FeedInventory::query()->value('id'),
    'feeding' => \App\Models\Feeding::query()->value('id'),
    'supplier' => \App\Models\FeedSupplier::query()->value('id'),
    'schedule' => \App\Models\FeedingSchedule::query()->value('id'),
    'milkSession' => \App\Models\MilkSession::query()->value('id'),
    'milkRecord' => \App\Models\MilkRecord::query()->value('id'),
    'milkStorage' => \App\Models\MilkStorage::query()->value('id'),
    'certificate' => \App\Models\Certificate::query()->value('id'),
    'movement' => \App\Models\Movement::query()->value('id'),
    'abattoirDispatch' => \App\Models\AbattoirDispatch::query()->value('id'),
    'employee' => \App\Models\EmployeeProfile::query()->value('id'),
    'vendor' => \App\Models\ExpenseVendor::query()->value('id'),
    'vaccination' => \App\Models\Vaccination::query()->value('id'),
    'treatment' => \App\Models\Treatment::query()->value('id'),
    'vetVisit' => \App\Models\VetVisit::query()->value('id'),
    'mortality' => \App\Models\Mortality::query()->value('id'),
    'record' => \App\Models\HealthRecord::query()->value('id'),
];

$skipNamePatterns = [
    '/^central\./',
    '/\.store$/',
    '/\.update$/',
    '/\.destroy$/',
    '/\.confirm$/',
    '/\.complete$/',
    '/\.cancel$/',
    '/payments\./',
    '/items\./',
    '/movements\.store/',
    '/returns\./',
    '/offspring\./',
    '/records\.bulk/',
    '/sessions\.complete/',
    '/sessions\.cancel/',
    '/sessions\.records/',
    '/login/',
    '/register/',
    '/logout/',
];

$failures = [];
$ok = 0;
$skipped = 0;

foreach (Route::getRoutes() as $route) {
    if (! in_array('GET', $route->methods(), true)) {
        continue;
    }

    $name = $route->getName();
    if (! $name) {
        continue;
    }

    foreach ($skipNamePatterns as $pattern) {
        if (preg_match($pattern, $name)) {
            $skipped++;
            continue 2;
        }
    }

    if (str_contains($route->uri(), '{') && ! routeHasResolvableParams($route, $ids)) {
        $skipped++;
        continue;
    }

    try {
        $params = paramsForRoute($route, $ids);
        $url = route($name, $params);
        $request = \Illuminate\Http\Request::create($url, 'GET');
        $request->setUserResolver(fn () => $user);

        $response = app()->handle($request);
        $status = $response->getStatusCode();

        if ($status >= 400) {
            $failures[] = ['route' => $name, 'url' => $url, 'status' => $status];
        } else {
            $ok++;
        }
    } catch (\Throwable $e) {
        $failures[] = ['route' => $name, 'url' => $name, 'status' => 0, 'error' => $e->getMessage()];
    }
}

echo "Smoke (tenant=demo, user={$user->email}): {$ok} OK, ".count($failures)." failed, {$skipped} skipped\n\n";

foreach ($failures as $f) {
    $msg = $f['error'] ?? 'HTTP '.$f['status'];
    echo "- [{$f['status']}] {$f['route']}: {$msg}\n";
    if (isset($f['url']) && $f['url'] !== $f['route']) {
        echo "  {$f['url']}\n";
    }
}

exit(count($failures) > 0 ? 1 : 0);

function routeHasResolvableParams(\Illuminate\Routing\Route $route, array $ids): bool
{
    foreach ($route->parameterNames() as $name) {
        if (empty($ids[$name])) {
            return false;
        }
    }

    return true;
}

function paramsForRoute(\Illuminate\Routing\Route $route, array $ids): array
{
    $params = [];
    foreach ($route->parameterNames() as $name) {
        $params[$name] = $ids[$name] ?? 1;
    }

    return $params;
}
