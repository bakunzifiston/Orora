<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SaleTransaction extends TenantModel
{
    protected $fillable = [
        'farm_id',
        'sale_number',
        'sale_type',
        'sale_date',
        'customer_id',
        'pricing_method',
        'subtotal_amount',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'currency',
        'payment_status',
        'sale_status',
        'delivery_method',
        'movement_permit_id',
        'abattoir_dispatch_id',
        'notes',
        'approved_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function abattoirDispatch(): BelongsTo
    {
        return $this->belongsTo(AbattoirDispatch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SaleDocument::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SaleLog::class)->orderByDesc('action_at');
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount_paid');
    }

    public function balanceDue(): float
    {
        return max(0, (float) $this->total_amount - $this->totalPaid());
    }

    public function typeLabel(): string
    {
        return config('modules.sale_type_labels.'.$this->sale_type, $this->sale_type);
    }

    public function statusLabel(): string
    {
        return config('modules.sale_status_labels.'.$this->sale_status, ucfirst(str_replace('_', ' ', $this->sale_status)));
    }

    public function statusBadgeClass(): string
    {
        return config('modules.sale_status_badge_classes.'.$this->sale_status, 'dash-sale-status--default');
    }

    public function paymentStatusLabel(): string
    {
        return config('modules.sale_payment_status_labels.'.$this->payment_status, ucfirst(str_replace('_', ' ', (string) $this->payment_status)));
    }

    public function paymentStatusBadgeClass(): string
    {
        return config('modules.sale_payment_status_badge_classes.'.$this->payment_status, 'dash-sale-payment--default');
    }

    public function scopeAnimalSales(Builder $query): Builder
    {
        return $query->where('sale_type', 'animal_sale');
    }

    public function scopeMeatSales(Builder $query): Builder
    {
        return $query->where('sale_type', 'meat_sale');
    }

    public function scopeMilkSales(Builder $query): Builder
    {
        return $query->where('sale_type', 'milk_sale');
    }
}
