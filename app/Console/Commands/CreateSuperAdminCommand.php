<?php

namespace App\Console\Commands;

use App\Models\Central\AdminUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdminCommand extends Command
{
    protected $signature = 'orora:super-admin
                            {--name= : Super admin display name}
                            {--email= : Login email}
                            {--password= : Login password (min 8 characters)}';

    protected $description = 'Create or update a super admin account for the /admin workspace';

    public function handle(): int
    {
        if (! Schema::connection('central')->hasTable('admin_users')) {
            $this->components->error('admin_users table missing. Run: php artisan migrate --force');

            return self::FAILURE;
        }

        $name = $this->option('name')
            ?: config('admin.super_admin.name')
            ?: $this->ask('Name', 'Super Admin');
        $email = $this->option('email')
            ?: config('admin.super_admin.email')
            ?: $this->ask('Email');
        $password = $this->option('password')
            ?: config('admin.super_admin.password')
            ?: $this->secret('Password (min 8 characters)');

        $validator = Validator::make(compact('name', 'email', 'password'), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $admin = AdminUser::query()->firstOrNew(['email' => $email]);
        $admin->fill([
            'name' => $name,
            'is_super_admin' => true,
        ]);
        $admin->password = $password;
        $admin->save();

        if (! Hash::check($password, $admin->fresh()->password)) {
            $this->components->error('Password could not be saved correctly. Please run the command again.');

            return self::FAILURE;
        }

        $this->components->info($admin->wasRecentlyCreated ? 'Super admin created.' : 'Super admin updated.');
        $this->line('Email: '.$admin->email);
        $this->line('Sign in at: '.url('/login').' (you will be redirected to /admin)');

        return self::SUCCESS;
    }
}
