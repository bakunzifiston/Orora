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
        $search = trim((string) $request->input('q', ''));
        $role = $request->string('role')->toString();
        $status = $request->string('status')->toString();
        $farmId = $request->filled('farm') ? $request->integer('farm') : null;

        $roles = array_keys(config('modules.employee_job_roles', []));
        $statuses = config('modules.employee_statuses', []);

        if ($role !== '' && ! in_array($role, $roles, true)) {
            $role = '';
        }

        if ($status !== '' && ! in_array($status, $statuses, true)) {
            $status = '';
        }

        $employees = Employee::query()
            ->with(['profile', 'primaryFarm', 'payroll'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($role !== '', fn ($q) => $q->where('job_role', $role))
            ->when($farmId, fn ($q) => $q->where('primary_farm_id', $farmId))
            ->when($search !== '', function ($q) use ($search) {
                $term = '%'.$search.'%';
                $q->where(function ($query) use ($term) {
                    $query->where('display_name', 'like', $term)
                        ->orWhere('employee_code', 'like', $term)
                        ->orWhereHas('profile', fn ($p) => $p->where('phone', 'like', $term)
                            ->orWhere('national_id', 'like', $term));
                });
            })
            ->orderBy('display_name')
            ->paginate(15)
            ->withQueryString();

        $filtersActive = $search !== '' || $role !== '' || $status !== '' || $farmId !== null;

        $stats = [
            'total' => Employee::query()->count(),
            'active' => Employee::query()->where('status', 'active')->count(),
            'on_leave' => Employee::query()->where('status', 'on_leave')->count(),
            'monthly_payroll' => (float) Employee::query()
                ->join('employee_payroll', 'employees.id', '=', 'employee_payroll.employee_id')
                ->where('employees.status', 'active')
                ->where('employee_payroll.pay_frequency', 'monthly')
                ->sum('employee_payroll.base_salary'),
        ];

        $byRole = Employee::query()
            ->where('status', 'active')
            ->selectRaw('job_role, COUNT(*) as total')
            ->groupBy('job_role')
            ->orderByDesc('total')
            ->get();

        return view('modules.employees.directory', $this->employeeSectionData('directory', [
            'employees' => $employees,
            'farms' => Farm::query()->orderBy('name')->get(),
            'stats' => $stats,
            'byRole' => $byRole,
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'farmId' => $farmId,
            'filtersActive' => $filtersActive,
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
