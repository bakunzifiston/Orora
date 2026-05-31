<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EmployeeSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Models\Farm;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    use EmployeeSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(private readonly EmployeeService $employeeService) {}

    public function directory(Request $request): View
    {
        $employees = Employee::query()
            ->with(['profile', 'primaryFarm', 'payroll'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('role'), fn ($q) => $q->where('job_role', $request->input('role')))
            ->when($request->filled('farm'), fn ($q) => $q->where('primary_farm_id', $request->input('farm')))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($query) use ($request) {
                $term = '%'.$request->input('q').'%';
                $query->where('display_name', 'like', $term)
                    ->orWhere('employee_code', 'like', $term)
                    ->orWhereHas('profile', fn ($p) => $p->where('phone', 'like', $term)
                        ->orWhere('national_id', 'like', $term));
            }))
            ->orderBy('display_name')
            ->paginate(15)
            ->withQueryString();

        return view('modules.employees.directory', $this->employeeSectionData('directory', [
            'employees' => $employees,
            'farms' => Farm::query()->orderBy('name')->get(),
            'filterStatus' => $request->input('status'),
            'filterRole' => $request->input('role'),
            'filterFarm' => $request->input('farm'),
            'filterQuery' => $request->input('q'),
        ]));
    }

    public function create(): View
    {
        return view('modules.employees.create', $this->employeeSectionData('directory', [
            'farms' => Farm::query()->orderBy('name')->get(),
        ]));
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        $employee = $this->employeeService->create(
            $request->employeeAttributes(),
            $request->profileAttributes(),
            $request->payrollAttributes(),
            $request->emergencyContactAttributes(),
        );

        return redirect()->route('employees.show', $employee)->with('success', 'Employee registered successfully.');
    }

    public function show(Employee $employee): View
    {
        $employee->load([
            'profile',
            'payroll',
            'primaryFarm',
            'emergencyContacts',
            'addresses',
            'farmAssignments.farm',
            'documents.uploader',
            'logs.actor',
        ]);

        return view('modules.employees.show', $this->employeeSectionData('directory', [
            'employee' => $employee,
            'farms' => Farm::query()->orderBy('name')->get(),
        ]));
    }

    public function edit(Employee $employee): View
    {
        $employee->load(['profile', 'payroll']);

        return view('modules.employees.edit', $this->employeeSectionData('directory', [
            'employee' => $employee,
            'farms' => Farm::query()->orderBy('name')->get(),
        ]));
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->employeeService->update(
            $employee,
            $request->employeeAttributes(),
            $request->profileAttributes(),
        );

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('employees.directory')->with('success', 'Employee removed successfully.');
    }
}
