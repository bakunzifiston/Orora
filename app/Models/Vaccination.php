<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Support\TenantStorageUrl;

class Vaccination extends TenantModel
{
    protected $fillable = [
        'farm_id',
        'animal_id',
        'health_record_id',
        'vaccine_name',
        'vaccine_type',
        'manufacturer',
        'batch_number',
        'dosage',
        'administration_method',
        'vaccination_date',
        'next_due_date',
        'status',
        'veterinarian_name',
        'veterinary_clinic',
        'administered_by',
        'side_effects',
        'reaction_notes',
        'notes',
        'attachment_path',
    ];

    protected function casts(): array
    {
        return [
            'vaccination_date' => 'date',
            'next_due_date' => 'date',
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
