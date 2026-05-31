<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLog extends Model
{
    protected $fillable = [
        'employee_id',
        'action_type',
        'action_by',
        'action_at',
        'old_values',
        'new_values',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'action_at' => 'datetime',
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
