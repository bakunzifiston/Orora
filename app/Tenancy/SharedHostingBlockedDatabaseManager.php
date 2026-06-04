<?php

namespace App\Tenancy;

use RuntimeException;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager;

/**
 * Prevents opaque MySQL 1044 errors on cPanel when API credentials are not configured.
 */
class SharedHostingBlockedDatabaseManager extends MySQLDatabaseManager
{
    public function createDatabase(TenantWithDatabase $tenant): bool
    {
        throw new RuntimeException(
            'This server uses cPanel-style MySQL (user cannot CREATE DATABASE via SQL). '
            .'Set TENANCY_USE_CPANEL=true and configure TENANCY_CPANEL_HOST, TENANCY_CPANEL_USERNAME, '
            .'and TENANCY_CPANEL_API_TOKEN in .env, then run php artisan config:clear. '
            .'See MULTITENANCY.md.'
        );
    }
}
