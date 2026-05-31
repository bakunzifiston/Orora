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
    public function chartPayload(?Carbon $now = null): array
    {
        $now ??= Carbon::now();
        $compareStart = $now->copy()->subDays(self::ANALYTICS_COMPARE_DAYS)->startOfDay();

        $records = MilkRecord::query()
            ->whereHas('session', fn ($q) => $q
                ->where('status', 'completed')
                ->whereDate('session_date', '>=', $compareStart))
            ->with('session')
            ->get(['id', 'milk_session_id', 'animal_id', 'yield_liters']);

        $perAnimal = $this->yieldByAnimal($records);
        $perHerd = $this->yieldByHerd($records);

        return [
            'animalsCompare' => $perAnimal->take(10)->values()->all(),
            'herdsCompare' => $perHerd->take(10)->values()->all(),
            'bestAnimalsTable' => $perAnimal->take(15)->values()->all(),
            'meta' => [
                'compareDays' => self::ANALYTICS_COMPARE_DAYS,
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
    public function chartPayloadHydrated(?Carbon $now = null): array
    {
        $payload = $this->chartPayload($now);
        $payload['animalsCompare'] = $this->hydrateAnimals(collect($payload['animalsCompare']));
        $payload['herdsCompare'] = $this->hydrateHerds(collect($payload['herdsCompare']));
        $payload['bestAnimalsTable'] = $this->hydrateAnimals(collect($payload['bestAnimalsTable']));

        return $payload;
    }
}
