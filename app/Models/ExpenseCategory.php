<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends TenantModel
{
    protected $fillable = [
        'expense_group',
        'name',
        'code',
        'description',
        'is_system',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function groupLabel(): string
    {
        return config('modules.expense_groups.'.$this->expense_group.'.label', $this->expense_group);
    }
}
