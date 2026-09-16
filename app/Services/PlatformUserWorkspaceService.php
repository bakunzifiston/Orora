<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PlatformUserWorkspaceService
{
    /**
     * @return list<array{key: string, label: string, icon: string, count: int}>
     */
    public function relatedCounts(User $user): array
    {
        $tenantId = $user->tenant_id;

        if (! $tenantId) {
            return $this->definitions()
                ->map(fn (array $item) => [...$item, 'count' => 0])
                ->all();
        }

        return $this->definitions()
            ->map(function (array $item) use ($tenantId) {
                $item['count'] = $this->countTables($tenantId, $item['tables']);

                return $item;
            })
            ->all();
    }

    public function purge(User $user): void
    {
        $tenantId = $user->tenant_id;

        DB::transaction(function () use ($user, $tenantId): void {
            Schema::disableForeignKeyConstraints();

            try {
                if ($tenantId) {
                    $this->purgeTenant($tenantId);
                } else {
                    $this->deleteSessions([$user->id]);
                    DB::table('users')->where('id', $user->id)->delete();
                }
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        });
    }

    private function purgeTenant(string $tenantId): void
    {
        $userIds = Schema::hasTable('users') && Schema::hasColumn('users', 'tenant_id')
            ? DB::table('users')->where('tenant_id', $tenantId)->pluck('id')->all()
            : [];

        $farmIds = Schema::hasTable('farms') && Schema::hasColumn('farms', 'tenant_id')
            ? DB::table('farms')->where('tenant_id', $tenantId)->pluck('id')->all()
            : [];

        $this->deleteSessions($userIds);

        if ($farmIds !== []) {
            foreach ($this->tablesWithColumn('farm_id') as $table) {
                DB::table($table)->whereIn('farm_id', $farmIds)->delete();
            }
        }

        foreach ($this->tablesWithColumn('tenant_id') as $table) {
            if ($table === 'tenants') {
                continue;
            }

            DB::table($table)->where('tenant_id', $tenantId)->delete();
        }

        DB::table('tenants')->where('id', $tenantId)->delete();
    }

    /**
     * @param  list<int|string>  $userIds
     */
    private function deleteSessions(array $userIds): void
    {
        if ($userIds === [] || ! Schema::hasTable('sessions') || ! Schema::hasColumn('sessions', 'user_id')) {
            return;
        }

        DB::table('sessions')->whereIn('user_id', $userIds)->delete();
    }

    /**
     * @return Collection<int, array{key: string, label: string, icon: string, tables: list<string>}>
     */
    private function definitions()
    {
        return collect([
            ['key' => 'users', 'label' => 'Workspace users', 'icon' => 'customer', 'tables' => ['users']],
            ['key' => 'farms', 'label' => 'Farms', 'icon' => 'farm', 'tables' => ['farms']],
            ['key' => 'livestock', 'label' => 'Livestock groups', 'icon' => 'livestock', 'tables' => ['livestock']],
            ['key' => 'animals', 'label' => 'Animals', 'icon' => 'animal', 'tables' => ['animals']],
            ['key' => 'flocks', 'label' => 'Flocks', 'icon' => 'livestock', 'tables' => ['flocks']],
            ['key' => 'eggs', 'label' => 'Egg collections', 'icon' => 'box', 'tables' => ['egg_collections']],
            ['key' => 'milk', 'label' => 'Milk sessions', 'icon' => 'milk', 'tables' => ['milk_sessions', 'milk_records']],
            ['key' => 'sales', 'label' => 'Sales', 'icon' => 'sale', 'tables' => ['sale_transactions', 'sales']],
            ['key' => 'expenses', 'label' => 'Expenses', 'icon' => 'expense', 'tables' => ['expenses']],
            ['key' => 'health', 'label' => 'Health records', 'icon' => 'health', 'tables' => ['health_records', 'vaccinations', 'treatments', 'vet_visits', 'mortalities', 'disease_records']],
            ['key' => 'breeding', 'label' => 'Breeding records', 'icon' => 'breeding', 'tables' => ['breeding_records']],
            ['key' => 'feeding', 'label' => 'Feedings', 'icon' => 'feeding', 'tables' => ['feedings', 'feeding_schedules']],
            ['key' => 'certificates', 'label' => 'Certificates', 'icon' => 'certificate', 'tables' => ['certificates']],
            ['key' => 'employees', 'label' => 'Employees', 'icon' => 'employee', 'tables' => ['employees']],
            ['key' => 'customers', 'label' => 'Customers', 'icon' => 'customer', 'tables' => ['customers']],
            ['key' => 'listings', 'label' => 'Marketplace listings', 'icon' => 'sale', 'tables' => ['marketplace_listings']],
        ]);
    }

    /**
     * @param  list<string>  $tables
     */
    private function countTables(string $tenantId, array $tables): int
    {
        $total = 0;

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            $total += (int) DB::table($table)->where('tenant_id', $tenantId)->count();
        }

        return $total;
    }

    /**
     * @return list<string>
     */
    private function tablesWithColumn(string $column): array
    {
        $tables = [];

        foreach ($this->databaseTables() as $table) {
            try {
                if (Schema::hasColumn($table, $column)) {
                    $tables[] = $table;
                }
            } catch (Throwable) {
                continue;
            }
        }

        return $tables;
    }

    /**
     * @return list<string>
     */
    private function databaseTables(): array
    {
        if (method_exists(Schema::getFacadeRoot(), 'getTableListing')) {
            return Schema::getTableListing();
        }

        return collect(Schema::getTables())->pluck('name')->all();
    }
}
