<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'employee_code',
        'display_name',
        'status',
        'employment_type',
        'job_role',
        'primary_farm_id',
        'hire_date',
        'termination_date',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'termination_date' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function primaryFarm(): BelongsTo
    {
        return $this->belongsTo(Farm::class, 'primary_farm_id');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmployeeEmergencyContact::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(EmployeeAddress::class);
    }

    public function farmAssignments(): HasMany
    {
        return $this->hasMany(EmployeeFarmAssignment::class);
    }

    public function payroll(): HasOne
    {
        return $this->hasOne(EmployeePayroll::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(EmployeeLog::class)->orderByDesc('action_at');
    }

    public function roleLabel(): string
    {
        return config('modules.employee_job_roles.'.$this->job_role, ucfirst(str_replace('_', ' ', $this->job_role)));
    }

    public function statusLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function tenureMonths(): ?int
    {
        if (! $this->hire_date) {
            return null;
        }

        $end = $this->termination_date ?? now();

        return (int) $this->hire_date->diffInMonths($end);
    }
}
