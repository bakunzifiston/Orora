<?php

/**
 * Feed calculator reference rules and formulas.
 * The service applies these guidelines when generating recommendations.
 */
return [
    'calculation_methods' => [
        'weight_based' => [
            'label' => 'Body weight × dry matter %',
            'formula' => 'Total feed (kg/day) = weight (kg) × DM% ÷ 100',
            'breakdown' => 'Roughage / concentrate / supplement = total × each % ÷ 100',
        ],
        'fixed' => [
            'label' => 'Fixed daily amount',
            'formula' => 'Total feed (kg/day) = fixed amount per animal (herd total = amount × head count)',
            'breakdown' => 'Roughage / concentrate / supplement = total × each % ÷ 100',
        ],
    ],

    'lactating_cattle_tiers' => [
        [
            'key' => 'low',
            'label' => 'Lactating — low yield (<10 L/day)',
            'max_yield_liters' => 9.99,
            'dm_percent' => 3.5,
            'roughage_pct' => 50,
            'concentrate_pct' => 45,
            'supplement_pct' => 5,
            'milk_source' => 'Average of last 5 milking records',
        ],
        [
            'key' => 'medium',
            'label' => 'Lactating — medium yield (10–20 L/day)',
            'min_yield_liters' => 10,
            'max_yield_liters' => 20,
            'dm_percent' => 4.0,
            'roughage_pct' => 45,
            'concentrate_pct' => 50,
            'supplement_pct' => 5,
            'milk_source' => 'Average of last 5 milking records (default when no milk data)',
        ],
        [
            'key' => 'high',
            'label' => 'Lactating — high yield (>20 L/day)',
            'min_yield_liters' => 20.01,
            'dm_percent' => 4.5,
            'roughage_pct' => 40,
            'concentrate_pct' => 55,
            'supplement_pct' => 5,
            'milk_source' => 'Average of last 5 milking records',
        ],
    ],

    'animal_types' => [
        'cattle' => [
            'label' => 'Cattle',
            'method' => 'weight_based',
            'rules' => [
                ['label' => 'Growing (calf < 6 months)', 'status' => 'growing', 'max_age_months' => 6, 'dm_percent' => 3.5, 'roughage_pct' => 40, 'concentrate_pct' => 55, 'supplement_pct' => 5],
                ['label' => 'Growing (6–12 months)', 'status' => 'growing', 'min_age_months' => 6, 'max_age_months' => 12, 'dm_percent' => 3.0, 'roughage_pct' => 50, 'concentrate_pct' => 45, 'supplement_pct' => 5],
                ['label' => 'Growing (12–24 months)', 'status' => 'growing', 'min_age_months' => 12, 'max_age_months' => 24, 'dm_percent' => 2.5, 'roughage_pct' => 60, 'concentrate_pct' => 35, 'supplement_pct' => 5],
                ['label' => 'Dry (not lactating)', 'status' => 'dry', 'dm_percent' => 2.0, 'roughage_pct' => 70, 'concentrate_pct' => 25, 'supplement_pct' => 5],
                ['label' => 'Pregnant', 'status' => 'pregnant', 'dm_percent' => 2.5, 'roughage_pct' => 60, 'concentrate_pct' => 35, 'supplement_pct' => 5],
                ['label' => 'Breeding (bull)', 'status' => 'breeding', 'dm_percent' => 2.5, 'roughage_pct' => 60, 'concentrate_pct' => 35, 'supplement_pct' => 5],
            ],
        ],
        'goat' => [
            'label' => 'Goat',
            'method' => 'weight_based',
            'rules' => [
                ['label' => 'Growing (< 6 months)', 'status' => 'growing', 'max_age_months' => 6, 'dm_percent' => 4.0, 'roughage_pct' => 45, 'concentrate_pct' => 50, 'supplement_pct' => 5],
                ['label' => 'Growing (> 6 months)', 'status' => 'growing', 'min_age_months' => 6, 'dm_percent' => 3.5, 'roughage_pct' => 55, 'concentrate_pct' => 40, 'supplement_pct' => 5],
                ['label' => 'Dry', 'status' => 'dry', 'dm_percent' => 2.5, 'roughage_pct' => 70, 'concentrate_pct' => 25, 'supplement_pct' => 5],
                ['label' => 'Pregnant', 'status' => 'pregnant', 'dm_percent' => 3.0, 'roughage_pct' => 60, 'concentrate_pct' => 35, 'supplement_pct' => 5],
                ['label' => 'Lactating', 'status' => 'lactating', 'dm_percent' => 4.0, 'roughage_pct' => 50, 'concentrate_pct' => 45, 'supplement_pct' => 5],
                ['label' => 'Breeding', 'status' => 'breeding', 'dm_percent' => 3.0, 'roughage_pct' => 60, 'concentrate_pct' => 35, 'supplement_pct' => 5],
            ],
        ],
        'pig' => [
            'label' => 'Pig',
            'method' => 'fixed',
            'rules' => [
                ['label' => 'Growing (< 3 months)', 'status' => 'growing', 'max_age_months' => 3, 'fixed_kg' => 0.5, 'roughage_pct' => 10, 'concentrate_pct' => 85, 'supplement_pct' => 5],
                ['label' => 'Growing (3–6 months)', 'status' => 'growing', 'min_age_months' => 3, 'max_age_months' => 6, 'fixed_kg' => 1.5, 'roughage_pct' => 10, 'concentrate_pct' => 85, 'supplement_pct' => 5],
                ['label' => 'Growing (> 6 months)', 'status' => 'growing', 'min_age_months' => 6, 'fixed_kg' => 2.5, 'roughage_pct' => 15, 'concentrate_pct' => 80, 'supplement_pct' => 5],
                ['label' => 'Pregnant (sow)', 'status' => 'pregnant', 'fixed_kg' => 2.5, 'roughage_pct' => 15, 'concentrate_pct' => 80, 'supplement_pct' => 5],
                ['label' => 'Lactating (sow)', 'status' => 'lactating', 'fixed_kg' => 5.0, 'roughage_pct' => 10, 'concentrate_pct' => 85, 'supplement_pct' => 5],
                ['label' => 'Breeding (boar)', 'status' => 'breeding', 'fixed_kg' => 2.0, 'roughage_pct' => 15, 'concentrate_pct' => 80, 'supplement_pct' => 5],
            ],
        ],
        'poultry' => [
            'label' => 'Poultry',
            'method' => 'fixed',
            'rules' => [
                ['label' => 'Chick (< 4 weeks)', 'status' => 'growing', 'max_age_weeks' => 4, 'fixed_grams' => 30, 'roughage_pct' => 0, 'concentrate_pct' => 90, 'supplement_pct' => 10],
                ['label' => 'Grower (4–8 weeks)', 'status' => 'growing', 'min_age_weeks' => 4, 'max_age_weeks' => 8, 'fixed_grams' => 80, 'roughage_pct' => 5, 'concentrate_pct' => 90, 'supplement_pct' => 5],
                ['label' => 'Grower (> 8 weeks)', 'status' => 'growing', 'min_age_weeks' => 8, 'fixed_grams' => 120, 'roughage_pct' => 10, 'concentrate_pct' => 85, 'supplement_pct' => 5],
                ['label' => 'Laying hen', 'status' => 'laying', 'fixed_grams' => 120, 'roughage_pct' => 10, 'concentrate_pct' => 80, 'supplement_pct' => 10],
                ['label' => 'Broiler', 'status' => 'broiler', 'fixed_grams' => 150, 'roughage_pct' => 5, 'concentrate_pct' => 90, 'supplement_pct' => 5],
                ['label' => 'Breeding', 'status' => 'breeding', 'fixed_grams' => 130, 'roughage_pct' => 10, 'concentrate_pct' => 80, 'supplement_pct' => 10],
            ],
        ],
    ],

    'input_sources' => [
        'animal_type' => 'Animal species, or first livestock type on the herd',
        'weight' => 'Animal profile → weight (kg)',
        'production_status' => 'Animal profile → production status (e.g. Lactating, Gestating, Dry)',
        'age' => 'Months from date of birth (defaults to 12 months if birth date is missing)',
        'milk_yield' => 'Average liters from the last 5 milk records (lactating cattle only)',
    ],

    'status_normalization' => [
        'Lactating' => 'lactating (poultry → laying)',
        'Dry' => 'dry',
        'Growing' => 'growing',
        'Breeding' => 'breeding',
        'Gestating' => 'pregnant',
        'Fattening' => 'growing (poultry → broiler)',
    ],
];
