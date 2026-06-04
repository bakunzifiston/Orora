<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'customer_code',
        'customer_type',
        'display_name',
        'status',
        'trust_level',
        'preferred_payment_method',
        'currency',
        'notes',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function credit(): HasOne
    {
        return $this->hasOne(CustomerCredit::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CustomerDocument::class);
    }

    public function communications(): HasMany
    {
        return $this->hasMany(CustomerCommunication::class)->orderByDesc('communication_date');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CustomerLog::class)->orderByDesc('action_at');
    }

    public function saleTransactions(): HasMany
    {
        return $this->hasMany(SaleTransaction::class);
    }

    public function primaryContact(): ?CustomerContact
    {
        return $this->contacts->firstWhere('is_primary', true) ?? $this->contacts->first();
    }

    public function typeLabel(): string
    {
        return config('modules.customer_types.'.$this->customer_type, ucfirst(str_replace('_', ' ', $this->customer_type)));
    }

    public function isIndividual(): bool
    {
        return $this->customer_type === 'individual';
    }
}
