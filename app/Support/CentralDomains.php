<?php

namespace App\Support;

class CentralDomains
{
    /**
     * Hosts that run the main app (login, register, session-based tenant).
     *
     * @return list<string>
     */
    public static function all(): array
    {
        $domains = array_filter(array_map(
            static fn (string $domain): string => trim($domain),
            explode(',', (string) env('CENTRAL_DOMAINS', '127.0.0.1,localhost'))
        ));

        $host = parse_url((string) env('APP_URL', ''), PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return array_values(array_unique($domains));
        }

        $domains[] = $host;

        if (str_starts_with($host, 'www.')) {
            $domains[] = substr($host, 4);
        } else {
            $domains[] = 'www.'.$host;
        }

        return array_values(array_unique(array_filter($domains)));
    }
}
