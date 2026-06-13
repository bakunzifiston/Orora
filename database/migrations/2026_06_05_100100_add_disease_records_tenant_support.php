<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('disease_records')) {
            return;
        }

        if (! Schema::hasColumn('disease_records', 'tenant_id')) {
            Schema::table('disease_records', function (Blueprint $blueprint): void {
                $blueprint->string('tenant_id')->nullable()->after('id');
                $blueprint->index('tenant_id');
                $blueprint->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        $compositeName = 'disease_records_tenant_id_disease_code_unique';

        if ($this->hasIndex('disease_records', $compositeName)) {
            return;
        }

        $legacyIndex = $this->singleColumnUniqueIndex('disease_records', 'disease_code');

        if ($legacyIndex === null) {
            return;
        }

        Schema::table('disease_records', function (Blueprint $blueprint) use ($legacyIndex, $compositeName): void {
            $blueprint->dropUnique($legacyIndex);
            $blueprint->unique(['tenant_id', 'disease_code'], $compositeName);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('disease_records')) {
            return;
        }

        $compositeName = 'disease_records_tenant_id_disease_code_unique';

        if ($this->hasIndex('disease_records', $compositeName)) {
            Schema::table('disease_records', function (Blueprint $blueprint) use ($compositeName): void {
                $blueprint->dropUnique($compositeName);
                $blueprint->unique('disease_code');
            });
        }

        if (Schema::hasColumn('disease_records', 'tenant_id')) {
            Schema::table('disease_records', function (Blueprint $blueprint): void {
                $blueprint->dropForeign(['tenant_id']);
                $blueprint->dropIndex(['tenant_id']);
                $blueprint->dropColumn('tenant_id');
            });
        }
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
