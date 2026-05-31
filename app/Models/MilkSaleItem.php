<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilkSaleItem extends Model
{
    protected $fillable = [
        'milk_sale_id',
        'milk_storage_id',
        'quantity_liters',
        'unit_price',
        'line_total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_liters' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(MilkSale::class, 'milk_sale_id');
    }

    public function storage(): BelongsTo
    {
        return $this->belongsTo(MilkStorage::class, 'milk_storage_id');
    }
}
