<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\TenantAccount;
use App\Services\TenantContext;
use Database\Seeders\Tenant\ComprehensiveDemoSeeder;
use Illuminate\Database\Seeder;

class TenantDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = config('demo.tenant_id', 'demo');

        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            $tenant = Tenant::create([
                'id' => $tenantId,
                'name' => 'Orora Demo Farm',
            ]);
        }

        TenantContext::run($tenant, function () {
            $this->call(ComprehensiveDemoSeeder::class);
        });

        TenantAccount::query()->updateOrCreate(
            ['email' => strtolower(config('demo.user.email'))],
            ['tenant_id' => $tenant->id],
        );

        $this->command?->newLine();
        $this->command?->info('Demo tenant ready: '.$tenant->id);
        $this->command?->info('Sign in at http://127.0.0.1:8000/');
        $this->command?->info('  Email: '.config('demo.user.email'));
        $this->command?->info('  Password: '.config('demo.user.password'));
    }
}
