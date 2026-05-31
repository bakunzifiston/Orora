<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeLog;
use App\Models\EmployeePayroll;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function generateEmployeeCode(): string
    {
        $seq = Employee::withTrashed()->count() + 1;

        return sprintf('EMP-%04d', $seq);
    }

    public function create(array $employeeData, array $profileData, ?array $payrollData = null, ?array $emergencyContact = null): Employee
    {
        return DB::transaction(function () use ($employeeData, $profileData, $payrollData, $emergencyContact) {
            if (empty($employeeData['display_name'])) {
                $employeeData['display_name'] = trim(($profileData['first_name'] ?? '').' '.($profileData['last_name'] ?? ''));
            }

            $employee = Employee::create([
                ...$employeeData,
                'employee_code' => $employeeData['employee_code'] ?? $this->generateEmployeeCode(),
                'created_by' => auth()->id(),
            ]);

            $employee->profile()->create($profileData);

            EmployeePayroll::create([
                'employee_id' => $employee->id,
                'currency' => $payrollData['currency'] ?? 'RWF',
                'pay_frequency' => $payrollData['pay_frequency'] ?? 'monthly',
                'contract_type' => $payrollData['contract_type'] ?? 'permanent',
                ...($payrollData ?? []),
            ]);

            if ($employee->primary_farm_id) {
                $employee->farmAssignments()->create([
                    'farm_id' => $employee->primary_farm_id,
                    'assignment_role' => $employee->job_role,
                    'is_primary' => true,
                    'assigned_from' => $employee->hire_date,
                ]);
            }

            if ($emergencyContact && ! empty($emergencyContact['contact_name'])) {
                $employee->emergencyContacts()->create([
                    ...$emergencyContact,
                    'is_primary' => true,
                ]);
            }

            $this->log($employee, 'created', null, $employee->only(['display_name', 'status', 'job_role']));

            return $employee->fresh(['profile', 'payroll', 'primaryFarm']);
        });
    }

    public function update(Employee $employee, array $employeeData, array $profileData): Employee
    {
        return DB::transaction(function () use ($employee, $employeeData, $profileData) {
            $old = $employee->only(['display_name', 'status', 'job_role', 'employment_type']);

            if (empty($employeeData['display_name'])) {
                $employeeData['display_name'] = trim(($profileData['first_name'] ?? $employee->profile?->first_name).' '.($profileData['last_name'] ?? $employee->profile?->last_name));
            }

            $employee->update($employeeData);

            if ($employee->profile) {
                $employee->profile->update($profileData);
            } else {
                $employee->profile()->create($profileData);
            }

            $new = $employee->fresh()->only(['display_name', 'status', 'job_role', 'employment_type']);

            if ($old !== $new) {
                $this->log($employee, 'updated', $old, $new);
            }

            return $employee->fresh(['profile', 'payroll', 'primaryFarm']);
        });
    }

    public function updatePayroll(Employee $employee, array $data): EmployeePayroll
    {
        $payroll = $employee->payroll ?? $employee->payroll()->create(['currency' => 'RWF']);
        $old = $payroll->only(['base_salary', 'pay_frequency', 'contract_type']);
        $payroll->update($data);
        $this->log($employee, 'payroll_updated', $old, $payroll->fresh()->only(['base_salary', 'pay_frequency', 'contract_type']));

        return $payroll->fresh();
    }

    public function log(
        Employee $employee,
        string $actionType,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $notes = null,
    ): EmployeeLog {
        return EmployeeLog::create([
            'employee_id' => $employee->id,
            'action_type' => $actionType,
            'action_by' => auth()->id(),
            'action_at' => now(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'notes' => $notes,
        ]);
    }
}
