<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeAddressRequest;
use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;

class EmployeeAddressController extends Controller
{
    public function __construct(private readonly EmployeeService $employeeService) {}

    public function store(EmployeeAddressRequest $request, Employee $employee): RedirectResponse
    {
        if ($request->boolean('is_default')) {
            $employee->addresses()->update(['is_default' => false]);
        }

        $employee->addresses()->create($request->validated());
        $this->employeeService->log($employee, 'updated', null, null, 'Address added.');

        return redirect()->route('employees.show', $employee)->with('success', 'Address added.');
    }

    public function destroy(Employee $employee, EmployeeAddress $address): RedirectResponse
    {
        abort_unless($address->employee_id === $employee->id, 404);

        $address->delete();
        $this->employeeService->log($employee, 'updated', null, null, 'Address removed.');

        return redirect()->route('employees.show', $employee)->with('success', 'Address removed.');
    }
}
