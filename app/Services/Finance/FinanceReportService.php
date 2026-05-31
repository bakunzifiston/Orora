<?php

namespace App\Services\Finance;

use App\Models\FinanceAccount;
use App\Models\FinanceTransactionLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinanceReportService
{
    public function profitAndLoss(string $from, string $to, ?int $farmId = null, ?int $livestockId = null): array
    {
        $lines = $this->baseLineQuery($from, $to, $farmId, $livestockId)
            ->whereIn('finance_accounts.account_type', ['income', 'expense'])
            ->get();

        $incomeRows = $this->aggregateByAccount($lines, 'income');
        $expenseRows = $this->aggregateByAccount($lines, 'expense');

        $totalIncome = $incomeRows->sum('amount');
        $totalExpenses = $expenseRows->sum('amount');
        $netIncome = $totalIncome - $totalExpenses;

        return [
            'income' => $incomeRows,
            'expenses' => $expenseRows,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
        ];
    }

    public function cashFlow(string $from, string $to, ?int $farmId = null, ?int $livestockId = null): array
    {
        $lines = $this->baseLineQuery($from, $to, $farmId, $livestockId)
            ->whereIn('finance_accounts.account_subtype', ['cash', 'bank'])
            ->orderBy('finance_transactions.transaction_date')
            ->get();

        $movements = $lines->map(function ($row) {
            $signed = $row->entry_type === 'debit' ? (float) $row->amount : -(float) $row->amount;

            return [
                'date' => $row->transaction_date,
                'code' => $row->transaction_code,
                'description' => $row->description,
                'account' => $row->account_name,
                'amount' => $signed,
            ];
        });

        return [
            'movements' => $movements,
            'net_cash_change' => $movements->sum('amount'),
        ];
    }

    public function overviewStats(string $from, string $to, ?int $farmId = null, ?int $livestockId = null): array
    {
        $pl = $this->profitAndLoss($from, $to, $farmId, $livestockId);
        $cash = $this->cashFlow($from, $to, $farmId, $livestockId);

        $arBalance = $this->accountBalance(
            config('finance.accounts_receivable'),
            $from,
            $to,
            $farmId,
            $livestockId,
        );

        return [
            'revenue' => $pl['total_income'],
            'expenses' => $pl['total_expenses'],
            'net_income' => $pl['net_income'],
            'cash_change' => $cash['net_cash_change'],
            'accounts_receivable' => $arBalance,
            'transaction_count' => $this->transactionCount($from, $to, $farmId, $livestockId),
        ];
    }

    private function baseLineQuery(string $from, string $to, ?int $farmId, ?int $livestockId)
    {
        return FinanceTransactionLine::query()
            ->join('finance_transactions', 'finance_transaction_lines.finance_transaction_id', '=', 'finance_transactions.id')
            ->join('finance_accounts', 'finance_transaction_lines.finance_account_id', '=', 'finance_accounts.id')
            ->whereBetween('finance_transactions.transaction_date', [$from, $to])
            ->when($farmId, fn ($q) => $q->where('finance_transactions.farm_id', $farmId))
            ->when($livestockId, fn ($q) => $q->where('finance_transactions.livestock_id', $livestockId))
            ->select([
                'finance_accounts.account_code',
                'finance_accounts.account_name',
                'finance_accounts.account_type',
                'finance_accounts.account_subtype',
                'finance_accounts.normal_balance',
                'finance_transaction_lines.entry_type',
                'finance_transaction_lines.amount',
                'finance_transactions.transaction_date',
                'finance_transactions.transaction_code',
                'finance_transactions.description',
            ]);
    }

    private function aggregateByAccount(Collection $lines, string $accountType): Collection
    {
        return $lines
            ->where('account_type', $accountType)
            ->groupBy('account_code')
            ->map(function ($group, $code) use ($accountType) {
                $amount = $group->sum(function ($row) use ($accountType) {
                    $value = (float) $row->amount;

                    return $accountType === 'income'
                        ? ($row->entry_type === 'credit' ? $value : -$value)
                        : ($row->entry_type === 'debit' ? $value : -$value);
                });

                $first = $group->first();

                return [
                    'account_code' => $code,
                    'account_name' => $first->account_name,
                    'amount' => round($amount, 2),
                ];
            })
            ->values()
            ->sortBy('account_code')
            ->values();
    }

    private function accountBalance(string $accountCode, string $from, string $to, ?int $farmId, ?int $livestockId): float
    {
        $account = FinanceAccount::byCode($accountCode);

        if (! $account) {
            return 0;
        }

        $rows = FinanceTransactionLine::query()
            ->join('finance_transactions', 'finance_transaction_lines.finance_transaction_id', '=', 'finance_transactions.id')
            ->where('finance_transaction_lines.finance_account_id', $account->id)
            ->whereBetween('finance_transactions.transaction_date', [$from, $to])
            ->when($farmId, fn ($q) => $q->where('finance_transactions.farm_id', $farmId))
            ->when($livestockId, fn ($q) => $q->where('finance_transactions.livestock_id', $livestockId))
            ->get(['finance_transaction_lines.entry_type', 'finance_transaction_lines.amount']);

        return $rows->sum(fn ($row) => $row->entry_type === 'debit' ? (float) $row->amount : -(float) $row->amount);
    }

    private function transactionCount(string $from, string $to, ?int $farmId, ?int $livestockId): int
    {
        return (int) DB::table('finance_transactions')
            ->whereBetween('transaction_date', [$from, $to])
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->when($livestockId, fn ($q) => $q->where('livestock_id', $livestockId))
            ->count();
    }
}
