<?php

namespace App\Services\ImportExport;

final class CustomerCsvSchema
{
    /**
     * @return list<string>
     */
    public static function headers(): array
    {
        return [
            'customer_code',
            'customer_type',
            'display_name',
            'status',
            'trust_level',
            'preferred_payment_method',
            'currency',
            'notes',
            'first_name',
            'last_name',
            'national_id',
            'date_of_birth',
            'gender',
            'organization_name',
            'registration_number',
            'tax_id',
            'license_number',
            'license_expiry_date',
            'website',
            'industry',
            'number_of_employees',
            'established_date',
            'contact_name',
            'contact_role',
            'contact_phone',
            'contact_email',
        ];
    }

    /**
     * @return list<string|null>
     */
    public static function exampleRow(): array
    {
        return [
            null,
            'individual',
            'Jean Uwimana',
            'active',
            'new',
            'Mobile money',
            'RWF',
            null,
            'Jean',
            'Uwimana',
            null,
            '1990-04-12',
            'male',
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            'Jean Uwimana',
            null,
            '+250780000000',
            'jean@example.com',
        ];
    }
}
