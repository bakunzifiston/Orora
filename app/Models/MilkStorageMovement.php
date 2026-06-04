<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MilkStorageMovement extends TenantModel
{
    protected $fillable = [
        'milk_storage_id',
        'movement_type',
        'quantity_liters',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'moved_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity_liters' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'moved_at' => 'datetime',
        ];
    }

    public function storage(): BelongsTo
    {
        return $this->belongsTo(MilkStorage::class, 'milk_storage_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
