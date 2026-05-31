<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeePayrollRequest;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;

class EmployeePayrollController extends Controller
{
    public function __construct(private readonly EmployeeService $employeeService) {}

    public function update(EmployeePayrollRequest $request, Employee $employee): RedirectResponse
    {
        $this->employeeService->updatePayroll($employee, $request->validated());

        return redirect()->route('employees.show', $employee)->with('success', 'Payroll details updated.');
    }
}
