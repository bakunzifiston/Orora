<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FlockEvent extends TenantModel
{
    protected $fillable = [
        'flock_id',
        'event_type',
        'quantity',
        'balance_after',
        'occurred_on',
        'reference_type',
        'reference_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'occurred_on' => 'date',
            'quantity' => 'integer',
            'balance_after' => 'integer',
        ];
    }

    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
