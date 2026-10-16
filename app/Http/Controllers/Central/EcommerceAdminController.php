<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\MarketplaceListingAdminRequest;
use App\Models\Animal;
use App\Models\Central\MarketplaceCategory;
use App\Models\Central\MarketplaceInquiry;
use App\Models\Central\MarketplaceListing;
use App\Models\Farm;
use App\Services\Marketplace\MarketplaceDatabase;
use App\Services\Marketplace\MarketplaceListingService;
use App\Services\Marketplace\MarketplaceListingStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EcommerceAdminController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeSuperAdmin();
        abort_unless(MarketplaceDatabase::shopReady(), 503, __('Marketplace tables are not available.'));

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', Rule::in([...array_keys(config('marketplace.shop.listing_statuses', [])), 'expired'])],
            'species' => ['nullable', 'string', 'max:80'],
            'district' => ['nullable', 'string', 'max:120'],
        ]);

        $base = MarketplaceListing::query()->where('listing_type', 'animal');
        $listings = (clone $base)
            ->with(['farm', 'animal', 'tenant', 'category'])
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($subquery) use ($search): void {
                    $subquery->where('title', 'like', "%{$search}%")
                        ->orWhere('listing_code', 'like', "%{$search}%")
                        ->orWhere('breed', 'like', "%{$search}%")
                        ->orWhere('seller_name', 'like', "%{$search}%")
                        ->orWhere('seller_phone', 'like', "%{$search}%")
                        ->orWhere('location_district', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['species'] ?? null, function ($query, string $species): void {
                $query->where(function ($speciesQuery) use ($species): void {
                    $speciesQuery->where('animal_species', $species)
                        ->orWhereHas('animal', fn ($animalQuery) => $animalQuery->where('species', $species));
                });
            })
            ->when($filters['district'] ?? null, fn ($query, string $district) => $query->where('location_district', $district))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $inquiries = MarketplaceInquiry::query()
            ->whereHas('listing', fn ($query) => $query->where('listing_type', 'animal'))
            ->with('listing')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('central.ecommerce.index', [
            'activeNav' => 'ecommerce',
            'filters' => $filters,
            'listings' => $listings,
            'inquiries' => $inquiries,
            'statuses' => config('marketplace.shop.listing_statuses', []),
            'districts' => config('marketplace.shop.districts', []),
            'species' => collect([
                ...MarketplaceListing::query()
                    ->where('listing_type', 'animal')
                    ->whereNotNull('animal_species')
                    ->distinct()
                    ->orderBy('animal_species')
                    ->pluck('animal_species')
                    ->all(),
                ...Animal::query()
                    ->withoutGlobalScope('tenant')
                    ->whereNotNull('species')
                    ->distinct()
                    ->orderBy('species')
                    ->pluck('species')
                    ->all(),
            ])->filter()->unique()->sort()->values(),
            'stats' => [
                'total' => (clone $base)->count(),
                'pending' => (clone $base)->whereIn('status', ['pending', 'pending_review'])->count(),
                'published' => (clone $base)->whereIn('status', ['active', 'published'])->count(),
                'inactive' => (clone $base)->whereIn('status', ['sold', 'inactive', 'expired'])->count(),
                'inquiries' => MarketplaceInquiry::query()
                    ->whereHas('listing', fn ($query) => $query->where('listing_type', 'animal'))
                    ->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorizeSuperAdmin();

        return view('central.ecommerce.form', $this->formData());
    }

    public function store(
        MarketplaceListingAdminRequest $request,
        MarketplaceListingService $listings,
    ): RedirectResponse {
        $data = $request->validated();
        $farm = Farm::query()->withoutGlobalScope('tenant')->findOrFail($data['farm_id']);
        $listing = $listings->create(
            [...$data, 'listing_type' => 'animal'],
            $request->file('images', []),
            (string) $farm->tenant_id,
            'draft',
        );

        return redirect()
            ->route('central.ecommerce.listings.show', $listing)
            ->with('success', __('Animal listing created as a draft.'));
    }

    public function show(MarketplaceListing $listing): View
    {
        $this->authorizeSuperAdmin();
        abort_unless($listing->listing_type === 'animal', 404);

        $listing->load(['farm', 'animal', 'tenant', 'category', 'inquiries', 'statusChanges.admin', 'statusChanges.user']);

        return view('central.ecommerce.show', [
            'activeNav' => 'ecommerce',
            'listing' => $listing,
            'statuses' => config('marketplace.shop.listing_statuses', []),
        ]);
    }

    public function edit(MarketplaceListing $listing): View
    {
        $this->authorizeSuperAdmin();
        abort_unless($listing->listing_type === 'animal', 404);

        return view('central.ecommerce.form', $this->formData($listing->load(['farm', 'animal'])));
    }

    public function update(
        MarketplaceListingAdminRequest $request,
        MarketplaceListing $listing,
        MarketplaceListingService $listings,
    ): RedirectResponse {
        $this->authorizeSuperAdmin();
        abort_unless($listing->listing_type === 'animal', 404);

        $listings->update(
            $listing,
            [...$request->validated(), 'listing_type' => 'animal'],
            $request->file('images', []),
            $request->input('keep_images'),
            true,
        );

        return redirect()
            ->route('central.ecommerce.listings.show', $listing)
            ->with('success', __('Animal listing updated successfully.'));
    }

    public function updateStatus(Request $request, MarketplaceListing $listing, MarketplaceListingStatusService $statuses): RedirectResponse
    {
        $this->authorizeSuperAdmin();
        abort_unless($listing->listing_type === 'animal', 404);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(config('marketplace.shop.listing_statuses', [])))],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $statuses->change($listing, $data['status'], $data['note'] ?? null);

        return back()->with('success', __('Listing status updated.'));
    }

    public function updateInquiry(Request $request, MarketplaceInquiry $inquiry): RedirectResponse
    {
        $this->authorizeSuperAdmin();
        abort_unless($inquiry->listing()->where('listing_type', 'animal')->exists(), 404);

        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'contacted', 'closed'])],
        ]);

        $inquiry->update($data);

        return back()->with('success', __('Buyer inquiry updated.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?MarketplaceListing $listing = null): array
    {
        return [
            'activeNav' => 'ecommerce',
            'listing' => $listing,
            'farms' => Farm::query()->withoutGlobalScope('tenant')->orderBy('name')->get(),
            'animals' => Animal::query()
                ->withoutGlobalScope('tenant')
                ->with('farm')
                ->orderBy('tag_number')
                ->limit(500)
                ->get(),
            'categories' => MarketplaceCategory::query()
                ->active()
                ->orderBy('sort_order')
                ->get()
                ->filter(fn (MarketplaceCategory $category) => config('marketplace.shop.category_type_map.'.$category->slug) === 'animal')
                ->values(),
            'districts' => config('marketplace.shop.districts', []),
            'units' => config('marketplace.shop.units', []),
            'priceTypes' => config('marketplace.shop.price_types', []),
            'sellerTypes' => config('marketplace.shop.seller_types', []),
        ];
    }

    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth('admin')->user()?->isSuperAdmin(), 403);
    }
}
