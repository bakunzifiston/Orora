<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('product_type')->default('livestock')->after('farm_id');
        });

        Schema::table('feed_inventories', function (Blueprint $table) {
            $table->decimal('storage_capacity_kg', 14, 2)->nullable()->after('quantity_on_hand');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('product_type');
        });

        Schema::table('feed_inventories', function (Blueprint $table) {
            $table->dropColumn('storage_capacity_kg');
        });
    }
};
