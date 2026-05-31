<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breeding_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('female_animal_id')->constrained('animals')->cascadeOnDelete();
            $table->foreignId('male_animal_id')->nullable()->constrained('animals')->nullOnDelete();
            $table->string('external_sire_name')->nullable();
            $table->string('external_sire_breed')->nullable();
            $table->string('external_sire_code')->nullable();
            $table->string('breeding_code')->unique();
            $table->date('breeding_date');
            $table->string('breeding_type');
            $table->string('animal_type');
            $table->string('heat_detection_method')->nullable();
            $table->date('heat_detected_date')->nullable();
            $table->string('technician_name')->nullable();
            $table->string('semen_batch_number')->nullable();
            $table->string('semen_straw_code')->nullable();
            $table->string('semen_source')->nullable();
            $table->date('expected_calving_date')->nullable();
            $table->unsignedInteger('gestation_period_days')->nullable();
            $table->string('breeding_status')->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['farm_id', 'breeding_date']);
            $table->index(['breeding_status']);
        });

        Schema::create('pregnancy_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('breeding_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('animal_id')->constrained('animals')->cascadeOnDelete();
            $table->string('check_code')->unique();
            $table->date('check_date');
            $table->string('check_method');
            $table->string('result');
            $table->unsignedInteger('pregnancy_age_days')->nullable();
            $table->date('expected_calving_date')->nullable();
            $table->string('checked_by');
            $table->string('clinic_name')->nullable();
            $table->date('next_check_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->index(['check_date']);
        });

        Schema::create('birth_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('breeding_record_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('mother_animal_id')->constrained('animals')->cascadeOnDelete();
            $table->string('birth_code')->unique();
            $table->date('birth_date');
            $table->string('birth_type');
            $table->unsignedInteger('total_offspring');
            $table->unsignedInteger('alive_offspring');
            $table->unsignedInteger('stillborn_offspring')->default(0);
            $table->string('birth_difficulty');
            $table->decimal('birth_weight_kg', 8, 2)->nullable();
            $table->string('assisted_by')->nullable();
            $table->string('veterinarian_name')->nullable();
            $table->string('mother_condition_after');
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->index(['birth_date']);
        });

        Schema::create('offspring', function (Blueprint $table) {
            $table->id();
            $table->foreignId('birth_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mother_animal_id')->constrained('animals')->cascadeOnDelete();
            $table->foreignId('father_animal_id')->nullable()->constrained('animals')->nullOnDelete();
            $table->string('external_sire_name')->nullable();
            $table->foreignId('animal_id')->nullable()->constrained('animals')->nullOnDelete();
            $table->string('offspring_code')->unique();
            $table->string('gender');
            $table->decimal('birth_weight_kg', 8, 2)->nullable();
            $table->string('color_markings')->nullable();
            $table->string('health_status_at_birth');
            $table->boolean('is_registered')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('breeding_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('breeding_record_id')->constrained()->cascadeOnDelete();
            $table->string('action_type');
            $table->foreignId('action_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('action_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['breeding_record_id', 'action_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breeding_logs');
        Schema::dropIfExists('offspring');
        Schema::dropIfExists('birth_records');
        Schema::dropIfExists('pregnancy_checks');
        Schema::dropIfExists('breeding_records');
    }
};
