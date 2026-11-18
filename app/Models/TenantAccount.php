<?php

namespace App\Models;

use App\Support\AccountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantAccount extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'tenant_id',
        'email',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('account_type', function ($builder): void {
            if (AccountType::view() !== null) {
                AccountType::constrainToView($builder, 'tenant_accounts.tenant_id');
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
