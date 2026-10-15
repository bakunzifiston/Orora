<?php

namespace App\Models\Concerns;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            if (! $tenantId = TenantContext::id()) {
                return;
            }

            $model = $builder->getModel();

            if (! Schema::hasColumn($model->getTable(), 'tenant_id')) {
                return;
            }

            $builder->where($model->getTable().'.tenant_id', $tenantId);
        });

        static::creating(function (Model $model): void {
            if (! Schema::hasColumn($model->getTable(), 'tenant_id')) {
                return;
            }

            if (! $model->getAttribute('tenant_id') && TenantContext::id()) {
                $model->setAttribute('tenant_id', TenantContext::id());
            }
        });
    }

}