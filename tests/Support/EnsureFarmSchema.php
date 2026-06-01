<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class EnsureFarmSchema
{
    public static function install(): void
    {
        Schema::dropIfExists('animals');
        Schema::dropIfExists('livestock');
        Schema::dropIfExists('farms');

        Schema::create('farms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('registration_number')->unique();
            $table->string('country')->default('Rwanda');
            $table->unsignedTinyInteger('province_code')->nullable();
            $table->string('province')->nullable();
            $table->unsignedSmallInteger('district_code')->nullable();
            $table->string('district')->nullable();
            $table->string('sector')->nullable();
            $table->string('sector_code', 20)->nullable();
            $table->string('cell')->nullable();
            $table->unsignedInteger('cell_code')->nullable();
            $table->string('village')->nullable();
            $table->unsignedInteger('village_code')->nullable();
            $table->decimal('farm_size_hectares', 10, 2)->nullable();
            $table->date('registration_date')->nullable();
            $table->string('status')->default('active');
            $table->string('ownership_type')->default('individual');
            $table->string('owner_first_name')->nullable();
            $table->string('owner_last_name')->nullable();
            $table->string('owner_national_id')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('owner_emergency_contact')->nullable();
            $table->string('organization_name')->nullable();
            $table->string('tax_id')->nullable();
            $table->date('owner_dob')->nullable();
            $table->string('owner_gender')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('livestock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('herd_groups')->nullable();
            $table->string('herd_group_other')->nullable();
            $table->json('livestock_types')->nullable();
            $table->string('livestock_type_other')->nullable();
            $table->json('production_purposes')->nullable();
            $table->string('production_purpose_other')->nullable();
            $table->json('farming_methods')->nullable();
            $table->string('farming_method_other')->nullable();
            $table->json('feeding_methods')->nullable();
            $table->string('feeding_method_other')->nullable();
            $table->string('breed')->nullable();
            $table->unsignedInteger('head_count')->default(0);
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('livestock_id')->constrained('livestock')->cascadeOnDelete();
            $table->string('tag_number');
            $table->string('name')->nullable();
            $table->string('gender')->default('male');
            $table->string('photo_path')->nullable();
            $table->string('species')->nullable();
            $table->string('breed')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->string('color_markings')->nullable();
            $table->string('acquisition_type')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->string('source')->nullable();
            $table->string('mother_tag')->nullable();
            $table->string('father_tag')->nullable();
            $table->string('health_status')->default('Healthy');
            $table->string('production_status')->nullable();
            $table->string('lifecycle_status')->default('Active');
            $table->string('current_condition')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['farm_id', 'tag_number']);
        });
    }
}
