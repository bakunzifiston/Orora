<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeFarmAssignmentRequest;
use App\Models\Employee;
use App\Models\EmployeeFarmAssignment;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;

class EmployeeFarmAssignmentController extends Controller
{
    public function __construct(private readonly EmployeeService $employeeService) {}

    public function store(EmployeeFarmAssignmentRequest $request, Employee $employee): RedirectResponse
    {
        if ($employee->farmAssignments()->where('farm_id', $request->input('farm_id'))->exists()) {
            return redirect()->route('employees.show', $employee)->with('error', 'Employee is already assigned to that farm.');
        }

        if ($request->boolean('is_primary')) {
            $employee->farmAssignments()->update(['is_primary' => false]);
        }

        $employee->farmAssignments()->create($request->validated());
        $this->employeeService->log($employee, 'updated', null, null, 'Farm assignment added.');

        return redirect()->route('employees.show', $employee)->with('success', 'Farm assignment added.');
    }

    public function destroy(Employee $employee, EmployeeFarmAssignment $employeeFarmAssignment): RedirectResponse
    {
        abort_unless($employeeFarmAssignment->employee_id === $employee->id, 404);

        $employeeFarmAssignment->delete();
        $this->employeeService->log($employee, 'updated', null, null, 'Farm assignment removed.');

        return redirect()->route('employees.show', $employee)->with('success', 'Farm assignment removed.');
    }
}
