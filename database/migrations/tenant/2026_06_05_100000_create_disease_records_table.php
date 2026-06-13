<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disease_records', function (Blueprint $table) {
            $table->id();
            $table->string('disease_code')->unique();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('livestock_id')->constrained('livestock')->cascadeOnDelete();
            $table->foreignId('animal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('health_record_id')->nullable()->constrained()->nullOnDelete();
            $table->string('disease_name');
            $table->date('diagnosis_date');
            $table->string('severity_level')->default('medium');
            $table->string('recovery_status')->default('recovering');
            $table->string('contagious_status')->default('unknown');
            $table->boolean('quarantine_required')->default(false);
            $table->text('symptoms')->nullable();
            $table->string('veterinarian_name')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['farm_id', 'diagnosis_date']);
            $table->index(['animal_id', 'diagnosis_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disease_records');
    }
};
