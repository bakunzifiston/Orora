<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceTransactionLine extends Model
{
    protected $fillable = [
        'finance_transaction_id',
        'finance_account_id',
        'entry_type',
        'amount',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class, 'finance_transaction_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }
}
