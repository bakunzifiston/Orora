<?php

namespace App\Http\Middleware;

use App\Services\TenantAccountService;
use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyForAssets
{
    public function handle(Request $request, Closure $next): Response
    {
        if (tenancy()->initialized) {
            return $next($request);
        }

        if ($this->isCentralDomain($request)) {
            app(TenantAccountService::class)->initializeFromRequest($request);
        }

        return app(InitializeTenancyByDomain::class)->handle($request, $next);
    }

    protected function isCentralDomain(Request $request): bool
    {
        return in_array($request->getHost(), config('tenancy.central_domains', []), true);
    }
}
