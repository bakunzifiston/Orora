<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Classifies each workspace (tenant) as a real production account or a testing account.
 *
 * The column defaults to "real", so every existing account stays a real account.
 * Nothing is ever auto-classified as testing.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenants') || Schema::hasColumn('tenants', 'account_type')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('account_type', 16)->default('real')->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tenants') || ! Schema::hasColumn('tenants', 'account_type')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex(['account_type']);
            $table->dropColumn('account_type');
        });
    }
};
