<?php

namespace App\Services\Marketplace;

use App\Models\Animal;
use App\Models\Central\MarketplaceCategory;
use App\Models\Central\MarketplaceListing;
use App\Models\Central\MarketplaceListingStatusChange;
use App\Models\Farm;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MarketplaceListingService
{
    public function filter(Request $request, int $perPage = 12): LengthAwarePaginator
    {
        if (! MarketplaceDatabase::shopReady()) {
            return MarketplaceDatabase::emptyPaginator($perPage);
        }

        return MarketplaceDatabase::safe(function () use ($request, $perPage) {
            $query = MarketplaceListing::query()
                ->active()
                ->with('category');

            if ($search = $request->input('q')) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('breed', 'like', "%{$search}%");
                });
            }

            if ($categorySlug = $request->input('category')) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
            }

            if ($district = $request->input('district')) {
                $query->byDistrict($district);
            }

            if ($request->filled('price_min')) {
                $query->where('price', '>=', (float) $request->input('price_min'));
            }

            if ($request->filled('price_max')) {
                $query->where('price', '<=', (float) $request->input('price_max'));
            }

            $sellerTypes = array_filter((array) $request->input('seller_type', []));
            if ($sellerTypes) {
                $query->whereIn('seller_type', $sellerTypes);
            }

            if ($request->boolean('verified')) {
                $query->verified();
            }

            match ($request->input('sort', 'newest')) {
                'price_asc' => $query->orderBy('price'),
                'price_desc' => $query->orderByDesc('price'),
                'most_viewed' => $query->orderByDesc('views_count'),
                default => $query->orderByDesc('created_at'),
            };

            return $query->paginate($perPage)->withQueryString();
        }, MarketplaceDatabase::emptyPaginator($perPage));
    }

    public function related(MarketplaceListing $listing, int $limit = 4)
    {
        return MarketplaceDatabase::safe(
            fn () => MarketplaceListing::query()
                ->active()
                ->with('category')
                ->where('category_id', $listing->category_id)
                ->where('id', '!=', $listing->id)
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get(),
            collect(),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     */
    public function create(
        array $data,
        array $images,
        string $tenantId,
        string $initialStatus = 'pending_review',
    ): MarketplaceListing
    {
        $category = MarketplaceCategory::query()->findOrFail($data['category_id']);
        $listingType = config('marketplace.shop.category_type_map.'.$category->slug, $data['listing_type'] ?? 'animal');
        $animal = $this->resolveAnimal($data['animal_id'] ?? null, $tenantId, $data['farm_id'] ?? null);
        $farm = $animal?->farm ?? $this->resolveFarm($data['farm_id'] ?? null, $tenantId);

        $listing = MarketplaceListing::query()->create([
            ...$this->mappedAttributes($data, $listingType, $tenantId, $farm, $animal),
            'listing_code' => MarketplaceListing::generateCode(),
            'slug' => MarketplaceListing::uniqueSlug($data['title'], $tenantId),
            'images' => $this->storeImages($images)
                ?: ($animal?->photo_url ? [$animal->photo_url] : []),
            'status' => $initialStatus,
            'views_count' => 0,
        ]);

        MarketplaceListingStatusChange::query()->create([
            'listing_id' => $listing->id,
            'admin_user_id' => auth('admin')->id(),
            'user_id' => auth('admin')->check() ? null : auth()->id(),
            'to_status' => $initialStatus,
            'note' => auth('admin')->check() ? 'Created by platform administrator' : 'Farmer submission',
        ]);

        return $listing;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $newImages
     */
    public function update(
        MarketplaceListing $listing,
        array $data,
        array $newImages = [],
        ?array $keepImages = null,
        bool $platformAdmin = false,
    ): MarketplaceListing
    {
        $category = MarketplaceCategory::query()->findOrFail($data['category_id']);
        $listingType = config('marketplace.shop.category_type_map.'.$category->slug, $data['listing_type'] ?? $listing->listing_type);
        $animal = $this->resolveAnimal($data['animal_id'] ?? null, $listing->tenant_id, $data['farm_id'] ?? null, $platformAdmin);
        $farm = $animal?->farm ?? $this->resolveFarm($data['farm_id'] ?? null, $listing->tenant_id, $platformAdmin);
        $tenantId = $platformAdmin ? ($farm?->tenant_id ?? $listing->tenant_id) : $listing->tenant_id;

        $images = $keepImages ?? ($listing->images ?? []);
        if ($newImages) {
            $images = array_merge($images, $this->storeImages($newImages));
        }
        $images = array_slice(array_values(array_filter($images)), 0, 5);
        $previousStatus = $listing->status;

        $listing->update([
            ...$this->mappedAttributes($data, $listingType, $tenantId, $farm, $animal),
            'images' => $images,
            'slug' => $listing->title !== $data['title']
                ? MarketplaceListing::uniqueSlug($data['title'], $tenantId)
                : $listing->slug,
            ...(! $platformAdmin ? ['status' => 'pending_review', 'is_verified' => false] : []),
        ]);

        $updated = $listing->fresh(['category']);
        if (! $platformAdmin && $previousStatus !== 'pending_review') {
            MarketplaceListingStatusChange::query()->create([
                'listing_id' => $listing->id,
                'user_id' => auth()->id(),
                'from_status' => $previousStatus,
                'to_status' => 'pending_review',
                'note' => 'Resubmitted by farmer after editing',
            ]);
        }

        return $updated;
    }

    public function incrementViews(MarketplaceListing $listing): void
    {
        $listing->increment('views_count');
    }

    /**
     * @param  array<int, UploadedFile>  $images
     * @return array<int, string>
     */
    public function storeImages(array $images): array
    {
        $paths = [];

        foreach (array_slice($images, 0, 5) as $image) {
            if ($image instanceof UploadedFile && $image->isValid()) {
                $paths[] = '/storage/'.$image->store('marketplace/listings', 'public');
            }
        }

        return $paths;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mappedAttributes(
        array $data,
        string $listingType,
        ?string $tenantId,
        ?Farm $farm,
        ?Animal $animal,
    ): array
    {
        return [
            'tenant_id' => $tenantId,
            'farm_id' => $farm?->id,
            'animal_id' => $animal?->id,
            'animal_species' => $animal?->species ?? ($data['animal_species'] ?? null),
            'animal_sex' => $animal?->gender ?? ($data['animal_sex'] ?? null),
            'category_id' => $data['category_id'],
            'listing_type' => $listingType,
            'title' => $data['title'],
            'description' => $data['description'],
            'breed' => $animal?->breed ?? ($data['breed'] ?? null),
            'age' => $animal?->age_label ?? ($data['age'] ?? null),
            'weight_kg' => $animal?->weight_kg ?? ($data['weight_kg'] ?? null),
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'price' => $data['price'],
            'price_type' => $data['price_type'],
            'currency' => 'RWF',
            'seller_name' => $farm?->owner_full_name ?: $data['seller_name'],
            'seller_phone' => $farm?->contact_phone ?: $data['seller_phone'],
            'seller_email' => $farm?->contact_email ?: ($data['seller_email'] ?? null),
            'seller_type' => $data['seller_type'],
            'location_district' => $farm?->district ?: $data['location_district'],
            'location_sector' => $farm?->sector ?: ($data['location_sector'] ?? null),
        ];
    }

    private function resolveAnimal(mixed $animalId, ?string $tenantId, mixed $farmId, bool $platformAdmin = false): ?Animal
    {
        if (! $animalId) {
            return null;
        }

        $query = Animal::query()->with('farm');
        if ($platformAdmin) {
            $query->withoutGlobalScope('tenant');
        }
        $animal = $query->findOrFail($animalId);
        abort_unless(
            $animal->farm
                && ($platformAdmin || $animal->farm->tenant_id === $tenantId)
                && (! $farmId || (int) $animal->farm_id === (int) $farmId),
            403,
        );

        return $animal;
    }

    private function resolveFarm(mixed $farmId, ?string $tenantId, bool $platformAdmin = false): ?Farm
    {
        if (! $farmId) {
            return null;
        }

        $query = Farm::query();
        if ($platformAdmin) {
            $query->withoutGlobalScope('tenant');
        }
        $farm = $query->findOrFail($farmId);
        abort_unless($platformAdmin || $farm->tenant_id === $tenantId, 403);

        return $farm;
    }
}
