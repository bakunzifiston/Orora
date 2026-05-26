<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movement extends Model
{
    protected $fillable = [
        'animal_id',
        'from_farm_id',
        'to_farm_id',
        'movement_type',
        'moved_on',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'moved_on' => 'date',
        ];
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function fromFarm(): BelongsTo
    {
        return $this->belongsTo(Farm::class, 'from_farm_id');
    }

    public function toFarm(): BelongsTo
    {
        return $this->belongsTo(Farm::class, 'to_farm_id');
    }
}
