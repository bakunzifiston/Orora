<?php

namespace App\Services;

use App\Models\Farm;
use App\Models\MilkSession;
use App\Models\TenantAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminDashboardFilterService
{
    /**
     * @return array{period: string, from: string, to: string, label: string}
     */
    public function resolve(Request $request): array
    {
        $period = $request->input('period', 'all');

        if (! in_array($period, ['daily', 'monthly', 'yearly', 'custom', 'all', ''], true)) {
            $period = 'all';
        }

        if ($period === '') {
            $period = 'all';
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
            'monthly' => [
                now()->startOfMonth(),
                now()->endOfMonth(),
                now()->format('F Y'),
            ],
            'custom' => [
                Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay(),
                Carbon::parse($request->input('to', now()->endOfMonth()->toDateString()))->endOfDay(),
                'Custom range',
            ],
            default => [
                $this->allTimeStart(),
                now()->endOfDay(),
                'All time',
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

    private function allTimeStart(): Carbon
    {
        $candidates = [];

        try {
            if (Schema::hasTable('farms')) {
                $farmStart = Farm::query()->withoutGlobalScope('tenant')->min('created_at');
                if ($farmStart) {
                    $candidates[] = Carbon::parse($farmStart)->startOfDay();
                }
            }
        } catch (\Throwable) {
            // Keep fallback below.
        }

        try {
            if (Schema::hasTable('tenant_accounts')) {
                $accountStart = TenantAccount::query()->min('created_at');
                if ($accountStart) {
                    $candidates[] = Carbon::parse($accountStart)->startOfDay();
                }
            }
        } catch (\Throwable) {
            // Keep fallback below.
        }

        try {
            if (Schema::hasTable('milk_sessions')) {
                $milkStart = MilkSession::query()
                    ->where('status', 'completed')
                    ->min('session_date');
                if ($milkStart) {
                    $candidates[] = Carbon::parse($milkStart)->startOfDay();
                }
            }
        } catch (\Throwable) {
            // Keep fallback below.
        }

        if ($candidates === []) {
            return now()->subYear()->startOfYear();
        }

        return collect($candidates)->sort()->first();
    }
}
