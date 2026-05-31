<?php

namespace App\Services\Finance;

use App\Models\FinancePeriod;
use Carbon\Carbon;

class FinancePeriodService
{
    public function forDate(Carbon|string $date): FinancePeriod
    {
        $date = Carbon::parse($date)->toDateString();

        $period = FinancePeriod::query()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if ($period) {
            return $period;
        }

        return $this->createMonthlyPeriod(Carbon::parse($date));
    }

    public function createMonthlyPeriod(Carbon $date): FinancePeriod
    {
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        return FinancePeriod::query()->firstOrCreate(
            [
                'period_type' => 'monthly',
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
            ],
            [
                'period_name' => $start->format('F Y'),
                'status' => 'open',
            ],
        );
    }

    public function assertOpen(FinancePeriod $period): void
    {
        if (! $period->isOpen()) {
            throw new \InvalidArgumentException("Accounting period {$period->period_name} is not open for posting.");
        }
    }
}
