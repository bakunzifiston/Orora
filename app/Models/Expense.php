<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Support\TenantStorageUrl;

class Expense extends TenantModel
{
    protected $fillable = [
        'farm_id',
        'animal_id',
        'livestock_id',
        'expense_category_id',
        'expense_vendor_id',
        'expense_date',
        'amount',
        'currency',
        'payment_method',
        'paid_by',
        'source_type',
        'source_id',
        'title',
        'notes',
        'attachment_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function livestock(): BelongsTo
    {
        return $this->belongsTo(Livestock::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(ExpenseVendor::class, 'expense_vendor_id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function attachmentUrl(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        return TenantStorageUrl::forPublicDisk($this->attachment_path);
    }

    public function groupLabel(): string
    {
        return $this->category?->groupLabel() ?? '—';
    }
}
