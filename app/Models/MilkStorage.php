<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MilkStorage extends Model
{
    protected $table = 'milk_storage';

    protected $fillable = [
        'farm_id',
        'storage_code',
        'container_name',
        'container_type',
        'capacity_liters',
        'current_quantity_liters',
        'storage_temperature',
        'storage_location',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'capacity_liters' => 'decimal:2',
            'current_quantity_liters' => 'decimal:2',
            'storage_temperature' => 'decimal:2',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(MilkStorageMovement::class)->orderByDesc('moved_at');
    }

    public function isLowCapacity(): bool
    {
        return (float) $this->current_quantity_liters >= (float) $this->capacity_liters;
    }
}
