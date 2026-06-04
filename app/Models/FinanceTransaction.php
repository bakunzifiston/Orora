<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceTransaction extends TenantModel
{
    protected $fillable = [
        'farm_id',
        'livestock_id',
        'transaction_code',
        'transaction_date',
        'finance_period_id',
        'transaction_type',
        'posting_kind',
        'source_module',
        'source_type',
        'source_id',
        'description',
        'gross_amount',
        'tax_amount',
        'net_amount',
        'currency',
        'payment_method',
        'reference_number',
        'is_manual',
        'is_reconciled',
        'is_reversal',
        'reversed_transaction_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'gross_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'is_manual' => 'boolean',
            'is_reconciled' => 'boolean',
            'is_reversal' => 'boolean',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function livestock(): BelongsTo
    {
        return $this->belongsTo(Livestock::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(FinancePeriod::class, 'finance_period_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(FinanceTransactionLine::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(FinanceTransactionLog::class);
    }

    public function reversedTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversed_transaction_id');
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('transaction_type', 'income');
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('transaction_type', 'expense');
    }

    public function scopeFarm(Builder $query, ?int $farmId): Builder
    {
        return $farmId ? $query->where('farm_id', $farmId) : $query;
    }

    public function scopeLivestock(Builder $query, ?int $livestockId): Builder
    {
        return $livestockId ? $query->where('livestock_id', $livestockId) : $query;
    }

    public function scopeInDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('transaction_date', [$from, $to]);
    }
}
