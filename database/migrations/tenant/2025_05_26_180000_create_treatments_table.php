<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('animal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('health_record_id')->nullable()->constrained()->nullOnDelete();
            $table->string('disease_name');
            $table->string('medicine_name');
            $table->string('dosage')->nullable();
            $table->string('treatment_method')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('status')->default('Ongoing');
            $table->string('veterinarian_name')->nullable();
            $table->text('symptoms')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
