<?php

namespace App\Models\Concerns;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            if ($tenantId = TenantContext::id()) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', $tenantId);
            }
        });

        static::creating(function (Model $model): void {
            if (! $model->getAttribute('tenant_id') && TenantContext::id()) {
                $model->setAttribute('tenant_id', TenantContext::id());
            }
        });
    }

}