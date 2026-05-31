<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MilkSession extends Model
{
    protected $fillable = [
        'farm_id',
        'livestock_id',
        'session_code',
        'session_date',
        'session_shift',
        'total_yield_liters',
        'number_of_animals_milked',
        'average_yield_per_animal',
        'milked_by',
        'milking_method',
        'status',
        'notes',
        'destination_storage_id',
        'created_by',
        'completed_at',
        'completed_by',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'total_yield_liters' => 'decimal:2',
            'average_yield_per_animal' => 'decimal:2',
            'completed_at' => 'datetime',
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

    public function destinationStorage(): BelongsTo
    {
        return $this->belongsTo(MilkStorage::class, 'destination_storage_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function records(): HasMany
    {
        return $this->hasMany(MilkRecord::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function shiftLabel(): string
    {
        return config('modules.milk_session_shift_labels')[$this->session_shift] ?? ucfirst($this->session_shift);
    }
}
