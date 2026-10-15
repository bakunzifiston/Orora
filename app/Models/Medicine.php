<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends TenantModel
{
    protected $fillable = [
        'name',
        'category',
        'active_ingredient',
        'dosage_instructions',
        'administration_method',
        'unit',
        'quantity_on_hand',
        'reorder_level',
        'expiry_date',
        'supplier',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'decimal:2',
            'reorder_level' => 'decimal:2',
            'expiry_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(MedicineMovement::class)->orderByDesc('moved_at')->orderByDesc('id');
    }

    public function isExpired(\DateTimeInterface|string|null $on = null): bool
    {
        if (! $this->expiry_date) {
            return false;
        }

        $on = $on ? \Illuminate\Support\Carbon::parse($on)->startOfDay() : now()->startOfDay();

        return $this->expiry_date->lt($on);
    }

    public function isLowStock(): bool
    {
        return $this->reorder_level !== null
            && (float) $this->quantity_on_hand <= (float) $this->reorder_level;
    }

    public function isSelectableForTreatment(?\DateTimeInterface $on = null, ?int $includeId = null): bool
    {
        if ($includeId && (int) $this->id === (int) $includeId) {
            return true;
        }

        return $this->is_active
            && ! $this->isExpired($on)
            && (float) $this->quantity_on_hand > 0;
    }

    public function scopeSelectableForTreatment(Builder $query, ?\DateTimeInterface $on = null, ?int $includeId = null): Builder
    {
        $onDate = $on ? \Illuminate\Support\Carbon::parse($on)->toDateString() : now()->toDateString();

        return $query
            ->where(function (Builder $inner) use ($onDate, $includeId) {
                $inner->where('is_active', true)
                    ->where('quantity_on_hand', '>', 0)
                    ->where(function (Builder $expiry) use ($onDate) {
                        $expiry->whereNull('expiry_date')
                            ->orWhereDate('expiry_date', '>=', $onDate);
                    });

                if ($includeId) {
                    $inner->orWhereKey($includeId);
                }
            })
            ->orderBy('name');
    }

    public function stockLabel(): string
    {
        return trim(rtrim(rtrim(number_format((float) $this->quantity_on_hand, 2, '.', ''), '0'), '.').' '.($this->unit ?? ''));
    }
}
