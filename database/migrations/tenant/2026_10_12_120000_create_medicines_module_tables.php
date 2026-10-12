<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('active_ingredient')->nullable();
            $table->text('dosage_instructions')->nullable();
            $table->string('administration_method')->nullable();
            $table->string('unit')->default('ml');
            $table->decimal('quantity_on_hand', 12, 2)->default(0);
            $table->decimal('reorder_level', 12, 2)->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('supplier')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'name']);
            $table->index(['tenant_id', 'expiry_date']);
        });

        Schema::table('treatments', function (Blueprint $table) {
            if (! Schema::hasColumn('treatments', 'medicine_id')) {
                $table->foreignId('medicine_id')->nullable()->after('medicine_name')->constrained('medicines')->nullOnDelete();
            }

            if (! Schema::hasColumn('treatments', 'quantity_used')) {
                $table->decimal('quantity_used', 12, 2)->nullable()->after('dosage');
            }

            if (! Schema::hasColumn('treatments', 'recorded_by')) {
                $table->foreignId('recorded_by')->nullable()->after('attachment_path')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            if (Schema::hasColumn('treatments', 'recorded_by')) {
                $table->dropConstrainedForeignId('recorded_by');
            }

            if (Schema::hasColumn('treatments', 'quantity_used')) {
                $table->dropColumn('quantity_used');
            }

            if (Schema::hasColumn('treatments', 'medicine_id')) {
                $table->dropConstrainedForeignId('medicine_id');
            }
        });

        Schema::dropIfExists('medicines');
    }
};
