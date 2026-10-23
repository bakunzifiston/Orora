<?php

namespace App\Services\Feeding;

use App\Models\FeedingSchedule;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class FeedingScheduleService
{
    /**
     * After a feeding is recorded from a schedule, move "next due" to the first
     * occurrence after the feeding date. A schedule past its end date is completed.
     */
    public function recordFed(FeedingSchedule $schedule, CarbonInterface|string $fedOn): FeedingSchedule
    {
        $fedOn = Carbon::parse($fedOn)->startOfDay();
        $next = ($schedule->next_due_date ?? $schedule->start_date ?? $fedOn)->copy()->startOfDay();

        // Guard against bad data looping forever (e.g. a fed date decades ahead).
        for ($i = 0; $next->lte($fedOn) && $i < 5000; $i++) {
            $next = $this->step($next, $schedule->frequency);
        }

        $attributes = ['next_due_date' => $next->toDateString()];

        if ($schedule->end_date && $next->gt($schedule->end_date)) {
            $attributes['status'] = 'completed';
        }

        $schedule->update($attributes);

        return $schedule;
    }

    /**
     * Due state for display: overdue, due today or upcoming, with the day gap.
     *
     * @return array{state: string, days: int}|null
     */
    public function dueState(FeedingSchedule $schedule): ?array
    {
        if ($schedule->status !== 'active' || ! $schedule->next_due_date) {
            return null;
        }

        $days = (int) now()->startOfDay()->diffInDays($schedule->next_due_date->copy()->startOfDay(), false);

        return [
            'state' => match (true) {
                $days < 0 => 'overdue',
                $days === 0 => 'today',
                default => 'upcoming',
            },
            'days' => abs($days),
        ];
    }

    private function step(Carbon $date, ?string $frequency): Carbon
    {
        return match ($frequency) {
            'weekly' => $date->addWeek(),
            'biweekly' => $date->addWeeks(2),
            'monthly' => $date->addMonthNoOverflow(),
            default => $date->addDay(),
        };
    }
}
