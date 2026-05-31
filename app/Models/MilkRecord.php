<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilkRecord extends Model
{
    protected $fillable = [
        'milk_session_id',
        'animal_id',
        'record_code',
        'yield_liters',
        'milking_duration_minutes',
        'lactation_stage',
        'lactation_number',
        'udder_condition',
        'abnormal_milk',
        'abnormal_notes',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'yield_liters' => 'decimal:2',
            'abnormal_milk' => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(MilkSession::class, 'milk_session_id');
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
