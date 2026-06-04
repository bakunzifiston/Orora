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

        $this->components->info('Database ready. Farmers can register at /register.');

        return self::SUCCESS;
    }
}
