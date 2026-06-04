<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = ['period_type', 'start_date', 'end_date'];

    public function up(): void
    {
        $table = 'finance_periods';

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_id')) {
            return;
        }

        $compositeName = 'finance_periods_tenant_period_unique';

        if ($this->hasIndex($table, $compositeName)) {
            return;
        }

        $legacyIndex = $this->multiColumnUniqueIndex($table, $this->columns);

        if ($legacyIndex === null) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($legacyIndex, $compositeName): void {
            $blueprint->dropUnique($legacyIndex);
            $blueprint->unique(['tenant_id', ...$this->columns], $compositeName);
        });
    }

    public function down(): void
    {
        $table = 'finance_periods';
        $compositeName = 'finance_periods_tenant_period_unique';

        if (! Schema::hasTable($table) || ! $this->hasIndex($table, $compositeName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table, $compositeName): void {
            $blueprint->dropUnique($compositeName);

            if ($this->multiColumnUniqueIndex($table, $this->columns) === null) {
                $blueprint->unique($this->columns);
            }
        });
    }

    private function multiColumnUniqueIndex(string $table, array $columns): ?string
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (! ($index['unique'] ?? false) || ($index['primary'] ?? false)) {
                continue;
            }

            if (($index['columns'] ?? []) === $columns) {
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
