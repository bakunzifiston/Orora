<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalePayment extends TenantModel
{
    protected $fillable = [
        'sale_transaction_id',
        'customer_id',
        'payment_reference',
        'payment_date',
        'payment_method',
        'amount_paid',
        'remaining_balance',
        'transaction_reference',
        'received_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount_paid' => 'decimal:2',
            'remaining_balance' => 'decimal:2',
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
}
