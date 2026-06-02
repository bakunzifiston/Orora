<?php

return [

    'seed_force' => (bool) env('DEMO_SEED_FORCE', false),

    'tenant_id' => env('DEMO_TENANT_ID', 'demo'),

    'date_start' => env('DEMO_DATE_START', '2020-01-01'),

    'date_end' => env('DEMO_DATE_END', '2026-06-01'),

    /** Minimum rows created per operational module (health, sales, milk, etc.). */
    'min_records_per_module' => (int) env('DEMO_MIN_RECORDS', 25),

  /** Target animal count across all farms (must exceed min_records for animal-tied modules). */
    'animal_count' => (int) env('DEMO_ANIMAL_COUNT', 120),

    'farm_count' => (int) env('DEMO_FARM_COUNT', 4),

    'user' => [
        'name' => 'Orora Demo Manager',
        'email' => env('DEMO_USER_EMAIL', 'demo@ororafarm.rw'),
        'password' => env('DEMO_USER_PASSWORD', 'password'),
    ],

];
