<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_movements', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->string('movement_type');
            $table->decimal('quantity', 12, 2);
            $table->string('unit');
            $table->decimal('balance_after', 12, 2);
            $table->nullableMorphs('reference');
            $table->text('notes')->nullable();
            $table->timestamp('moved_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['medicine_id', 'moved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_movements');
    }
};
