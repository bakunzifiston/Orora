<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FeedInventory extends TenantModel
{
    protected $fillable = [
        'farm_id',
        'feed_type_id',
        'quantity_on_hand',
        'storage_capacity_kg',
        'unit',
        'reorder_level',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'decimal:2',
            'reorder_level' => 'decimal:2',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function feedType(): BelongsTo
    {
        return $this->belongsTo(FeedType::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(FeedInventoryMovement::class)->orderByDesc('moved_at');
    }

    /** Most recent stock purchase that has a logged expense (used to show and pre-fill the vendor). */
    public function latestPurchase(): HasOne
    {
        return $this->hasOne(FeedInventoryMovement::class)
            ->ofMany(['moved_at' => 'max', 'id' => 'max'], function ($query) {
                $query->where('movement_type', 'purchase')->whereHas('expense', fn ($q) => $q->whereNotNull('expense_vendor_id'));
            });
    }

    public function isLowStock(): bool
    {
        return $this->reorder_level !== null && $this->quantity_on_hand <= $this->reorder_level;
    }
}
