<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\TenantAccount;
use Database\Seeders\Tenant\ComprehensiveDemoSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

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

            $tenant->domains()->firstOrCreate(['domain' => 'demo.localhost']);
        }

        Artisan::call('tenants:migrate', [
            '--tenants' => [$tenant->id],
            '--force' => true,
        ]);

        tenancy()->initialize($tenant);

        try {
            $this->call(ComprehensiveDemoSeeder::class);
        } finally {
            tenancy()->end();
        }

        TenantAccount::on('central')->updateOrCreate(
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
