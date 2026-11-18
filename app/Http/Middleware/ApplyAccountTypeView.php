<?php

namespace App\Http\Middleware;

use App\Support\AccountType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Super Admin only. Decides whether platform-wide analytics include real accounts, testing
 * accounts, or both, from the "account_type" query parameter (real | testing | all).
 *
 * Aggregate screens (dashboard, farm directory) default to REAL accounts only, so testing
 * accounts never inflate production numbers. Detail and management screens (a single
 * account, farm, tenant) always see everything so any record can be opened and reclassified.
 */
class ApplyAccountTypeView
{
    /** Route names whose data is an aggregate / listing and defaults to real accounts. */
    private const AGGREGATE_ROUTES = [
        'central.dashboard',
        'central.farms.index',
        'central.ecommerce.index',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $name = (string) $request->route()?->getName();
        $requested = $request->query('account_type');

        if (in_array($name, self::AGGREGATE_ROUTES, true)) {
            AccountType::useView(in_array($requested, ['real', 'testing', 'all'], true) ? $requested : AccountType::REAL);
        } else {
            AccountType::useView(null);
        }

        try {
            return $next($request);
        } finally {
            AccountType::useView(null);
        }
    }
}
