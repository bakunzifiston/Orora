<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\SaleTransaction;
use App\Services\Finance\FinancePostingService;

class SaleTransactionObserver
{
    public function __construct(private readonly FinancePostingService $posting) {}

    public function updated(SaleTransaction $sale): void
    {
        if ($sale->wasChanged('sale_status')) {
            if ($sale->sale_status === 'completed') {
                $this->posting->postCompletedSale($sale);
            }

            if ($sale->getOriginal('sale_status') === 'completed' && $sale->sale_status === 'cancelled') {
                $this->posting->reverseCompletedSale($sale);
            }
        }
    }
}
