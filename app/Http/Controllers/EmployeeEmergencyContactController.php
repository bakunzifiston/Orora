<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeEmergencyContactRequest;
use App\Models\Employee;
use App\Models\EmployeeEmergencyContact;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;

class EmployeeEmergencyContactController extends Controller
{
    public function __construct(private readonly EmployeeService $employeeService) {}

    public function store(EmployeeEmergencyContactRequest $request, Employee $employee): RedirectResponse
    {
        if ($request->boolean('is_primary')) {
            $employee->emergencyContacts()->update(['is_primary' => false]);
        }

        $employee->emergencyContacts()->create($request->validated());
        $this->employeeService->log($employee, 'updated', null, null, 'Emergency contact added.');

        return redirect()->route('employees.show', $employee)->with('success', 'Emergency contact added.');
    }

    public function destroy(Employee $employee, EmployeeEmergencyContact $emergencyContact): RedirectResponse
    {
        abort_unless($emergencyContact->employee_id === $employee->id, 404);

        $emergencyContact->delete();
        $this->employeeService->log($employee, 'updated', null, null, 'Emergency contact removed.');

        return redirect()->route('employees.show', $employee)->with('success', 'Emergency contact removed.');
    }
}
