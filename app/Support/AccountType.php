<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Contracts\Database\Query\Builder as BuilderContract;
use Illuminate\Support\Facades\Schema;

/**
 * Real vs testing account classification, and the platform-wide "view" that decides
 * which of the two kinds the Super Admin analytics include.
 *
 * - The account type lives on the tenant (workspace) and is inherited by its users, farms and data.
 * - The "view" only matters when no workspace is active (central Super Admin screens).
 *   View = null means "no filtering", which is how the whole application behaves by default.
 */
class AccountType
{
    public const REAL = 'real';

    public const TESTING = 'testing';

    public const TYPES = [self::REAL, self::TESTING];

    private static ?string $view = null;

    /** @var list<string>|null */
    private static ?array $testingIds = null;

    private static ?bool $ready = null;

    public static function label(?string $type): string
    {
        return $type === self::TESTING ? 'Testing' : 'Active';
    }

    public static function normalize(?string $type): string
    {
        return $type === self::TESTING ? self::TESTING : self::REAL;
    }

    /** False until the migration that adds tenants.account_type has run (keeps deploys safe). */
    public static function ready(): bool
    {
        return self::$ready ??= Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'account_type');
    }

    /** @return list<string> */
    public static function testingTenantIds(): array
    {
        if (! self::ready()) {
            return [];
        }

        return self::$testingIds ??= Tenant::query()
            ->where('account_type', self::TESTING)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public static function isTestingTenant(?string $tenantId): bool
    {
        return $tenantId !== null && in_array($tenantId, self::testingTenantIds(), true);
    }

    /** Forget cached ids (call after a classification change). */
    public static function flush(): void
    {
        self::$testingIds = null;
        self::$ready = null;
    }

    public static function view(): ?string
    {
        return self::$view;
    }

    /** @param  string|null  $view  "real", "testing", or null/"all" for no filtering */
    public static function useView(?string $view): void
    {
        self::$view = in_array($view, self::TYPES, true) ? $view : null;
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function within(?string $view, callable $callback): mixed
    {
        $previous = self::$view;
        self::useView($view);

        try {
            return $callback();
        } finally {
            self::$view = $previous;
        }
    }

    /**
     * Limit a query to one account type (or leave it alone when $type is not real/testing).
     * Rows with no tenant (null) are treated as real.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    public static function apply($query, string $tenantColumn, ?string $type)
    {
        if (! in_array($type, self::TYPES, true) || ! self::ready()) {
            return $query;
        }

        $ids = self::testingTenantIds();

        if ($type === self::TESTING) {
            return $ids === [] ? $query->whereRaw('1 = 0') : $query->whereIn($tenantColumn, $ids);
        }

        if ($ids === []) {
            return $query;
        }

        return $query->where(function ($q) use ($tenantColumn, $ids) {
            $q->whereNull($tenantColumn)->orWhereNotIn($tenantColumn, $ids);
        });
    }

    /** Applied by the global "account_type" scope; does nothing inside a workspace or when no view is set. */
    public static function constrainToView($query, string $tenantColumn)
    {
        if (\App\Services\TenantContext::id() !== null) {
            return $query;
        }

        return self::apply($query, $tenantColumn, self::$view);
    }
}
