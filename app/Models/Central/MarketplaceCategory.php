<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketplaceCategory extends CentralModel
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function listings(): HasMany
    {
        return $this->hasMany(MarketplaceListing::class, 'category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
