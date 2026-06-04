<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbattoirReturn extends TenantModel
{
    protected $fillable = [
        'abattoir_dispatch_id',
        'animal_id',
        'return_date',
        'carcass_weight_kg',
        'dressing_percentage',
        'cut_type',
        'cut_weight_kg',
        'grade',
        'price_per_kg',
        'is_sold',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'carcass_weight_kg' => 'decimal:2',
            'dressing_percentage' => 'decimal:2',
            'cut_weight_kg' => 'decimal:2',
            'price_per_kg' => 'decimal:2',
            'is_sold' => 'boolean',
        ];
    }

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(AbattoirDispatch::class, 'abattoir_dispatch_id');
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
