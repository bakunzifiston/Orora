<?php

namespace App\Support;

class TenancyCpanel
{
    public static function isConfigured(): bool
    {
        return self::hasCredentials();
    }

    public static function hasCredentials(): bool
    {
        return self::filled(config('tenancy.cpanel.host'))
            && self::filled(config('tenancy.cpanel.username'))
            && self::filled(config('tenancy.cpanel.api_token'));
    }

    protected static function filled(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }
}
