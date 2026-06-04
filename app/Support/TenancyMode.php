<?php

namespace App\Support;

class TenancyMode
{
    public static function usesSingleDatabase(): bool
    {
        return filter_var(env('TENANCY_SINGLE_DATABASE', true), FILTER_VALIDATE_BOOL);
    }

    /**
     * @return list<class-string>
     */
    public static function bootstrappers(): array
    {
        if (self::usesSingleDatabase()) {
            return [];
        }

        return [
            \Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
            \Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class,
            \Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,
            \Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
        ];
    }
}
