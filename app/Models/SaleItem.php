<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends TenantModel
{
    protected $fillable = [
        'sale_transaction_id',
        'customer_id',
        'item_type',
        'animal_id',
        'livestock_id',
        'abattoir_return_id',
        'milk_storage_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'live_weight_kg',
        'carcass_weight_kg',
        'price_per_kg',
        'total_price',
        'animal_condition',
        'certificate_verified',
        'permit_verified',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'live_weight_kg' => 'decimal:2',
            'carcass_weight_kg' => 'decimal:2',
            'price_per_kg' => 'decimal:2',
            'total_price' => 'decimal:2',
            'certificate_verified' => 'boolean',
            'permit_verified' => 'boolean',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(SaleTransaction::class, 'sale_transaction_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function livestock(): BelongsTo
    {
        return $this->belongsTo(Livestock::class);
    }

    public function abattoirReturn(): BelongsTo
    {
        return $this->belongsTo(AbattoirReturn::class);
    }

    public function milkStorage(): BelongsTo
    {
        return $this->belongsTo(MilkStorage::class, 'milk_storage_id');
    }
}
