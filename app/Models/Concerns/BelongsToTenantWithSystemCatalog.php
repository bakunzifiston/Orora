<?php

namespace App\Models\Concerns;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Tenant isolation with shared system rows (is_system = true) visible to every tenant.
 */
trait BelongsToTenantWithSystemCatalog
{
    public static function bootBelongsToTenantWithSystemCatalog(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            if ($tenantId = TenantContext::id()) {
                $table = $builder->getModel()->getTable();
                $builder->where(function (Builder $query) use ($table, $tenantId): void {
                    $query->where("{$table}.tenant_id", $tenantId)
                        ->orWhere("{$table}.is_system", true);
                });
            }
        });

        static::creating(function (Model $model): void {
            if ($model->getAttribute('is_system')) {
                return;
            }

            if (! $model->getAttribute('tenant_id') && TenantContext::id()) {
                $model->setAttribute('tenant_id', TenantContext::id());
            }
        });
    }
}
