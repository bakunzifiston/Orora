<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardFilterService
{
    /**
     * @return array{period: string, from: string, to: string, label: string}
     */
    public function resolve(Request $request): array
    {
        $period = $request->input('period', 'monthly');

        if (! in_array($period, ['daily', 'monthly', 'yearly', 'custom', 'all'], true)) {
            $period = 'monthly';
        }

        [$from, $to, $label] = match ($period) {
            'daily' => [
                now()->startOfDay(),
                now()->endOfDay(),
                'Today',
            ],
            'yearly' => [
                now()->startOfYear(),
                now()->endOfYear(),
                (string) now()->year,
            ],
            'all' => [
                Carbon::parse('2000-01-01')->startOfDay(),
                now()->endOfDay(),
                'All time',
            ],
            'custom' => [
                Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay(),
                Carbon::parse($request->input('to', now()->endOfMonth()->toDateString()))->endOfDay(),
                'Custom range',
            ],
            default => [
                now()->startOfMonth(),
                now()->endOfMonth(),
                now()->format('F Y'),
            ],
        };

        if ($period === 'custom') {
            $label = $from->format('M j, Y').' – '.$to->format('M j, Y');
        }

        return [
            'period' => $period,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'label' => $label,
        ];
    }

    public function rangeStart(array $filters): Carbon
    {
        return Carbon::parse($filters['from'])->startOfDay();
    }

    public function rangeEnd(array $filters): Carbon
    {
        return Carbon::parse($filters['to'])->endOfDay();
    }
}
