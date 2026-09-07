<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EggCollection extends TenantModel
{
    protected $fillable = [
        'farm_id',
        'flock_id',
        'collection_code',
        'collected_on',
        'shift',
        'eggs_count',
        'cracked_count',
        'weight_kg',
        'collected_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'collected_on' => 'date',
            'eggs_count' => 'integer',
            'cracked_count' => 'integer',
            'weight_kg' => 'decimal:2',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }

    public function saleableCount(): int
    {
        return max(0, $this->eggs_count - $this->cracked_count);
    }

    public function layRate(?int $birdCount = null): ?float
    {
        $birds = $birdCount ?? $this->flock?->current_count;

        if (! $birds) {
            return null;
        }

        return round(($this->eggs_count / $birds) * 100, 1);
    }
}
