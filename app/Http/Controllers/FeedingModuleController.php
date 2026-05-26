<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FeedingSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Models\Feeding;
use App\Models\FeedInventory;
use App\Models\FeedSupplier;
use App\Models\FeedType;
use App\Models\FeedingSchedule;
use Illuminate\View\View;

class FeedingModuleController extends Controller
{
    use FeedingSectionViews;
    use ProvidesModuleNavigation;

    public function overview(): View
    {
        $stats = [
            'suppliers' => FeedSupplier::query()->where('is_active', true)->count(),
            'feed_types' => FeedType::query()->where('is_active', true)->count(),
            'inventory_items' => FeedInventory::query()->count(),
            'low_stock' => FeedInventory::query()->whereNotNull('reorder_level')
                ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                ->count(),
            'active_schedules' => FeedingSchedule::query()->where('status', 'active')->count(),
            'records_this_month' => Feeding::query()
                ->whereYear('fed_on', now()->year)
                ->whereMonth('fed_on', now()->month)
                ->count(),
        ];

        $recentFeedings = Feeding::query()
            ->with(['farm', 'feedType', 'animal', 'livestock'])
            ->orderByDesc('fed_on')
            ->limit(8)
            ->get();

        $lowStockItems = FeedInventory::query()
            ->with(['farm', 'feedType'])
            ->whereNotNull('reorder_level')
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->limit(6)
            ->get();

        return view('modules.feeding.overview', $this->feedingSectionData('overview', compact('stats', 'recentFeedings', 'lowStockItems')));
    }
}
