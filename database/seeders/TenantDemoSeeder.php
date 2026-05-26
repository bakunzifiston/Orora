<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Tenant::find('demo')) {
            return;
        }

        $tenant = Tenant::create([
            'id' => 'demo',
            'name' => 'Demo Company',
        ]);

        $tenant->domains()->create([
            'domain' => 'demo.localhost',
        ]);
    }
}
