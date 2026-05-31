<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'address_type',
        'address_label',
        'country',
        'province',
        'district',
        'sector',
        'cell',
        'village',
        'street_address',
        'gps_latitude',
        'gps_longitude',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'gps_latitude' => 'decimal:7',
            'gps_longitude' => 'decimal:7',
            'is_default' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function locationLabel(): string
    {
        return collect([$this->village, $this->cell, $this->sector, $this->district, $this->province])
            ->filter()
            ->implode(', ');
    }
}
