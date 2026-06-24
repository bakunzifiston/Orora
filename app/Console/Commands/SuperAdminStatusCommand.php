<?php

namespace App\Console\Commands;

use App\Models\Central\AdminUser;
use App\Support\SuperAdminSetup;
use Illuminate\Console\Command;

class SuperAdminStatusCommand extends Command
{
    protected $signature = 'orora:super-admin-status';

    protected $description = 'Check whether super admin login is ready on this server';

    public function handle(): int
    {
        if (! SuperAdminSetup::tableReady()) {
            $this->components->error('admin_users table is missing.');
            $this->line('Run: php artisan migrate --force');

            return self::FAILURE;
        }

        $this->components->info('admin_users table exists.');

        $admins = AdminUser::query()->orderBy('id')->get(['id', 'email', 'name', 'last_login_at']);

        if ($admins->isEmpty()) {
            $this->components->warn('No super admin account exists yet.');
            $this->line('Option A — add to .env on the server, then run:');
            $this->line('  SUPER_ADMIN_EMAIL=you@yourdomain.com');
            $this->line('  SUPER_ADMIN_PASSWORD=your-secure-password');
            $this->line('  php artisan config:clear');
            $this->line('  php artisan orora:super-admin');
            $this->line('Option B — one command:');
            $this->line('  php artisan orora:super-admin --email=you@yourdomain.com --password=your-secure-password');

            return self::FAILURE;
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Last login'],
            $admins->map(fn (AdminUser $admin) => [
                $admin->id,
                $admin->name,
                $admin->email,
                $admin->last_login_at?->toDateTimeString() ?? '—',
            ]),
        );

        $this->line('Login URL: '.url('/login'));

        if (SuperAdminSetup::configuredCredentials()) {
            $this->line('SUPER_ADMIN_EMAIL is set in .env — run `php artisan orora:super-admin` to sync password from .env.');
        }

        return self::SUCCESS;
    }
}
