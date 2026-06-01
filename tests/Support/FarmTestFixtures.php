<?php

namespace Tests\Support;

use App\Models\Animal;
use App\Models\Farm;
use App\Models\Livestock;

class FarmTestFixtures
{
    public static function farm(array $overrides = []): Farm
    {
        static $sequence = 0;
        $sequence++;

        return Farm::create(array_merge([
            'name' => 'Test Farm '.$sequence,
            'registration_number' => 'REG-TEST-'.$sequence.'-'.uniqid(),
            'country' => 'Rwanda',
            'province_code' => 1,
            'province' => 'Kigali',
            'district_code' => 1,
            'district' => 'Gasabo',
            'sector_code' => '1',
            'sector' => 'Sector',
            'cell_code' => 1,
            'cell' => 'Cell',
            'village_code' => 1,
            'village' => 'Village',
            'farm_size_hectares' => 1.5,
            'registration_date' => now()->toDateString(),
            'status' => 'active',
            'ownership_type' => 'individual',
            'owner_first_name' => 'Test',
            'owner_last_name' => 'Owner',
            'owner_national_id' => '1199880012345678',
            'contact_phone' => '0780000000',
            'owner_gender' => 'male',
        ], $overrides));
    }

    public static function livestock(Farm $farm, array $overrides = []): Livestock
    {
        static $sequence = 0;
        $sequence++;

        return Livestock::create(array_merge([
            'farm_id' => $farm->id,
            'name' => 'Herd '.$sequence,
            'herd_groups' => ['dairy'],
            'livestock_types' => ['cattle'],
            'production_purposes' => ['milk'],
            'farming_methods' => ['zero_grazing'],
            'feeding_methods' => ['pasture'],
            'head_count' => 10,
            'status' => 'active',
        ], $overrides));
    }

    public static function animal(Farm $farm, Livestock $livestock, string $gender = 'female', array $overrides = []): Animal
    {
        static $sequence = 0;
        $sequence++;

        return Animal::create(array_merge([
            'farm_id' => $farm->id,
            'livestock_id' => $livestock->id,
            'tag_number' => 'TAG-'.$sequence.'-'.uniqid(),
            'name' => 'Animal '.$sequence,
            'gender' => $gender,
            'health_status' => 'Healthy',
            'lifecycle_status' => 'Active',
        ], $overrides));
    }
}
