<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milk_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('animal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('livestock_id')->nullable()->constrained('livestock')->nullOnDelete();
            $table->date('recorded_on');
            $table->string('session')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('L');
            $table->decimal('fat_percentage', 5, 2)->nullable();
            $table->string('quality_grade')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['recorded_on', 'farm_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milk_records');
    }
};
