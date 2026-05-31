<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MilkSale extends Model
{
    protected $fillable = [
        'farm_id',
        'sale_code',
        'buyer_name',
        'buyer_contact',
        'sold_on',
        'total_amount',
        'currency',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sold_on' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MilkSaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(MilkSalePayment::class);
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function balanceDue(): float
    {
        return max(0, (float) $this->total_amount - $this->totalPaid());
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MilkSaleLog::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
