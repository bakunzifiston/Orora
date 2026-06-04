<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class BreedingRecord extends TenantModel
{
    protected $fillable = [
        'farm_id',
        'female_animal_id',
        'male_animal_id',
        'external_sire_name',
        'external_sire_breed',
        'external_sire_code',
        'breeding_code',
        'breeding_date',
        'pregnancy_check_due_on',
        'breeding_type',
        'animal_type',
        'heat_detection_method',
        'heat_detected_date',
        'technician_name',
        'semen_batch_number',
        'semen_straw_code',
        'semen_source',
        'expected_calving_date',
        'gestation_period_days',
        'breeding_status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'breeding_date' => 'date',
            'pregnancy_check_due_on' => 'date',
            'heat_detected_date' => 'date',
            'expected_calving_date' => 'date',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function femaleAnimal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'female_animal_id');
    }

    public function maleAnimal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'male_animal_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pregnancyChecks(): HasMany
    {
        return $this->hasMany(PregnancyCheck::class);
    }

    public function birthRecord(): HasOne
    {
        return $this->hasOne(BirthRecord::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(BreedingLog::class)->orderByDesc('action_date');
    }

    public function expense(): MorphOne
    {
        return $this->morphOne(Expense::class, 'source');
    }

    public function statusLabel(): string
    {
        return config('modules.breeding_status_labels')[$this->breeding_status] ?? ucfirst(str_replace('_', ' ', $this->breeding_status));
    }

    public function breedingTypeLabel(): string
    {
        return config('modules.breeding_type_labels')[$this->breeding_type] ?? ucfirst(str_replace('_', ' ', $this->breeding_type));
    }

    public function sireLabel(): string
    {
        if ($this->maleAnimal) {
            return $this->maleAnimal->tag_number.($this->maleAnimal->name ? " ({$this->maleAnimal->name})" : '');
        }

        if ($this->external_sire_name) {
            return $this->external_sire_name.($this->external_sire_breed ? " — {$this->external_sire_breed}" : '');
        }

        return 'Unknown';
    }
}
