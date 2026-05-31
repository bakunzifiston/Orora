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

        $records = MilkRecord::query()
            ->whereDate('recorded_on', '>=', $now->copy()->subMonths(12)->startOfMonth())
            ->get(['recorded_on', 'quantity', 'unit', 'animal_id', 'livestock_id']);

        $compareStart = $now->copy()->subDays(self::ANALYTICS_COMPARE_DAYS)->startOfDay();
        $compareRecords = $records->filter(fn ($r) => $r->recorded_on->gte($compareStart));

        $perAnimal = $this->yieldByAnimal($compareRecords);
        $perHerd = $this->yieldByHerd($compareRecords);

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
     * @return Collection<int, array{animal_id:int,tag:string,name:?string,liters:float}>
     */
    private function yieldByAnimal(Collection $records): Collection
    {
        return $records
            ->groupBy('animal_id')
            ->map(function (Collection $group) {
                /** @var MilkRecord|null $first */
                $first = $group->first();

                return [
                    'animal_id' => (int) $first->animal_id,
                    'tag' => '',
                    'name' => null,
                    'liters' => round($group->sum(fn (MilkRecord $r) => $this->toLiters((float) $r->quantity, (string) $r->unit)), 2),
                ];
            })
            ->values()
            ->sortByDesc('liters')
            ->values();
    }

    /**
     * Hydrate animal tags/names onto yield rows.
     *
     * @param  Collection<int, array<string, mixed>>  $animalRows
     */
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

    /**
     * @param  Collection<int, MilkRecord>  $records
     * @return Collection<int, array{livestock_id:?int,name:string,liters:float}>
     */
    private function yieldByHerd(Collection $records): Collection
    {
        /** @phpstan-ignore-next-line */
        return $records
            ->groupBy(fn (MilkRecord $r) => $r->livestock_id ?: 'none')
            ->map(function (Collection $group, $key) {
                $first = $group->first();

                return [
                    'livestock_id' => $first->livestock_id ? (int) $first->livestock_id : null,
                    'name' => '',
                    'liters' => round($group->sum(fn (MilkRecord $r) => $this->toLiters((float) $r->quantity, (string) $r->unit)), 2),
                ];
            })
            ->values()
            ->sortByDesc('liters')
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $herdRows
     */
    private function hydrateHerds(Collection $herdRows): Collection
    {
        if ($herdRows->isEmpty()) {
            return $herdRows;
        }

        $withIds = $herdRows->filter(fn ($r) => ! empty($r['livestock_id']));
        $ids = $withIds->pluck('livestock_id')->unique()->values()->all();

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

    private function toLiters(float $quantity, string $unit): float
    {
        $unit = strtolower(trim($unit));

        return match ($unit) {
            'ml' => $quantity / 1000,
            default => $quantity,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function chartPayloadHydrated(?Carbon $now = null): array
    {
        $payload = $this->chartPayload($now);
        /** @phpstan-ignore-next-line */
        $payload['animalsCompare'] = $this->hydrateAnimals(collect($payload['animalsCompare']));
        /** @phpstan-ignore-next-line */
        $payload['herdsCompare'] = $this->hydrateHerds(collect($payload['herdsCompare']));
        /** @phpstan-ignore-next-line */
        $payload['bestAnimalsTable'] = $this->hydrateAnimals(collect($payload['bestAnimalsTable']));

        return $payload;
    }
}
