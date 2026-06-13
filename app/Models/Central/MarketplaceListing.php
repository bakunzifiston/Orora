<?php

namespace App\Models\Central;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MarketplaceListing extends CentralModel
{
    protected $fillable = [
        'tenant_id',
        'category_id',
        'listing_code',
        'listing_type',
        'title',
        'slug',
        'description',
        'breed',
        'age',
        'weight_kg',
        'quantity',
        'unit',
        'price',
        'price_type',
        'currency',
        'images',
        'seller_name',
        'seller_phone',
        'seller_email',
        'seller_type',
        'location_district',
        'location_sector',
        'is_featured',
        'is_verified',
        'status',
        'expires_at',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'weight_kg' => 'decimal:2',
            'quantity' => 'decimal:3',
            'images' => 'array',
            'is_featured' => 'boolean',
            'is_verified' => 'boolean',
            'expires_at' => 'datetime',
            'views_count' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MarketplaceCategory::class, 'category_id');
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(MarketplaceInquiry::class, 'listing_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('listing_type', $type);
    }

    public function scopeByDistrict(Builder $query, string $district): Builder
    {
        return $query->where('location_district', $district);
    }

    /** @deprecated Use scopeActive() */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function mainImage(): ?string
    {
        $images = $this->images ?? [];

        return $images[0] ?? null;
    }

    public function formattedPrice(): string
    {
        $amount = number_format((float) $this->price, 0).' '.$this->currency;

        return match ($this->price_type) {
            'negotiable' => $amount.' — Negotiable',
            'per_kg' => $amount.' / kg',
            'per_liter' => $amount.' / litre',
            default => $amount,
        };
    }

    public function priceTypeLabel(): string
    {
        return config('marketplace.shop.price_types.'.$this->price_type, ucfirst($this->price_type));
    }

    public function sellerTypeLabel(): string
    {
        return config('marketplace.shop.seller_types.'.$this->seller_type, ucfirst($this->seller_type));
    }

    public function locationLabel(): string
    {
        return collect([$this->location_district, $this->location_sector, 'Rwanda'])
            ->filter()
            ->implode(', ');
    }

    public function quantityLabel(): ?string
    {
        if ($this->quantity === null) {
            return null;
        }

        $unit = config('marketplace.shop.units.'.$this->unit, $this->unit);

        return rtrim(rtrim(number_format((float) $this->quantity, 3), '0'), '.').' '.$unit;
    }

    public static function generateCode(): string
    {
        $dateKey = now()->format('Ymd');
        $prefix = "LST-{$dateKey}-";
        $lastCode = static::query()
            ->where('listing_code', 'like', $prefix.'%')
            ->orderByDesc('listing_code')
            ->value('listing_code');
        $seq = $lastCode ? ((int) substr($lastCode, -4)) + 1 : 1;

        return sprintf('%s%04d', $prefix, $seq);
    }

    public static function uniqueSlug(string $title, ?string $tenantId = null): string
    {
        $base = Str::slug($title) ?: 'listing';
        $slug = $base;
        $i = 1;

        while (static::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    public function isOwnedByTenant(?string $tenantId): bool
    {
        return $tenantId !== null && $this->tenant_id === $tenantId;
    }
}
