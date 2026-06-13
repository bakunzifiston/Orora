<?php

namespace App\Services\Marketplace;

use App\Models\Central\MarketplaceCategory;
use App\Models\Central\MarketplaceListing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MarketplaceListingService
{
    public function filter(Request $request, int $perPage = 12): LengthAwarePaginator
    {
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
    }

    public function related(MarketplaceListing $listing, int $limit = 4)
    {
        return MarketplaceListing::query()
            ->active()
            ->with('category')
            ->where('category_id', $listing->category_id)
            ->where('id', '!=', $listing->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     */
    public function create(array $data, array $images, string $tenantId): MarketplaceListing
    {
        $category = MarketplaceCategory::query()->findOrFail($data['category_id']);
        $listingType = config('marketplace.shop.category_type_map.'.$category->slug, $data['listing_type'] ?? 'animal');

        return MarketplaceListing::query()->create([
            ...$this->mappedAttributes($data, $listingType, $tenantId),
            'listing_code' => MarketplaceListing::generateCode(),
            'slug' => MarketplaceListing::uniqueSlug($data['title'], $tenantId),
            'images' => $this->storeImages($images),
            'status' => 'active',
            'views_count' => 0,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $newImages
     */
    public function update(MarketplaceListing $listing, array $data, array $newImages = [], ?array $keepImages = null): MarketplaceListing
    {
        $category = MarketplaceCategory::query()->findOrFail($data['category_id']);
        $listingType = config('marketplace.shop.category_type_map.'.$category->slug, $data['listing_type'] ?? $listing->listing_type);

        $images = $keepImages ?? ($listing->images ?? []);
        if ($newImages) {
            $images = array_merge($images, $this->storeImages($newImages));
        }
        $images = array_slice(array_values(array_filter($images)), 0, 5);

        $listing->update([
            ...$this->mappedAttributes($data, $listingType, $listing->tenant_id),
            'images' => $images,
            'slug' => $listing->title !== $data['title']
                ? MarketplaceListing::uniqueSlug($data['title'], $listing->tenant_id)
                : $listing->slug,
        ]);

        return $listing->fresh(['category']);
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
    private function mappedAttributes(array $data, string $listingType, ?string $tenantId): array
    {
        return [
            'tenant_id' => $tenantId,
            'category_id' => $data['category_id'],
            'listing_type' => $listingType,
            'title' => $data['title'],
            'description' => $data['description'],
            'breed' => $data['breed'] ?? null,
            'age' => $data['age'] ?? null,
            'weight_kg' => $data['weight_kg'] ?? null,
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'price' => $data['price'],
            'price_type' => $data['price_type'],
            'currency' => 'RWF',
            'seller_name' => $data['seller_name'],
            'seller_phone' => $data['seller_phone'],
            'seller_email' => $data['seller_email'] ?? null,
            'seller_type' => $data['seller_type'],
            'location_district' => $data['location_district'],
            'location_sector' => $data['location_sector'] ?? null,
        ];
    }
}
