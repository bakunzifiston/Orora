<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Database\Seeders\TenantDemoSeeder;
use Illuminate\Console\Command;

class SeedDemoCommand extends Command
{
    protected $signature = 'orora:seed-demo
                            {--fresh : Delete and recreate the demo tenant database before seeding}';

    protected $description = 'Seed the Orora demo tenant with linked Rwanda farm data';

    public function handle(): int
    {
        $tenantId = config('demo.tenant_id', 'demo');

        if ($this->option('fresh')) {
            if ($tenant = Tenant::find($tenantId)) {
                $this->warn("Deleting tenant `{$tenantId}` and its database…");
                $tenant->delete();
            }
        }

        $this->call('db:seed', [
            '--class' => TenantDemoSeeder::class,
            '--force' => true,
        ]);

        return self::SUCCESS;
    }
}
