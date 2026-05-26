<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Mortality extends Model
{
    protected $fillable = [
        'farm_id',
        'animal_id',
        'health_record_id',
        'death_date',
        'cause_of_death',
        'reported_by',
        'veterinarian_name',
        'disposal_method',
        'postmortem_done',
        'notes',
        'attachment_path',
    ];

    protected function casts(): array
    {
        return [
            'death_date' => 'date',
            'postmortem_done' => 'boolean',
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

    public function healthRecord(): BelongsTo
    {
        return $this->belongsTo(HealthRecord::class);
    }

    public function attachmentUrl(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        return Storage::disk('public')->url($this->attachment_path);
    }
}
