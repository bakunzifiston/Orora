<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('animal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('health_record_id')->nullable()->constrained()->nullOnDelete();
            $table->string('vaccine_name');
            $table->string('vaccine_type')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('batch_number')->nullable();
            $table->string('dosage')->nullable();
            $table->string('administration_method')->nullable();
            $table->date('vaccination_date');
            $table->date('next_due_date')->nullable();
            $table->string('status')->default('Scheduled');
            $table->string('veterinarian_name')->nullable();
            $table->string('veterinary_clinic')->nullable();
            $table->string('administered_by')->nullable();
            $table->text('side_effects')->nullable();
            $table->text('reaction_notes')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccinations');
    }
};
