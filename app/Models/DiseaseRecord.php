<?php

namespace App\Models;

use App\Support\TenantStorageUrl;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class DiseaseRecord extends TenantModel
{
    protected $fillable = [
        'disease_code',
        'farm_id',
        'livestock_id',
        'animal_id',
        'health_record_id',
        'disease_name',
        'diagnosis_date',
        'severity_level',
        'recovery_status',
        'contagious_status',
        'quarantine_required',
        'symptoms',
        'veterinarian_name',
        'notes',
        'attachment_path',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'diagnosis_date' => 'date',
            'quarantine_required' => 'boolean',
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

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function healthRecord(): BelongsTo
    {
        return $this->belongsTo(HealthRecord::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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

    public function severityLabel(): string
    {
        return config('modules.disease_severity_labels')[$this->severity_level] ?? ucfirst($this->severity_level);
    }

    public function recoveryLabel(): string
    {
        return config('modules.disease_recovery_labels')[$this->recovery_status] ?? ucfirst($this->recovery_status);
    }

    public function contagiousLabel(): string
    {
        return config('modules.disease_contagious_labels')[$this->contagious_status] ?? ucfirst(str_replace('_', ' ', $this->contagious_status));
    }
}
