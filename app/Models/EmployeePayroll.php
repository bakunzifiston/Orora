<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePayroll extends TenantModel
{
    protected $table = 'employee_payroll';

    protected $fillable = [
        'employee_id',
        'contract_type',
        'contract_start',
        'contract_end',
        'base_salary',
        'currency',
        'pay_frequency',
        'payment_method',
        'bank_name',
        'bank_account',
        'mobile_money_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'contract_start' => 'date',
            'contract_end' => 'date',
            'base_salary' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
