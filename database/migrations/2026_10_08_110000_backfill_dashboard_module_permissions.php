<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dashboard used to be always available. After it became a grantable module,
 * existing members would lose it unless we backfill View access.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('workspace_module_permissions')) {
            return;
        }

        $now = now();
        $memberIds = DB::table('users')
            ->where('role', 'member')
            ->where('is_active', true)
            ->pluck('id');

        foreach ($memberIds as $userId) {
            $exists = DB::table('workspace_module_permissions')
                ->where('user_id', $userId)
                ->where('module_key', 'dashboard')
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('workspace_module_permissions')->insert([
                'user_id' => $userId,
                'module_key' => 'dashboard',
                'can_view' => true,
                'can_create' => false,
                'can_edit' => false,
                'can_delete' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('workspace_module_permissions')) {
            return;
        }

        DB::table('workspace_module_permissions')
            ->where('module_key', 'dashboard')
            ->where('can_create', false)
            ->where('can_edit', false)
            ->where('can_delete', false)
            ->delete();
    }
};
