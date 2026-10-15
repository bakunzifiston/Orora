<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dewormings') || Schema::hasColumn('dewormings', 'tenant_id')) {
            return;
        }

        Schema::table('dewormings', function (Blueprint $table): void {
            $table->string('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('dewormings') || ! Schema::hasColumn('dewormings', 'tenant_id')) {
            return;
        }

        Schema::table('dewormings', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
