<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EmployeeSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Models\Employee;
use Illuminate\View\View;

class EmployeeModuleController extends Controller
{
    use EmployeeSectionViews;
    use ProvidesModuleNavigation;

    public function overview(): View
    {
        $stats = [
            'total' => Employee::query()->count(),
            'active' => Employee::query()->where('status', 'active')->count(),
            'on_leave' => Employee::query()->where('status', 'on_leave')->count(),
            'terminated' => Employee::query()->where('status', 'terminated')->count(),
            'monthly_payroll' => (float) Employee::query()
                ->join('employee_payroll', 'employees.id', '=', 'employee_payroll.employee_id')
                ->where('employees.status', 'active')
                ->where('employee_payroll.pay_frequency', 'monthly')
                ->sum('employee_payroll.base_salary'),
        ];

        $recentEmployees = Employee::query()
            ->with(['profile', 'primaryFarm'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $byRole = Employee::query()
            ->where('status', 'active')
            ->selectRaw('job_role, COUNT(*) as total')
            ->groupBy('job_role')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $byFarm = Employee::query()
            ->join('employee_farm_assignments', 'employees.id', '=', 'employee_farm_assignments.employee_id')
            ->join('farms', 'employee_farm_assignments.farm_id', '=', 'farms.id')
            ->where('employees.status', 'active')
            ->selectRaw('farms.name as farm_name, COUNT(DISTINCT employees.id) as total')
            ->groupBy('farms.id', 'farms.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return view('modules.employees.overview', $this->employeeSectionData('overview', compact(
            'stats',
            'recentEmployees',
            'byRole',
            'byFarm',
        )));
    }
}
