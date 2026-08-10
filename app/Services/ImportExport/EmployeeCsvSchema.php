<?php

namespace App\Services\ImportExport;

final class EmployeeCsvSchema
{
    /**
     * @return list<string>
     */
    public static function headers(): array
    {
        return [
            'primary_farm_name',
            'employee_code',
            'display_name',
            'status',
            'employment_type',
            'job_role',
            'hire_date',
            'termination_date',
            'notes',
            'first_name',
            'last_name',
            'national_id',
            'passport_number',
            'date_of_birth',
            'gender',
            'marital_status',
            'nationality',
            'phone',
            'email',
            'education_level',
            'skills',
            'contract_type',
            'base_salary',
            'currency',
            'pay_frequency',
            'payment_method',
            'emergency_contact_name',
            'emergency_relationship',
            'emergency_phone',
            'emergency_email',
        ];
    }

    /**
     * @return list<string|null>
     */
    public static function exampleRow(): array
    {
        return [
            'Demo Farm',
            null,
            null,
            'active',
            'full_time',
            'farm_worker',
            '2024-01-15',
            null,
            null,
            'Jean',
            'Uwimana',
            null,
            null,
            '1995-06-20',
            'male',
            'single',
            'Rwandan',
            '+250780000000',
            'jean@example.com',
            null,
            null,
            'permanent',
            '150000',
            'RWF',
            'monthly',
            'bank_transfer',
            null,
            null,
            null,
            null,
        ];
    }
}
