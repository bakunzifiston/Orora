<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeProfile extends TenantModel
{
    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'national_id',
        'passport_number',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'phone',
        'email',
        'photo_path',
        'education_level',
        'skills',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
