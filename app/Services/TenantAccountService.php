<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantAccount;
use App\Models\User;
use Illuminate\Support\Str;

class TenantAccountService
{
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
