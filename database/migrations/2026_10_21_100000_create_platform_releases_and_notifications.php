<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        if (! Schema::connection($this->connection)->hasTable('platform_releases')) {
            Schema::connection($this->connection)->create('platform_releases', function (Blueprint $table) {
                $table->id();
                $table->string('version', 50);
                $table->string('title');
                $table->text('description');
                $table->string('category', 40);
                $table->date('released_on');
                $table->string('module', 60)->nullable();
                $table->string('link_path')->nullable();
                $table->string('audience', 20)->default('all');
                $table->string('status', 20)->default('draft');
                $table->timestamp('published_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('admin_users')->nullOnDelete();
                $table->foreignId('published_by')->nullable()->constrained('admin_users')->nullOnDelete();
                $table->timestamps();

                $table->index(['status', 'released_on']);
            });
        }

        if (! Schema::connection($this->connection)->hasTable('platform_release_tenant')) {
            Schema::connection($this->connection)->create('platform_release_tenant', function (Blueprint $table) {
                $table->foreignId('platform_release_id')->constrained()->cascadeOnDelete();
                $table->string('tenant_id');
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->primary(['platform_release_id', 'tenant_id']);
            });
        }

        // Laravel's standard database-notification table, plus a release link so each
        // release can be retracted and so a user can never get the same release twice.
        if (! Schema::connection($this->connection)->hasTable('notifications')) {
            Schema::connection($this->connection)->create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->foreignId('platform_release_id')->nullable()->constrained()->cascadeOnDelete();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->unique(['platform_release_id', 'notifiable_type', 'notifiable_id'], 'notifications_release_recipient_unique');
                $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('notifications');
        Schema::connection($this->connection)->dropIfExists('platform_release_tenant');
        Schema::connection($this->connection)->dropIfExists('platform_releases');
    }
};
