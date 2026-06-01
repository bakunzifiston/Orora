<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Storage;

class PregnancyCheck extends Model
{
    protected $fillable = [
        'breeding_record_id',
        'animal_id',
        'check_code',
        'check_date',
        'check_method',
        'result',
        'pregnancy_age_days',
        'expected_calving_date',
        'checked_by',
        'clinic_name',
        'next_check_date',
        'notes',
        'attachment_path',
    ];

    protected function casts(): array
    {
        return [
            'check_date' => 'date',
            'expected_calving_date' => 'date',
            'next_check_date' => 'date',
        ];
    }

    public function breedingRecord(): BelongsTo
    {
        return $this->belongsTo(BreedingRecord::class);
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function expense(): MorphOne
    {
        return $this->morphOne(Expense::class, 'source');
    }

    public function resultLabel(): string
    {
        return config('modules.pregnancy_check_result_labels')[$this->result] ?? ucfirst(str_replace('_', ' ', $this->result));
    }

    public function methodLabel(): string
    {
        return config('modules.pregnancy_check_method_labels')[$this->check_method] ?? ucfirst(str_replace('_', ' ', $this->check_method));
    }

    public function attachmentUrl(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        return Storage::disk('public')->url($this->attachment_path);
    }
}
