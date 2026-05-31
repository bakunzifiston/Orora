<?php

namespace App\Services\Finance;

use App\Models\Expense;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\FinanceTransactionLine;
use App\Models\FinanceTransactionLog;
use App\Models\SalePayment;
use App\Models\SaleTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancePostingService
{
    public function __construct(
        private readonly FinanceAccountResolver $accounts,
        private readonly FinancePeriodService $periods,
    ) {}

    public function postCompletedSale(SaleTransaction $sale): ?FinanceTransaction
    {
        if ($sale->sale_status !== 'completed') {
            return null;
        }

        if ($this->findSourcePosting('sales', 'sale_transaction', $sale->id, 'recognition')) {
            return null;
        }

        $sale->loadMissing(['items.animal']);

        $gross = (float) $sale->total_amount;
        $tax = (float) $sale->tax_amount;
        $net = max(0, $gross - $tax);

        if ($gross <= 0) {
            return null;
        }

        $period = $this->periods->forDate($sale->sale_date);
        $this->periods->assertOpen($period);

        $revenue = $this->accounts->revenueAccountForSale($sale);
        $ar = $this->accounts->accountsReceivable();
        $taxPayable = $this->accounts->taxesPayable();

        return DB::transaction(function () use ($sale, $gross, $tax, $net, $period, $revenue, $ar, $taxPayable) {
            $transaction = FinanceTransaction::create([
                'farm_id' => $sale->farm_id,
                'livestock_id' => $this->resolveLivestockId($sale),
                'transaction_code' => $this->generateCode('FT', $sale->sale_date),
                'transaction_date' => $sale->sale_date,
                'finance_period_id' => $period->id,
                'transaction_type' => 'income',
                'posting_kind' => 'recognition',
                'source_module' => 'sales',
                'source_type' => 'sale_transaction',
                'source_id' => $sale->id,
                'description' => $sale->typeLabel().' — '.$sale->sale_number,
                'gross_amount' => $gross,
                'tax_amount' => $tax,
                'net_amount' => $net,
                'currency' => $sale->currency,
                'reference_number' => $sale->sale_number,
                'created_by' => auth()->id(),
            ]);

            $this->addLine($transaction, $ar, 'debit', $gross, 'Accounts receivable');
            $this->addLine($transaction, $revenue, 'credit', $net, 'Sales revenue');

            if ($tax > 0) {
                $this->addLine($transaction, $taxPayable, 'credit', $tax, 'Tax payable');
            }

            $this->log($transaction, 'created', 'Posted from completed sale.');

            $this->syncPendingPayments($sale);

            return $transaction;
        });
    }

    public function reverseCompletedSale(SaleTransaction $sale): ?FinanceTransaction
    {
        $original = $this->findSourcePosting('sales', 'sale_transaction', $sale->id, 'recognition');

        if (! $original || $this->findSourcePosting('sales', 'sale_transaction', $sale->id, 'reversal')) {
            return null;
        }

        return $this->createReversal($original, 'Sale cancelled — '.$sale->sale_number);
    }

    public function postPaidExpense(Expense $expense): ?FinanceTransaction
    {
        if ($expense->status !== 'paid') {
            return null;
        }

        if ($this->findSourcePosting('expenses', 'expense', $expense->id, 'recognition')) {
            return null;
        }

        $amount = (float) $expense->amount;

        if ($amount <= 0) {
            return null;
        }

        $expense->loadMissing('category');

        $period = $this->periods->forDate($expense->expense_date);
        $this->periods->assertOpen($period);

        $expenseAccount = $this->accounts->expenseAccountForExpense($expense);
        $cash = $this->accounts->cashAccount($expense->payment_method);

        return DB::transaction(function () use ($expense, $amount, $period, $expenseAccount, $cash) {
            $transaction = FinanceTransaction::create([
                'farm_id' => $expense->farm_id,
                'livestock_id' => $expense->livestock_id,
                'transaction_code' => $this->generateCode('FT', $expense->expense_date),
                'transaction_date' => $expense->expense_date,
                'finance_period_id' => $period->id,
                'transaction_type' => 'expense',
                'posting_kind' => 'recognition',
                'source_module' => 'expenses',
                'source_type' => 'expense',
                'source_id' => $expense->id,
                'description' => $expense->title ?: ($expense->category?->name ?? 'Expense'),
                'gross_amount' => $amount,
                'tax_amount' => 0,
                'net_amount' => $amount,
                'currency' => $expense->currency,
                'payment_method' => $expense->payment_method,
                'created_by' => auth()->id(),
            ]);

            $this->addLine($transaction, $expenseAccount, 'debit', $amount, 'Expense');
            $this->addLine($transaction, $cash, 'credit', $amount, 'Cash paid out');

            $this->log($transaction, 'created', 'Posted from paid expense.');

            return $transaction;
        });
    }

    public function reversePaidExpense(Expense $expense): ?FinanceTransaction
    {
        $original = $this->findSourcePosting('expenses', 'expense', $expense->id, 'recognition');

        if (! $original || $this->findSourcePosting('expenses', 'expense', $expense->id, 'reversal')) {
            return null;
        }

        return $this->createReversal($original, 'Expense voided — '.($expense->title ?? '#'.$expense->id));
    }

    public function postSalePayment(SalePayment $payment): ?FinanceTransaction
    {
        $payment->loadMissing('transaction');

        $sale = $payment->transaction;

        if (! $sale || $sale->sale_status !== 'completed') {
            return null;
        }

        if ($this->findSourcePosting('sales', 'sale_payment', $payment->id, 'collection')) {
            return null;
        }

        $amount = (float) $payment->amount_paid;

        if ($amount <= 0) {
            return null;
        }

        $period = $this->periods->forDate($payment->payment_date);
        $this->periods->assertOpen($period);

        $cash = $this->accounts->cashAccount($payment->payment_method);
        $ar = $this->accounts->accountsReceivable();

        return DB::transaction(function () use ($payment, $sale, $amount, $period, $cash, $ar) {
            $transaction = FinanceTransaction::create([
                'farm_id' => $sale->farm_id,
                'livestock_id' => $this->resolveLivestockId($sale),
                'transaction_code' => $this->generateCode('FT', $payment->payment_date),
                'transaction_date' => $payment->payment_date,
                'finance_period_id' => $period->id,
                'transaction_type' => 'income',
                'posting_kind' => 'collection',
                'source_module' => 'sales',
                'source_type' => 'sale_payment',
                'source_id' => $payment->id,
                'description' => 'Payment received — '.$sale->sale_number,
                'gross_amount' => $amount,
                'tax_amount' => 0,
                'net_amount' => $amount,
                'currency' => $sale->currency,
                'payment_method' => $payment->payment_method,
                'reference_number' => $payment->payment_reference,
                'created_by' => auth()->id(),
            ]);

            $this->addLine($transaction, $cash, 'debit', $amount, 'Cash received');
            $this->addLine($transaction, $ar, 'credit', $amount, 'Accounts receivable cleared');

            $this->log($transaction, 'created', 'Posted from sale payment.');

            return $transaction;
        });
    }

    public function syncPendingPayments(SaleTransaction $sale): void
    {
        $sale->loadMissing('payments');

        foreach ($sale->payments as $payment) {
            $this->postSalePayment($payment);
        }
    }

    private function createReversal(FinanceTransaction $original, string $description): FinanceTransaction
    {
        $period = $this->periods->forDate($original->transaction_date);
        $this->periods->assertOpen($period);

        return DB::transaction(function () use ($original, $description, $period) {
            $original->loadMissing('lines.account');

            $reversal = FinanceTransaction::create([
                'farm_id' => $original->farm_id,
                'livestock_id' => $original->livestock_id,
                'transaction_code' => $this->generateCode('FT', now()),
                'transaction_date' => now()->toDateString(),
                'finance_period_id' => $period->id,
                'transaction_type' => $original->transaction_type,
                'posting_kind' => 'reversal',
                'source_module' => $original->source_module,
                'source_type' => $original->source_type,
                'source_id' => $original->source_id,
                'description' => $description,
                'gross_amount' => $original->gross_amount,
                'tax_amount' => $original->tax_amount,
                'net_amount' => $original->net_amount,
                'currency' => $original->currency,
                'is_reversal' => true,
                'reversed_transaction_id' => $original->id,
                'created_by' => auth()->id(),
            ]);

            foreach ($original->lines as $line) {
                $opposite = $line->entry_type === 'debit' ? 'credit' : 'debit';
                $this->addLine($reversal, $line->account, $opposite, (float) $line->amount, 'Reversal');
            }

            $this->log($reversal, 'reversed', 'Reversal of '.$original->transaction_code);

            return $reversal;
        });
    }

    private function findSourcePosting(string $module, string $type, int $sourceId, string $kind): ?FinanceTransaction
    {
        $query = FinanceTransaction::query()
            ->where('source_module', $module)
            ->where('source_type', $type)
            ->where('source_id', $sourceId)
            ->where('posting_kind', $kind);

        if ($kind !== 'reversal') {
            $query->where('is_reversal', false);
        }

        return $query->first();
    }

    private function resolveLivestockId(SaleTransaction $sale): ?int
    {
        $fromItems = $sale->items
            ->pluck('livestock_id')
            ->merge($sale->items->map(fn ($item) => $item->animal?->livestock_id))
            ->filter()
            ->unique()
            ->values();

        return $fromItems->count() === 1 ? (int) $fromItems->first() : null;
    }

    private function addLine(
        FinanceTransaction $transaction,
        FinanceAccount $account,
        string $entryType,
        float $amount,
        ?string $description = null,
    ): FinanceTransactionLine {
        return $transaction->lines()->create([
            'finance_account_id' => $account->id,
            'entry_type' => $entryType,
            'amount' => round($amount, 2),
            'description' => $description,
        ]);
    }

    private function generateCode(string $prefix, Carbon|string $date): string
    {
        $dateKey = Carbon::parse($date)->format('Ymd');
        $pattern = "{$prefix}-{$dateKey}-%";

        $last = FinanceTransaction::query()
            ->where('transaction_code', 'like', $pattern)
            ->orderByDesc('transaction_code')
            ->value('transaction_code');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('%s-%s-%04d', $prefix, $dateKey, $seq);
    }

    private function log(FinanceTransaction $transaction, string $action, ?string $notes = null): void
    {
        FinanceTransactionLog::create([
            'finance_transaction_id' => $transaction->id,
            'action_type' => $action,
            'action_by' => auth()->id(),
            'action_at' => now(),
            'notes' => $notes,
        ]);
    }
}
