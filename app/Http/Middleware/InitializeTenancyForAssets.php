<?php

namespace App\Http\Middleware;

use App\Services\TenantAccountService;
use App\Services\TenantContext;
use App\Support\CentralDomains;
use App\Support\TenancyMode;
use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyForAssets
{
    public function handle(Request $request, Closure $next): Response
    {
        if (TenancyMode::usesSingleDatabase()) {
            if (! TenantContext::isActive() && $this->isCentralDomain($request)) {
                app(TenantAccountService::class)->initializeFromRequest($request);
            }

            return $next($request);
        }

        if (tenancy()->initialized) {
            return $next($request);
        }

        if ($this->isCentralDomain($request)) {
            app(TenantAccountService::class)->initializeFromRequest($request);

            return $next($request);
        }

        return app(InitializeTenancyByDomain::class)->handle($request, $next);
    }

    protected function isCentralDomain(Request $request): bool
    {
        return CentralDomains::isCentralHost($request->getHost());
    }
}
