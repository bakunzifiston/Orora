<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFarmAssignment extends TenantModel
{
    protected $fillable = [
        'employee_id',
        'farm_id',
        'assignment_role',
        'is_primary',
        'assigned_from',
        'assigned_until',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'assigned_from' => 'date',
            'assigned_until' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
}
