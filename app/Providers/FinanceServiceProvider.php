<?php

namespace App\Providers;

use App\Models\Expense;
use App\Models\SalePayment;
use App\Models\SaleTransaction;
use App\Observers\ExpenseObserver;
use App\Observers\SalePaymentObserver;
use App\Observers\SaleTransactionObserver;
use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        SaleTransaction::observe(SaleTransactionObserver::class);
        Expense::observe(ExpenseObserver::class);
        SalePayment::observe(SalePaymentObserver::class);
    }
}
