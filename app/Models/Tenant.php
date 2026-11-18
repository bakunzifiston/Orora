<?php

namespace App\Models;

use App\Support\AccountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Models\Domain;

class Tenant extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'account_type',
    ];

    public function isTesting(): bool
    {
        return ($this->attributes['account_type'] ?? AccountType::REAL) === AccountType::TESTING;
    }

    public function accountType(): string
    {
        return AccountType::normalize($this->attributes['account_type'] ?? null);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }
}
