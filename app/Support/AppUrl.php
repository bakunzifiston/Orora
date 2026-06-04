<?php

namespace App\Support;

class AppUrl
{
    /**
     * Normalized application base URL (always includes http:// or https://).
     */
    public static function base(): string
    {
        $url = trim((string) env('APP_URL', 'http://localhost'));

        if ($url === '') {
            return 'http://localhost';
        }

        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.ltrim($url, '/');
        }

        return rtrim($url, '/');
    }
}
