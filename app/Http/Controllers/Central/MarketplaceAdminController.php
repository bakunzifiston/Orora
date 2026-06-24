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
            ]);
        }

        $categories = MarketplaceCategory::query()->orderBy('sort_order')->get();
        $listings = MarketplaceListing::query()
            ->with(['category', 'tenant'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('central.marketplace.index', [
            'activeNav' => 'marketplace',
            'categories' => $categories,
            'listings' => $listings,
            'shopReady' => true,
        ]);
    }
}
