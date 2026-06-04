<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeEmergencyContact extends TenantModel
{
    protected $fillable = [
        'employee_id',
        'contact_name',
        'relationship',
        'phone',
        'email',
        'is_primary',
        'notes',
    ];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
