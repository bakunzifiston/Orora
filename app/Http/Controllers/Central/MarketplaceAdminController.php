<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\MarketplaceCategory;
use App\Models\Central\MarketplaceListing;
use App\Services\Marketplace\MarketplaceDatabase;
use Illuminate\View\View;

class MarketplaceAdminController extends Controller
{
    public function index(): View
    {
        if (! MarketplaceDatabase::shopReady()) {
            return view('central.marketplace.index', [
                'activeNav' => 'marketplace',
                'categories' => collect(),
                'listings' => MarketplaceDatabase::emptyPaginator(),
                'shopReady' => false,
                'stats' => $this->emptyStats(),
            ]);
        }

        $categories = MarketplaceCategory::query()->orderBy('sort_order')->get();
        $listings = MarketplaceListing::query()
            ->with(['category', 'tenant'])
            ->orderByDesc('created_at')
            ->paginate(15);

        $base = MarketplaceListing::query();

        return view('central.marketplace.index', [
            'activeNav' => 'marketplace',
            'categories' => $categories,
            'listings' => $listings,
            'shopReady' => true,
            'stats' => [
                'categories' => $categories->count(),
                'listings' => (clone $base)->count(),
                'active' => (clone $base)->whereIn('status', ['active', 'published'])->count(),
                'pending' => (clone $base)->where('status', 'pending')->count(),
            ],
        ]);
    }

    /**
     * @return array{categories: int, listings: int, active: int, pending: int}
     */
    private function emptyStats(): array
    {
        return [
            'categories' => 0,
            'listings' => 0,
            'active' => 0,
            'pending' => 0,
        ];
    }
}
