<?php

namespace App\Support;

use App\Models\Central\AdminUser;
use Illuminate\Support\Facades\Schema;

class SuperAdminSetup
{
    public static function tableReady(): bool
    {
        try {
            return Schema::connection('central')->hasTable('admin_users');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function hasAccounts(): bool
    {
        if (! self::tableReady()) {
            return false;
        }

        try {
            return AdminUser::query()->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    public static function configuredCredentials(): bool
    {
        $email = config('admin.super_admin.email');
        $password = config('admin.super_admin.password');

        return filled($email) && filled($password);
    }

    public static function ensureFromConfig(): ?AdminUser
    {
        if (! self::tableReady() || ! self::configuredCredentials()) {
            return null;
        }

        $admin = AdminUser::query()->firstOrNew(['email' => config('admin.super_admin.email')]);
        $admin->fill([
            'name' => config('admin.super_admin.name', 'Super Admin'),
            'is_super_admin' => true,
        ]);
        $admin->password = config('admin.super_admin.password');
        $admin->save();

        return $admin->fresh();
    }
}
