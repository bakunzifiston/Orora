<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbattoirDispatchAnimal extends Model
{
    protected $fillable = [
        'abattoir_dispatch_id',
        'animal_id',
        'live_weight_kg',
        'animal_condition',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'live_weight_kg' => 'decimal:2',
        ];
    }

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(AbattoirDispatch::class, 'abattoir_dispatch_id');
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }
}
