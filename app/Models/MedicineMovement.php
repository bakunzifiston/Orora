<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MedicineMovement extends TenantModel
{
    protected $fillable = [
        'medicine_id',
        'movement_type',
        'quantity',
        'unit',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'moved_at',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'moved_at' => 'datetime',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
