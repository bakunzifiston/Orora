<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offspring extends TenantModel
{
    protected $table = 'offspring';

    protected $fillable = [
        'birth_record_id',
        'mother_animal_id',
        'father_animal_id',
        'external_sire_name',
        'animal_id',
        'offspring_code',
        'gender',
        'birth_weight_kg',
        'color_markings',
        'health_status_at_birth',
        'is_registered',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'birth_weight_kg' => 'decimal:2',
            'is_registered' => 'boolean',
        ];
    }

    public function birthRecord(): BelongsTo
    {
        return $this->belongsTo(BirthRecord::class);
    }

    public function motherAnimal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'mother_animal_id');
    }

    public function fatherAnimal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'father_animal_id');
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function genderLabel(): string
    {
        return config('modules.animal_genders')[$this->gender] ?? ucfirst($this->gender);
    }
}
