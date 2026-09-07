<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flock extends TenantModel
{
    protected $fillable = [
        'farm_id',
        'livestock_id',
        'flock_code',
        'name',
        'production_type',
        'breed',
        'source',
        'placed_on',
        'placed_count',
        'current_count',
        'male_count',
        'female_count',
        'house_or_pen',
        'expected_end_on',
        'lifecycle_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'placed_on' => 'date',
            'expected_end_on' => 'date',
            'placed_count' => 'integer',
            'current_count' => 'integer',
            'male_count' => 'integer',
            'female_count' => 'integer',
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

    public function events(): HasMany
    {
        return $this->hasMany(FlockEvent::class)->orderByDesc('occurred_on')->orderByDesc('id');
    }

    public function eggCollections(): HasMany
    {
        return $this->hasMany(EggCollection::class);
    }

    public function label(): string
    {
        return collect([$this->flock_code, $this->name, $this->production_type])
            ->filter()
            ->implode(' · ');
    }

    public function mortalityPercent(): float
    {
        if ($this->placed_count <= 0) {
            return 0;
        }

        $lost = max(0, $this->placed_count - $this->current_count);

        return round(($lost / $this->placed_count) * 100, 1);
    }

    public function isActive(): bool
    {
        return strcasecmp((string) $this->lifecycle_status, 'Active') === 0;
    }
}
