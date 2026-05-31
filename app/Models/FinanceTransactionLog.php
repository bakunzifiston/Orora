<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceTransactionLog extends Model
{
    protected $fillable = [
        'finance_transaction_id',
        'action_type',
        'action_by',
        'action_at',
        'old_values',
        'new_values',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'action_at' => 'datetime',
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class, 'finance_transaction_id');
    }
}
