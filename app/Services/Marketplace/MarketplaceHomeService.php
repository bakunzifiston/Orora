<?php

namespace App\Services\Marketplace;

use App\Models\Animal;
use App\Models\Central\LearningPost;
use App\Models\Central\MarketplaceCategory;
use App\Models\Central\MarketplaceListing;
use App\Models\Farm;
use App\Models\Tenant;
use Illuminate\Support\Collection;

class MarketplaceHomeService
{
    public function featuredListings(int $limit = 4): Collection
    {
        return $this->safeQuery(
            fn () => MarketplaceListing::query()
                ->active()
                ->with(['category', 'tenant'])
                ->orderByDesc('is_featured')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get(),
            collect(),
        );
    }

    public function categories(): Collection
    {
        return $this->safeQuery(
            fn () => MarketplaceCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            collect(),
        );
    }

    public function latestLearning(int $limit = 3): Collection
    {
        return $this->safeQuery(
            fn () => LearningPost::query()
                ->published()
                ->with('category')
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get(),
            collect(),
        );
    }

    /**
     * @return array<int, array{value: ?int, suffix: string, label: string, animate: bool, display: ?string}>
     */
    public function landingStats(): array
    {
        $stats = config('marketplace.landing_stats', []);

        try {
            $animals = Animal::query()->count();
            $farms = max(Farm::query()->count(), Tenant::query()->count());

            if ($animals > 0 || $farms > 0) {
                return [
                    [
                        'value' => max($farms, 50),
                        'suffix' => '+',
                        'label' => 'Farms Registered',
                        'animate' => true,
                        'display' => null,
                    ],
                    [
                        'value' => max($animals, 1200),
                        'suffix' => '+',
                        'label' => 'Animals Tracked',
                        'animate' => true,
                        'display' => null,
                    ],
                    [
                        'value' => null,
                        'suffix' => '',
                        'label' => 'Serving farmers',
                        'animate' => false,
                        'display' => 'Rwanda & Africa',
                    ],
                    [
                        'value' => null,
                        'suffix' => '',
                        'label' => 'By Farmers',
                        'animate' => false,
                        'display' => 'Trusted',
                    ],
                ];
            }
        } catch (\Throwable) {
            // Fall back to config defaults.
        }

        return $stats;
    }

    /**
     * @return array<int, array{value: ?int, suffix: string, label: string, animate: bool, display: ?string}>
     */
    public function aboutStats(): array
    {
        $farms = 50;
        $animals = 1200;

        try {
            $farms = max(Tenant::query()->count(), 50);
            $animals = max(Animal::query()->count(), 1200);
            MarketplaceListing::query()->active()->count();
        } catch (\Throwable) {
            // Fall back to defaults.
        }

        return [
            [
                'value' => $farms,
                'suffix' => '+',
                'label' => 'Farms Registered',
                'animate' => true,
                'display' => null,
            ],
            [
                'value' => $animals,
                'suffix' => '+',
                'label' => 'Animals Tracked',
                'animate' => true,
                'display' => null,
            ],
            [
                'value' => (int) config('marketplace.about.modules_count', 14),
                'suffix' => '',
                'label' => 'Modules Built',
                'animate' => false,
                'display' => null,
            ],
            [
                'value' => null,
                'suffix' => '',
                'label' => '& Africa',
                'animate' => false,
                'display' => 'Rwanda',
            ],
        ];
    }

    /**
     * @return array<int, array{icon: string, value: string, label: string}>
     */
    public function liveStats(): array
    {
        return collect($this->landingStats())->map(function (array $stat) {
            $value = $stat['display'] ?? (($stat['value'] ?? 0).($stat['suffix'] ?? ''));

            return [
                'icon' => '📊',
                'value' => (string) $value,
                'label' => $stat['label'],
            ];
        })->all();
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @param  T  $default
     * @return T
     */
    private function safeQuery(callable $callback, mixed $default): mixed
    {
        try {
            return $callback();
        } catch (\Throwable) {
            return $default;
        }
    }
}
