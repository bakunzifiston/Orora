<?php

namespace App\Support;

class TenancyDatabaseNaming
{
    /**
     * Tenant DB name prefix (cPanel: account_user + "_tenant").
     */
    public static function prefix(): string
    {
        $explicit = env('TENANCY_DATABASE_PREFIX');

        if (is_string($explicit) && $explicit !== '') {
            return $explicit;
        }

        $central = (string) env('DB_DATABASE', 'orora');

        if (str_contains($central, '_')) {
            return strstr($central, '_', true).'_tenant';
        }

        return 'tenant';
    }
}
