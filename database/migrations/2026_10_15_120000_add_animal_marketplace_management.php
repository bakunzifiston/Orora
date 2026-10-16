<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table): void {
            $table->foreignId('farm_id')->nullable()->after('tenant_id')->constrained('farms')->nullOnDelete();
            $table->foreignId('animal_id')->nullable()->after('farm_id')->constrained('animals')->nullOnDelete();
            $table->string('animal_species', 80)->nullable()->after('animal_id');
            $table->string('animal_sex', 40)->nullable()->after('animal_species');
            $table->index(['farm_id', 'animal_id']);
        });

        Schema::create('marketplace_listing_status_changes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('listing_id')->constrained('marketplace_listings')->cascadeOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['listing_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_listing_status_changes');

        Schema::table('marketplace_listings', function (Blueprint $table): void {
            $table->dropForeign(['farm_id']);
            $table->dropForeign(['animal_id']);
            $table->dropIndex(['farm_id', 'animal_id']);
            $table->dropColumn(['farm_id', 'animal_id', 'animal_species', 'animal_sex']);
        });
    }
};
