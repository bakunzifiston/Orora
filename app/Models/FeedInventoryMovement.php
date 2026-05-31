<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeedInventoryMovement extends Model
{
    protected $fillable = [
        'feed_inventory_id',
        'movement_type',
        'quantity',
        'unit',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'moved_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'moved_at' => 'datetime',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(FeedInventory::class, 'feed_inventory_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function expense(): MorphOne
    {
        return $this->morphOne(Expense::class, 'source');
    }
}
