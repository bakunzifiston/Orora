<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceAccount extends Model
{
    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'account_subtype',
        'parent_account_id',
        'source_module',
        'expense_category_code',
        'normal_balance',
        'is_system',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_account_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_account_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(FinanceTransactionLine::class);
    }

    public static function byCode(string $code): ?self
    {
        return static::query()->where('account_code', $code)->first();
    }
}
