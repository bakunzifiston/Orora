<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\HealthRecord;
use Carbon\Carbon;

class HealthOverviewAnalyticsService
{
    private const MONTHS = 6;

    /**
     * @return array<string, mixed>
     */
    public function chartPayload(?Carbon $now = null): array
    {
        $now ??= Carbon::now();

        return [
            'recordsByMonth' => $this->recordsByMonth($now),
            'animalsByStatus' => $this->animalsByStatus(),
            'recordsByType' => $this->recordsByTypeChart(),
            'meta' => [
                'months' => self::MONTHS,
            ],
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function recordsByMonth(Carbon $now): array
    {
        $start = $now->copy()->subMonths(self::MONTHS - 1)->startOfMonth();

        $labels = [];
        $values = [];

        for ($i = 0; $i < self::MONTHS; $i++) {
            $month = $start->copy()->addMonths($i);
            $labels[] = $month->format('M Y');
            $values[] = HealthRecord::query()
                ->whereYear('recorded_on', $month->year)
                ->whereMonth('recorded_on', $month->month)
                ->count();
        }

        return compact('labels', 'values');
    }

    /**
     * @return array{labels: list<string>, values: list<int>, colors: list<string>}
     */
    private function animalsByStatus(): array
    {
        $counts = Animal::query()
            ->selectRaw('health_status, count(*) as total')
            ->groupBy('health_status')
            ->pluck('total', 'health_status');

        $statusColors = [
            'Healthy' => '#A4D400',
            'Pregnant' => '#4ade80',
            'Sick' => '#f87171',
            'Under treatment' => '#fb923c',
            'Quarantined' => '#fbbf24',
            'Recovering' => '#60a5fa',
            'Deceased' => '#6b7280',
        ];

        $labels = [];
        $values = [];
        $colors = [];

        foreach (config('modules.health_statuses', []) as $status) {
            $count = (int) ($counts[$status] ?? 0);
            if ($count === 0) {
                continue;
            }

            $labels[] = $status;
            $values[] = $count;
            $colors[] = $statusColors[$status] ?? '#94a3b8';
        }

        $known = config('modules.health_statuses', []);
        $unknown = 0;
        foreach ($counts as $status => $count) {
            if (! in_array($status, $known, true)) {
                $unknown += (int) $count;
            }
        }
        if ($unknown > 0) {
            $labels[] = 'Other / unset';
            $values[] = (int) $unknown;
            $colors[] = '#94a3b8';
        }

        return compact('labels', 'values', 'colors');
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function recordsByTypeChart(): array
    {
        $rows = HealthRecord::query()
            ->selectRaw('record_type, count(*) as total')
            ->groupBy('record_type')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('record_type')->values()->all(),
            'values' => $rows->pluck('total')->map(fn ($value) => (int) $value)->values()->all(),
        ];
    }
}
