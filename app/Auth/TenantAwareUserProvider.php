<?php

namespace App\Auth;

use App\Services\TenantContext;
use Illuminate\Auth\EloquentUserProvider;

class TenantAwareUserProvider extends EloquentUserProvider
{
    public function retrieveById($identifier)
    {
        if (! $this->tenancyIsActive()) {
            return null;
        }

        return parent::retrieveById($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        if (! $this->tenancyIsActive()) {
            return null;
        }

        return parent::retrieveByToken($identifier, $token);
    }

    protected function tenancyIsActive(): bool
    {
        if (config('tenancy.single_database', true)) {
            return TenantContext::isActive();
        }

        return tenancy()->initialized;
    }
}
