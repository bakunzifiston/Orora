<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('customer_type')->default('individual');
            $table->string('display_name');
            $table->string('status')->default('active');
            $table->string('trust_level')->default('new');
            $table->string('preferred_payment_method')->nullable();
            $table->string('currency')->default('RWF');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'customer_type']);
        });

        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('national_id')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('organization_name')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('license_number')->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->unsignedInteger('number_of_employees')->nullable();
            $table->date('established_date')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('contact_name');
            $table->string('role')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('address_type')->default('physical');
            $table->string('address_label')->nullable();
            $table->string('country')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('sector')->nullable();
            $table->string('cell')->nullable();
            $table->string('village')->nullable();
            $table->text('street_address')->nullable();
            $table->decimal('gps_latitude', 10, 7)->nullable();
            $table->decimal('gps_longitude', 10, 7)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('customer_credit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('outstanding_balance', 15, 2)->default(0);
            $table->string('payment_terms')->nullable();
            $table->date('last_reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->string('document_name');
            $table->string('file_path')->nullable();
            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('customer_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('communication_type');
            $table->string('direction')->nullable();
            $table->string('subject')->nullable();
            $table->text('summary');
            $table->dateTime('communication_date');
            $table->string('contact_person')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->text('follow_up_notes')->nullable();
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['customer_id', 'communication_date']);
        });

        Schema::create('customer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('action_type');
            $table->foreignId('action_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('action_at');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $buyerMap = $this->migrateBuyersToCustomers();

        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('sale_date')->constrained()->nullOnDelete();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('sale_transaction_id')->constrained()->nullOnDelete();
        });

        Schema::table('sale_payments', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('sale_transaction_id')->constrained()->nullOnDelete();
        });

        $this->migrateSalesBuyerReferences($buyerMap);

        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('buyer_id');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('buyer_id');
        });

        Schema::table('sale_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('buyer_id');
        });

        Schema::dropIfExists('buyers');

        $this->syncAllCustomerBalances();
    }

    public function down(): void
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

        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->foreignId('buyer_id')->nullable()->after('sale_date')->constrained()->nullOnDelete();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('buyer_id')->nullable()->after('sale_transaction_id')->constrained()->nullOnDelete();
        });

        Schema::table('sale_payments', function (Blueprint $table) {
            $table->foreignId('buyer_id')->nullable()->after('sale_transaction_id')->constrained()->nullOnDelete();
        });

        Schema::table('sale_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
        });

        Schema::table('sale_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
        });

        Schema::dropIfExists('customer_logs');
        Schema::dropIfExists('customer_communications');
        Schema::dropIfExists('customer_documents');
        Schema::dropIfExists('customer_credit');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customer_contacts');
        Schema::dropIfExists('customer_profiles');
        Schema::dropIfExists('customers');
    }

    /** @return array<int, int> buyer_id => customer_id */
    private function migrateBuyersToCustomers(): array
    {
        if (! Schema::hasTable('buyers')) {
            return [];
        }

        $map = [];
        $seq = 0;

        foreach (DB::table('buyers')->orderBy('id')->get() as $buyer) {
            $seq++;
            $customerId = DB::table('customers')->insertGetId([
                'customer_code' => sprintf('CUS-%04d', $seq),
                'customer_type' => $this->mapBuyerType($buyer->buyer_type),
                'display_name' => $buyer->buyer_name,
                'status' => $buyer->status === 'inactive' ? 'inactive' : 'active',
                'trust_level' => $this->mapTrustLevel($buyer->trust_level),
                'preferred_payment_method' => $buyer->preferred_payment_method,
                'currency' => 'RWF',
                'notes' => $buyer->notes,
                'created_at' => $buyer->created_at ?? now(),
                'updated_at' => $buyer->updated_at ?? now(),
            ]);

            $isIndividual = in_array($buyer->buyer_type, ['individual'], true);

            DB::table('customer_profiles')->insert([
                'customer_id' => $customerId,
                'first_name' => $isIndividual ? $this->firstNameFrom($buyer->buyer_name) : null,
                'last_name' => $isIndividual ? $this->lastNameFrom($buyer->buyer_name) : null,
                'national_id' => $buyer->national_id,
                'organization_name' => $isIndividual ? null : $buyer->buyer_name,
                'registration_number' => $buyer->company_registration,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($buyer->contact_person || $buyer->phone || $buyer->email) {
                DB::table('customer_contacts')->insert([
                    'customer_id' => $customerId,
                    'contact_name' => $buyer->contact_person ?: $buyer->buyer_name,
                    'phone' => $buyer->phone,
                    'email' => $buyer->email,
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($buyer->district || $buyer->address) {
                DB::table('customer_addresses')->insert([
                    'customer_id' => $customerId,
                    'address_type' => 'physical',
                    'district' => $buyer->district,
                    'street_address' => $buyer->address,
                    'is_default' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('customer_credit')->insert([
                'customer_id' => $customerId,
                'credit_limit' => 0,
                'outstanding_balance' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('customer_logs')->insert([
                'customer_id' => $customerId,
                'action_type' => 'created',
                'action_at' => now(),
                'notes' => 'Migrated from buyer record #'.$buyer->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $map[(int) $buyer->id] = $customerId;
        }

        return $map;
    }

    /** @param array<int, int> $buyerMap */
    private function migrateSalesBuyerReferences(array $buyerMap): void
    {
        if ($buyerMap === []) {
            return;
        }

        foreach ($buyerMap as $buyerId => $customerId) {
            DB::table('sale_transactions')->where('buyer_id', $buyerId)->update(['customer_id' => $customerId]);
            DB::table('sale_items')->where('buyer_id', $buyerId)->update(['customer_id' => $customerId]);
            DB::table('sale_payments')->where('buyer_id', $buyerId)->update(['customer_id' => $customerId]);
        }
    }

    private function syncAllCustomerBalances(): void
    {
        foreach (DB::table('customers')->pluck('id') as $customerId) {
            $this->syncCustomerBalance((int) $customerId);
        }
    }

    private function syncCustomerBalance(int $customerId): void
    {
        $transactions = DB::table('sale_transactions')
            ->where('customer_id', $customerId)
            ->whereNotIn('sale_status', ['cancelled', 'draft'])
            ->get(['id', 'total_amount']);

        $balance = 0.0;

        foreach ($transactions as $transaction) {
            $paid = (float) DB::table('sale_payments')
                ->where('sale_transaction_id', $transaction->id)
                ->sum('amount_paid');

            $balance += max(0, (float) $transaction->total_amount - $paid);
        }

        DB::table('customer_credit')
            ->where('customer_id', $customerId)
            ->update(['outstanding_balance' => $balance, 'updated_at' => now()]);
    }

    private function mapBuyerType(string $type): string
    {
        return match ($type) {
            'company' => 'company',
            'cooperative' => 'cooperative',
            'abattoir' => 'abattoir',
            'exporter' => 'exporter',
            default => 'individual',
        };
    }

    private function mapTrustLevel(string $level): string
    {
        return match ($level) {
            'regular' => 'regular',
            'trusted' => 'trusted',
            default => 'new',
        };
    }

    private function firstNameFrom(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return $parts[0] ?? $name;
    }

    private function lastNameFrom(string $name): ?string
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return $parts[1] ?? null;
    }
};
