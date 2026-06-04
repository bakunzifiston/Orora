<?php

namespace App\Services;

use App\Models\Tenant;

class TenantContext
{
    protected static ?string $tenantId = null;

    public static function id(): ?string
    {
        return static::$tenantId;
    }

    public static function isActive(): bool
    {
        return static::$tenantId !== null;
    }

    public static function tenant(): ?Tenant
    {
        if (! static::$tenantId) {
            return null;
        }

        return Tenant::query()->find(static::$tenantId);
    }

    public static function set(Tenant|string $tenant): Tenant
    {
        $tenant = $tenant instanceof Tenant
            ? $tenant
            : Tenant::query()->findOrFail($tenant);

        static::$tenantId = $tenant->id;

        return $tenant;
    }

    public static function forget(): void
    {
        static::$tenantId = null;
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function run(Tenant|string $tenant, callable $callback): mixed
    {
        $previous = static::$tenantId;

        static::set($tenant);

        try {
            return $callback();
        } finally {
            static::$tenantId = $previous;
        }
    }
}
