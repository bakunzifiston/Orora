<?php

namespace App\Services\Export;

use App\Models\Employee;
use App\Services\Export\Concerns\StreamsCsv;
use App\Services\ImportExport\EmployeeCsvSchema;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeCsvExporter
{
    use StreamsCsv;

    public function export(Request $request): StreamedResponse
    {
        $headers = EmployeeCsvSchema::headers();
        $filename = 'employees-'.now()->format('Y-m-d-His').'.csv';

        $query = Employee::query()
            ->with(['profile', 'primaryFarm', 'payroll', 'emergencyContacts'])
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
            ->orderBy('display_name');

        return $this->streamCsv($filename, $headers, function ($handle) use ($query): void {
            $query->chunk(200, function ($employees) use ($handle): void {
                foreach ($employees as $employee) {
                    $profile = $employee->profile;
                    $payroll = $employee->payroll;
                    $emergency = $employee->emergencyContacts
                        ->firstWhere('is_primary', true)
                        ?? $employee->emergencyContacts->first();

                    fputcsv($handle, [
                        $employee->primaryFarm?->name,
                        $employee->employee_code,
                        $employee->display_name,
                        $employee->status,
                        $employee->employment_type,
                        $employee->job_role,
                        optional($employee->hire_date)?->format('Y-m-d'),
                        optional($employee->termination_date)?->format('Y-m-d'),
                        $employee->notes,
                        $profile?->first_name,
                        $profile?->last_name,
                        $profile?->national_id,
                        $profile?->passport_number,
                        optional($profile?->date_of_birth)?->format('Y-m-d'),
                        $profile?->gender,
                        $profile?->marital_status,
                        $profile?->nationality,
                        $profile?->phone,
                        $profile?->email,
                        $profile?->education_level,
                        $profile?->skills,
                        $payroll?->contract_type,
                        $payroll?->base_salary,
                        $payroll?->currency,
                        $payroll?->pay_frequency,
                        $payroll?->payment_method,
                        $emergency?->contact_name,
                        $emergency?->relationship,
                        $emergency?->phone,
                        $emergency?->email,
                    ]);
                }
            });
        });
    }

    public function template(): StreamedResponse
    {
        return $this->streamCsv('employees-import-template.csv', EmployeeCsvSchema::headers(), function ($handle): void {
            fputcsv($handle, EmployeeCsvSchema::exampleRow());
        });
    }
}
