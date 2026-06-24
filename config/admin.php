<?php

return [
    'navigation' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'central.dashboard', 'icon' => 'grid'],
        ['key' => 'users', 'label' => 'Users', 'route' => 'central.users.index', 'icon' => 'employee'],
        ['key' => 'marketplace', 'label' => 'Marketplace', 'route' => 'central.marketplace.index', 'icon' => 'sale'],
        ['key' => 'contact', 'label' => 'Contact inbox', 'route' => 'central.contact-messages.index', 'icon' => 'mail'],
    ],

    'super_admin' => [
        'name' => env('SUPER_ADMIN_NAME', 'Super Admin'),
        'email' => env('SUPER_ADMIN_EMAIL'),
        'password' => env('SUPER_ADMIN_PASSWORD'),
    ],
];
