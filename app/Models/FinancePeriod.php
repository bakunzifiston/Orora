<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancePeriod extends TenantModel
{
    protected $fillable = [
        'period_name',
        'period_type',
        'start_date',
        'end_date',
        'status',
        'closed_by',
        'closed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
