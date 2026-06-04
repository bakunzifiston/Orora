<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\TenancyMode;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;

class TenancyServiceProvider extends ServiceProvider
{
    public static string $controllerNamespace = '';

    public function events()
    {
        $tenantCreatedPipeline = TenancyMode::usesSingleDatabase()
            ? []
            : [
                Jobs\CreateDatabase::class,
                Jobs\MigrateDatabase::class,
            ];

        $tenantDeletedPipeline = TenancyMode::usesSingleDatabase()
            ? []
            : [
                Jobs\DeleteDatabase::class,
            ];

        return [
            Events\CreatingTenant::class => [],
            Events\TenantCreated::class => $tenantCreatedPipeline === []
                ? []
                : [
                    JobPipeline::make($tenantCreatedPipeline)->send(function (Events\TenantCreated $event) {
                        return $event->tenant;
                    })->shouldBeQueued(false),
                ],
            Events\SavingTenant::class => [],
            Events\TenantSaved::class => [],
            Events\UpdatingTenant::class => [],
            Events\TenantUpdated::class => [],
            Events\DeletingTenant::class => [],
            Events\TenantDeleted::class => $tenantDeletedPipeline === []
                ? []
                : [
                    JobPipeline::make($tenantDeletedPipeline)->send(function (Events\TenantDeleted $event) {
                        return $event->tenant;
                    })->shouldBeQueued(false),
                ],

            Events\CreatingDomain::class => [],
            Events\DomainCreated::class => [],
            Events\SavingDomain::class => [],
            Events\DomainSaved::class => [],
            Events\UpdatingDomain::class => [],
            Events\DomainUpdated::class => [],
            Events\DeletingDomain::class => [],
            Events\DomainDeleted::class => [],

            Events\DatabaseCreated::class => [],
            Events\DatabaseMigrated::class => [],
            Events\DatabaseSeeded::class => [],
            Events\DatabaseRolledBack::class => [],
            Events\DatabaseDeleted::class => [],

            Events\InitializingTenancy::class => [],
            Events\TenancyInitialized::class => TenancyMode::usesSingleDatabase()
                ? []
                : [Listeners\BootstrapTenancy::class],
            Events\EndingTenancy::class => [],
            Events\TenancyEnded::class => TenancyMode::usesSingleDatabase()
                ? []
                : [Listeners\RevertToCentralContext::class],
            Events\BootstrappingTenancy::class => [],
            Events\TenancyBootstrapped::class => [],
            Events\RevertingToCentralContext::class => [],
            Events\RevertedToCentralContext::class => [],

            Events\SyncedResourceSaved::class => [
                Listeners\UpdateSyncedResource::class,
            ],
            Events\SyncedResourceChangedInForeignDatabase::class => [],
        ];
    }

    public function register()
    {
        //
    }

    public function boot()
    {
        $this->bootEvents();
        $this->mapRoutes();

        if (! TenancyMode::usesSingleDatabase()) {
            $this->makeTenancyMiddlewareHighestPriority();
        }
    }

    protected function bootEvents()
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }

                Event::listen($event, $listener);
            }
        }
    }

    protected function mapRoutes()
    {
        if (! config('tenancy.enable_domain_routes', false)) {
            return;
        }

        $this->app->booted(function () {
            if (file_exists(base_path('routes/tenant.php'))) {
                Route::namespace(static::$controllerNamespace)
                    ->group(base_path('routes/tenant.php'));
            }
        });
    }

    protected function makeTenancyMiddlewareHighestPriority()
    {
        $tenancyMiddleware = [
            Middleware\PreventAccessFromCentralDomains::class,
            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }
}
