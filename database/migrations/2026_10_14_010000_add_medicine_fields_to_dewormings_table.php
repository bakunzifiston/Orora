<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dewormings')) {
            return;
        }

        Schema::table('dewormings', function (Blueprint $table): void {
            if (! Schema::hasColumn('dewormings', 'medicine_id')) {
                $table->foreignId('medicine_id')->nullable()->after('product_name')->constrained('medicines')->nullOnDelete();
            }

            if (! Schema::hasColumn('dewormings', 'quantity_used')) {
                $table->decimal('quantity_used', 12, 2)->nullable()->after('dosage');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('dewormings')) {
            return;
        }

        Schema::table('dewormings', function (Blueprint $table): void {
            if (Schema::hasColumn('dewormings', 'quantity_used')) {
                $table->dropColumn('quantity_used');
            }

            if (Schema::hasColumn('dewormings', 'medicine_id')) {
                $table->dropConstrainedForeignId('medicine_id');
            }
        });
    }
};
