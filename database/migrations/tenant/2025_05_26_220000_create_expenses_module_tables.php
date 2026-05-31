<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('expense_group');
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expense_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('animal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('livestock_id')->nullable()->constrained('livestock')->nullOnDelete();
            $table->foreignId('expense_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->date('expense_date');
            $table->decimal('amount', 14, 2);
            $table->string('currency')->default('RWF');
            $table->string('payment_method')->nullable();
            $table->string('paid_by')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('paid');
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index(['expense_date']);
        });

        $this->seedCategories();
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_vendors');
        Schema::dropIfExists('expense_categories');
    }

    private function seedCategories(): void
    {
        $now = now();
        $rows = [
            ['feed', 'Feed purchase', 'feed.purchase', 'Feed stock purchases (feeding module)'],
            ['feed', 'Feed transport', 'feed.transport', 'Delivery and transport of feed'],
            ['feed', 'Feed storage', 'feed.storage', 'Storage and handling costs'],
            ['health', 'Vaccination', 'health.vaccination', 'Vaccination costs'],
            ['health', 'Medicine / treatment', 'health.treatment', 'Treatment and medicine costs'],
            ['health', 'Vet visit', 'health.vet_visit', 'Veterinary consultation and visit fees'],
            ['health', 'Lab / diagnostics', 'health.diagnostics', 'Laboratory and diagnostic fees'],
            ['farm_operations', 'Labor', 'farm.labor', 'Wages and labor on the farm'],
            ['farm_operations', 'Utilities', 'farm.utilities', 'Water, power, and utilities'],
            ['farm_operations', 'Equipment', 'farm.equipment', 'Tools and equipment'],
            ['farm_operations', 'Maintenance', 'farm.maintenance', 'Repairs and maintenance'],
            ['farm_operations', 'Fencing / structures', 'farm.structures', 'Buildings and fencing'],
            ['general', 'Office / admin', 'general.admin', 'Administrative costs'],
            ['general', 'Transport', 'general.transport', 'Non-feed transport'],
            ['general', 'Insurance / licenses', 'general.insurance', 'Insurance and licenses'],
            ['general', 'Other', 'general.other', 'Other business costs'],
        ];

        foreach ($rows as [$group, $name, $code, $description]) {
            DB::table('expense_categories')->insert([
                'expense_group' => $group,
                'name' => $name,
                'code' => $code,
                'description' => $description,
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
