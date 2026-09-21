<?php

namespace App\Services\ImportExport;

final class AnimalCsvSchema
{
    /**
     * Canonical column names expected by the importer.
     *
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
     * Columns that must be present in the file header.
     *
     * @return list<string>
     */
    public static function requiredHeaders(): array
    {
        return [
            'farm_name',
            'livestock_name',
        ];
    }

    /**
     * Alternate spreadsheet header labels → canonical names.
     *
     * @return array<string, string>
     */
    public static function headerAliases(): array
    {
        return [
            'farm' => 'farm_name',
            'farm name' => 'farm_name',
            'farm_name' => 'farm_name',
            'livestock' => 'livestock_name',
            'livestock name' => 'livestock_name',
            'livestock group' => 'livestock_name',
            'livestock_group' => 'livestock_name',
            'group' => 'livestock_name',
            'group name' => 'livestock_name',
            'herd' => 'livestock_name',
            'herd name' => 'livestock_name',
            'tag' => 'tag_number',
            'tag number' => 'tag_number',
            'tag_number' => 'tag_number',
            'animal tag' => 'tag_number',
            'animal name' => 'name',
            'name' => 'name',
            'sex' => 'gender',
            'gender' => 'gender',
            'health' => 'health_status',
            'health status' => 'health_status',
            'health_status' => 'health_status',
            'lifecycle' => 'lifecycle_status',
            'lifecycle status' => 'lifecycle_status',
            'lifecycle_status' => 'lifecycle_status',
            'status' => 'lifecycle_status',
            'dob' => 'date_of_birth',
            'date of birth' => 'date_of_birth',
            'birth date' => 'date_of_birth',
            'date_of_birth' => 'date_of_birth',
            'weight' => 'weight_kg',
            'weight kg' => 'weight_kg',
            'weight_kg' => 'weight_kg',
            'color' => 'color_markings',
            'colour' => 'color_markings',
            'color markings' => 'color_markings',
            'color_markings' => 'color_markings',
            'species' => 'species',
            'breed' => 'breed',
            'acquisition' => 'acquisition_type',
            'acquisition type' => 'acquisition_type',
            'acquisition_type' => 'acquisition_type',
            'acquisition date' => 'acquisition_date',
            'acquisition_date' => 'acquisition_date',
            'source' => 'source',
            'mother' => 'mother_tag',
            'mother tag' => 'mother_tag',
            'mother_tag' => 'mother_tag',
            'father' => 'father_tag',
            'father tag' => 'father_tag',
            'father_tag' => 'father_tag',
            'production' => 'production_status',
            'production status' => 'production_status',
            'production_status' => 'production_status',
            'condition' => 'current_condition',
            'current condition' => 'current_condition',
            'current_condition' => 'current_condition',
            'notes' => 'notes',
            'note' => 'notes',
            'comment' => 'notes',
            'comments' => 'notes',
        ];
    }

    /**
     * @return list<string|null>
     */
    public static function exampleRow(): array
    {
        return [
            'Demo Farm',
            'Cows (lactating)',
            'RW-001',
            'Bella',
            'female',
            'Pregnant',
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
            'Pregnancy',
            'Good',
            null,
        ];
    }
}
