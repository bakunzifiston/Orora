<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dewormings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('livestock_id')->nullable()->constrained('livestock')->nullOnDelete();
            $table->foreignId('animal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('flock_id')->nullable()->constrained('flocks')->nullOnDelete();
            $table->foreignId('health_record_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->foreignId('medicine_id')->nullable()->constrained('medicines')->nullOnDelete();
            $table->decimal('quantity_used', 12, 2)->nullable();
            $table->string('product_type')->nullable();
            $table->string('active_ingredient')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('batch_number')->nullable();
            $table->string('dosage')->nullable();
            $table->string('administration_method')->nullable();
            $table->date('deworming_date');
            $table->date('next_due_date')->nullable();
            $table->string('status')->default('Scheduled');
            $table->string('veterinarian_name')->nullable();
            $table->string('administered_by')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['farm_id', 'deworming_date']);
        });

        $now = now();
        if (! DB::table('expense_categories')->where('code', 'health.deworming')->exists()) {
            DB::table('expense_categories')->insert([
                'expense_group' => 'health',
                'name' => 'Deworming',
                'code' => 'health.deworming',
                'description' => 'Dewormer products and related costs',
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('expense_categories')->where('code', 'health.deworming')->delete();
        Schema::dropIfExists('dewormings');
    }
};
