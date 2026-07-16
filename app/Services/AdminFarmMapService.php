<?php

namespace App\Services;

use App\Models\Farm;
use Illuminate\Support\Facades\Schema;

class AdminFarmMapService
{
    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     lat: float,
     *     lng: float,
     *     location: string,
     *     status: ?string,
     *     url: string
     * }>
     */
    public function markers(): array
    {
        if (! Schema::hasTable('farms')) {
            return [];
        }

        $columns = ['id', 'name', 'status', 'province', 'district', 'district_code', 'province_code'];

        if (Schema::hasColumn('farms', 'latitude')) {
            $columns[] = 'latitude';
        }

        if (Schema::hasColumn('farms', 'longitude')) {
            $columns[] = 'longitude';
        }

        return Farm::query()
            ->withoutGlobalScope('tenant')
            ->orderBy('name')
            ->get($columns)
            ->map(fn (Farm $farm) => $this->toMarker($farm))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     lat: float,
     *     lng: float,
     *     location: string,
     *     status: ?string,
     *     url: string
     * }|null
     */
    private function toMarker(Farm $farm): ?array
    {
        [$lat, $lng] = $this->coordinatesFor($farm);

        return [
            'id' => $farm->id,
            'name' => $farm->name,
            'lat' => $lat,
            'lng' => $lng,
            'location' => collect([$farm->district, $farm->province])->filter()->implode(', ') ?: 'Rwanda',
            'status' => $farm->status,
            'url' => route('central.users.show', $farm),
        ];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function coordinatesFor(Farm $farm): array
    {
        if (filled($farm->latitude) && filled($farm->longitude)) {
            return [(float) $farm->latitude, (float) $farm->longitude];
        }

        $districtCode = (int) ($farm->district_code ?? 0);

        if ($districtCode > 0) {
            return $this->districtCoordinates($districtCode, $farm->id);
        }

        $provinceCode = (int) ($farm->province_code ?? 0);
        $base = config('rwanda_map.provinces.'.$provinceCode, config('rwanda_map.default'));
        $jitter = $this->farmJitter($farm->id);

        return [
            (float) $base['lat'] + $jitter[0],
            (float) $base['lng'] + $jitter[1],
        ];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function districtCoordinates(int $districtCode, int $farmId): array
    {
        $provinceCode = intdiv($districtCode, 100);
        $districtIndex = $districtCode % 100;
        $base = config('rwanda_map.provinces.'.$provinceCode, config('rwanda_map.default'));

        $angle = ($districtIndex / 12) * 2 * M_PI;
        $radius = 0.06;

        $jitter = $this->farmJitter($farmId);

        return [
            (float) $base['lat'] + ($radius * cos($angle)) + $jitter[0],
            (float) $base['lng'] + ($radius * sin($angle)) + $jitter[1],
        ];
    }

    /**
     * Small deterministic offset so farms in the same district do not stack.
     *
     * @return array{0: float, 1: float}
     */
    private function farmJitter(int $farmId): array
    {
        $seed = ($farmId * 37) % 360;
        $radians = deg2rad($seed);

        return [
            0.012 * cos($radians),
            0.012 * sin($radians),
        ];
    }
}
