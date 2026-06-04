<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Livestock extends TenantModel
{
    protected $table = 'livestock';

    protected $fillable = [
        'farm_id',
        'name',
        'herd_groups',
        'herd_group_other',
        'livestock_types',
        'livestock_type_other',
        'production_purposes',
        'production_purpose_other',
        'farming_methods',
        'farming_method_other',
        'feeding_methods',
        'feeding_method_other',
        'breed',
        'head_count',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'herd_groups' => 'array',
            'livestock_types' => 'array',
            'production_purposes' => 'array',
            'farming_methods' => 'array',
            'feeding_methods' => 'array',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class);
    }

    protected function herdGroupsLabel(): Attribute
    {
        return Attribute::get(fn () => $this->formatSelection($this->herd_groups, $this->herd_group_other));
    }

    protected function livestockTypesLabel(): Attribute
    {
        return Attribute::get(fn () => $this->formatSelection($this->livestock_types, $this->livestock_type_other));
    }

    protected function productionPurposesLabel(): Attribute
    {
        return Attribute::get(fn () => $this->formatSelection($this->production_purposes, $this->production_purpose_other));
    }

    protected function farmingMethodsLabel(): Attribute
    {
        return Attribute::get(fn () => $this->formatSelection($this->farming_methods, $this->farming_method_other));
    }

    protected function feedingMethodsLabel(): Attribute
    {
        return Attribute::get(fn () => $this->formatSelection($this->feeding_methods, $this->feeding_method_other));
    }

    private function formatSelection(?array $values, ?string $other): string
    {
        $values = $values ?? [];

        $labels = array_map(function (string $value) use ($other) {
            if ($value === 'Other' && $other) {
                return 'Other: '.$other;
            }

            return $value;
        }, $values);

        return implode(', ', $labels) ?: '—';
    }
}
