<?php

namespace App\Services\Finance;

use App\Models\Expense;
use App\Models\FinanceAccount;
use App\Models\FinancePeriod;
use App\Models\FinanceTransaction;
use App\Models\FinanceTransactionLine;
use App\Models\FinanceTransactionLog;
use App\Models\SalePayment;
use App\Models\SaleTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FinanceAccountResolver
{
    public function revenueAccountForSale(SaleTransaction $sale): FinanceAccount
    {
        $code = config('finance.sale_type_to_revenue_account.'.$sale->sale_type)
            ?? config('finance.sale_type_to_revenue_account.animal_sale');

        return $this->requireAccount($code);
    }

    public function expenseAccountForExpense(Expense $expense): FinanceAccount
    {
        $code = $expense->category?->code
            ? (config('finance.expense_category_to_account.'.$expense->category->code) ?? config('finance.default_expense_account'))
            : config('finance.default_expense_account');

        return $this->requireAccount($code);
    }

    public function cashAccount(?string $paymentMethod): FinanceAccount
    {
        $bankMethods = config('finance.bank_payment_methods', []);
        $code = in_array($paymentMethod, $bankMethods, true)
            ? config('finance.cash_accounts.bank')
            : config('finance.cash_accounts.cash');

        return $this->requireAccount($code);
    }

    public function accountsReceivable(): FinanceAccount
    {
        return $this->requireAccount(config('finance.accounts_receivable'));
    }

    public function taxesPayable(): FinanceAccount
    {
        return $this->requireAccount(config('finance.taxes_payable'));
    }

    public function requireAccount(string $code): FinanceAccount
    {
        $account = FinanceAccount::byCode($code);

        if (! $account) {
            throw new InvalidArgumentException("Finance account {$code} is not configured.");
        }

        return $account;
    }
}
