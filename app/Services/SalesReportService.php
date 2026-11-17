<?php

namespace App\Services;

use App\Models\SaleTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SalesReportService
{
    public const PDF_ROW_LIMIT = 2000;

    /**
     * @param  array<string, mixed>  $filters  from, to, farm_id, customer_id, type, status, payment_status
     */
    public function query(array $filters): Builder
    {
        return SaleTransaction::query()
            ->with(['farm:id,name', 'customer'])
            ->withSum('payments as paid_total', 'amount_paid')
            ->when(filled($filters['from'] ?? null), fn ($q) => $q->whereDate('sale_date', '>=', $filters['from']))
            ->when(filled($filters['to'] ?? null), fn ($q) => $q->whereDate('sale_date', '<=', $filters['to']))
            ->when(filled($filters['farm_id'] ?? null), fn ($q) => $q->where('farm_id', (int) $filters['farm_id']))
            ->when(filled($filters['customer_id'] ?? null), fn ($q) => $q->where('customer_id', (int) $filters['customer_id']))
            ->when(filled($filters['type'] ?? null), fn ($q) => $q->where('sale_type', $filters['type']))
            ->when(filled($filters['status'] ?? null), fn ($q) => $q->where('sale_status', $filters['status']))
            ->when(filled($filters['payment_status'] ?? null), fn ($q) => $q->where('payment_status', $filters['payment_status']))
            ->orderBy('sale_date')
            ->orderBy('id');
    }

    /**
     * Totals per currency so amounts in different currencies are never added together.
     *
     * @return Collection<string, array{count:int,total:float,paid:float,balance:float}>
     */
    public function summarize(Collection $sales): Collection
    {
        return $sales->groupBy(fn (SaleTransaction $s) => $s->currency ?: 'RWF')->map(function (Collection $group) {
            $total = (float) $group->sum('total_amount');
            $paid = (float) $group->sum(fn (SaleTransaction $s) => (float) $s->paid_total);

            return [
                'count' => $group->count(),
                'total' => $total,
                'paid' => $paid,
                'balance' => max(0, $total - $paid),
            ];
        });
    }

    public function rowBalance(SaleTransaction $sale): float
    {
        return max(0, (float) $sale->total_amount - (float) $sale->paid_total);
    }
}
