<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCredit extends TenantModel
{
    protected $table = 'customer_credit';

    protected $fillable = [
        'customer_id',
        'credit_limit',
        'outstanding_balance',
        'payment_terms',
        'last_reviewed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'outstanding_balance' => 'decimal:2',
            'last_reviewed_at' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function availableCredit(): float
    {
        return max(0, (float) $this->credit_limit - (float) $this->outstanding_balance);
    }

    public function isOverLimit(): bool
    {
        return (float) $this->credit_limit > 0
            && (float) $this->outstanding_balance > (float) $this->credit_limit;
    }
}
