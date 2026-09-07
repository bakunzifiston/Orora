<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFlock;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthRecord extends TenantModel
{
    use BelongsToFlock;

    protected $fillable = [
        'farm_id',
        'animal_id',
        'flock_id',
        'record_type',
        'recorded_on',
        'health_status',
        'title',
        'treatment',
        'medication',
        'veterinarian',
        'next_follow_up',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'recorded_on' => 'date',
            'next_follow_up' => 'date',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }
}
