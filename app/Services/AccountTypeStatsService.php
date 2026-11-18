<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\Farm;
use App\Models\SaleTransaction;
use App\Models\Tenant;
use App\Models\User;
use App\Support\AccountType;

/**
 * Side-by-side figures for real vs testing accounts (Super Admin).
 * Counted explicitly per type, independent of the selected dashboard view.
 */
class AccountTypeStatsService
{
    /**
     * @return array{real: array<string,int|float>, testing: array<string,int|float>}
     */
    public function compare(): array
    {
        return [
            AccountType::REAL => $this->forType(AccountType::REAL),
            AccountType::TESTING => $this->forType(AccountType::TESTING),
        ];
    }

    /**
     * @return array{workspaces:int,users:int,farms:int,animals:int,sales:int,revenue:float}
     */
    public function forType(string $type): array
    {
        return AccountType::within(null, function () use ($type): array {
            $workspaces = AccountType::apply(Tenant::query(), 'tenants.id', $type)->count();

            $users = AccountType::apply(User::query()->withoutGlobalScope('tenant'), 'users.tenant_id', $type)->count();
            $farms = AccountType::apply(Farm::query()->withoutGlobalScope('tenant'), 'farms.tenant_id', $type)->count();
            $animals = AccountType::apply(Animal::query()->withoutGlobalScope('tenant'), 'animals.tenant_id', $type)->count();

            $sales = AccountType::apply(
                SaleTransaction::query()->withoutGlobalScope('tenant')->where('sale_status', 'completed'),
                'sale_transactions.tenant_id',
                $type,
            );

            return [
                'workspaces' => $workspaces,
                'users' => $users,
                'farms' => $farms,
                'animals' => $animals,
                'sales' => (clone $sales)->count(),
                'revenue' => (float) (clone $sales)->sum('total_amount'),
            ];
        });
    }
}
