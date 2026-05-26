<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feeding extends Model
{
    protected $fillable = [
        'farm_id',
        'livestock_id',
        'animal_id',
        'feed_type_id',
        'feed_inventory_id',
        'feeding_schedule_id',
        'quantity',
        'unit',
        'fed_on',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'fed_on' => 'date',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function livestock(): BelongsTo
    {
        return $this->belongsTo(Livestock::class);
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function feedType(): BelongsTo
    {
        return $this->belongsTo(FeedType::class);
    }

    public function feedInventory(): BelongsTo
    {
        return $this->belongsTo(FeedInventory::class);
    }

    public function feedingSchedule(): BelongsTo
    {
        return $this->belongsTo(FeedingSchedule::class);
    }
}
