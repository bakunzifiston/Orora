<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class TenantStorageUrl
{
    public static function forPublicDisk(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (function_exists('tenancy') && tenancy()->initialized) {
            return route('stancl.tenancy.asset', ['path' => $path], absolute: false);
        }

        return Storage::disk('public')->url($path);
    }
}
