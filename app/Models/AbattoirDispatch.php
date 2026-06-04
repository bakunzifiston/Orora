<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AbattoirDispatch extends TenantModel
{
    protected $fillable = [
        'farm_id',
        'dispatch_code',
        'dispatch_date',
        'abattoir_name',
        'abattoir_location',
        'contact_person',
        'transport_method',
        'vehicle_plate',
        'driver_name',
        'movement_permit_id',
        'total_animals_dispatched',
        'expected_return_date',
        'dispatch_status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'dispatch_date' => 'date',
            'expected_return_date' => 'date',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function animals(): HasMany
    {
        return $this->hasMany(AbattoirDispatchAnimal::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(AbattoirReturn::class);
    }

    public function saleTransaction(): HasOne
    {
        return $this->hasOne(SaleTransaction::class);
    }
}
