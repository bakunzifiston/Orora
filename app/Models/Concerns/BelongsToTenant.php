<?php

namespace App\Models\Concerns;

use App\Services\TenantContext;
use App\Support\AccountType;
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

        // Super Admin analytics can exclude testing accounts. No-op inside a workspace, and
        // no-op unless a view ("real" / "testing") has been selected (see AccountType).
        static::addGlobalScope('account_type', function (Builder $builder): void {
            $model = $builder->getModel();

            if (AccountType::view() === null || ! Schema::hasColumn($model->getTable(), 'tenant_id')) {
                return;
            }

            AccountType::constrainToView($builder, $model->getTable().'.tenant_id');
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