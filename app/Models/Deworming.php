<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFlock;
use App\Support\TenantStorageUrl;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Deworming extends TenantModel
{
    use BelongsToFlock;

    protected $fillable = [
        'farm_id',
        'livestock_id',
        'animal_id',
        'flock_id',
        'health_record_id',
        'product_name',
        'product_type',
        'active_ingredient',
        'manufacturer',
        'batch_number',
        'dosage',
        'administration_method',
        'deworming_date',
        'next_due_date',
        'status',
        'veterinarian_name',
        'administered_by',
        'notes',
        'attachment_path',
    ];

    protected function casts(): array
    {
        return [
            'deworming_date' => 'date',
            'next_due_date' => 'date',
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
