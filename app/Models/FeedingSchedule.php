<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedingSchedule extends Model
{
    protected $fillable = [
        'farm_id',
        'feed_type_id',
        'feed_inventory_id',
        'livestock_id',
        'animal_id',
        'quantity',
        'unit',
        'frequency',
        'days_of_week',
        'scheduled_time',
        'start_date',
        'end_date',
        'next_due_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'days_of_week' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'next_due_date' => 'date',
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

    public function feedInventory(): BelongsTo
    {
        return $this->belongsTo(FeedInventory::class);
    }

    public function livestock(): BelongsTo
    {
        return $this->belongsTo(Livestock::class);
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function feedings(): HasMany
    {
        return $this->hasMany(Feeding::class);
    }
}
