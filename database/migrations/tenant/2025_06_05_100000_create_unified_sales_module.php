<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('buyer_code');
            $table->string('buyer_name');
            $table->string('buyer_type')->default('individual');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('national_id')->nullable();
            $table->string('company_registration')->nullable();
            $table->string('district')->nullable();
            $table->text('address')->nullable();
            $table->string('preferred_payment_method')->nullable();
            $table->string('trust_level')->default('new_buyer');
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['farm_id', 'buyer_code']);
        });

        Schema::create('abattoir_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('dispatch_code')->unique();
            $table->date('dispatch_date');
            $table->string('abattoir_name');
            $table->string('abattoir_location')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('transport_method')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->string('driver_name')->nullable();
            $table->unsignedBigInteger('movement_permit_id')->nullable();
            $table->unsignedInteger('total_animals_dispatched')->default(0);
            $table->date('expected_return_date')->nullable();
            $table->string('dispatch_status')->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('abattoir_dispatch_animals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abattoir_dispatch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('animal_id')->constrained()->cascadeOnDelete();
            $table->decimal('live_weight_kg', 10, 2)->nullable();
            $table->string('animal_condition')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('abattoir_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abattoir_dispatch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('animal_id')->constrained()->cascadeOnDelete();
            $table->date('return_date');
            $table->decimal('carcass_weight_kg', 10, 2)->nullable();
            $table->decimal('dressing_percentage', 5, 2)->nullable();
            $table->string('cut_type');
            $table->decimal('cut_weight_kg', 10, 2);
            $table->string('grade')->nullable();
            $table->decimal('price_per_kg', 15, 2)->nullable();
            $table->boolean('is_sold')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('sale_number')->unique();
            $table->string('sale_type');
            $table->date('sale_date');
            $table->foreignId('buyer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('pricing_method')->nullable();
            $table->decimal('subtotal_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('currency')->default('RWF');
            $table->string('payment_status')->default('unpaid');
            $table->string('sale_status')->default('draft');
            $table->string('delivery_method')->nullable();
            $table->unsignedBigInteger('movement_permit_id')->nullable();
            $table->foreignId('abattoir_dispatch_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->string('approved_by')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['sale_type', 'sale_date']);
            $table->index(['farm_id', 'sale_status']);
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_type');
            $table->foreignId('animal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('livestock_id')->nullable()->constrained('livestock')->nullOnDelete();
            $table->foreignId('abattoir_return_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('milk_storage_id')->nullable()->constrained('milk_storage')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 3)->default(1);
            $table->string('unit')->default('head');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('live_weight_kg', 10, 2)->nullable();
            $table->decimal('carcass_weight_kg', 10, 2)->nullable();
            $table->decimal('price_per_kg', 15, 2)->nullable();
            $table->decimal('total_price', 15, 2)->default(0);
            $table->string('animal_condition')->nullable();
            $table->boolean('certificate_verified')->default(false);
            $table->boolean('permit_verified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_reference')->unique();
            $table->date('payment_date');
            $table->string('payment_method')->nullable();
            $table->decimal('amount_paid', 15, 2);
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->string('transaction_reference')->nullable();
            $table->string('received_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_transaction_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->string('document_number')->nullable();
            $table->string('file_path')->nullable();
            $table->string('generated_by')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_transaction_id')->constrained()->cascadeOnDelete();
            $table->string('action_type');
            $table->foreignId('action_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('action_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $this->migrateLegacySales();
        $this->migrateLegacyMilkSales();

        Schema::dropIfExists('milk_sale_logs');
        Schema::dropIfExists('milk_sale_payments');
        Schema::dropIfExists('milk_sale_items');
        Schema::dropIfExists('milk_sales');
        Schema::dropIfExists('sales');
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_logs');
        Schema::dropIfExists('sale_documents');
        Schema::dropIfExists('sale_payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sale_transactions');
        Schema::dropIfExists('abattoir_returns');
        Schema::dropIfExists('abattoir_dispatch_animals');
        Schema::dropIfExists('abattoir_dispatches');
        Schema::dropIfExists('buyers');

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('product_type')->default('livestock');
            $table->foreignId('animal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('livestock_id')->nullable()->constrained('livestock')->nullOnDelete();
            $table->string('buyer_name');
            $table->string('buyer_contact')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->string('currency')->default('RWF');
            $table->date('sold_on');
            $table->string('payment_status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    private function migrateLegacySales(): void
    {
        if (! Schema::hasTable('sales')) {
            return;
        }

        foreach (DB::table('sales')->orderBy('id')->cursor() as $row) {
            $buyerId = $this->resolveBuyer((int) $row->farm_id, $row->buyer_name, $row->buyer_contact);
            $saleDate = $row->sold_on;
            $number = $this->legacySaleNumber($saleDate, (int) $row->id);
            $total = (float) ($row->total_amount ?? 0);
            $paymentStatus = match ($row->payment_status) {
                'paid' => 'paid',
                'partial' => 'partial',
                default => 'unpaid',
            };

            $transactionId = DB::table('sale_transactions')->insertGetId([
                'farm_id' => $row->farm_id,
                'sale_number' => $number,
                'sale_type' => 'animal_sale',
                'sale_date' => $saleDate,
                'buyer_id' => $buyerId,
                'pricing_method' => 'per_animal',
                'subtotal_amount' => $total,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $total,
                'currency' => $row->currency ?? 'RWF',
                'payment_status' => $paymentStatus,
                'sale_status' => 'completed',
                'notes' => $row->notes,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);

            $description = $row->animal_id
                ? 'Animal #'.$row->animal_id
                : ($row->livestock_id ? 'Livestock group #'.$row->livestock_id : 'Livestock sale');

            DB::table('sale_items')->insert([
                'sale_transaction_id' => $transactionId,
                'item_type' => 'animal',
                'animal_id' => $row->animal_id,
                'livestock_id' => $row->livestock_id,
                'description' => $description,
                'quantity' => $row->quantity ?? 1,
                'unit' => 'head',
                'unit_price' => $row->unit_price ?? 0,
                'total_price' => $total,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);

            DB::table('sale_logs')->insert([
                'sale_transaction_id' => $transactionId,
                'action_type' => 'migrated',
                'action_at' => $row->created_at ?? now(),
                'notes' => 'Imported from legacy sales table.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function migrateLegacyMilkSales(): void
    {
        if (! Schema::hasTable('milk_sales')) {
            return;
        }

        foreach (DB::table('milk_sales')->orderBy('id')->cursor() as $row) {
            $buyerId = $this->resolveBuyer((int) $row->farm_id, $row->buyer_name, $row->buyer_contact);
            $saleStatus = match ($row->status) {
                'confirmed' => 'completed',
                'cancelled' => 'cancelled',
                default => 'draft',
            };

            $transactionId = DB::table('sale_transactions')->insertGetId([
                'farm_id' => $row->farm_id,
                'sale_number' => $row->sale_code,
                'sale_type' => 'milk_sale',
                'sale_date' => $row->sold_on,
                'buyer_id' => $buyerId,
                'pricing_method' => 'per_liter',
                'subtotal_amount' => $row->total_amount,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $row->total_amount,
                'currency' => $row->currency ?? 'RWF',
                'payment_status' => 'unpaid',
                'sale_status' => $saleStatus,
                'notes' => $row->notes,
                'created_by' => $row->created_by,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);

            if (Schema::hasTable('milk_sale_items')) {
                foreach (DB::table('milk_sale_items')->where('milk_sale_id', $row->id)->get() as $item) {
                    DB::table('sale_items')->insert([
                        'sale_transaction_id' => $transactionId,
                        'item_type' => 'milk',
                        'milk_storage_id' => $item->milk_storage_id,
                        'description' => 'Milk sale',
                        'quantity' => $item->quantity_liters,
                        'unit' => 'liter',
                        'unit_price' => $item->unit_price ?? 0,
                        'total_price' => $item->line_total ?? 0,
                        'notes' => $item->notes,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ]);
                }
            }

            if (Schema::hasTable('milk_sale_payments')) {
                foreach (DB::table('milk_sale_payments')->where('milk_sale_id', $row->id)->get() as $payment) {
                    DB::table('sale_payments')->insert([
                        'sale_transaction_id' => $transactionId,
                        'payment_reference' => 'MIG-PAY-'.$payment->id,
                        'payment_date' => $payment->paid_on,
                        'payment_method' => $payment->payment_method,
                        'amount_paid' => $payment->amount,
                        'remaining_balance' => 0,
                        'notes' => $payment->notes,
                        'created_at' => $payment->created_at,
                        'updated_at' => $payment->updated_at,
                    ]);
                }
            }

            if (Schema::hasTable('milk_sale_logs')) {
                foreach (DB::table('milk_sale_logs')->where('milk_sale_id', $row->id)->get() as $log) {
                    DB::table('sale_logs')->insert([
                        'sale_transaction_id' => $transactionId,
                        'action_type' => $log->event,
                        'action_by' => $log->user_id,
                        'action_at' => $log->created_at ?? now(),
                        'notes' => $log->meta,
                        'created_at' => $log->created_at,
                        'updated_at' => $log->updated_at,
                    ]);
                }
            }
        }
    }

    private function resolveBuyer(int $farmId, string $name, ?string $contact): int
    {
        $existing = DB::table('buyers')
            ->where('farm_id', $farmId)
            ->where('buyer_name', $name)
            ->value('id');

        if ($existing) {
            return (int) $existing;
        }

        $seq = DB::table('buyers')->where('farm_id', $farmId)->count() + 1;

        return DB::table('buyers')->insertGetId([
            'farm_id' => $farmId,
            'buyer_code' => sprintf('BUY-%04d', $seq),
            'buyer_name' => $name,
            'phone' => $contact,
            'buyer_type' => 'individual',
            'trust_level' => 'new_buyer',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function legacySaleNumber(string $date, int $id): string
    {
        return sprintf('SL-%s-%04d', str_replace('-', '', substr($date, 0, 10)), $id);
    }
};
