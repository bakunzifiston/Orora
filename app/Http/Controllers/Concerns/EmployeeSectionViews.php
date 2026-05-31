<?php

namespace App\Http\Controllers\Concerns;

trait EmployeeSectionViews
{
    protected function employeeSectionData(string $activeSection, array $data = []): array
    {
        return array_merge($this->moduleViewData('employees', [
            'activeEmployeeSection' => $activeSection,
            'employeeSections' => config('modules.employee_sections'),
        ]), $data);
    }
}
