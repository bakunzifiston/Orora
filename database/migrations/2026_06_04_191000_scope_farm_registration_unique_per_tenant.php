<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = 'farms';
        $column = 'registration_number';

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

    public function down(): void
    {
        $table = 'farms';
        $column = 'registration_number';
        $compositeName = "{$table}_tenant_id_{$column}_unique";

        if (! Schema::hasTable($table) || ! $this->hasIndex($table, $compositeName)) {
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
