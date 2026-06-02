<?php

namespace Database\Seeders\Support;

final class RwandaDemoData
{
    /** @var list<array{first: string, last: string, gender: string}> */
    private const PEOPLE = [
        ['first' => 'Jean Baptiste', 'last' => 'Nkurunziza', 'gender' => 'male'],
        ['first' => 'Marie Claire', 'last' => 'Uwimana', 'gender' => 'female'],
        ['first' => 'Emmanuel', 'last' => 'Habimana', 'gender' => 'male'],
        ['first' => 'Grace', 'last' => 'Mukamana', 'gender' => 'female'],
        ['first' => 'Patrick', 'last' => 'Niyonzima', 'gender' => 'male'],
        ['first' => 'Chantal', 'last' => 'Ingabire', 'gender' => 'female'],
        ['first' => 'Eric', 'last' => 'Bizimana', 'gender' => 'male'],
        ['first' => 'Alice', 'last' => 'Nyiramajyambere', 'gender' => 'female'],
        ['first' => 'Faustin', 'last' => 'Mugisha', 'gender' => 'male'],
        ['first' => 'Vestine', 'last' => 'Uwineza', 'gender' => 'female'],
        ['first' => 'Olivier', 'last' => 'Nshimiyimana', 'gender' => 'male'],
        ['first' => 'Donatille', 'last' => 'Mukeshimana', 'gender' => 'female'],
    ];

  /** @var list<array<string, mixed>> */
    public const LOCATIONS = [
        [
            'key' => 'kigali_gasabo',
            'province_code' => 1,
            'province' => 'Kigali City',
            'district_code' => 11,
            'district' => 'Gasabo',
            'sector_code' => '1101',
            'sector' => 'Remera',
            'cell_code' => 110101,
            'cell' => 'Rukiri I',
            'village_code' => 11010101,
            'village' => 'Amahoro',
        ],
        [
            'key' => 'musanze_kinigi',
            'province_code' => 3,
            'province' => 'Northern Province',
            'district_code' => 35,
            'district' => 'Musanze',
            'sector_code' => '3502',
            'sector' => 'Kinigi',
            'cell_code' => 350201,
            'cell' => 'Kabatwa',
            'village_code' => 35020102,
            'village' => 'Nyabigoma',
        ],
        [
            'key' => 'huye_tumba',
            'province_code' => 5,
            'province' => 'Southern Province',
            'district_code' => 52,
            'district' => 'Huye',
            'sector_code' => '5203',
            'sector' => 'Tumba',
            'cell_code' => 520301,
            'cell' => 'Cyarwa',
            'village_code' => 52030101,
            'village' => 'Gikundamvura',
        ],
        [
            'key' => 'rubavu_gisenyi',
            'province_code' => 4,
            'province' => 'Western Province',
            'district_code' => 42,
            'district' => 'Rubavu',
            'sector_code' => '4201',
            'sector' => 'Gisenyi',
            'cell_code' => 420101,
            'cell' => 'Rubavu',
            'village_code' => 42010103,
            'village' => 'Mbugangari',
        ],
    ];

    /** @var list<string> */
    public const FARM_NAMES = [
        'Gihanga Dairy Farm',
        'Kinigi Highlands Cooperative Farm',
        'Tumba Valley Livestock Farm',
        'Lake Kivu Border Farm',
    ];

    /** @var list<string> */
    public const BUSINESS_NAMES = [
        'Kigali Fresh Milk Ltd',
        'Musanze Meat Traders',
        'Remera Dairy Buyers Cooperative',
        'Huye Agro Processors',
        'Rubavu Hotel Supplies',
        'Nyabugogo Market Vendors Union',
        'Kimironko Butchers Association',
        'Gisenyi Cross-Border Livestock',
        'RAB Input Shop Remera',
        'Buffet Restaurant Umucyo',
    ];

    /** @var list<string> */
    public const VACCINES = [
        'FMD Trivalent Vaccine',
        'Lumpy Skin Disease Vaccine',
        'Brucellosis RB51',
        'Anthrax Spore Vaccine',
        'Blackleg Vaccine',
    ];

    /** @var list<string> */
    public const DISEASES = [
        'Mastitis (subclinical)',
        'Tick-borne fever',
        'Foot rot',
        'Bloat',
        'Pink eye',
        'Diarrhea in calves',
    ];

    public static function person(int $index): array
    {
        $p = self::PEOPLE[$index % count(self::PEOPLE)];

        return [
            'first_name' => $p['first'],
            'last_name' => $p['last'],
            'full_name' => "{$p['first']} {$p['last']}",
            'gender' => $p['gender'],
        ];
    }

    public static function phone(int $seed = 0): string
    {
        $prefixes = ['078', '079', '072'];
        $prefix = $prefixes[$seed % count($prefixes)];

        return $prefix.sprintf('%07d', 1000000 + ($seed % 8999999));
    }

    public static function nationalId(int $seed = 0): string
    {
        return '1'.sprintf('%02d', 90 + ($seed % 10)).sprintf('%02d%02d', 1 + ($seed % 28), 1 + ($seed % 12))
            .sprintf('%05d', 10000 + $seed).'7';
    }

    public static function location(string $key): array
    {
        foreach (self::LOCATIONS as $location) {
            if ($location['key'] === $key) {
                return $location;
            }
        }

        return self::LOCATIONS[0];
    }

    public static function animalName(int $seed): string
    {
        $names = ['Inka', 'Kibondo', 'Nyiramwiza', 'Impano', 'Ubwiza', 'Icyizere', 'Keza', 'Mpenzi', 'Shema', 'Akazi'];

        return $names[$seed % count($names)];
    }

    public static function rwf(int $amount): float
    {
        return (float) $amount;
    }
}
