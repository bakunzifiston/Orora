<?php

namespace App\Http\Middleware;

use App\Services\TenantAccountService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeDefaultTenant
{
    public function __construct(private readonly TenantAccountService $tenantAccounts) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isCentralDomain($request)) {
            return $next($request);
        }

        if (! tenancy()->initialized) {
            $this->tenantAccounts->initializeForSession(
                $request->session()->get('tenant_id'),
            );
        }

        return $next($request);
    }

    protected function isCentralDomain(Request $request): bool
    {
        return in_array($request->getHost(), config('tenancy.central_domains', []), true);
    }
}
