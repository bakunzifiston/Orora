<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->unique();
            $table->string('account_name');
            $table->string('account_type');
            $table->string('account_subtype');
            $table->foreignId('parent_account_id')->nullable()->constrained('finance_accounts')->nullOnDelete();
            $table->string('source_module')->nullable();
            $table->string('expense_category_code')->nullable();
            $table->string('normal_balance');
            $table->boolean('is_system')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('finance_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period_name');
            $table->string('period_type')->default('monthly');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('open');
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['period_type', 'start_date', 'end_date']);
        });

        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('livestock_id')->nullable()->constrained('livestock')->nullOnDelete();
            $table->string('transaction_code')->unique();
            $table->date('transaction_date');
            $table->foreignId('finance_period_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_type');
            $table->string('posting_kind')->default('recognition');
            $table->string('source_module')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('description');
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->string('currency')->default('RWF');
            $table->string('payment_method')->nullable();
            $table->string('reference_number')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->boolean('is_reconciled')->default(false);
            $table->boolean('is_reversal')->default(false);
            $table->foreignId('reversed_transaction_id')->nullable()->constrained('finance_transactions')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['source_module', 'source_type', 'source_id', 'posting_kind'], 'finance_source_posting_unique');
            $table->index(['transaction_date', 'farm_id']);
            $table->index(['farm_id', 'livestock_id']);
        });

        Schema::create('finance_transaction_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('finance_account_id')->constrained()->cascadeOnDelete();
            $table->string('entry_type');
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('finance_transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_transaction_id')->constrained()->cascadeOnDelete();
            $table->string('action_type');
            $table->foreignId('action_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('action_at');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('finance_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->nullable()->constrained()->nullOnDelete();
            $table->string('budget_code')->unique();
            $table->string('budget_name');
            $table->string('period_type');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_income_budget', 15, 2)->default(0);
            $table->decimal('total_expense_budget', 15, 2)->default(0);
            $table->decimal('net_budget', 15, 2)->default(0);
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('finance_budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('finance_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('livestock_id')->nullable()->constrained('livestock')->nullOnDelete();
            $table->decimal('budgeted_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2)->default(0);
            $table->decimal('variance', 15, 2)->default(0);
            $table->decimal('variance_percentage', 5, 2)->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('finance_tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('tax_name');
            $table->string('tax_code')->unique();
            $table->decimal('tax_rate', 5, 2);
            $table->string('applies_to');
            $table->foreignId('finance_account_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('finance_tax_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('finance_tax_rate_id')->constrained()->cascadeOnDelete();
            $table->decimal('taxable_amount', 15, 2);
            $table->decimal('tax_rate_snapshot', 5, 2);
            $table->decimal('tax_amount', 15, 2);
            $table->string('tax_period')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('finance_balance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('farm_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('finance_account_id')->constrained()->cascadeOnDelete();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('total_debits', 15, 2)->default(0);
            $table->decimal('total_credits', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['finance_period_id', 'farm_id', 'finance_account_id'], 'finance_snapshot_unique');
        });

        $this->seedChartOfAccounts();
        $this->seedCurrentPeriod();
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_balance_snapshots');
        Schema::dropIfExists('finance_tax_entries');
        Schema::dropIfExists('finance_tax_rates');
        Schema::dropIfExists('finance_budget_items');
        Schema::dropIfExists('finance_budgets');
        Schema::dropIfExists('finance_transaction_logs');
        Schema::dropIfExists('finance_transaction_lines');
        Schema::dropIfExists('finance_transactions');
        Schema::dropIfExists('finance_periods');
        Schema::dropIfExists('finance_accounts');
    }

    private function seedChartOfAccounts(): void
    {
        $now = now();
        $accounts = [
            ['1000', 'Cash', 'asset', 'cash', 'debit'],
            ['1100', 'Bank Account', 'asset', 'bank', 'debit'],
            ['1200', 'Accounts Receivable', 'asset', 'accounts_receivable', 'debit'],
            ['1300', 'Feed Inventory', 'asset', 'inventory', 'debit'],
            ['1400', 'Livestock Assets', 'asset', 'fixed_asset', 'debit'],
            ['2000', 'Accounts Payable', 'liability', 'accounts_payable', 'credit'],
            ['2100', 'Taxes Payable', 'liability', 'taxes_payable', 'credit'],
            ['2200', 'Loans Payable', 'liability', 'loans', 'credit'],
            ['3000', 'Owner Equity', 'equity', 'owner_equity', 'credit'],
            ['3100', 'Retained Earnings', 'equity', 'retained_earnings', 'credit'],
            ['4000', 'Milk Sales Revenue', 'income', 'milk_revenue', 'credit', 'sales'],
            ['4100', 'Animal Sales Revenue', 'income', 'animal_revenue', 'credit', 'sales'],
            ['4200', 'Meat Sales Revenue', 'income', 'meat_revenue', 'credit', 'sales'],
            ['4300', 'Breeding Service Revenue', 'income', 'breeding_revenue', 'credit', 'breeding'],
            ['4900', 'Other Income', 'income', 'other_income', 'credit'],
            ['5000', 'Feed Costs', 'expense', 'feed_cost', 'debit', 'expenses', 'feed.purchase'],
            ['5100', 'Health & Vet Costs', 'expense', 'health_cost', 'debit', 'expenses', 'health.vaccination'],
            ['5200', 'Labor Costs', 'expense', 'labor_cost', 'debit', 'expenses', 'farm.labor'],
            ['5300', 'Transport Costs', 'expense', 'transport_cost', 'debit', 'expenses', 'general.transport'],
            ['5400', 'Equipment & Maintenance', 'expense', 'equipment_cost', 'debit', 'expenses', 'farm.equipment'],
            ['5900', 'Other Expenses', 'expense', 'other_expense', 'debit', 'expenses', 'general.other'],
        ];

        foreach ($accounts as $row) {
            DB::table('finance_accounts')->insert([
                'account_code' => $row[0],
                'account_name' => $row[1],
                'account_type' => $row[2],
                'account_subtype' => $row[3],
                'normal_balance' => $row[4],
                'source_module' => $row[5] ?? null,
                'expense_category_code' => $row[6] ?? null,
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedCurrentPeriod(): void
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        DB::table('finance_periods')->insert([
            'period_name' => $start->format('F Y'),
            'period_type' => 'monthly',
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
