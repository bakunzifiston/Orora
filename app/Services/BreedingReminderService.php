<?php

namespace App\Services;

use App\Models\BreedingRecord;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BreedingReminderService
{
    public function dueAfterDays(): int
    {
        return (int) config('modules.breeding_pregnancy_check_due_days', 35);
    }

    public function pregnancyCheckDueOn(Carbon|string $breedingDate): Carbon
    {
        return Carbon::parse($breedingDate)->addDays($this->dueAfterDays());
    }

    public function pregnancyCheckDueQuery(?int $farmId = null): Builder
    {
        $dueBy = now()->subDays($this->dueAfterDays())->toDateString();

        return BreedingRecord::query()
            ->when($farmId, fn (Builder $q) => $q->where('farm_id', $farmId))
            ->where('breeding_status', 'pending')
            ->whereDoesntHave('pregnancyChecks')
            ->where(function (Builder $q) use ($dueBy) {
                $q->whereDate('pregnancy_check_due_on', '<=', now()->toDateString())
                    ->orWhere(function (Builder $q) use ($dueBy) {
                        $q->whereNull('pregnancy_check_due_on')
                            ->whereDate('breeding_date', '<=', $dueBy);
                    });
            });
    }

    public function dueCount(?int $farmId = null): int
    {
        return $this->pregnancyCheckDueQuery($farmId)->count();
    }

    /**
     * @return Collection<int, BreedingRecord>
     */
    public function dueRecords(?int $farmId = null, ?int $limit = null): Collection
    {
        $query = $this->pregnancyCheckDueQuery($farmId)
            ->with(['farm', 'femaleAnimal'])
            ->orderBy('pregnancy_check_due_on');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function isPregnancyCheckDue(BreedingRecord $record): bool
    {
        if ($record->breeding_status !== 'pending' || $record->pregnancyChecks()->exists()) {
            return false;
        }

        if (! $record->pregnancy_check_due_on) {
            return $record->breeding_date->copy()->addDays($this->dueAfterDays())->lte(now()->startOfDay());
        }

        return $record->pregnancy_check_due_on->lte(now()->startOfDay());
    }

    public function daysUntilPregnancyCheck(BreedingRecord $record): ?int
    {
        if ($record->breeding_status !== 'pending' || $record->pregnancyChecks()->exists()) {
            return null;
        }

        $dueOn = $record->pregnancy_check_due_on
            ?? $this->pregnancyCheckDueOn($record->breeding_date);

        return now()->startOfDay()->diffInDays($dueOn, false);
    }
}
