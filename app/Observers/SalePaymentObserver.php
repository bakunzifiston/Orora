<?php

namespace App\Observers;

use App\Models\SalePayment;
use App\Services\Finance\FinancePostingService;

class SalePaymentObserver
{
    public function __construct(private readonly FinancePostingService $posting) {}

    public function created(SalePayment $payment): void
    {
        $this->posting->postSalePayment($payment);
    }
}
