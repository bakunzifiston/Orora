<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('marketplace_listings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('category_id')->constrained('marketplace_categories')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('price_unit')->nullable();
            $table->string('currency', 3)->default('RWF');
            $table->string('location')->nullable();
            $table->string('quantity_available')->nullable();
            $table->string('image_path')->nullable();
            $table->string('status', 20)->default('draft');
            $table->boolean('featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'slug']);
            $table->index(['status', 'published_at']);
        });

        Schema::create('marketplace_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('marketplace_listings')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message');
            $table->string('status', 20)->default('new');
            $table->timestamps();
        });

        Schema::create('learning_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('learning_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('learning_categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->string('type', 20)->default('article');
            $table->longText('content')->nullable();
            $table->string('external_url')->nullable();
            $table->string('file_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('status', 20)->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('learning_posts');
        Schema::dropIfExists('learning_categories');
        Schema::dropIfExists('marketplace_inquiries');
        Schema::dropIfExists('marketplace_listings');
        Schema::dropIfExists('marketplace_categories');
    }
};
