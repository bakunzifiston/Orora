<?php

return [

    'navigation' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid'],
        ['key' => 'traceability', 'label' => 'Traceability', 'route' => null, 'icon' => 'route'],
        ['key' => 'inventory', 'label' => 'Inventory', 'route' => null, 'icon' => 'box'],
        ['key' => 'compliance', 'label' => 'Compliance', 'route' => null, 'icon' => 'shield'],
        ['key' => 'reports', 'label' => 'Reports', 'route' => null, 'icon' => 'chart'],
        ['key' => 'settings', 'label' => 'Profile', 'route' => 'profile.edit', 'icon' => 'gear'],
    ],

    'stats' => [
        ['label' => 'Total Shipments', 'value' => '2,485', 'change' => '+12%', 'icon' => 'truck'],
        ['label' => 'Inventory Value', 'value' => '95.7M', 'suffix' => 'RWF', 'icon' => 'wallet'],
        ['label' => 'Compliance Score', 'value' => 'A+', 'highlight' => true, 'icon' => 'shield'],
        ['label' => 'Incidents', 'value' => '21', 'alert' => true, 'icon' => 'alert'],
    ],

    'modules' => [
        [
            'key' => 'traceability',
            'title' => 'Traceability',
            'description' => 'Live tracking, origin records, and shipment chains.',
            'enabled' => false,
        ],
        [
            'key' => 'inventory',
            'title' => 'Inventory',
            'description' => 'Stock levels, temperatures, and warehouse overview.',
            'enabled' => false,
        ],
        [
            'key' => 'compliance',
            'title' => 'Compliance',
            'description' => 'Scores, alerts, and regulatory checkpoints.',
            'enabled' => false,
        ],
    ],

];
