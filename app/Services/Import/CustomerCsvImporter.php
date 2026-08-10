<?php

namespace App\Services\Import;

use App\Services\CustomerService;
use App\Services\Import\Concerns\ParsesCsv;
use App\Services\ImportExport\CustomerCsvSchema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Throwable;

class CustomerCsvImporter
{
    use ParsesCsv;

    public const MAX_ROWS = 2000;

    public function __construct(private readonly CustomerService $customerService) {}

    /**
     * @return array{created: int, failed: int, errors: list<array{row: int, message: string}>}
     */
    public function import(UploadedFile $file): array
    {
        $created = 0;
        $failed = 0;
        $errors = [];

        try {
            $rows = $this->parseCsvRows($file, CustomerCsvSchema::headers(), self::MAX_ROWS);
        } catch (InvalidArgumentException $e) {
            return [
                'created' => 0,
                'failed' => 1,
                'errors' => [['row' => 0, 'message' => $e->getMessage()]],
            ];
        }

        foreach ($rows as $rowNumber => $row) {
            try {
                [$customerData, $profileData, $contactData] = $this->attributesForRow($row);
                $this->customerService->create($customerData, $profileData, $contactData);
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
     * @return array{0: array<string, mixed>, 1: array<string, mixed>, 2: ?array<string, mixed>}
     */
    protected function attributesForRow(array $row): array
    {
        $type = isset($row['customer_type']) ? strtolower((string) $row['customer_type']) : null;

        $payload = [
            'customer_code' => $row['customer_code'] ?? null,
            'customer_type' => $type,
            'display_name' => $row['display_name'] ?? null,
            'status' => isset($row['status']) ? strtolower((string) $row['status']) : null,
            'trust_level' => isset($row['trust_level']) ? strtolower((string) $row['trust_level']) : null,
            'preferred_payment_method' => $row['preferred_payment_method'] ?? null,
            'currency' => isset($row['currency']) ? strtoupper((string) $row['currency']) : null,
            'notes' => $row['notes'] ?? null,
            'first_name' => $row['first_name'] ?? null,
            'last_name' => $row['last_name'] ?? null,
            'national_id' => $row['national_id'] ?? null,
            'date_of_birth' => $row['date_of_birth'] ?? null,
            'gender' => isset($row['gender']) ? strtolower((string) $row['gender']) : null,
            'organization_name' => $row['organization_name'] ?? null,
            'registration_number' => $row['registration_number'] ?? null,
            'tax_id' => $row['tax_id'] ?? null,
            'license_number' => $row['license_number'] ?? null,
            'license_expiry_date' => $row['license_expiry_date'] ?? null,
            'website' => $row['website'] ?? null,
            'industry' => $row['industry'] ?? null,
            'number_of_employees' => $row['number_of_employees'] ?? null,
            'established_date' => $row['established_date'] ?? null,
            'contact_name' => $row['contact_name'] ?? null,
            'contact_role' => $row['contact_role'] ?? null,
            'contact_phone' => $row['contact_phone'] ?? null,
            'contact_email' => $row['contact_email'] ?? null,
        ];

        $rules = [
            'customer_code' => ['nullable', 'string', 'max:50', Rule::unique('customers', 'customer_code')],
            'customer_type' => ['required', Rule::in(array_keys(config('modules.customer_types')))],
            'display_name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(config('modules.customer_statuses'))],
            'trust_level' => ['required', Rule::in(array_keys(config('modules.customer_trust_levels')))],
            'preferred_payment_method' => ['nullable', Rule::in(config('modules.expense_payment_methods'))],
            'currency' => ['required', 'string', 'max:3'],
            'notes' => ['nullable', 'string'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_role' => ['nullable', 'string', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ];

        if ($type === 'individual') {
            $rules += [
                'first_name' => ['nullable', 'string', 'max:100'],
                'last_name' => ['nullable', 'string', 'max:100'],
                'national_id' => ['nullable', 'string', 'max:50'],
                'date_of_birth' => ['nullable', 'date'],
                'gender' => ['nullable', Rule::in(array_keys(config('modules.customer_genders')))],
            ];
        } else {
            $rules += [
                'organization_name' => ['nullable', 'string', 'max:255'],
                'registration_number' => ['nullable', 'string', 'max:100'],
                'tax_id' => ['nullable', 'string', 'max:100'],
                'license_number' => ['nullable', 'string', 'max:100'],
                'license_expiry_date' => ['nullable', 'date'],
                'website' => ['nullable', 'string', 'max:255'],
                'industry' => ['nullable', 'string', 'max:100'],
                'number_of_employees' => ['nullable', 'integer', 'min:0'],
                'established_date' => ['nullable', 'date'],
            ];
        }

        $validator = Validator::make($payload, $rules);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        $validated = $validator->validated();

        $customerData = array_filter([
            'customer_code' => $validated['customer_code'] ?? null,
            'customer_type' => $validated['customer_type'],
            'display_name' => $validated['display_name'],
            'status' => $validated['status'],
            'trust_level' => $validated['trust_level'],
            'preferred_payment_method' => $validated['preferred_payment_method'] ?? null,
            'currency' => $validated['currency'],
            'notes' => $validated['notes'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        if ($validated['customer_type'] === 'individual') {
            $profileData = array_filter([
                'first_name' => $validated['first_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'national_id' => $validated['national_id'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
            ], fn ($value) => $value !== null && $value !== '');
        } else {
            $profileData = array_filter([
                'organization_name' => $validated['organization_name'] ?? null,
                'registration_number' => $validated['registration_number'] ?? null,
                'tax_id' => $validated['tax_id'] ?? null,
                'license_number' => $validated['license_number'] ?? null,
                'license_expiry_date' => $validated['license_expiry_date'] ?? null,
                'website' => $validated['website'] ?? null,
                'industry' => $validated['industry'] ?? null,
                'number_of_employees' => $validated['number_of_employees'] ?? null,
                'established_date' => $validated['established_date'] ?? null,
            ], fn ($value) => $value !== null && $value !== '');
        }

        $contactData = null;
        if (! empty($validated['contact_name']) || ! empty($validated['contact_phone']) || ! empty($validated['contact_email'])) {
            $contactData = [
                'contact_name' => $validated['contact_name'] ?: $validated['display_name'],
                'role' => $validated['contact_role'] ?? null,
                'phone' => $validated['contact_phone'] ?? null,
                'email' => $validated['contact_email'] ?? null,
            ];
        }

        return [$customerData, $profileData, $contactData];
    }
}
