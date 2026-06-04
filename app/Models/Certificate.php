<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends TenantModel
{
    protected $fillable = [
        'farm_id',
        'animal_id',
        'certificate_type',
        'certificate_number',
        'issuing_authority',
        'issued_on',
        'expires_on',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issued_on' => 'date',
            'expires_on' => 'date',
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
}
