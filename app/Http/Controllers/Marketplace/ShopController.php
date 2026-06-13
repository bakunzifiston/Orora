<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\MarketplaceInquiryRequest;
use App\Http\Requests\Marketplace\StoreMarketplaceListingRequest;
use App\Http\Requests\Marketplace\UpdateMarketplaceListingRequest;
use App\Models\Central\MarketplaceCategory;
use App\Models\Central\MarketplaceInquiry;
use App\Models\Central\MarketplaceListing;
use App\Services\Marketplace\MarketplaceDatabase;
use App\Services\Marketplace\MarketplaceListingService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function __construct(private readonly MarketplaceListingService $listings) {}

    public function index(Request $request): View
    {
        $listings = $this->listings->filter($request);

        return view('marketplace.shop.index', [
            'activePage' => 'shop',
            'listings' => $listings,
            'categories' => MarketplaceDatabase::safe(
                fn () => MarketplaceCategory::query()->active()->orderBy('sort_order')->get(),
                collect(),
            ),
            'filters' => $request->only(['q', 'category', 'district', 'price_min', 'price_max', 'seller_type', 'verified', 'sort']),
            'districts' => config('marketplace.shop.districts', []),
            'sortOptions' => config('marketplace.shop.sort_options', []),
            'sellerTypes' => config('marketplace.shop.seller_types', []),
            'totalCount' => MarketplaceDatabase::safe(
                fn () => MarketplaceListing::query()->active()->count(),
                0,
            ),
            'shopReady' => MarketplaceDatabase::shopReady(),
        ]);
    }

    public function show(MarketplaceListing $listing): View
    {
        abort_unless($listing->status === 'active', 404);

        $this->listings->incrementViews($listing);
        $listing->load('category');

        return view('marketplace.shop.show', [
            'activePage' => 'shop',
            'listing' => $listing->fresh(),
            'relatedListings' => $this->listings->related($listing),
            'canEdit' => auth()->check() && $listing->isOwnedByTenant(TenantContext::id()),
        ]);
    }

    public function create(): View
    {
        return view('marketplace.shop.create', $this->formData());
    }

    public function store(StoreMarketplaceListingRequest $request): RedirectResponse
    {
        $tenantId = TenantContext::id() ?? auth()->user()?->tenant_id;
        abort_unless($tenantId, 403);

        $listing = $this->listings->create(
            $request->validated(),
            $request->file('images', []),
            $tenantId
        );

        return redirect()
            ->route('marketplace.shop.show', $listing)
            ->with('success', 'Your listing has been posted successfully.');
    }

    public function edit(MarketplaceListing $listing): View
    {
        $this->authorizeOwner($listing);

        return view('marketplace.shop.edit', array_merge($this->formData(), [
            'listing' => $listing->load('category'),
        ]));
    }

    public function update(UpdateMarketplaceListingRequest $request, MarketplaceListing $listing): RedirectResponse
    {
        $this->authorizeOwner($listing);

        $this->listings->update(
            $listing,
            $request->validated(),
            $request->file('images', []),
            $request->input('keep_images')
        );

        return redirect()
            ->route('marketplace.shop.show', $listing)
            ->with('success', 'Listing updated successfully.');
    }

    public function destroy(MarketplaceListing $listing): RedirectResponse
    {
        $this->authorizeOwner($listing);

        $listing->update(['status' => 'expired']);

        return redirect()
            ->route('marketplace.shop')
            ->with('success', 'Listing removed.');
    }

    public function inquiry(MarketplaceInquiryRequest $request, MarketplaceListing $listing): RedirectResponse
    {
        abort_unless($listing->status === 'active', 404);

        MarketplaceInquiry::query()->create([
            'listing_id' => $listing->id,
            ...$request->validated(),
            'status' => 'new',
        ]);

        return redirect()
            ->route('marketplace.shop.show', $listing)
            ->with('success', 'Your inquiry has been sent to the seller.');
    }

    private function authorizeOwner(MarketplaceListing $listing): void
    {
        abort_unless(auth()->check(), 403);
        abort_unless($listing->isOwnedByTenant(TenantContext::id()), 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        $user = auth()->user();

        return [
            'activePage' => 'shop',
            'categories' => MarketplaceDatabase::safe(
                fn () => MarketplaceCategory::query()->active()->orderBy('sort_order')->get(),
                collect(),
            ),
            'shopReady' => MarketplaceDatabase::shopReady(),
            'listingTypes' => config('marketplace.shop.listing_types', []),
            'units' => config('marketplace.shop.units', []),
            'priceTypes' => config('marketplace.shop.price_types', []),
            'sellerTypes' => config('marketplace.shop.seller_types', []),
            'districts' => config('marketplace.shop.districts', []),
            'defaultSeller' => [
                'seller_name' => $user?->name,
                'seller_email' => $user?->email,
            ],
        ];
    }
}
