<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerLog extends TenantModel
{
    protected $fillable = [
        'customer_id',
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
