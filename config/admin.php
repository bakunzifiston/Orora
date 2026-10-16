<?php

return [
    'navigation' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'central.dashboard', 'icon' => 'grid'],
        ['key' => 'accounts', 'label' => 'Users', 'route' => 'central.accounts.index', 'icon' => 'customer'],
        ['key' => 'farms', 'label' => 'Farms', 'route' => 'central.farms.index', 'icon' => 'farm'],
        ['key' => 'ecommerce', 'label' => 'Ecommerce', 'route' => 'central.ecommerce.index', 'icon' => 'box'],
        ['key' => 'contact', 'label' => 'Contact inbox', 'route' => 'central.contact-messages.index', 'icon' => 'mail'],
    ],

    'super_admin' => [
        'name' => env('SUPER_ADMIN_NAME', 'Super Admin'),
        'email' => env('SUPER_ADMIN_EMAIL'),
        'password' => env('SUPER_ADMIN_PASSWORD'),
    ],
];
