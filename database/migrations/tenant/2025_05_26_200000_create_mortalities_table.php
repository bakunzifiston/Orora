<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mortalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('animal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('health_record_id')->nullable()->constrained()->nullOnDelete();
            $table->date('death_date');
            $table->string('cause_of_death')->nullable();
            $table->string('reported_by')->nullable();
            $table->string('veterinarian_name')->nullable();
            $table->string('disposal_method')->nullable();
            $table->boolean('postmortem_done')->default(false);
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mortalities');
    }
};
