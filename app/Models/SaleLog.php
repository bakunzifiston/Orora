<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleLog extends TenantModel
{
    protected $fillable = [
        'sale_transaction_id',
        'action_type',
        'action_by',
        'action_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'action_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(SaleTransaction::class, 'sale_transaction_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
