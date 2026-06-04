<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Support\TenantStorageUrl;

class BirthRecord extends TenantModel
{
    protected $fillable = [
        'breeding_record_id',
        'mother_animal_id',
        'birth_code',
        'birth_date',
        'birth_type',
        'total_offspring',
        'alive_offspring',
        'stillborn_offspring',
        'birth_difficulty',
        'birth_weight_kg',
        'assisted_by',
        'veterinarian_name',
        'mother_condition_after',
        'notes',
        'attachment_path',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'birth_weight_kg' => 'decimal:2',
        ];
    }

    public function breedingRecord(): BelongsTo
    {
        return $this->belongsTo(BreedingRecord::class);
    }

    public function motherAnimal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'mother_animal_id');
    }

    public function offspring(): HasMany
    {
        return $this->hasMany(Offspring::class);
    }

    public function expense(): MorphOne
    {
        return $this->morphOne(Expense::class, 'source');
    }

    public function attachmentUrl(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        return TenantStorageUrl::forPublicDisk($this->attachment_path);
    }
}
