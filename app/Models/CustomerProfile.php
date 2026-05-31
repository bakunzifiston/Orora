<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerProfile extends Model
{
    protected $fillable = [
        'customer_id',
        'first_name',
        'last_name',
        'national_id',
        'date_of_birth',
        'gender',
        'organization_name',
        'registration_number',
        'tax_id',
        'license_number',
        'license_expiry_date',
        'website',
        'industry',
        'number_of_employees',
        'established_date',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'license_expiry_date' => 'date',
            'established_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
