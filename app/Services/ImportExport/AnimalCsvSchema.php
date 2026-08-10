<?php

namespace App\Services\ImportExport;

final class AnimalCsvSchema
{
    /**
     * @return list<string>
     */
    public static function headers(): array
    {
        return [
            'farm_name',
            'livestock_name',
            'tag_number',
            'name',
            'gender',
            'health_status',
            'lifecycle_status',
            'date_of_birth',
            'weight_kg',
            'color_markings',
            'species',
            'breed',
            'acquisition_type',
            'acquisition_date',
            'source',
            'mother_tag',
            'father_tag',
            'production_status',
            'current_condition',
            'notes',
        ];
    }

    /**
     * @return list<string|null>
     */
    public static function exampleRow(): array
    {
        return [
            'Demo Farm',
            'Dairy herd',
            'RW-001',
            'Bella',
            'female',
            'Healthy',
            'Active',
            '2022-05-10',
            '420',
            'Black and white',
            'Cattle',
            'Friesian',
            'Born on farm',
            '2022-05-10',
            null,
            null,
            null,
            'Lactating',
            'Good',
            null,
        ];
    }
}
