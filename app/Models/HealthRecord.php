<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthRecord extends Model
{
    protected $fillable = [
        'farm_id',
        'animal_id',
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
