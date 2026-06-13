<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class InstallOroraCommand extends Command
{
    protected $signature = 'orora:install';

    protected $description = 'Run all migrations and verify the database is ready for registration';

    public function handle(): int
    {
        $this->components->info('Running migrations…');
        $this->call('migrate', ['--force' => true]);

        $required = ['tenants', 'tenant_accounts', 'users', 'farms', 'animals'];

        $missing = array_values(array_filter($required, fn (string $table) => ! Schema::hasTable($table)));

        if ($missing !== []) {
            $this->components->error('Missing tables: '.implode(', ', $missing));
            $this->line('Ensure TENANCY_SINGLE_DATABASE=true, deploy the latest code, then run: php artisan migrate --force');

            return self::FAILURE;
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'tenant_id')) {
            $this->components->error('users table exists but tenant_id column is missing.');
            $this->line('Run: php artisan migrate --force');

            return self::FAILURE;
        }

        if (! is_file(database_path('data/rwanda_locations.json'))) {
            $this->components->warn('Rwanda locations file missing — farm address dropdowns will fail until you run: php artisan rwanda:download-locations');
        }

        if (! is_file(public_path('build/manifest.json'))) {
            $this->components->warn('Vite build missing — upload public/build/ from your computer after npm run build');
        }

        $marketplaceTables = ['marketplace_categories', 'marketplace_listings', 'learning_posts', 'contact_messages'];
        $missingMarketplace = array_values(array_filter($marketplaceTables, fn (string $table) => ! Schema::hasTable($table)));

        if ($missingMarketplace !== []) {
            $this->components->warn('Marketplace tables missing: '.implode(', ', $missingMarketplace));
            $this->line('Deploy the latest code, then run: php artisan migrate --force');
        } elseif (Schema::hasTable('marketplace_categories') && \App\Models\Central\MarketplaceCategory::query()->count() === 0) {
            $this->line('Optional: seed marketplace demo content with php artisan db:seed --class=MarketplaceSeeder --force');
        }

        if (! Schema::hasTable('disease_records')) {
            $this->components->warn('disease_records table missing — Health → Disease will fail until you run: php artisan migrate --force');
        }

        $this->components->info('Database ready. Farmers can register at /register.');

        return self::SUCCESS;
    }
}
