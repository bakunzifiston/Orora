<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeDefaultTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isCentralDomain($request)) {
            return $next($request);
        }

        if (! tenancy()->initialized) {
            $tenantId = $request->session()->get('tenant_id')
                ?? config('tenancy.default_tenant_id');

            if ($tenantId && $tenant = Tenant::find($tenantId)) {
                tenancy()->initialize($tenant);
            }
        }

        return $next($request);
    }

    protected function isCentralDomain(Request $request): bool
    {
        return in_array($request->getHost(), config('tenancy.central_domains', []), true);
    }
}
