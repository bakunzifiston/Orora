<?php

namespace App\Http\Middleware;

use App\Services\TenantAccountService;
use App\Services\TenantContext;
use App\Support\CentralDomains;
use App\Support\TenancyMode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeDefaultTenant
{
    public function __construct(private readonly TenantAccountService $tenantAccounts) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin', 'admin/*')) {
            return $next($request);
        }

        if (! $this->isCentralDomain($request)) {
            return $next($request);
        }

        if (! $request->hasSession()) {
            return $next($request);
        }

        if (! $this->tenancyIsActive()) {
            $this->tenantAccounts->initializeFromRequest($request);
        }

        if (! $this->tenancyIsActive() && $this->tenantAccounts->requestExpectsAuthenticatedUser($request)) {
            $this->clearStaleAuthState($request);

            $response = redirect()
                ->route('login')
                ->with('error', 'Your session expired. Please sign in again.');

            foreach ($request->cookies->keys() as $key) {
                if (str_starts_with($key, 'remember_web_')) {
                    $response = $response->withoutCookie($key);
                }
            }

            $this->tenantAccounts->forgetTenantCookies();

            return $response;
        }

        return $next($request);
    }

    protected function isCentralDomain(Request $request): bool
    {
        return CentralDomains::isCentralHost($request->getHost());
    }

    protected function tenancyIsActive(): bool
    {
        if (TenancyMode::usesSingleDatabase()) {
            return TenantContext::isActive();
        }

        return tenancy()->initialized;
    }

    protected function clearStaleAuthState(Request $request): void
    {
        $session = $request->session();

        foreach (array_keys($session->all()) as $key) {
            if (str_starts_with($key, 'login_web_')) {
                $session->forget($key);
            }
        }

        $session->regenerateToken();
    }
}
