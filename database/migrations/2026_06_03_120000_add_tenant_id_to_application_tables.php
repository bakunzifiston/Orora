<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Application tables that belong to a farm account (tenant).
     *
     * @var list<string>
     */
    private array $tenantTables = [
        'users',
        'farms',
        'farm_members',
        'livestock',
        'animals',
        'feedings',
        'certificates',
        'movements',
        'sales',
        'health_records',
        'vaccinations',
        'treatments',
        'vet_visits',
        'mortalities',
        'disease_records',
        'feed_suppliers',
        'feed_types',
        'feed_inventories',
        'feed_inventory_movements',
        'feeding_schedules',
        'expense_categories',
        'expense_vendors',
        'expenses',
        'milk_records',
        'milk_sessions',
        'milk_storage',
        'milk_storage_movements',
        'milk_sales',
        'milk_sale_items',
        'milk_sale_payments',
        'milk_sale_logs',
        'breeding_records',
        'pregnancy_checks',
        'birth_records',
        'offspring',
        'breeding_logs',
        'customers',
        'customer_profiles',
        'customer_contacts',
        'customer_addresses',
        'customer_credit',
        'customer_documents',
        'customer_communications',
        'customer_logs',
        'buyers',
        'abattoir_dispatches',
        'abattoir_dispatch_animals',
        'abattoir_returns',
        'sale_transactions',
        'sale_items',
        'sale_payments',
        'sale_documents',
        'sale_logs',
        'employees',
        'employee_profiles',
        'employee_emergency_contacts',
        'employee_addresses',
        'employee_farm_assignments',
        'employee_payroll',
        'employee_documents',
        'employee_logs',
        'finance_accounts',
        'finance_periods',
        'finance_transactions',
        'finance_transaction_lines',
        'finance_transaction_logs',
        'finance_budgets',
        'finance_budget_items',
        'finance_tax_rates',
        'finance_tax_entries',
        'finance_balance_snapshots',
    ];

    public function up(): void
    {
        foreach ($this->tenantTables as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->string('tenant_id')->nullable()->after('id');
                $blueprint->index('tenant_id');
                $blueprint->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'email')) {
            Schema::table('users', function (Blueprint $blueprint): void {
                $blueprint->dropUnique(['email']);
                $blueprint->unique(['tenant_id', 'email']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $blueprint): void {
                $blueprint->dropUnique(['tenant_id', 'email']);
                $blueprint->unique('email');
            });
        }

        foreach (array_reverse($this->tenantTables) as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropForeign(['tenant_id']);
                $blueprint->dropIndex(['tenant_id']);
                $blueprint->dropColumn('tenant_id');
            });
        }
    }
};
