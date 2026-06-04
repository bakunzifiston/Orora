<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Single-database tenancy: business codes must be unique per tenant, not globally.
     *
     * @var array<string, string>
     */
    private array $codeColumns = [
        'customers' => 'customer_code',
        'employees' => 'employee_code',
        'expense_categories' => 'code',
        'milk_sessions' => 'session_code',
        'milk_storage' => 'storage_code',
        'milk_records' => 'record_code',
        'breeding_records' => 'breeding_code',
        'pregnancy_checks' => 'check_code',
        'birth_records' => 'birth_code',
        'offspring' => 'offspring_code',
        'abattoir_dispatches' => 'dispatch_code',
        'sale_transactions' => 'sale_number',
        'sale_payments' => 'payment_reference',
        'finance_accounts' => 'account_code',
        'finance_transactions' => 'transaction_code',
        'finance_budgets' => 'budget_code',
        'finance_tax_rates' => 'tax_code',
    ];

    public function up(): void
    {
        foreach ($this->codeColumns as $table => $column) {
            $this->rescopeUnique($table, $column);
        }
    }

    public function down(): void
    {
        foreach ($this->codeColumns as $table => $column) {
            $this->restoreGlobalUnique($table, $column);
        }
    }

    private function rescopeUnique(string $table, string $column): void
    {
        if (! Schema::hasTable($table)
            || ! Schema::hasColumn($table, 'tenant_id')
            || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $compositeName = "{$table}_tenant_id_{$column}_unique";

        if ($this->hasIndex($table, $compositeName)) {
            return;
        }

        $singleColumnIndex = $this->singleColumnUniqueIndex($table, $column);

        if ($singleColumnIndex === null) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($singleColumnIndex, $compositeName, $column): void {
            $blueprint->dropUnique($singleColumnIndex);
            $blueprint->unique(['tenant_id', $column], $compositeName);
        });
    }

    private function restoreGlobalUnique(string $table, string $column): void
    {
        if (! Schema::hasTable($table)
            || ! Schema::hasColumn($table, 'tenant_id')
            || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $compositeName = "{$table}_tenant_id_{$column}_unique";

        if (! $this->hasIndex($table, $compositeName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table, $column, $compositeName): void {
            $blueprint->dropUnique($compositeName);

            if ($this->singleColumnUniqueIndex($table, $column) === null) {
                $blueprint->unique($column);
            }
        });
    }

    private function singleColumnUniqueIndex(string $table, string $column): ?string
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (! ($index['unique'] ?? false) || ($index['primary'] ?? false)) {
                continue;
            }

            if (($index['columns'] ?? []) === [$column]) {
                return $index['name'];
            }
        }

        return null;
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn (array $index) => ($index['name'] ?? '') === $indexName);
    }
};
