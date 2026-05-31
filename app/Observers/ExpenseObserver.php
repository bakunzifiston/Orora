<?php

namespace App\Observers;

use App\Models\Expense;
use App\Services\Finance\FinancePostingService;

class ExpenseObserver
{
    public function __construct(private readonly FinancePostingService $posting) {}

    public function saved(Expense $expense): void
    {
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
        if ($expense->status === 'paid') {
            $this->posting->reversePaidExpense($expense);
        }
    }
}
