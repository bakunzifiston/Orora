<?php

namespace App\Services\Marketplace;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Schema;

class MarketplaceDatabase
{
    private static ?bool $shopReady = null;

    private static ?bool $learningReady = null;

    private static ?bool $contactReady = null;

    public static function shopReady(): bool
    {
        if (self::$shopReady === null) {
            self::$shopReady = self::hasTables(['marketplace_categories', 'marketplace_listings']);
        }

        return self::$shopReady;
    }

    public static function learningReady(): bool
    {
        if (self::$learningReady === null) {
            self::$learningReady = self::hasTables(['learning_categories', 'learning_posts']);
        }

        return self::$learningReady;
    }

    public static function contactReady(): bool
    {
        if (self::$contactReady === null) {
            self::$contactReady = self::hasTable('contact_messages');
        }

        return self::$contactReady;
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @param  T  $default
     * @return T
     */
    public static function safe(callable $callback, mixed $default): mixed
    {
        try {
            return $callback();
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function emptyPaginator(int $perPage = 12): LengthAwarePaginator
    {
        return new Paginator([], 0, $perPage);
    }

    /**
     * @param  array<int, string>  $tables
     */
    private static function hasTables(array $tables): bool
    {
        foreach ($tables as $table) {
            if (! self::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    private static function hasTable(string $table): bool
    {
        try {
            return Schema::connection('central')->hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
