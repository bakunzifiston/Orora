<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilkSalePayment extends TenantModel
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
