<?php

namespace App\Services;

use App\Models\MilkRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MilkOverviewAnalyticsService
{
    private const ANALYTICS_COMPARE_DAYS = 90;

    /**
     * @return array<string, mixed>
     */
    public function chartPayload(
        ?Carbon $now = null,
        ?int $farmId = null,
        ?Carbon $from = null,
        ?Carbon $to = null,
        bool $allTime = false,
    ): array {
        $now ??= Carbon::now();

        if ($allTime) {
            $compareStart = null;
            $compareEnd = null;
        } else {
            $compareStart = $from?->copy()->startOfDay()
                ?? $now->copy()->subDays(self::ANALYTICS_COMPARE_DAYS)->startOfDay();
            $compareEnd = $to?->copy()->endOfDay();
        }

        $records = MilkRecord::query()
            ->whereHas('session', function ($q) use ($farmId, $compareStart, $compareEnd) {
                $q->where('status', 'completed')
                    ->when($farmId, fn ($sq) => $sq->where('farm_id', $farmId));

                if ($compareStart && $compareEnd) {
                    $q->whereBetween('session_date', [
                        $compareStart->toDateString(),
                        $compareEnd->toDateString(),
                    ]);
                } elseif ($compareStart) {
                    $q->whereDate('session_date', '>=', $compareStart->toDateString());
                }
            })
            ->with('session')
            ->get(['id', 'milk_session_id', 'animal_id', 'yield_liters']);

        $perAnimal = $this->yieldByAnimal($records);
        $perHerd = $this->yieldByHerd($records);

        $days = match (true) {
            $allTime => null,
            $compareStart && $compareEnd => max(1, (int) $compareStart->diffInDays($compareEnd) + 1),
            default => self::ANALYTICS_COMPARE_DAYS,
        };

        return [
            'animalsCompare' => $perAnimal->take(10)->values()->all(),
            'herdsCompare' => $perHerd->take(10)->values()->all(),
            'bestAnimalsTable' => $perAnimal->take(15)->values()->all(),
            'meta' => [
                'compareDays' => $days,
                'allTime' => $allTime,
            ],
        ];
    }

    /**
     * @param  Collection<int, MilkRecord>  $records
     */
    private function yieldByAnimal(Collection $records): Collection
    {
        return $records
            ->groupBy('animal_id')
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'animal_id' => (int) $first->animal_id,
                    'tag' => '',
                    'name' => null,
                    'liters' => round($group->sum(fn (MilkRecord $r) => (float) $r->yield_liters), 2),
                ];
            })
            ->values()
            ->sortByDesc('liters')
            ->values();
    }

    /**
     * @param  Collection<int, MilkRecord>  $records
     */
    private function yieldByHerd(Collection $records): Collection
    {
        return $records
            ->groupBy(fn (MilkRecord $r) => $r->session?->livestock_id ?? 'none')
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'livestock_id' => $first->session?->livestock_id ? (int) $first->session->livestock_id : null,
                    'name' => '',
                    'liters' => round($group->sum(fn (MilkRecord $r) => (float) $r->yield_liters), 2),
                ];
            })
            ->values()
            ->sortByDesc('liters')
            ->values();
    }

    private function hydrateAnimals(Collection $animalRows): Collection
    {
        if ($animalRows->isEmpty()) {
            return $animalRows;
        }

        $ids = $animalRows->pluck('animal_id')->unique()->values()->all();
        $attrs = DB::table('animals')->whereIn('id', $ids)->get(['id', 'tag_number', 'name'])->keyBy('id');

        return $animalRows->map(function ($row) use ($attrs) {
            $a = $attrs->get((int) $row['animal_id']);

            return array_merge($row, [
                'tag' => $a ? (string) $a->tag_number : '?',
                'name' => $a?->name,
            ]);
        });
    }

    private function hydrateHerds(Collection $herdRows): Collection
    {
        if ($herdRows->isEmpty()) {
            return $herdRows;
        }

        $ids = $herdRows->pluck('livestock_id')->filter()->unique()->values()->all();
        $names = $ids === []
            ? collect()
            : DB::table('livestock')->whereIn('id', $ids)->pluck('name', 'id');

        return $herdRows->map(function ($row) use ($names) {
            $id = $row['livestock_id'];

            return array_merge($row, [
                'name' => $id === null ? 'No herd / group set' : (string) ($names[(int) $id] ?? 'Herd ##'.$id),
            ]);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function chartPayloadHydrated(
        ?Carbon $now = null,
        ?int $farmId = null,
        ?Carbon $from = null,
        ?Carbon $to = null,
        bool $allTime = false,
    ): array {
        $payload = $this->chartPayload($now, $farmId, $from, $to, $allTime);
        $payload['animalsCompare'] = $this->hydrateAnimals(collect($payload['animalsCompare']))->values()->all();
        $payload['herdsCompare'] = $this->hydrateHerds(collect($payload['herdsCompare']))->values()->all();
        $payload['bestAnimalsTable'] = $this->hydrateAnimals(collect($payload['bestAnimalsTable']))->values()->all();

        return $payload;
    }
}
