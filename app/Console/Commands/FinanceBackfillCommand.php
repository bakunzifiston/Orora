<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\SalePayment;
use App\Models\SaleTransaction;
use App\Services\Finance\FinancePostingService;
use Illuminate\Console\Command;

class FinanceBackfillCommand extends Command
{
    protected $signature = 'finance:backfill {--tenant=}';

    protected $description = 'Post finance ledger entries for existing completed sales and paid expenses';

    public function handle(FinancePostingService $posting): int
    {
        $sales = 0;
        $expenses = 0;
        $payments = 0;

        SaleTransaction::query()->where('sale_status', 'completed')->orderBy('id')->each(function ($sale) use ($posting, &$sales) {
            if ($posting->postCompletedSale($sale)) {
                $sales++;
            }
        });

        Expense::query()->where('status', 'paid')->orderBy('id')->each(function ($expense) use ($posting, &$expenses) {
            if ($posting->postPaidExpense($expense)) {
                $expenses++;
            }
        });

        SalePayment::query()
            ->whereHas('transaction', fn ($q) => $q->where('sale_status', 'completed'))
            ->orderBy('id')
            ->each(function ($payment) use ($posting, &$payments) {
                if ($posting->postSalePayment($payment)) {
                    $payments++;
                }
            });

        $this->info("Posted {$sales} sales, {$expenses} expenses, {$payments} payments.");

        return self::SUCCESS;
    }
}
