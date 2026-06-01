<?php

namespace Tests;

use App\Models\Tenant;
use App\Models\TenantAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class TenantTestCase extends BaseTestCase
{
    protected Tenant $tenant;

    protected static bool $centralMigrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->mysqlAvailable()) {
            $this->markTestSkipped('MySQL is required for tenant feature tests (orora_test database).');
        }

        $this->configureTestConnections();

        if (! static::$centralMigrated) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => database_path('migrations'),
                '--realpath' => true,
                '--force' => true,
            ]);
            static::$centralMigrated = true;
        }

        $this->tenant = Tenant::query()->create([
            'id' => 'test_'.Str::lower(Str::random(10)),
            'name' => 'Test tenant',
        ]);

        tenancy()->initialize($this->tenant);
    }

    protected function tearDown(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        if (isset($this->tenant)) {
            $this->tenant->delete();
        }

        parent::tearDown();
    }

    protected function createTenantUser(array $overrides = []): User
    {
        tenancy()->initialize($this->tenant);

        $user = User::factory()->create($overrides);

        tenancy()->end();

        TenantAccount::query()->create([
            'tenant_id' => $this->tenant->id,
            'email' => strtolower($user->email),
        ]);

        tenancy()->initialize($this->tenant);

        return $user;
    }

    protected function actingAsTenantUser(?User $user = null): User
    {
        $user ??= $this->createTenantUser();

        $this->withSession(['tenant_id' => $this->tenant->id]);
        $this->actingAs($user);

        return $user;
    }

    private function mysqlAvailable(): bool
    {
        try {
            $this->configureTestConnections();
            DB::connection('central')->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function configureTestConnections(): void
    {
        $database = env('TEST_DB_DATABASE', 'orora_test');

        config([
            'database.connections.central.database' => $database,
            'database.connections.mysql.database' => $database,
            'tenancy.database.central_connection' => 'central',
        ]);
    }
}
