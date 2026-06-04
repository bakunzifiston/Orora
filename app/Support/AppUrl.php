<?php

namespace App\Support;

class AppUrl
{
    /**
     * Normalized application base URL (scheme + host [+ port], no path).
     */
    public static function base(): string
    {
        return self::normalizeRaw((string) env('APP_URL', 'http://localhost'));
    }

    /**
     * Fix APP_URL values from .env (quotes, missing scheme, empty host, etc.).
     */
    public static function normalizeRaw(string $url): string
    {
        $url = trim($url, " \t\n\r\0\x0B\"'");

        if ($url === '') {
            return 'http://localhost';
        }

        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.ltrim($url, '/');
        }

        $parts = parse_url($url);
        $host = $parts['host'] ?? null;

        if (! is_string($host) || $host === '' || ! self::isValidHost($host)) {
            return 'http://localhost';
        }

        $scheme = strtolower($parts['scheme'] ?? 'https');
        if (! in_array($scheme, ['http', 'https'], true)) {
            $scheme = 'https';
        }

        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $scheme.'://'.$host.$port;
    }

    /**
     * Apply a safe APP_URL before Laravel boots (Artisan on broken .env values).
     */
    public static function applyToEnvironment(): void
    {
        $normalized = self::normalizeRaw(
            (string) (getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? 'http://localhost'))
        );

        putenv('APP_URL='.$normalized);
        $_ENV['APP_URL'] = $normalized;
        $_SERVER['APP_URL'] = $normalized;
    }

    protected static function isValidHost(string $host): bool
    {
        if (str_contains($host, ' ') || str_contains($host, '/')) {
            return false;
        }

        return (bool) filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
            || filter_var($host, FILTER_VALIDATE_IP);
    }
}
