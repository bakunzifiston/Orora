<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilkSalePayment extends Model
{
    protected $fillable = [
        'milk_sale_id',
        'amount',
        'payment_method',
        'paid_on',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_on' => 'date',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(MilkSale::class, 'milk_sale_id');
    }
}
