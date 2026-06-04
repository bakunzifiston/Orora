<?php

namespace App\Observers;

use App\Models\Expense;
use App\Services\Finance\FinancePostingService;
use App\Services\Milk\MilkCostPerLitreService;

class ExpenseObserver
{
    public function __construct(
        private readonly FinancePostingService $posting,
        private readonly MilkCostPerLitreService $costPerLitre,
    ) {}

    public function saved(Expense $expense): void
    {
        if ($expense->wasChanged(['status', 'amount', 'expense_date', 'farm_id']) || $expense->wasRecentlyCreated) {
            $this->costPerLitre->invalidateCache(
                $expense->farm_id,
                $expense->expense_date?->toDateString(),
            );
        }

        if ($expense->wasRecentlyCreated && $expense->status === 'paid') {
            $this->posting->postPaidExpense($expense);

            return;
        }

        if ($expense->wasChanged('status')) {
            $original = $expense->getOriginal('status');

            if ($expense->status === 'paid') {
                $this->posting->postPaidExpense($expense);
            } elseif ($original === 'paid' && in_array($expense->status, ['void', 'draft'], true)) {
                $this->posting->reversePaidExpense($expense);
            }
        } elseif ($expense->status === 'paid' && $expense->wasChanged('amount')) {
            $this->posting->reversePaidExpense($expense);
            $this->posting->postPaidExpense($expense);
        }
    }

    public function deleting(Expense $expense): void
    {
        $this->costPerLitre->invalidateCache(
            $expense->farm_id,
            $expense->expense_date?->toDateString(),
        );

        if ($expense->status === 'paid') {
            $this->posting->reversePaidExpense($expense);
        }
    }
}
