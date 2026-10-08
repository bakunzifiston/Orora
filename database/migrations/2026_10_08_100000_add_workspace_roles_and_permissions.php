<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'role')) {
                    $table->string('role', 32)->default('workspace_admin')->after('password');
                }
                if (! Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('role');
                }
            });

            // Existing accounts keep full access as workspace admins.
            DB::table('users')
                ->where(function ($q) {
                    $q->whereNull('role')
                        ->orWhere('role', '')
                        ->orWhereIn('role', ['owner', 'admin']);
                })
                ->update([
                    'role' => 'workspace_admin',
                    'is_active' => true,
                ]);
        }

        if (! Schema::hasTable('workspace_module_permissions')) {
            Schema::create('workspace_module_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('module_key', 64);
                $table->boolean('can_view')->default(false);
                $table->boolean('can_create')->default(false);
                $table->boolean('can_edit')->default(false);
                $table->boolean('can_delete')->default(false);
                $table->timestamps();

                $table->unique(['user_id', 'module_key']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_module_permissions');

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'is_active')) {
                    $table->dropColumn('is_active');
                }
                if (Schema::hasColumn('users', 'role')) {
                    $table->dropColumn('role');
                }
            });
        }
    }
};
