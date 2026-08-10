<?php

namespace App\Services\Import;

use App\Models\Farm;
use App\Services\EmployeeService;
use App\Services\Import\Concerns\ParsesCsv;
use App\Services\ImportExport\EmployeeCsvSchema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Throwable;

class EmployeeCsvImporter
{
    use ParsesCsv;

    public const MAX_ROWS = 2000;

    public function __construct(private readonly EmployeeService $employeeService) {}

    /**
     * @return array{created: int, failed: int, errors: list<array{row: int, message: string}>}
     */
    public function import(UploadedFile $file): array
    {
        $created = 0;
        $failed = 0;
        $errors = [];

        try {
            $rows = $this->parseCsvRows($file, EmployeeCsvSchema::headers(), self::MAX_ROWS);
        } catch (InvalidArgumentException $e) {
            return [
                'created' => 0,
                'failed' => 1,
                'errors' => [['row' => 0, 'message' => $e->getMessage()]],
            ];
        }

        foreach ($rows as $rowNumber => $row) {
            try {
                [$employeeData, $profileData, $payrollData, $emergencyContact] = $this->attributesForRow($row);
                $this->employeeService->create($employeeData, $profileData, $payrollData, $emergencyContact);
                $created++;
            } catch (Throwable $e) {
                $failed++;
                $errors[] = [
                    'row' => $rowNumber,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return compact('created', 'failed', 'errors');
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array{0: array<string, mixed>, 1: array<string, mixed>, 2: array<string, mixed>, 3: ?array<string, mixed>}
     */
    protected function attributesForRow(array $row): array
    {
        $farmId = $this->resolvePrimaryFarmId($row['primary_farm_name'] ?? null);

        $payload = [
            'primary_farm_id' => $farmId,
            'employee_code' => $row['employee_code'] ?? null,
            'display_name' => $row['display_name'] ?? null,
            'status' => isset($row['status']) ? strtolower((string) $row['status']) : null,
            'employment_type' => isset($row['employment_type']) ? strtolower((string) $row['employment_type']) : null,
            'job_role' => isset($row['job_role']) ? strtolower((string) $row['job_role']) : null,
            'hire_date' => $row['hire_date'] ?? null,
            'termination_date' => $row['termination_date'] ?? null,
            'notes' => $row['notes'] ?? null,
            'first_name' => $row['first_name'] ?? null,
            'last_name' => $row['last_name'] ?? null,
            'national_id' => $row['national_id'] ?? null,
            'passport_number' => $row['passport_number'] ?? null,
            'date_of_birth' => $row['date_of_birth'] ?? null,
            'gender' => isset($row['gender']) ? strtolower((string) $row['gender']) : null,
            'marital_status' => isset($row['marital_status']) ? strtolower((string) $row['marital_status']) : null,
            'nationality' => $row['nationality'] ?? null,
            'phone' => $row['phone'] ?? null,
            'email' => $row['email'] ?? null,
            'education_level' => $row['education_level'] ?? null,
            'skills' => $row['skills'] ?? null,
            'contract_type' => isset($row['contract_type']) ? strtolower((string) $row['contract_type']) : null,
            'base_salary' => $row['base_salary'] ?? null,
            'currency' => isset($row['currency']) ? strtoupper((string) $row['currency']) : null,
            'pay_frequency' => isset($row['pay_frequency']) ? strtolower((string) $row['pay_frequency']) : null,
            'payment_method' => $row['payment_method'] ?? null,
            'emergency_contact_name' => $row['emergency_contact_name'] ?? null,
            'emergency_relationship' => $row['emergency_relationship'] ?? null,
            'emergency_phone' => $row['emergency_phone'] ?? null,
            'emergency_email' => $row['emergency_email'] ?? null,
        ];

        $validator = Validator::make($payload, [
            'display_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(config('modules.employee_statuses'))],
            'employment_type' => ['required', Rule::in(array_keys(config('modules.employee_employment_types')))],
            'job_role' => ['required', Rule::in(array_keys(config('modules.employee_job_roles')))],
            'primary_farm_id' => ['nullable', 'exists:farms,id'],
            'employee_code' => ['nullable', 'string', 'max:50', Rule::unique('employees', 'employee_code')],
            'hire_date' => ['nullable', 'date'],
            'termination_date' => ['nullable', 'date', 'after_or_equal:hire_date'],
            'notes' => ['nullable', 'string'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(array_keys(config('modules.customer_genders')))],
            'marital_status' => ['nullable', Rule::in(array_keys(config('modules.employee_marital_statuses')))],
            'nationality' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'education_level' => ['nullable', 'string', 'max:100'],
            'skills' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_relationship' => ['nullable', 'string', 'max:100'],
            'emergency_phone' => ['nullable', 'string', 'max:50'],
            'emergency_email' => ['nullable', 'email', 'max:255'],
            'contract_type' => ['nullable', Rule::in(array_keys(config('modules.employee_contract_types')))],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'pay_frequency' => ['nullable', Rule::in(array_keys(config('modules.employee_pay_frequencies')))],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        $validated = $validator->validated();

        $employeeData = array_filter([
            'display_name' => $validated['display_name'] ?? null,
            'status' => $validated['status'],
            'employment_type' => $validated['employment_type'],
            'job_role' => $validated['job_role'],
            'primary_farm_id' => $validated['primary_farm_id'] ?? null,
            'employee_code' => $validated['employee_code'] ?? null,
            'hire_date' => $validated['hire_date'] ?? null,
            'termination_date' => $validated['termination_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        $profileData = array_filter([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'national_id' => $validated['national_id'] ?? null,
            'passport_number' => $validated['passport_number'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'marital_status' => $validated['marital_status'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'education_level' => $validated['education_level'] ?? null,
            'skills' => $validated['skills'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        $payrollData = array_filter([
            'contract_type' => $validated['contract_type'] ?? null,
            'base_salary' => $validated['base_salary'] ?? null,
            'currency' => $validated['currency'] ?? null,
            'pay_frequency' => $validated['pay_frequency'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        $emergencyContact = null;
        if (! empty($validated['emergency_contact_name']) || ! empty($validated['emergency_phone'])) {
            $emergencyContact = [
                'contact_name' => $validated['emergency_contact_name'] ?? null,
                'relationship' => $validated['emergency_relationship'] ?? null,
                'phone' => $validated['emergency_phone'] ?? null,
                'email' => $validated['emergency_email'] ?? null,
            ];
        }

        return [$employeeData, $profileData, $payrollData, $emergencyContact];
    }

    protected function resolvePrimaryFarmId(?string $farmName): ?int
    {
        if ($farmName === null || $farmName === '') {
            return null;
        }

        $farms = Farm::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($farmName)])
            ->get();

        if ($farms->isEmpty()) {
            throw new InvalidArgumentException("Farm \"{$farmName}\" was not found.");
        }

        if ($farms->count() > 1) {
            throw new InvalidArgumentException("Farm name \"{$farmName}\" matches more than one farm.");
        }

        return $farms->first()->id;
    }
}
