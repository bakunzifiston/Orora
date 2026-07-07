<?php

namespace App\Services;

use App\Models\Farm;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminUserDirectoryFilterService
{
    public function __construct(
        private readonly AdminDashboardFilterService $dateFilters,
        private readonly RwandaLocationService $locations,
    ) {}

    /**
     * @return array{
     *     period: string,
     *     from: string,
     *     to: string,
     *     label: string,
     *     farm_id: ?int,
     *     province_code: ?int,
     *     district_code: ?int,
     *     scope_label: string
     * }
     */
    public function resolve(Request $request): array
    {
        $filters = $this->dateFilters->resolve($request);

        $farmId = $request->filled('farm_id') ? (int) $request->input('farm_id') : null;
        $provinceCode = $request->filled('province_code') ? (int) $request->input('province_code') : null;
        $districtCode = $request->filled('district_code') ? (int) $request->input('district_code') : null;

        if ($districtCode && ! $provinceCode) {
            $districtCode = null;
        }

        if ($farmId) {
            $provinceCode = null;
            $districtCode = null;
        }

        $filters['farm_id'] = $farmId;
        $filters['province_code'] = $provinceCode;
        $filters['district_code'] = $districtCode;
        $filters['scope_label'] = $this->scopeLabel($farmId, $provinceCode, $districtCode);

        return $filters;
    }

    public function rangeStart(array $filters): Carbon
    {
        return $this->dateFilters->rangeStart($filters);
    }

    public function rangeEnd(array $filters): Carbon
    {
        return $this->dateFilters->rangeEnd($filters);
    }

    /**
     * @param  Builder<Farm>  $query
     * @param  array{farm_id?: ?int, province_code?: ?int, district_code?: ?int}  $filters
     */
    public function applyToFarms(Builder $query, array $filters): Builder
    {
        if (! empty($filters['farm_id'])) {
            return $query->whereKey((int) $filters['farm_id']);
        }

        if (! empty($filters['province_code'])) {
            $query->where('province_code', (int) $filters['province_code']);
        }

        if (! empty($filters['district_code'])) {
            $query->where('district_code', (int) $filters['district_code']);
        }

        return $query;
    }

    /**
     * @param  array{farm_id?: ?int, province_code?: ?int, district_code?: ?int}  $filters
     * @return list<int>|null
     */
    public function scopedFarmIds(array $filters): ?array
    {
        if (empty($filters['farm_id']) && empty($filters['province_code']) && empty($filters['district_code'])) {
            return null;
        }

        return $this->applyToFarms(Farm::query()->withoutGlobalScope('tenant'), $filters)->pluck('id')->all();
    }

    public function hasScope(array $filters): bool
    {
        return ! empty($filters['farm_id'])
            || ! empty($filters['province_code'])
            || ! empty($filters['district_code']);
    }

    /**
     * @return list<array{code: int, name: string}>
     */
    public function provinces(): array
    {
        try {
            return $this->locations->provinces();
        } catch (\Throwable) {
            return Farm::query()
                ->whereNotNull('province_code')
                ->select('province_code as code', 'province as name')
                ->distinct()
                ->orderBy('province')
                ->get()
                ->map(fn ($row) => ['code' => (int) $row->code, 'name' => (string) $row->name])
                ->all();
        }
    }

    /**
     * @return list<array{code: int, name: string, province_code: int}>
     */
    public function districts(?int $provinceCode): array
    {
        if (! $provinceCode) {
            return [];
        }

        try {
            return $this->locations->districts($provinceCode);
        } catch (\Throwable) {
            return Farm::query()
                ->where('province_code', $provinceCode)
                ->whereNotNull('district_code')
                ->select('district_code as code', 'district as name', 'province_code')
                ->distinct()
                ->orderBy('district')
                ->get()
                ->map(fn ($row) => [
                    'code' => (int) $row->code,
                    'name' => (string) $row->name,
                    'province_code' => (int) $row->province_code,
                ])
                ->all();
        }
    }

    private function scopeLabel(?int $farmId, ?int $provinceCode, ?int $districtCode): string
    {
        if ($farmId) {
            $name = Farm::query()->whereKey($farmId)->value('name');

            return $name ? (string) $name : 'Selected farm';
        }

        $parts = [];

        if ($districtCode) {
            try {
                $district = $this->locations->findDistrict($districtCode);
                if ($district) {
                    $parts[] = $district['name'];
                }
            } catch (\Throwable) {
                $districtName = Farm::query()->where('district_code', $districtCode)->value('district');
                if ($districtName) {
                    $parts[] = (string) $districtName;
                }
            }
        } elseif ($provinceCode) {
            try {
                $province = $this->locations->findProvince($provinceCode);
                if ($province) {
                    $parts[] = $province['name'];
                }
            } catch (\Throwable) {
                $provinceName = Farm::query()->where('province_code', $provinceCode)->value('province');
                if ($provinceName) {
                    $parts[] = (string) $provinceName;
                }
            }
        }

        return $parts !== [] ? implode(', ', $parts) : 'All locations';
    }

    public function filtersActive(Request $request): bool
    {
        if ($request->filled('farm_id') || $request->filled('province_code') || $request->filled('district_code')) {
            return true;
        }

        if ($request->filled('from') || $request->filled('to')) {
            return true;
        }

        if ($request->has('period')) {
            $period = (string) $request->input('period', 'all');

            return $period !== '' && $period !== 'all';
        }

        return false;
    }

    /**
     * @return array{
     *     period: string,
     *     from: string,
     *     to: string,
     *     label: string,
     *     farm_id: ?int,
     *     province_code: ?int,
     *     district_code: ?int,
     *     scope_label: string
     * }
     */
    public function defaults(): array
    {
        return [
            'period' => '',
            'from' => '',
            'to' => '',
            'label' => 'All time',
            'farm_id' => null,
            'province_code' => null,
            'district_code' => null,
            'scope_label' => 'All locations',
        ];
    }
}
