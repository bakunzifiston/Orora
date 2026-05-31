<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
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
            $tenantId = config('tenancy.default_tenant_id');

            if ($request->hasSession()) {
                $tenantId = $request->session()->get('tenant_id', $tenantId);
            }

            if ($tenantId && $tenant = Tenant::find($tenantId)) {
                tenancy()->initialize($tenant);

                return $next($request);
            }
        }

        return app(InitializeTenancyByDomain::class)->handle($request, $next);
    }

    protected function isCentralDomain(Request $request): bool
    {
        return in_array($request->getHost(), config('tenancy.central_domains', []), true);
    }
}
