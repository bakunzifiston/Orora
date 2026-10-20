<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('milk_distributions')) {
            Schema::create('milk_distributions', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id')->nullable()->index();
                $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
                $table->string('distribution_code')->unique();
                $table->dateTime('distributed_at');
                $table->decimal('quantity_liters', 10, 2);
                $table->string('category');
                $table->string('other_use')->nullable();
                $table->string('recipient_type');
                $table->string('recipient_name')->nullable();
                $table->string('recipient_phone', 30)->nullable();
                $table->foreignId('animal_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('milk_storage_id')->nullable()->constrained('milk_storage')->nullOnDelete();
                $table->foreignId('storage_movement_id')->nullable()->constrained('milk_storage_movements')->nullOnDelete();
                $table->foreignId('sale_transaction_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('sale_item_id')->nullable()->unique()->constrained()->nullOnDelete();
                $table->text('notes')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
                $table->index(['farm_id', 'distributed_at']);
                $table->index(['category', 'distributed_at']);
            });
        }

        $this->backfillConfirmedMilkSales();
    }

    public function down(): void
    {
        Schema::dropIfExists('milk_distributions');
    }

    /**
     * Existing confirmed or completed milk sales become "sold" distribution rows,
     * so historical usage reports include milk that has already been sold.
     */
    private function backfillConfirmedMilkSales(): void
    {
        if (! Schema::hasTable('sale_items') || ! Schema::hasTable('sale_transactions')) {
            return;
        }

        $hasTenant = Schema::hasColumn('sale_transactions', 'tenant_id');
        $hasCustomers = Schema::hasTable('customers') && Schema::hasColumn('sale_transactions', 'customer_id');

        $rows = DB::table('sale_items')
            ->join('sale_transactions', 'sale_items.sale_transaction_id', '=', 'sale_transactions.id')
            ->when($hasCustomers, fn ($q) => $q->leftJoin('customers', 'sale_transactions.customer_id', '=', 'customers.id'))
            ->leftJoin('milk_distributions', 'milk_distributions.sale_item_id', '=', 'sale_items.id')
            ->whereNull('milk_distributions.id')
            ->where('sale_items.item_type', 'milk')
            ->where('sale_transactions.sale_type', 'milk_sale')
            ->whereIn('sale_transactions.sale_status', ['confirmed', 'completed'])
            ->where('sale_items.quantity', '>', 0)
            ->orderBy('sale_items.id')
            ->select([
                'sale_items.id as sale_item_id',
                'sale_items.quantity',
                'sale_items.milk_storage_id',
                'sale_transactions.id as sale_transaction_id',
                'sale_transactions.farm_id',
                'sale_transactions.sale_date',
                'sale_transactions.sale_number',
                'sale_transactions.created_by',
                ...($hasTenant ? ['sale_transactions.tenant_id'] : []),
                ...($hasCustomers ? ['customers.display_name as customer_name'] : []),
            ])
            ->get();

        $now = now();

        foreach ($rows as $row) {
            DB::table('milk_distributions')->insert([
                'tenant_id' => $hasTenant ? $row->tenant_id : null,
                'farm_id' => $row->farm_id,
                'distribution_code' => 'MD-SALE-'.$row->sale_item_id,
                'distributed_at' => $row->sale_date.' 00:00:00',
                'quantity_liters' => $row->quantity,
                'category' => 'sold',
                'recipient_type' => 'customer',
                'recipient_name' => ($hasCustomers ? $row->customer_name : null) ?: 'Customer',
                'milk_storage_id' => $row->milk_storage_id,
                'sale_transaction_id' => $row->sale_transaction_id,
                'sale_item_id' => $row->sale_item_id,
                'notes' => "Sale {$row->sale_number}",
                'recorded_by' => $row->created_by,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
