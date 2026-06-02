<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantAccountService
{
    public const TENANT_COOKIE = 'orora_tenant_id';

    public const EMAIL_COOKIE = 'orora_auth_email';
    public function tenantIdForEmail(string $email): ?string
    {
        return TenantAccount::query()
            ->where('email', Str::lower($email))
            ->value('tenant_id');
    }

    public function initializeForEmail(string $email): ?Tenant
    {
        $tenantId = $this->tenantIdForEmail($email);

        if (! $tenantId) {
            return $this->initializeDefaultTenantIfConfigured();
        }

        return $this->initializeTenant($tenantId);
    }

    public function initializeForSession(?string $tenantId): ?Tenant
    {
        if ($tenantId) {
            return $this->initializeTenant($tenantId);
        }

        return null;
    }

    public function initializeFromRequest(Request $request): ?Tenant
    {
        $session = $request->session();

        foreach ($this->tenantIdCandidates($request) as $tenantId) {
            if ($tenant = $this->initializeForSession($tenantId)) {
                $session->put('tenant_id', $tenant->id);

                return $tenant;
            }
        }

        foreach ($this->emailCandidates($request) as $email) {
            if ($tenant = $this->initializeForEmail($email)) {
                $session->put('tenant_id', $tenant->id);
                $session->put('auth_email', Str::lower($email));

                return $tenant;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public function tenantIdCandidates(Request $request): array
    {
        return array_values(array_filter(array_unique([
            $request->session()->get('tenant_id'),
            $request->cookie(self::TENANT_COOKIE),
        ])));
    }

    /**
     * @return list<string>
     */
    public function emailCandidates(Request $request): array
    {
        return array_values(array_filter(array_unique([
            $request->session()->get('auth_email'),
            $request->cookie(self::EMAIL_COOKIE),
        ])));
    }

    public function requestExpectsAuthenticatedUser(Request $request): bool
    {
        foreach (array_keys($request->session()->all()) as $key) {
            if (str_starts_with($key, 'login_web_')) {
                return true;
            }
        }

        foreach ($request->cookies->keys() as $key) {
            if (str_starts_with($key, 'remember_web_')) {
                return true;
            }
        }

        return false;
    }

    public function rememberTenantCookies(string $tenantId, string $email): void
    {
        $minutes = 525600; // ~1 year; auth still required via session / remember cookie

        cookie()->queue(cookie()->make(self::TENANT_COOKIE, $tenantId, $minutes));
        cookie()->queue(cookie()->make(self::EMAIL_COOKIE, Str::lower($email), $minutes));
    }

    public function forgetTenantCookies(): void
    {
        cookie()->queue(cookie()->forget(self::TENANT_COOKIE));
        cookie()->queue(cookie()->forget(self::EMAIL_COOKIE));
    }

    /**
     * @return array{tenant: Tenant, user: User}
     */
    public function registerAccount(string $name, string $email, string $password): array
    {
        $email = Str::lower($email);

        $this->endTenancy();

        $tenant = Tenant::create([
            'id' => $this->generateTenantId($email),
            'name' => $name."'s farm",
        ]);

        $this->initializeTenant($tenant->id);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        TenantAccount::create([
            'tenant_id' => $tenant->id,
            'email' => $email,
        ]);

        return ['tenant' => $tenant, 'user' => $user];
    }

    public function endTenancy(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }
    }

    private function initializeTenant(string $tenantId): ?Tenant
    {
        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            return null;
        }

        if (! tenancy()->initialized || tenant('id') !== $tenant->id) {
            tenancy()->initialize($tenant);
        }

        return $tenant;
    }

    private function initializeDefaultTenantIfConfigured(): ?Tenant
    {
        $defaultId = config('tenancy.default_tenant_id');

        if (! $defaultId) {
            return null;
        }

        return $this->initializeTenant($defaultId);
    }

    private function generateTenantId(string $email): string
    {
        $base = Str::slug(Str::before($email, '@'));
        $base = $base !== '' ? Str::limit($base, 20, '') : 'farm';
        $candidate = $base;
        $suffix = 0;

        while (Tenant::find($candidate)) {
            $suffix++;
            $candidate = $base.'-'.$suffix;
        }

        return $candidate;
    }
}
