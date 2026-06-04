<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAddress extends TenantModel
{
    protected $fillable = [
        'employee_id',
        'address_type',
        'address_label',
        'country',
        'province',
        'district',
        'sector',
        'cell',
        'village',
        'street_address',
        'is_default',
    ];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function locationLabel(): string
    {
        return collect([$this->village, $this->cell, $this->sector, $this->district, $this->province])
            ->filter()
            ->implode(', ');
    }
}
