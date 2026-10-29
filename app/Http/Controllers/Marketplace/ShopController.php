<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\MarketplaceInquiryRequest;
use App\Http\Requests\Marketplace\StoreMarketplaceListingRequest;
use App\Http\Requests\Marketplace\UpdateMarketplaceListingRequest;
use App\Models\Animal;
use App\Models\Central\MarketplaceCategory;
use App\Models\Central\MarketplaceInquiry;
use App\Models\Central\MarketplaceListing;
use App\Models\Farm;
use App\Models\User;
use App\Notifications\MarketplaceInquiryReceived;
use App\Services\Marketplace\MarketplaceDatabase;
use App\Services\Marketplace\MarketplaceListingService;
use App\Services\Marketplace\MarketplaceListingStatusService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
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

    public function mine(): View
    {
        $tenantId = TenantContext::id() ?? auth()->user()?->tenant_id;
        abort_unless($tenantId, 403);

        return view('marketplace.shop.mine', [
            'activePage' => 'shop',
            'listings' => MarketplaceListing::query()
                ->where('tenant_id', $tenantId)
                ->with('farm')
                ->withCount(['inquiries', 'inquiries as new_inquiries_count' => fn ($query) => $query->where('status', 'new')])
                ->orderByDesc('created_at')
                ->paginate(15),
            'inquiries' => MarketplaceInquiry::query()
                ->whereHas('listing', fn ($query) => $query->where('tenant_id', $tenantId))
                ->with('listing')
                ->orderByRaw("status = 'new' desc")
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(),
        ]);
    }

    /**
     * Let the seller track a buyer inquiry on one of their own listings.
     */
    public function updateInquiry(Request $request, MarketplaceInquiry $inquiry): RedirectResponse
    {
        $tenantId = TenantContext::id() ?? auth()->user()?->tenant_id;
        abort_unless($tenantId && $inquiry->listing?->tenant_id === $tenantId, 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'contacted', 'closed'])],
        ]);

        $inquiry->update($data);

        return redirect()
            ->to(route('marketplace.shop.mine').'#inquiries')
            ->with('success', __('Inquiry updated.'));
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
            ->route('marketplace.shop.mine')
            ->with('success', __('Your animal listing has been submitted for review.'));
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
            ->route('marketplace.shop.mine')
            ->with('success', __('Your listing has been resubmitted for review.'));
    }

    public function destroy(MarketplaceListing $listing, MarketplaceListingStatusService $statuses): RedirectResponse
    {
        $this->authorizeOwner($listing);

        $statuses->change($listing, 'inactive', __('Removed by seller.'));

        return redirect()
            ->route('marketplace.shop.mine')
            ->with('success', __('Listing removed.'));
    }

    public function inquiry(MarketplaceInquiryRequest $request, MarketplaceListing $listing): RedirectResponse
    {
        abort_unless($listing->status === 'active', 404);

        $inquiry = MarketplaceInquiry::query()->create([
            'listing_id' => $listing->id,
            ...$request->validated(),
            'status' => 'new',
        ]);

        try {
            $this->notifySeller($inquiry->setRelation('listing', $listing));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()
            ->route('marketplace.shop.show', $listing)
            ->with('success', __('Your inquiry has been sent to the seller.'));
    }

    /**
     * Notify the seller's workspace admins (or every active user when none is an admin).
     */
    private function notifySeller(MarketplaceInquiry $inquiry): void
    {
        $tenantId = $inquiry->listing?->tenant_id;

        if (! $tenantId) {
            return;
        }

        $users = User::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where(fn ($query) => $query->whereNull('is_active')->orWhere('is_active', true))
            ->get();

        $admins = $users->filter(fn (User $user) => $user->isWorkspaceAdmin());

        Notification::send($admins->isNotEmpty() ? $admins : $users, new MarketplaceInquiryReceived($inquiry));
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
        $tenantId = TenantContext::id() ?? $user?->tenant_id;

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
            'farms' => Farm::query()
                ->where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get(),
            'animals' => Animal::query()
                ->with('farm')
                ->whereHas('farm', fn ($query) => $query->where('tenant_id', $tenantId))
                ->orderBy('tag_number')
                ->limit(500)
                ->get(),
            'defaultSeller' => [
                'seller_name' => $user?->name,
                'seller_email' => $user?->email,
            ],
        ];
    }
}
