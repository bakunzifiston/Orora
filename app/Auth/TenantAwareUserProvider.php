<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;

class TenantAwareUserProvider extends EloquentUserProvider
{
    public function retrieveById($identifier)
    {
        if (! tenancy()->initialized) {
            return null;
        }

        return parent::retrieveById($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        if (! tenancy()->initialized) {
            return null;
        }

        return parent::retrieveByToken($identifier, $token);
    }
}
