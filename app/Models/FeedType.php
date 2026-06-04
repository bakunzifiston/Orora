<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedType extends TenantModel
{
    protected $fillable = [
        'feed_supplier_id',
        'name',
        'unit',
        'category',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(FeedSupplier::class, 'feed_supplier_id');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(FeedInventory::class);
    }

    public function feedings(): HasMany
    {
        return $this->hasMany(Feeding::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(FeedingSchedule::class);
    }
}
