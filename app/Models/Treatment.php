<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFlock;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Support\TenantStorageUrl;

class Treatment extends TenantModel
{
    use BelongsToFlock;

    protected $fillable = [
        'farm_id',
        'livestock_id',
        'animal_id',
        'flock_id',
        'health_record_id',
        'disease_name',
        'medicine_name',
        'medicine_id',
        'dosage',
        'quantity_used',
        'treatment_method',
        'start_date',
        'end_date',
        'follow_up_date',
        'status',
        'veterinarian_name',
        'symptoms',
        'diagnosis',
        'notes',
        'attachment_path',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'follow_up_date' => 'date',
            'quantity_used' => 'decimal:2',
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

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function stockLabel(): string
    {
        if ($this->flock) {
            return $this->flock->label();
        }

        if ($this->animal) {
            return collect([$this->animal->tag_number, $this->animal->name])->filter()->implode(' — ');
        }

        if ($this->livestock) {
            return $this->livestock->name;
        }

        return '—';
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
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
